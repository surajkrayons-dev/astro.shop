<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;

class OrderApiController extends Controller
{
    // ✅ PLACE ORDER
    public function place(Request $request)
    {
        $request->validate([
            'coupon_code' => 'nullable|string',
        ]);

        $user = $request->user();

        $cart = Cart::with('items.product.images')
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Cart is empty'
            ], 422);
        }

        DB::beginTransaction();

        try {

            $subtotal = 0;

            foreach ($cart->items as $item) {

                if (!$item->product) {
                    throw new \Exception('Product not found');
                }

                if ($item->quantity > $item->product->stock_qty) {
                    throw new \Exception("Stock issue: {$item->product->name}");
                }

                $subtotal += $item->total_price;
            }

            // ✅ COUPON
            $discount = 0;
            $coupon = null;

            if ($request->filled('coupon_code')) {

                $coupon = Coupon::whereRaw('LOWER(code) = ?', [strtolower($request->coupon_code)])
                    ->where('status', 1)
                    ->whereDate('expiry_date', '>=', now())
                    ->first();

                if (!$coupon) {
                    throw new \Exception('Invalid coupon');
                }

                if ($coupon->min_amount && $subtotal < $coupon->min_amount) {
                    throw new \Exception('Minimum amount not reached');
                }

                $discount = $coupon->discount_type === 'percentage'
                    ? ($subtotal * $coupon->discount_value) / 100
                    : $coupon->discount_value;

                if ($coupon->max_discount) {
                    $discount = min($discount, $coupon->max_discount);
                }
            }

            $totalAmount = max(0, $subtotal - $discount);

            // ✅ CREATE ORDER
            $order = Order::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon?->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // ✅ ORDER ITEMS
            foreach ($cart->items as $item) {

                $product = $item->product;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'product_image' => $product->image,
                    'ratti' => $item->ratti,
                    'quantity' => $item->quantity,
                    'price' => $item->price_at_time,
                    'total' => $item->total_price,
                ]);

                // stock update
                $product->stock_qty -= $item->quantity;
                $product->stock_status = Product::resolveStockStatus($product->stock_qty);
                $product->save();
            }

            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => $this->formatOrder($order->id)
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // ✅ ORDER LIST
    public function index(Request $request)
    {
        $orders = Order::with(['items.product.images', 'coupon'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $orders->map(fn($o) => $this->formatOrderData($o))
        ]);
    }

    // ✅ ORDER DETAIL
    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product.images', 'coupon'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $this->formatOrderData($order)
        ]);
    }

    // ✅ DELIVERED
    public function markDelivered($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status === 'delivered') {
            return response()->json([
                'status' => false,
                'message' => 'Already delivered'
            ], 422);
        }

        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Order delivered',
            'data' => $this->formatOrder($order->id)
        ]);
    }

    // ✅ FORMAT ORDER
    private function formatOrder($id)
    {
        $order = Order::with(['items.product.images', 'coupon'])->find($id);
        return $this->formatOrderData($order);
    }

    private function formatOrderData($order)
    {
        return [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'total_amount' => $order->total_amount,

            'items' => $order->items->map(function ($item) {

                $product = $item->product;

                return [
                    'product_id' => $item->product_id,

                    // 🔥 SNAPSHOT
                    'name' => $item->product_name,
                    'slug' => $item->product_slug,
                    'image' => $item->product_image
                        ? asset('storage/product/' . $item->product_image)
                        : null,

                    // 🔥 LIVE DATA
                    'product' => $product ? [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'description' => $product->description,
                        'stock' => $product->stock_qty,
                        'status' => $product->stock_status,

                        'images' => $product->images->map(fn($img) =>
                            asset('storage/product/' . $img->images)
                        ),
                    ] : null,

                    'ratti' => $item->ratti,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ];
            }),

            'coupon' => $order->coupon ? [
                'code' => $order->coupon->code,
                'type' => $order->coupon->discount_type,
                'value' => $order->coupon->discount_value,
            ] : null,

            'created_at' => $order->created_at,
        ];
    }
}
