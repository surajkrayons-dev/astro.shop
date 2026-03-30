<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletRecharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletApiController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance,
                'total_added' => $wallet->total_added,
                'total_spent' => $wallet->total_spent,
                'total_earned' => $wallet->total_earned,
                'total_withdrawn' => $wallet->total_withdrawn,
                'last_recharge_amount' => $wallet->last_recharge_amount,
                'last_recharge_at' => $wallet->last_recharge_at,
            ]
        ]);
    }

    public function recharge(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:upi,card,netbanking',
            'gateway_txn_id' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        DB::transaction(function () use ($request, $user) {

            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => $user->id]);

            $before = $wallet->balance;
            $amount = $request->amount;

            $wallet->balance += $amount;
            $wallet->total_added += $amount;
            $wallet->last_recharge_amount = $amount;
            $wallet->last_recharge_at = now();
            $wallet->save();

            WalletRecharge::create([
                'wallet_id'      => $wallet->id,
                'amount'         => $amount,
                'balance_before' => $before,
                'balance_after'  => $wallet->balance,
                'payment_method' => $request->payment_method,
                'gateway_txn_id' => $request->gateway_txn_id,
                'recharged_at'   => now(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Wallet recharged successfully'
        ], 201);
    }

    public function rechargeHistory(Request $request)
    {
        $user = auth()->user();

        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $history = WalletRecharge::where('wallet_id', $wallet->id)
            ->orderByDesc('recharged_at')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}
