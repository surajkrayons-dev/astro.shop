<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\UserPaymentAccount;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutApiController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $history = PayoutRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $user = auth()->user();

        DB::transaction(function () use ($request, $user) {

            $wallet = Wallet::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($wallet->balance < $request->amount) {
                abort(422, 'Insufficient wallet balance');
            }
            
            $paymentAccount = UserPaymentAccount::where('user_id', $user->id)->first();

            if (!$paymentAccount) {
                abort(422, 'Please add payment account first');
            }

            PayoutRequest::create([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'payment_account_id' => $paymentAccount->id,
                'amount' => $request->amount,
                'status' => 'pending',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Payout request submitted successfully'
        ], 201);
    }
}
