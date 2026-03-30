<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreReview;
use App\Models\Product;
use Illuminate\Http\Request;
use DB;

class StoreReviewApiController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'review'     => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            // one user → one review per product
            $review = StoreReview::updateOrCreate(
                [
                    'user_id'    => $request->user()->id,
                    'product_id' => $request->product_id,
                ],
                [
                    'rating' => $request->rating,
                    'review' => $request->review,
                ]
            );

            //  product rating update
            Product::updateRating($request->product_id);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Review submitted successfully',
                'data'    => $review
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    public function productReviews($product_id)
    {
        $reviews = StoreReview::with('user:id,name')
            ->where('product_id', $product_id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reviews
        ]);
    }
}
