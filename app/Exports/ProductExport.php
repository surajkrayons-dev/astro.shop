<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\User;
use Carbon\Carbon;

class ProductExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $query = Product::with(['category', 'brand', 'creator', 'modifier', 'client'])
            ->select(
                'id',
                'client_id',
                'code',
                'name',
                'product_category_id',
                'product_brand_id',
                'price',
                'description',
                'order',
                'status',
                'created_by',
                'modified_by',
                'created_at',
                'updated_at'
            );
            
            // ✅ Apply filters
            if (!empty($this->filters['search_product'])) {
                $query->where(function ($q) {
                    $q->where('products.code', 'like', '%' . $this->filters['search_product'] . '%')
                        ->orWhere('products.name', 'like', '%' . $this->filters['search_product'] . '%');
                });
            }

            if (!empty($this->filters['client_id'])) {
                $query->where('products.client_id', $this->filters['client_id']);
            }

            if (!empty($this->filters['product_category_id'])) {
                $query->where('products.product_category_id', $this->filters['product_category_id']);
            }

            if (!empty($this->filters['product_brand_id'])) {
                $query->where('products.product_brand_id', $this->filters['product_brand_id']);
            }

            if (isset($this->filters['status']) && $this->filters['status'] !== '') {
                $query->where('products.status', $this->filters['status']);
            }

        // 🔐 Subordinate access restriction
        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));

            // Get subordinate IDs recursively
            $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
            $allStaffIds = array_merge([$authUser->id], $subordinateIds);

            // Get all related client IDs
            $relatedClientIds = User::whereIn('id', $allStaffIds)
                ->whereNotNull('client_id')
                ->pluck('client_id')
                ->toArray();

            // Flatten comma-separated values
            $flatClientIds = [];
            foreach ($relatedClientIds as $cids) {
                foreach (explode(',', $cids) as $cid) {
                    $cid = trim($cid);
                    if (!empty($cid)) $flatClientIds[] = $cid;
                }
            }
            $flatClientIds = array_unique($flatClientIds);

            if (!empty($flatClientIds)) {
                $query->whereIn('products.client_id', $flatClientIds);
            } else {
                $query->whereRaw('1=0'); // no access
            }
        }

        return $query->get()->map(function ($product) {
            $product->category_code = $product->category->code ?? 'N/A';
            $product->category_name = $product->category->name ?? 'N/A';
            $product->brand_code = $product->brand->code ?? 'N/A';
            $product->brand_name = $product->brand->name ?? 'N/A';
            $product->creator_name = $product->creator ? $product->creator->code : 'N/A';
            $product->modifier_name = $product->modifier ? $product->modifier->code : 'N/A';
            $product->client_code = $product->client ? $product->client->code : 'N/A';
            $product->client_name = $product->client ? $product->client->name : 'N/A';
            $product->price = number_format($product->price, 2);
            return $product;
        });
    }

    public function map($product): array
    {
        return [
            $product->code ?? 'N/A',
            $product->name ?? 'N/A',
            $product->client_code ?? 'N/A',
            $product->client_name ?? 'N/A',
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
            'Created Date & Time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
