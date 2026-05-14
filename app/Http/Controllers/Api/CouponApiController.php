<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Coupon::query()
            ->where('status', 1)
            ->whereDate('expiry_date', '>=', now());

        if ($request->filled('id')) {

            $coupon = $baseQuery
                ->where('id', $request->id)
                ->first();

            if (!$coupon) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $this->formatCoupon($coupon)
            ]);
        }

        if ($request->filled('code')) {

            $coupon = $baseQuery
                ->where('code', $request->code)
                ->first();

            if (!$coupon) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $this->formatCoupon($coupon)
            ]);
        }

        $coupons = $baseQuery
            ->where('is_visible', 1)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data' => $coupons->map(fn ($c) => $this->formatCoupon($c))
        ]);
    }

    private function formatCoupon($c)
    {
        return [
            'id' => $c->id,
            'code' => $c->code,
            'discount_type' => $c->discount_type,
            'discount_value' => $c->discount_value,
            'min_amount' => $c->min_amount,
            'max_discount' => $c->max_discount,
            'expiry_date' => $c->expiry_date,

            'is_valid' => now()->lte($c->expiry_date),

            'label' => $this->formatCouponText($c),
        ];
    }

    private function formatCouponText($c)
    {
        if ($c->discount_type === 'percentage') {
            return $c->discount_value . '% OFF up to ₹' . $c->max_discount;
        }

        return 'Flat ₹' . $c->discount_value . ' OFF';
    }
}