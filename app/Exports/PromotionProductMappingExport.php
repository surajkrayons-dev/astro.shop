<?php

namespace App\Exports;

use App\Models\PromotionProductMapping;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PromotionProductMappingExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $query = PromotionProductMapping::with([
            'promotionProduct', 'store', 'chain', 'productCategory', 'productBrand',
            'state', 'region', 'format', 'createdBy', 'modifiedBy'
        ])
        ->select(
            'promotion_product_mappings.*'
        )
        ->leftJoin('promotion_products', 'promotion_product_mappings.promotion_product_id', '=', 'promotion_products.id');

        // ✅ filters
        if (!empty($this->filters['promotion_product_id'])) {
            $query->where('promotion_product_mappings.promotion_product_id', $this->filters['promotion_product_id']);
        }
        if (!empty($this->filters['store_id'])) {
            $query->where('promotion_product_mappings.store_id', $this->filters['store_id']);
        }
        if (!empty($this->filters['product_category_id'])) {
            $query->where('promotion_product_mappings.product_category_id', $this->filters['product_category_id']);
        }
        if (!empty($this->filters['product_brand_id'])) {
            $query->where('promotion_product_mappings.product_brand_id', $this->filters['product_brand_id']);
        }
        if (!empty($this->filters['month'])) {
            $query->where('promotion_product_mappings.month', $this->filters['month']);
        }
        if (!empty($this->filters['year'])) {
            $query->where('promotion_product_mappings.year', $this->filters['year']);
        }

        // ✅ subordinate aur client filters (ab join ke baad safe)
        if ($authUser->role_id != 1) {
            $query->where(function ($q) use ($allowedUserIds, $clientIds) {
                $q->whereIn('promotion_products.created_by', $allowedUserIds)
                ->orWhereIn('promotion_product_mappings.created_by', $allowedUserIds);
            });

            if (!empty($clientIds)) {
                $query->whereHas('store', function ($q) use ($clientIds) {
                    $q->whereIn('client_id', $clientIds);
                });
            }
        }

        return $query->get();
    }

    /**
     * Map data for each row to include both code & name for related entities.
     */
    public function map($mapping): array
    {
        // $region = config('system.region')[$mapping->region] ?? 'N/A';
        // $format = config('system.format')[$mapping->format] ?? 'N/A';
        $offer_type = config('system.target_format')[$mapping->offer_type] ?? ucfirst($mapping->offer_type ?? 'N/A');

        return [
            $mapping->promotionProduct->code ?? 'N/A',
            $mapping->promotionProduct->name ?? 'N/A',
            $mapping->store->code ?? 'N/A',
            $mapping->store->name ?? 'N/A',
            $mapping->chain->code ?? 'N/A',
            $mapping->chain->name ?? 'N/A',
            $mapping->productCategory->code ?? 'N/A',
            $mapping->productCategory->name ?? 'N/A',
            $mapping->productBrand->code ?? 'N/A',
            $mapping->productBrand->name ?? 'N/A',
            $mapping->region->code ?? 'N/A',
            $mapping->region->name ?? 'N/A',
            $mapping->format->code ?? 'N/A',
            $mapping->format->name ?? 'N/A',
            $offer_type,
            $mapping->offer ?? 'N/A',
            $mapping->state->name ?? 'N/A',
            $mapping->month ?? 'N/A',
            $mapping->year ?? 'N/A',
            $mapping->is_required ? 'Yes' : 'No',
            $mapping->is_photo_required ? 'Yes' : 'No',
            $mapping->is_enter_stock_enabled ? 'Yes' : 'No',
            $mapping->is_enter_stock_required ? 'Yes' : 'No',
            $mapping->createdBy ? $mapping->createdBy->code : 'N/A',
            $mapping->created_at instanceof Carbon ? $mapping->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $mapping->modifiedBy ? $mapping->modifiedBy->code : 'N/A',
            $mapping->updated_at instanceof Carbon ? $mapping->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Define headings for the exported file.
     */
    public function headings(): array
    {
        return [
            'Promotion Product Code',
            'Promotion Product Name',
            'Store Code',
            'Store Name',
            'Chain Code',
            'Chain Name',
            'Product Category Code',
            'Product Category Name',
            'Product Brand Code',
            'Product Brand Name',
            'Region Code',
            'Region Name',
            'Format Code',
            'Format Name',
            'Offer Type',
            'Offer',
            'State',
            'Month',
            'Year',
            'Is Required',
            'Is Photo Required',
            'Is Enter Stock Enabled',
            'Is Enter Stock Required',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }
}
