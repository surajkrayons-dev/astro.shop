<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreWallet;
use App\Models\StoreWalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreWalletApiController extends Controller
{
    public function show()
    {
        $wallet = StoreWallet::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'balance' => 0,
                'total_added' => 0,
                'total_spent' => 0,
                'total_refunded' => 0
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance,
                'total_added' => $wallet->total_added,
                'total_spent' => $wallet->total_spent,
                'total_refunded' => $wallet->total_refunded,
                'last_recharge_amount' => $wallet->last_recharge_amount,
                'last_recharge_at' => $wallet->last_recharge_at,
            ]
        ]);
    }

    public function history(Request $request)
    {
        $query = StoreWalletTransaction::with('order')
            ->where('user_id', Auth::id());

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->source) {
            $query->where('source', $request->source); 
        }

        $transactions = $query->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    public function summary()
    {
        // $wallet = StoreWallet::firstOrCreate(['user_id' => Auth::id()]);
        $wallet = StoreWallet::where('user_id', Auth::id())
            ->lockForUpdate()
            ->firstOrCreate(['user_id' => Auth::id()]);

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance ?? 0,
                'total_added' => $wallet->total_added ?? 0,
                'total_spent' => $wallet->total_spent ?? 0,
                'total_refunded' => $wallet->total_refunded ?? 0,
            ]
        ]);
    }

    public function spendMoney(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        DB::beginTransaction();

        try {
            $wallet = StoreWallet::where('user_id', Auth::id())
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => Auth::id()]);

            if (!$wallet || $wallet->balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            $before = $wallet->balance;
            $after = $before - $request->amount;

            $wallet->update([
                'balance' => $after,
                'total_spent' => $wallet->total_spent + $request->amount
            ]);

            StoreWalletTransaction::create([
                'user_id' => Auth::id(),
                'order_id' => $request->order_id ?? null,   
                'type' => 'debit',
                'amount' => $request->amount,
                'source' => 'order_payment',
                'balance_before' => $before,
                'balance_after' => $after,
                'note' => 'Order payment #' . ($request->order_id ?? '')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'balance' => $after
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}