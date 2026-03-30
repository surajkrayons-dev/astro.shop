<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ProductExport implements FromCollection, WithHeadings, WithMapping
{
    protected $productIds;

    public function __construct($productIds = null)
    {
        $this->productIds = $productIds;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $query = Product::with(['category', 'brand', 'creator', 'modifier', 'client'])
            ->select('client_id', 'code', 'name', 'product_category_id', 'product_brand_id', 'price', 'description', 'order', 'status', 'created_by', 'modified_by', 'created_at', 'updated_at');

        if ($this->productIds) {
            $query->whereIn('id', $this->productIds);
        }

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('products.client_id', $clientIds);
        }

        return $query->get()->map(function($product) {
            $product->category_code = $product->category->code ?? 'N/A';
            $product->category_name = $product->category->name ?? 'N/A';
            $product->brand_code = $product->brand->code ?? 'N/A';
            $product->brand_name = $product->brand->name ?? 'N/A';
            $product->creator_name = $product->creator ? $product->creator->code : 'N/A';
            $product->modifier_name = $product->modifier ? $product->modifier->code : 'N/A';

            $product->price = number_format($product->price, 2);

            return $product;
        });
    }

    public function map($product): array
    {
        return [
            $product->code ?? 'N/A',
            $product->name ?? 'N/A',
            $product->client->code ?? 'N/A',
            $product->client->name ?? 'N/A',
            $product->category_code ?? 'N/A',
            $product->category_name ?? 'N/A',
            $product->brand_code ?? 'N/A',
            $product->brand_name ?? 'N/A',
            $product->price ?? 'N/A',
            $product->description ?? 'N/A',
            $product->order ?? 'N/A',
            $product->status ? 'Yes' : 'No',
            $product->creator_name,
            $product->created_at instanceof Carbon ? $product->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $product->modifier_name,
            $product->updated_at instanceof Carbon ? $product->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            'Client Code',
            'Client Name',
            'Category Code',
            'Category Name',
            'Brand Code',
            'Brand Name',
            'Price',
            'Description',
            'Order',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
