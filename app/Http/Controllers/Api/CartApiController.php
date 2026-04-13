<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use DB;

class CartApiController extends Controller
{

    public function view(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with('items.product')
            ->where('user_id', $user->id)
            ->first();

        if (!$cart) {
            return response()->json([
                'status' => true,
                'data' => ['items' => [], 'grand_total' => 0]
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $this->formatCart($cart)
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'ratti'      => 'nullable|numeric'
        ]);

        $user = $request->user();

        DB::beginTransaction();

        try {
            $product = Product::with('category')
                ->where('id', $request->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            $price = 0;
            $ratti = null;

            // 🔥 GEMSTONE LOGIC
            if ($product->category && str_starts_with(strtoupper($product->category->code), 'GEM')) {

                if (!$request->ratti) {
                    throw new \Exception('Ratti required');
                }

                $ratti = (float)$request->ratti;

                $options = collect($product->ratti_options ?? []);

                $selected = $options->first(function ($opt) use ($ratti) {
                    return (float)$opt['ratti'] === $ratti;
                });

                if (!$selected) {
                    throw new \Exception('Invalid ratti');
                }

                $price = $selected['ratti_afterPrice'];

            } else {
                $ratti = null;
                $price = $product->after_price ?? 0;
            }

            $item = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('ratti', $ratti)
                ->first();

            $newQty = $request->quantity;

            if ($item) {
                $newQty = $item->quantity + $request->quantity;
            }

            if ($product->stock_qty < $newQty) {
                throw new \Exception('Only ' . $product->stock_qty . ' items available');
            }

            if ($item) {
                $item->quantity += $request->quantity;
            } else {
                $item = new CartItem([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'ratti' => $ratti,
                    'price_at_time' => $price,
                ]);
            }

            $item->total_price = $item->quantity * $price;
            $item->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Added to cart'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateQty(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $request->user();

        $item = CartItem::where('id', $request->item_id)
            ->whereHas('cart', fn($q) => $q->where('user_id', $user->id))
            ->firstOrFail();

        $product = $item->product;
        
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        if ($product && $product->stock_qty < $request->quantity) {
            return response()->json([
                'status' => false,
                'message' => 'Stock not available'
            ], 400);
        }


        $item->quantity = $request->quantity;
        $item->total_price = $item->quantity * $item->price_at_time;
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Updated'
        ]);
    }

    public function remove(Request $request, $id)
    {
        $user = $request->user();

        $item = CartItem::where('id', $id)
            ->whereHas('cart', fn($q) => $q->where('user_id', $user->id))
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'status' => true,
            'message' => 'Removed'
        ]);
    }

    public function clear(Request $request)
    {
        $user = $request->user();

        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared'
        ]);
    }

    private function formatCart(Cart $cart)
    {
        $items = [];
        $total = 0;

        foreach ($cart->items as $item) {

            $items[] = [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'name' => optional($item->product)->name,
                'price' => $item->price_at_time,
                'ratti' => $item->ratti,
                'quantity' => $item->quantity,
                'total' => $item->total_price,
                'image' => optional($item->product)->image
                    ? asset('storage/product/' . $item->product->image)
                    : null
            ];

            $total += $item->total_price;
        }

        return [
            'items' => $items,
            'grand_total' => $total
        ];
    }
}