<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use DB;

class PurchaseApiController extends Controller
{
    // public function initiate(Request $request)
    // {
    //     $request->validate([
    //         'order_id' => 'required|exists:orders,id',
    //         'method'   => 'required|in:cod,upi,netbanking,card',
    //     ]);

    //     $order = Order::where('id', $request->order_id)
    //         ->where('user_id', $request->user()->id)
    //         ->firstOrFail();

    //     DB::beginTransaction();

    //     try {

    //         $payment = Payment::create([
    //             'order_id' => $order->id,
    //             'method'   => $request->method,
    //             'amount'   => $order->total_amount,
    //             'status'   => $request->method === 'cod' ? 'pending' : 'pending',
    //         ]);

    //         // COD → Order stays pending
    //         if ($request->method === 'cod') {
    //             DB::commit();

    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'Order placed with Cash on Delivery',
    //                 'data' => [
    //                     'order_id' => $order->id,
    //                     'payment_status' => 'pending'
    //                 ]
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Payment initiated',
    //             'data' => [
    //                 'payment_id' => $payment->id,
    //                 'amount' => $payment->amount,
    //                 'method' => $payment->method
    //             ]
    //         ]);

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         \Log::error($e);

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Payment initiation failed'
    //         ], 500);
    //     }
    // }

    // public function verify(Request $request)
    // {
    //     $request->validate([
    //         'payment_id'     => 'required|exists:payments,id',
    //         'transaction_id'=> 'required|string',
    //         'status'         => 'required|in:success,failed',
    //     ]);

    //     DB::beginTransaction();

    //     try {

    //         $payment = Payment::with('order')->findOrFail($request->payment_id);

    //         $payment->update([
    //             'transaction_id' => $request->transaction_id,
    //             'status' => $request->status,
    //         ]);

    //         if ($request->status === 'success') {
    //             $payment->order->update([
    //                 'status' => 'paid'
    //             ]);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Payment status updated'
    //         ]);

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         \Log::error($e);

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Payment verification failed'
    //         ], 500);
    //     }
    // }

    public function initiate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'method'   => 'required|in:cod,upi,netbanking,card',
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        DB::beginTransaction();

        try {

            $purchase = StorePurchase::create([
                'order_id' => $order->id,
                'method'   => $request->method,
                'amount'   => $order->total_amount,
                'status'   => 'pending',
            ]);

            // COD case
            if ($request->method === 'cod') {

                $order->update([
                    'status' => 'pending'
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Order placed with Cash on Delivery',
                    'data' => [
                        'order_id' => $order->id,
                        'payment_id' => $purchase->id,
                        'payment_status' => 'pending'
                    ]
                ]);
            }

            // Online payment case (Razorpay etc.)
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment initiated',
                'data' => [
                    'payment_id' => $purchase->id,
                    'amount' => $purchase->amount,
                    'method' => $purchase->method
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'status' => false,
                'message' => 'Payment initiation failed'
            ], 500);
        }
    }

    public function verify(Request $request)
    {
        $request->validate([
            'payment_id'     => 'required|exists:store_purchases,id',
            'transaction_id' => 'required|string',
            'status'         => 'required|in:success,failed',
        ]);

        DB::beginTransaction();

        try {

            $purchase = StorePurchase::with('order')->findOrFail($request->payment_id);

            $purchase->update([
                'transaction_id' => $request->transaction_id,
                'status' => $request->status,
            ]);

            if ($request->status === 'success') {
                $purchase->order->update([
                    'status' => 'paid'
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment status updated'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'status' => false,
                'message' => 'Payment verification failed'
            ], 500);
        }
    }
}
