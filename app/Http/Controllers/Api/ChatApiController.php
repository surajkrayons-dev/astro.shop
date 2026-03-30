<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatApiController extends Controller
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
                'message' => 'Only users can start chats.'
            ], 403);
        }

        $astrologer = User::where('id', $request->astrologer_id)
            ->where('type', 'astro')
            ->firstOrFail();

        if ($astrologer->chat_price <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Chat price not configured.'
            ], 400);
        }

        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        if ($wallet->balance <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient balance.'
            ], 400);
        }

        $allowedSeconds = floor(($wallet->balance / $astrologer->chat_price) * 60);

        if ($allowedSeconds < 5) {
            return response()->json([
                'status' => false,
                'message' => 'Minimum balance required.'
            ], 400);
        }

        $chat = ChatSession::create([
            'astrologer_id' => $astrologer->id,
            'user_id'       => $user->id,
            'started_at'    => now(),
            'duration'      => 0,
            'amount'        => 0,
            'status'        => 'active'
        ]);

        return response()->json([
            'status' => true,
            'chat_id' => $chat->id,
            'allowed_seconds' => $allowedSeconds,
            'price_per_minute' => $astrologer->chat_price,
            'current_balance' => $wallet->balance
        ]);
    }

    public function pulse(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chat_sessions,id'
        ]);

        DB::beginTransaction();

        try {

            $chat = ChatSession::lockForUpdate()->find($request->chat_id);

            if (!$chat) {
                return response()->json([
                    'status' => false,
                    'message' => 'Chat not found.'
                ], 404);
            }

            if ($chat->status !== 'active') {
                return response()->json([
                    'status' => false,
                    'message' => 'Chat already ended.'
                ], 400);
            }

            $astrologer = User::findOrFail($chat->astrologer_id);

            $userWallet = Wallet::where('user_id', $chat->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $astroWallet = Wallet::where('user_id', $chat->astrologer_id)
                ->lockForUpdate()
                ->firstOrFail();

            $perSecond = round($astrologer->chat_price / 60, 4);
            $deductSeconds = 5;

            $charge = round($perSecond * $deductSeconds, 2);

            if ($userWallet->balance <= 0) {

                $chat->status = 'completed';
                $chat->ended_at = now();
                $chat->save();

                DB::commit();

                return response()->json([
                    'status' => false,
                    'auto_ended' => true,
                    'message' => 'Balance exhausted.'
                ]);
            }

            if ($charge > $userWallet->balance) {
                $charge = $userWallet->balance;
                $deductSeconds = floor(($charge / $astrologer->chat_price) * 60);
            }

            $beforeUser = $userWallet->balance;

            $userWallet->balance = round($userWallet->balance - $charge, 2);
            $userWallet->total_spent += $charge;
            $userWallet->save();

            WalletTransaction::create([
                'wallet_id' => $userWallet->id,
                'type' => 'chat_debit',
                'direction' => 'debit',
                'amount' => $charge,
                'balance_before' => $beforeUser,
                'balance_after' => $userWallet->balance,
                'reference_id' => $chat->id
            ]);

            $beforeAstro = $astroWallet->balance;

            $astroWallet->balance = round($astroWallet->balance + $charge, 2);
            $astroWallet->total_earned += $charge;
            $astroWallet->save();

            WalletTransaction::create([
                'wallet_id' => $astroWallet->id,
                'type' => 'chat_credit',
                'direction' => 'credit',
                'amount' => $charge,
                'balance_before' => $beforeAstro,
                'balance_after' => $astroWallet->balance,
                'reference_id' => $chat->id
            ]);

            $chat->duration += $deductSeconds;
            $chat->amount += $charge;

            if ($userWallet->balance <= 0) {
                $chat->status = 'completed';
                $chat->ended_at = now();
            }

            $chat->save();

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
            'chat_id' => 'required|exists:chat_sessions,id'
        ]);

        $chat = ChatSession::find($request->chat_id);

        if (!$chat) {
            return response()->json([
                'status' => false,
                'message' => 'Chat not found.'
            ], 404);
        }

        if ($chat->status !== 'active') {
            return response()->json([
                'status' => false,
                'message' => 'Chat already ended.'
            ], 400);
        }

        $chat->status = 'completed';
        $chat->ended_at = now();
        $chat->save();

        return response()->json([
            'status' => true,
            'message' => 'Chat ended successfully.'
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = ChatSession::query();

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