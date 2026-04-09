<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class EasyGoApiController extends Controller
{
    public function initiateCall(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'astrologer_id' => 'required|exists:users,id'
            ]);

            $user = auth()->user();
            if (!$user) {
                throw new \Exception("User not authenticated");
            }

            // 🔮 ASTRO
            $astro = DB::table('users')
                ->where('id', $request->astrologer_id)
                ->where('type', 'astro')
                ->lockForUpdate()
                ->first();

            if (!$astro || !$astro->is_online || $astro->is_busy) {
                throw new \Exception("Astrologer not available");
            }

            // 💰 WALLET LOCK
            $wallet = DB::table('wallets')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            $ratePerMinute = $astro->call_price ?? 10;

            if (!$wallet || $wallet->balance < $ratePerMinute) {
                throw new \Exception("Low balance");
            }

            // 🔒 LOCK AMOUNT
            DB::table('wallets')->where('user_id', $user->id)->update([
                'balance' => DB::raw("balance - $ratePerMinute"),
                'locked_balance' => DB::raw("locked_balance + $ratePerMinute")
            ]);

            // 📞 FORMAT NUMBERS
            $customerNumber = $this->formatNumber($user->mobile);
            $agentNumber    = $this->formatNumber($astro->mobile);

            // 🔑 TOKEN
            $token = $this->getToken();

            // 📞 CALL API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'API_TOKEN'    => $token
            ])->post('https://client.easygoivr.com/easygoapiJwt/request/dial', [
                "number" => $customerNumber,
                "exten"  => $agentNumber,
                "did"    => env('SMARTFLO_CALLER_ID')
            ]);

            $result = $response->json();

            if (!$response->ok() || ($result['status'] ?? '') !== 'Success') {
                throw new \Exception("Dial failed");
            }

            // 📊 SESSION CREATE
            $sessionId = DB::table('call_sessions')->insertGetId([
                'user_id'        => $user->id,
                'astrologer_id'  => $astro->id,
                'user_number'    => $customerNumber,
                'astro_number'   => $agentNumber,
                'lock_amount'    => $ratePerMinute,
                'status'         => 'initiated',
                'created_at'     => now(),
                'updated_at'     => now()
            ]);

            // 🔒 ASTRO BUSY
            DB::table('users')->where('id', $astro->id)->update([
                'is_busy' => 1
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Calling...',
                'session_id' => $sessionId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function callWebhook(Request $request)
    {
        \Log::info('📞 WEBHOOK HIT', $request->all());

        DB::beginTransaction();

        try {

            $userNumber = $this->formatNumber(
                $request->input('Caller') ?? $request->input('caller_id')
            );

            $astroNumber = $this->formatNumber(
                $request->input('Agent') ?? $request->input('extension_no')
            );

            $status = strtolower(
                $request->input('CallStatus') ?? $request->input('action') ?? ''
            );

            // 🔍 SESSION FIND
            $session = DB::table('call_sessions')
                ->where(function ($q) use ($userNumber) {
                    $q->where('user_number', $userNumber)
                      ->orWhere('astro_number', $userNumber);
                })
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->latest()
                ->first();

            if (!$session) {
                throw new \Exception("Session not found");
            }

            // 💰 WALLET LOCK
            $userWallet = DB::table('wallets')
                ->where('user_id', $session->user_id)
                ->lockForUpdate()
                ->first();

            $astroWallet = DB::table('wallets')
                ->where('user_id', $session->astrologer_id)
                ->lockForUpdate()
                ->first();

            $astro = DB::table('users')->where('id', $session->astrologer_id)->first();

            // 🟢 ANSWERED
            if (in_array($status, ['answer', 'answered'])) {
                DB::table('call_sessions')->where('id', $session->id)->update([
                    'status' => 'active',
                    'started_at' => now()
                ]);
            }

            // 🔴 MISSED
            if (in_array($status, ['no-answer', 'busy', 'failed'])) {

                $lock = $session->lock_amount ?? 10;

                DB::table('wallets')->where('user_id', $session->user_id)->update([
                    'balance' => DB::raw("balance + $lock"),
                    'locked_balance' => DB::raw("locked_balance - $lock")
                ]);

                DB::table('call_sessions')->where('id', $session->id)->update([
                    'status' => 'missed',
                    'ended_at' => now()
                ]);

                DB::commit();
                return response()->json(['status' => true]);
            }

            // 🟢 COMPLETED
            if (in_array($status, ['hangup', 'completed'])) {

                if ($session->is_deducted ?? false) {
                    DB::commit();
                    return response()->json(['status' => true]);
                }

                $duration = $request->input('duration')
                    ?? $request->input('billsec')
                    ?? 0;

                $ratePerSec = ($astro->call_price ?? 10) / 60;
                $billable = ceil($duration / 60) * 60;
                $amount = $billable * $ratePerSec;

                $lock = $session->lock_amount ?? 10;

                // 💰 USER
                $refund = max(0, $lock - $amount);

                DB::table('wallets')->where('user_id', $session->user_id)->update([
                    'locked_balance' => DB::raw("locked_balance - $amount"),
                    'balance' => DB::raw("balance + $refund"),
                    'total_spent' => DB::raw("total_spent + $amount")
                ]);

                // 💸 ASTRO
                // $earning = $amount * 0.7;
                $earning = $amount;

                DB::table('wallets')->where('user_id', $session->astrologer_id)->update([
                    'balance' => DB::raw("balance + $earning"),
                    'total_earned' => DB::raw("total_earned + $earning")
                ]);

                DB::table('call_sessions')->where('id', $session->id)->update([
                    'ended_at' => now(),
                    'duration' => $duration,
                    'amount' => $amount,
                    'status' => 'completed',
                    'is_deducted' => 1
                ]);

                DB::table('users')->where('id', $session->astrologer_id)->update([
                    'is_busy' => 0
                ]);
            }

            DB::commit();

            return response()->json(['status' => true]);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('WEBHOOK ERROR', [
                'msg' => $e->getMessage()
            ]);

            return response()->json(['status' => false]);
        }
    }

    private function getToken()
    {
        $res = Http::withBasicAuth(
            env('SMARTFLO_USERNAME'),
            env('SMARTFLO_PASSWORD')
        )->post('https://client.easygoivr.com/masterapiJwt/gentoken');

        $data = $res->json();

        if (!$res->ok() || !isset($data['API_TOKEN'])) {
            throw new \Exception("Token failed");
        }

        return $data['API_TOKEN'];
    }

    private function formatNumber($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (substr($phone, 0, 2) === '91') {
            $phone = substr($phone, 2);
        }

        if (substr($phone, 0, 1) !== '0') {
            $phone = '0' . $phone;
        }

        return $phone;
    }
}