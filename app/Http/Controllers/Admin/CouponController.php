<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.coupons.index');
    }

    public function getList(Request $request)
    {
        $list = Coupon::query()
            ->when($request->filled('id'),
                fn($q) => $q->where('id', $request->id)
            )
            ->when($request->filled('discount_type'),
                fn($q) => $q->where('discount_type', $request->discount_type)
            )
            ->when($request->status !== null && $request->status !== "",
                fn($q) => $q->where('status', $request->status)
            )
            ->orderByDesc('id');

        return \DataTables::of($list)
            ->editColumn('code', fn($row) =>
                '<strong>'.e($row->code).'</strong>'
            )
            ->editColumn('discount_type', function ($row) {
                return $row->discount_type === 'flat'
                    ? '<span class="badge bg-info">Flat</span>'
                    : '<span class="badge bg-primary">Percentage</span>';
            })
            ->editColumn('discount_value', function ($row) {
                return $row->discount_type === 'percentage'
                    ? $row->discount_value . ' %'
                    : '₹ ' . number_format($row->discount_value, 2);
            })
            ->editColumn('expiry_date', function ($row) {
                return Carbon::parse($row->expiry_date)->format('d M Y');
            })
            ->addColumn('status_label', function ($row) {
                return $row->status
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->rawColumns(['code','discount_type','status_label'])
            ->make(true);
    }

    public function getCreate()
    {
        return view('admin.coupons.create');
    }

    public function postCreate(Request $request)
    {
        $request->validate([
            'code'            => 'required|string|max:50|unique:coupons,code',
            'discount_type'   => 'required|in:flat,percentage',
            'discount_value'  => 'required|numeric|min:0.01',
            'min_amount'      => 'nullable|numeric|min:0',
            'max_discount'    => 'nullable|numeric|min:0',
            'expiry_date'     => 'required|date|after:today',
            'status'          => 'nullable|in:0,1',
        ]);

        if ($request->discount_type === 'percentage') {
            if ($request->discount_value > 100) {
                return response()->json([
                    'message' => 'Percentage discount cannot exceed 100%'
                ], 422);
            }
            if (!$request->max_discount) {
                return response()->json([
                    'message' => 'Maximum discount is required for percentage type'
                ], 422);
            }
        } else {
            $request->merge(['max_discount' => null]);
        }

        Coupon::create([
            'code'           => strtoupper(trim($request->code)),
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_amount'     => $request->min_amount ?? 0,
            'max_discount'   => $request->max_discount,
            'expiry_date'    => $request->expiry_date,
            'status'         => $request->status ?? 1,
        ]);

        return response()->json([
            'message' => 'Coupon created successfully'
        ]);
    }

    public function getUpdate(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);
        return view('admin.coupons.update', compact('coupon'));
    }

    public function postUpdate(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code'           => 'required|string|max:50|unique:coupons,code,'.$coupon->id,
            'discount_type'  => 'required|in:flat,percentage',
            'discount_value' => 'required|numeric|min:0.01',
            'min_amount'     => 'nullable|numeric|min:0',
            'max_discount'   => 'nullable|numeric|min:0',
            'expiry_date'    => 'required|date',
            'status'         => 'nullable|in:0,1',
        ]);

        if ($request->discount_type === 'percentage') {
            if ($request->discount_value > 100) {
                return response()->json([
                    'message' => 'Percentage discount cannot exceed 100%'
                ], 422);
            }
            if (!$request->max_discount) {
                return response()->json([
                    'message' => 'Maximum discount is required for percentage type'
                ], 422);
            }

        } else {
            $request->merge(['max_discount' => null]);
        }

        $coupon->update([
            'code'           => strtoupper(trim($request->code)),
            'discount_type'  => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_amount'     => $request->min_amount ?? 0,
            'max_discount'   => $request->max_discount,
            'expiry_date'    => $request->expiry_date,
            'status'         => $request->status ?? 1,
        ]);

        return response()->json([
            'message' => 'Coupon updated successfully'
        ]);
    }

    public function getDelete(Request $request)
    {
        Coupon::findOrFail($request->id)->delete();

        return response()->json([
            'message' => 'Coupon deleted successfully'
        ]);
    }

    public function getChangeStatus(Request $request)
    {
        $coupon = Coupon::findOrFail($request->id);
        $coupon->update([
            'status' => !$coupon->status
        ]);

        return response()->json([
            'message' => 'Status updated successfully'
        ]);
    }
}
