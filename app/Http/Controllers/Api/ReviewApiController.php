<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;

class ReviewApiController extends Controller
{
    public function index()
    {
        $reviews = Review::with(['astrologer:id,name', 'user:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Review list fetched successfully',
            'data' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'astrologer_id' => $review->astrologer_id,
                    'astrologer_name' => $review->astrologer->name ?? null,
                    'user_id' => $review->user_id,
                    'user_name' => $review->user->name ?? null,
                    'rating' => $review->rating,
                    'review' => $review->review,
                    'created_at' => $review->created_at->format('d M Y h:i A'),
                    'updated_at' => $review->updated_at->format('d M Y h:i A'),
                ];
            })
        ]);
    }
}