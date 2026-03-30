<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\user;
use App\Models\ReturnRequest;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreRefundHistory;
use App\Models\UserPaymentAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function getIndex()
    {
        return view('admin.returns.index');
    }

    public function getList(Request $request)
    {
        $returns = ReturnRequest::with([
            'orderItem.product.category',
            'orderItem.order.user',
            'paymentAccount'
        ])

        ->when($request->status, fn($q) =>
            $q->where('status', $request->status)
        )
        ->when($request->user_id, function ($q) use ($request) {
            $q->whereHas('orderItem.order', function ($sub) use ($request) {
                $sub->where('user_id', $request->user_id);
            });
        })
        ->when($request->category_id, function ($q) use ($request) {
            $q->whereHas('orderItem.product', function ($sub) use ($request) {
                $sub->where('category_id', $request->category_id);
            });
        })
        ->when($request->product_id, function ($q) use ($request) {
            $q->whereHas('orderItem', function ($sub) use ($request) {
                $sub->where('product_id', $request->product_id);
            });
        })
        ->latest();

        return datatables()->of($returns)
            ->addColumn('return_id', fn($r) => $r->id)
            ->addColumn('order_no', fn($r) =>
                $r->orderItem->order->order_number ?? '-'
            )
            ->addColumn('user', function ($r) {
                $user = $r->orderItem->order->user ?? null;
                return $user
                    ? '[ <b>'.$user->code.'</b> ]<br>'.$user->name
                    : '-';
            })
            ->addColumn('product', fn($r) =>
                optional($r->orderItem->product)->name ?? 'Deleted'
            )
            ->addColumn('category', fn($r) =>
                optional(optional($r->orderItem->product)->category)->name ?? '-'
            )
            ->addColumn('qty', fn($r) =>
                $r->orderItem->quantity ?? 0
            )
            ->addColumn('amount', fn($r) =>
                '₹ '.number_format($r->orderItem->total ?? 0, 2)
            )
            ->addColumn('status', function ($r) {
                return match ($r->status) {
                    'requested' => '<span class="badge bg-warning">Requested</span>',
                    'approved'  => '<span class="badge bg-info">Approved</span>',
                    'picked'    => '<span class="badge bg-primary">Picked</span>',
                    'refunded'  => '<span class="badge bg-success">Refunded</span>',
                    'rejected'  => '<span class="badge bg-danger">Rejected</span>',
                    default     => $r->status,
                };
            })
            ->addColumn('requested_at', fn($r) =>
                optional($r->created_at)->format('d M Y h:i A')
            )
            ->rawColumns(['user','status'])
            ->make(true);
    }

    public function getView($id)
    {
        $return = ReturnRequest::with([
            'orderItem.product.category',
            'orderItem.order.user',
            'orderItem.order.payment',
            'orderItem.order.coupon',
            'paymentAccount'
        ])->findOrFail($id);

        $order   = $return->orderItem->order;
        $item    = $return->orderItem;
        $product = $item->product;
        $user    = $order->user;

        $refundHistory = \App\Models\StoreRefundHistory::where('order_item_id', $item->id)
            ->latest()
            ->get();

        $userAccounts = \App\Models\UserPaymentAccount::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->get();

        return view('admin.returns.view', compact(
            'return',
            'order',
            'item',
            'product',
            'user',
            'refundHistory',
            'userAccounts'
        ));
    }
}