<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Order;
use App\Models\EmployeeCommission;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class OrderDeliveryController extends AdminController
{
    public function getIndex()
    {
        return view('admin.order_delivery.index');
    }

    public function postSearch(Request $request)
    {
        $request->validate([
            'keyword' => 'required'
        ]);

        $keyword = trim($request->keyword);

        $order = Order::with([
                'user',
                'coupon.employee',
                'items'
            ])
            ->where('order_number', $keyword)
            ->orWhere('id', $keyword)
            ->orWhere('mobile', $keyword)
            ->orWhere('email', $keyword)
            ->first();

        if (!$order) {

            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'order' => $order
        ]);
    }

    public function postDelivered($id)
    {
        DB::beginTransaction();

        try {

            $order = Order::with([
                'coupon.employee'
            ])->findOrFail($id);

            if ($order->status === 'delivered') {

                return response()->json([
                    'status' => false,
                    'message' => 'Order already delivered'
                ], 422);
            }

            $order->update([
                'status' => 'delivered',
                'delivered_at' => now()
            ]);

            EmployeeCommission::where(
                'order_id',
                $order->id
            )->where(
                'status',
                'delivery_pending'
            )->update([
                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order marked as delivered successfully.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
