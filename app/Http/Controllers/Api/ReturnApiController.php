<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreRefundHistory;
use App\Models\UserPaymentAccount;
use Illuminate\Http\Request;
use DB;

class ReturnApiController extends Controller
{
    public function request(Request $request)
    {
        $request->validate([
            'order_item_id'      => 'required|exists:order_items,id',
            'reason'             => 'required|string',
            'payment_account_id' => 'required|integer'
        ]);

        $item = OrderItem::with('order')
            ->whereHas('order', fn ($q) =>
                $q->where('user_id', $request->user()->id)
            )
            ->findOrFail($request->order_item_id);

        if (
            !$item->order->delivered_at ||
            now()->diffInDays($item->order->delivered_at) > 7
        ) {
            return response()->json([
                'status' => false,
                'message' => 'Return allowed only within 7 days of delivery'
            ], 422);
        }

        $account = UserPaymentAccount::where('id', $request->payment_account_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$account) {
            return response()->json([
                'status'  => false,
                'message' => 'Please add/select a valid refund account before requesting return'
            ], 422);
        }

        $return = ReturnRequest::create([
            'order_item_id'      => $item->id,
            'reason'             => $request->reason,
            'payment_account_id' => $account->id,
            'status'             => 'requested'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Return request submitted',
            'data' => [
                'return' => $return,
                'refund_account' => $account
            ]
        ]);
    }

    public function myReturns(Request $request)
    {
        $returns = ReturnRequest::with([
                'orderItem.product',
                'orderItem.order'
            ])
            ->whereHas('orderItem.order', fn ($q) =>
                $q->where('user_id', $request->user()->id)
            )
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $returns
        ]);
    }

    public function picked($id)
    {
        DB::beginTransaction();

        try {

            $return = ReturnRequest::with([
                'orderItem.product',
                'orderItem.order'
            ])->findOrFail($id);

            if ($return->status !== 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'Return not approved'
                ], 422);
            }

            $item    = $return->orderItem;
            $order   = $item->order;
            $product = $item->product;

            $return->update([
                'status' => 'picked'
            ]);

            $product->increment('stock_qty', $item->quantity);
            $product->update([
                'stock_status' => Product::resolveStockStatus($product->stock_qty)
            ]);

            $account = UserPaymentAccount::where('user_id', $order->user_id)
                ->where('is_default', 1)
                ->first();

            StoreRefundHistory::create([
                'user_id'        => $order->user_id,
                'order_id'       => $order->id,
                'order_item_id'  => $item->id,
                'product_id'     => $item->product_id,
                'quantity'       => $item->quantity,
                'amount'         => $item->total,
                'picked_at'      => now(),
                'refunded_at'    => now(),
                'refund_method'  => $return->paymentAccount->type, // upi / bank
                'refund_reference' => null,
            ]);

            // 5️⃣ FINAL STATUS
            $return->update([
                'status' => 'refunded'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Item picked & refund recorded successfully'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
