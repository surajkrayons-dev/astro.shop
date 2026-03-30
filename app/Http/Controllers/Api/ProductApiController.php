<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'images'
        ])->where('status', 1);

        $userWishlistIds = auth()->check()
            ? auth()->user()->wishlistProducts()->pluck('product_id')->toArray()
            : [];

        // SINGLE PRODUCT
        if ($request->filled('product_id')) {

            $product = $query->find($request->product_id);

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data'   => $this->fullProduct($product, $userWishlistIds)
            ]);
        }

        // LIST
        $products = $query->latest()->get();

        return response()->json([
            'status' => true,
            'data'   => $products->map(fn($p) => $this->fullProduct($p, $userWishlistIds))
        ]);
    }

    private function fullProduct(Product $product, $userWishlistIds = [])
    {
        return [

            'id' => $product->id,
            'category_id' => $product->category_id,
            'code' => $product->code,
            'name' => $product->name,
            'slug' => $product->slug,
            'stone_name' => $product->stone_name,
            'is_wishlist' => in_array($product->id, $userWishlistIds),
            'ratti_options' => $product->ratti_options,
            'specifications' => $product->specifications,
            'faq' => $product->faq,
            'lab_certificates' => collect($product->lab_certificates ?? [])->map(function ($c) {
                return [
                    'image' => !empty($c['image'])
                        ? asset('storage/lab_certificates/' . $c['image'])
                        : null,
                    'number' => $c['number'] ?? null
                ];
            }),
            'description' => $product->description,
            'benefits' => $product->benefits,
            'how_to_use' => $product->how_to_use,
            'purity' => $product->purity,
            'shipping_info' => $product->shipping_info,
            'origin' => $product->origin,
            'planet' => $product->planet,
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
            'meta_keywords' => $product->meta_keywords,
            'before_price' => $product->before_price,
            'after_price' => $product->after_price,
            'rating_avg' => $product->rating_avg,
            'rating_count' => $product->rating_count,
            'stock_qty' => $product->stock_qty,
            'stock_status' => $product->stock_status,
            'status' => $product->status,
            'image' => $product->image
                ? asset('storage/product/' . $product->image)
                : null,
            'images' => $product->images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'product_id' => $img->product_id,
                    'image' => asset('storage/product/' . $img->images),
                    'file_name' => $img->images
                ];
            }),
            'category' => $product->category,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at,
            'deleted_at' => $product->deleted_at,
        ];
    }

    public function wishlist()
    {
        $user = auth()->user();

        $products = $user->wishlistProducts()
            ->with(['category', 'images'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $products->map(fn($p) => $this->fullProduct($p, [$p->id]))
        ]);
    }

    public function addToWishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = auth()->user();

        if ($user->wishlistProducts()->where('product_id', $request->product_id)->exists()) {

            return response()->json([
                'status' => false,
                'message' => 'Product already in wishlist'
            ]);
        }

        $user->wishlistProducts()->attach($request->product_id);

        return response()->json([
            'status' => true,
            'message' => 'Added to wishlist'
        ]);
    }

    public function removeFromWishlist(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = auth()->user();

        if (!$user->wishlistProducts()->where('product_id', $request->product_id)->exists()) {

            return response()->json([
                'status' => false,
                'message' => 'Product not in wishlist'
            ]);
        }

        $user->wishlistProducts()->detach($request->product_id);

        return response()->json([
            'status' => true,
            'message' => 'Removed from wishlist'
        ]);
    }
}
