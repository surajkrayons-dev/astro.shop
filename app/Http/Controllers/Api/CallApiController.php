<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CallApiController extends Controller
{
    public function start(Request $request)
    {
        $request->validate([
            'astrologer_id' => 'required|exists:users,id',
        ]);

        $user = auth()->user();

        if ($user->type !== 'user') {
            return response()->json([
                'status' => false,
                'message' => 'Only users can start calls.'
            ], 403);
        }

        $astrologer = User::where('id', $request->astrologer_id)
            ->where('type', 'astro')
            ->firstOrFail();

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->balance <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance.'
            ], 400);
        }

        $allowedSeconds = floor(($wallet->balance / $astrologer->call_price) * 60);

        if ($allowedSeconds < 5) {
            return response()->json([
                'status' => false,
                'message' => 'Minimum balance required.'
            ], 400);
        }

        $call = CallSession::create([
            'astrologer_id' => $astrologer->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'duration' => 0,
            'amount' => 0,
            'status' => 'active'
        ]);

        return response()->json([
            'status' => true,
            'call_id' => $call->id,
            'allowed_seconds' => $allowedSeconds,
            'price_per_minute' => $astrologer->call_price,
            'current_balance' => $wallet->balance
        ]);
    }

    public function pulse(Request $request)
    {
        $request->validate([
            'call_id' => 'required|exists:call_sessions,id'
        ]);

        DB::beginTransaction();

        try {

            $call = CallSession::lockForUpdate()->find($request->call_id);

            if (!$call) {
                return response()->json([
                    'status' => false,
                    'message' => 'Call not found.'
                ], 404);
            }

            if ($call->status !== 'active') {
                return response()->json([
                    'status' => false,
                    'message' => 'Call already ended.'
                ], 400);
            }

            $astrologer = User::findOrFail($call->astrologer_id);

            $userWallet = Wallet::where('user_id', $call->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $astroWallet = Wallet::where('user_id', $call->astrologer_id)
                ->lockForUpdate()
                ->firstOrFail();

            $perSecond = round($astrologer->call_price / 60, 4);
            $deductSeconds = 5;
            $charge = round($perSecond * $deductSeconds, 2);

            if ($userWallet->balance <= 0) {

                $call->status = 'completed';
                $call->ended_at = now();
                $call->save();

                DB::commit();

                return response()->json([
                    'status' => false,
                    'auto_ended' => true,
                    'message' => 'Balance exhausted.'
                ]);
            }

            if ($charge > $userWallet->balance) {
                $charge = $userWallet->balance;
            }

            $beforeUser = $userWallet->balance;

            $userWallet->balance = round($userWallet->balance - $charge, 2);
            $userWallet->total_spent += $charge;
            $userWallet->save();

            WalletTransaction::create([
                'wallet_id' => $userWallet->id,
                'type' => 'call_debit',
                'direction' => 'debit',
                'amount' => $charge,
                'balance_before' => $beforeUser,
                'balance_after' => $userWallet->balance,
                'reference_id' => $call->id
            ]);

            $beforeAstro = $astroWallet->balance;

            $astroWallet->balance = round($astroWallet->balance + $charge, 2);
            $astroWallet->total_earned += $charge;
            $astroWallet->save();

            WalletTransaction::create([
                'wallet_id' => $astroWallet->id,
                'type' => 'call_credit',
                'direction' => 'credit',
                'amount' => $charge,
                'balance_before' => $beforeAstro,
                'balance_after' => $astroWallet->balance,
                'reference_id' => $call->id
            ]);

            $call->duration += $deductSeconds;
            $call->amount += $charge;
            $call->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'charged' => $charge,
                'remaining_balance' => $userWallet->balance
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function end(Request $request)
    {
        $request->validate([
            'call_id' => 'required|exists:call_sessions,id'
        ]);

        $call = CallSession::where('id', $request->call_id)
            ->where('status', 'active')
            ->firstOrFail();

        $call->status = 'completed';
        $call->ended_at = now();
        $call->save();

        return response()->json([
            'status' => true,
            'message' => 'Call ended successfully.'
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = CallSession::query();

        if ($user->type === 'user') {
            $query->where('user_id', $user->id);
        } else {
            $query->where('astrologer_id', $user->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date) {
            $query->whereDate('started_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('started_at', '<=', $request->to_date);
        }

        return response()->json([
            'status' => true,
            'data' => $query->orderByDesc('started_at')->paginate(20)
        ]);
    }
}
