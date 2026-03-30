<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DB;

class ProductStockController extends AdminController
{
    public function getIndex(Request $request)
    {
        return view('admin.product_stocks.index');
    }

    public function getList(Request $request)
    {
        $products = Product::with('category')
            ->when($request->category_id, fn($q) =>
                $q->where('category_id', $request->category_id)
            )
            ->when($request->product_id, fn($q) =>
                $q->where('id', $request->product_id)
            )
            ->when($request->stock_status, fn($q) =>
                $q->where('stock_status', $request->stock_status)
            )
            ->latest();

        return datatables()->of($products)
            ->addColumn('category', function($p) {
                if (!$p->category) return '-';

                return '[ <b>'.e($p->category->code).'</b> ]<br>'
                    .e($p->category->name);
            })
            ->addColumn('product', function($p) {
                $fullName = $p->name;
                $shortName = \Illuminate\Support\Str::limit($fullName, 28, '...');

                return '
                    [ <b>'.e($p->code).'</b> ]<br>
                    <span title="'.e($fullName).'">'.e($shortName).'</span>
                ';
            })
            ->addColumn('stock_qty', fn($p) => $p->stock_qty)
            ->addColumn('status', function($p) {
                return match($p->stock_status) {
                    'in_stock' => '<span class="badge bg-success">In Stock</span>',
                    'few_left' => '<span class="badge bg-warning">Few Left</span>',
                    'out_of_stock' => '<span class="badge bg-danger">Out Of Stock</span>',
                    default => $p->stock_status,
                };

            })
            ->rawColumns(['category','product','status'])
            ->make(true);
    }

    public function getView($id)
    {
        $product = Product::with(['category','images'])
            ->findOrFail($id);

        return view('admin.product_stocks.view', compact('product'));
    }

    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'stock_qty' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($id);

        $product->stock_qty += $request->stock_qty;

        $product->stock_status = Product::resolveStockStatus($product->stock_qty);

        $product->save();

        return response()->json([
            'stock_qty'    => $product->stock_qty,
            'stock_status' => $product->stock_status
        ]);
    }

}
