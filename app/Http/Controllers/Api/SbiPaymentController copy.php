<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class SbiPaymentController extends Controller
{
    // 🔥 Initiate (frontend form use karega)
    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        return response()->json([
            'status' => true,
            'txn_id' => 'SBI_' . uniqid(),
            'amount' => $request->amount
        ]);
    }

    // 🔥 Verify (bank return pe hit hoga)
    public function verify(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required',
            'amount' => 'required|numeric|min:1',
            'status' => 'required'
        ]);

        DB::beginTransaction();

        try {

            Payment::create([
                'user_id' => $request->user()->id,
                'platform' => 'astrotring_store',
                'payment_gateway' => 'sbi',
                'transaction_id' => $request->transaction_id,
                'amount' => $request->amount,
                'payment_status' => $request->status,
                'payment_response_data' => $request->all(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'SBI Payment saved'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false]);
        }
    }
}