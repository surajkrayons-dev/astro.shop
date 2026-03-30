<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreWallet;
use Illuminate\Support\Facades\Auth;

class StoreWalletApiController extends Controller
{
    public function show()
    {
        $wallet = StoreWallet::firstOrCreate(
            ['user_id' => Auth::id()],
            ['balance' => 0, 'total_added' => 0, 'total_spent' => 0]
        );

        return response()->json([
            'success' => true,
            'wallet' => $wallet
        ]);
    }

    public function addMoney(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $wallet = StoreWallet::firstOrCreate(['user_id' => Auth::id()]);
        $wallet->balance += $request->amount;
        $wallet->total_added += $request->amount;
        $wallet->last_recharge_amount = $request->amount;
        $wallet->last_recharge_at = now();
        $wallet->save();

        return response()->json(['success' => true, 'wallet' => $wallet]);
    }

    public function spendMoney(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $wallet = StoreWallet::where('user_id', Auth::id())->first();

        if (!$wallet || $wallet->balance < $request->amount) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
        }

        $wallet->balance -= $request->amount;
        $wallet->total_spent += $request->amount;
        $wallet->save();

        return response()->json(['success' => true, 'wallet' => $wallet]);
    }
}