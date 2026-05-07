<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\user;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function getIndex()
    {
        return view('admin.orders.index');
    }

    public function getList(Request $request)
    {
        $orders = Order::with(['user', 'items.product.category'])
            ->when($request->user_id, function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            })
            ->when($request->category_id, function ($q) use ($request) {
                $q->whereHas('items.product', function ($p) use ($request) {
                    $p->where('category_id', $request->category_id);
                });
            })
            ->when($request->product_id, function ($q) use ($request) {
                $q->whereHas('items', function ($i) use ($request) {
                    $i->where('product_id', $request->product_id);
                });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->latest();

        return datatables()->of($orders)
            ->addColumn('order_no', fn ($o) => $o->order_number)

            ->addColumn('user', function ($o) {
                return '[ <b>'.$o->user->code.'</b> ]<br>'.$o->user->name;
            })
            ->addColumn('category', function ($o) {
                return $o->items
                    ->pluck('product.category.name')
                    ->unique()
                    ->implode(', ');
            })
            ->addColumn('products', function ($order) {
                return $order->items->map(function ($item) {
                    $name = \Illuminate\Support\Str::limit($item->product_name, 20);
                    return '<div class="mb-1">'.$name.'</div>';
                })->implode('');
            })
            ->addColumn('items_count', function ($order) {
                return '<span class="fw-bold">'.$order->items->count().'</span>';
            })
            ->addColumn('amount', fn ($o) => '₹ '.number_format($o->total_amount, 2))
            ->addColumn('status', function ($o) {
                return match ($o->status) {
                    'pending'   => '<span class="badge bg-warning">Pending</span>',
                    'paid'      => '<span class="badge bg-info">Paid</span>',
                    'packed'    => '<span class="badge bg-primary">Packed</span>',
                    'shipped'   => '<span class="badge bg-dark">Shipped</span>',
                    'delivered' => '<span class="badge bg-success">Delivered</span>',
                    'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
                    default     => $o->status
                };
            })
            ->addColumn('created_at', fn ($o) =>
                $o->created_at->format('d M Y h:i A')
            )
            ->rawColumns(['user', 'products', 'status'])
            ->make(true);
    }


    public function getView(Request $request, $id)
    {
        $order = Order::with([
            'user',
            'payment',
            'coupon',
            'addressData',
            'items',
            'items.product' => function ($q) {
                $q->withTrashed();
            },
            'items.product.images',
            'items.product.category',
            'items.product.storeReviews' => function ($q) {
                $q->select('id','product_id','user_id','rating','review');
            }
        ])->findOrFail($id);

        return view('admin.orders.view', compact('order'));
    }
}