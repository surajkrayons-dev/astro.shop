<?php

namespace App\Exports;

use App\Models\CategoryTrackingProduct;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CategoryTrackingProductExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        // Current user ke client_ids aur subordinates nikal lo
        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $query = CategoryTrackingProduct::with([
            'categoryTracking', 'product', 'creator', 'modifier'
        ])
        ->select('category_tracking_id', 'product_id', 'bay1_title', 'bay1_photo', 'bay2_title', 'bay2_photo', 'bay3_title', 'bay3_photo', 'bay4_title', 'bay4_photo', 'bay5_title', 'bay5_photo', 'bay6_title', 'bay6_photo', 'is_required', 'is_photo_required', 'is_facing_enabled', 'is_shelf_enabled', 'is_depth_enabled', 'is_stack_enabled', 'is_shelf_product_edit_enabled', 'created_by', 'modified_by', 'created_at', 'updated_at');

        // Apply filters from request
        $query
            ->when(!empty($this->filters['category_tracking_id']), function ($builder) {
                $builder->where('category_tracking_id', $this->filters['category_tracking_id']);
            })
            ->when(!empty($this->filters['product_id']), function ($builder) {
                $builder->where('product_id', $this->filters['product_id']);
            })
            ->when(!empty($this->filters['start_date']) && !empty($this->filters['end_date']), function ($builder) {
                $builder->whereBetween(\DB::raw('DATE(category_tracking_products.created_at)'), [
                    $this->filters['start_date'],
                    $this->filters['end_date']
                ]);
            });

        // Restrict data for non-admin users
        if ($authUser->role_id != 1) {
            $query->where(function ($q) use ($allowedUserIds, $clientIds) {
                $q->whereHas('categoryTracking', function ($subQ) use ($allowedUserIds) {
                    $subQ->whereIn('created_by', $allowedUserIds);
                })
                ->orWhereHas('product', function ($subQ) use ($allowedUserIds, $clientIds) {
                    $subQ->whereIn('created_by', $allowedUserIds)
                         ->orWhereIn('client_id', $clientIds);
                });
            });
        }

        return $query->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($mapping): array
    {
        return [
            $mapping->categoryTracking->code ?? 'N/A',
            $mapping->categoryTracking->name ?? 'N/A',
            $mapping->product->code ?? 'N/A',
            $mapping->product->name ?? 'N/A',
            // $mapping->order ?? 'N/A',
            $mapping->bay1_title ?? 'N/A',
            $mapping->bay1_photo ?? 'N/A',
            $mapping->bay2_title ?? 'N/A',
            $mapping->bay2_photo ?? 'N/A',
            $mapping->bay3_title ?? 'N/A',
            $mapping->bay3_photo ?? 'N/A',
            $mapping->bay4_title ?? 'N/A',
            $mapping->bay4_photo ?? 'N/A',
            $mapping->bay5_title ?? 'N/A',
            $mapping->bay5_photo ?? 'N/A',
            $mapping->bay6_title ?? 'N/A',
            $mapping->bay6_photo ?? 'N/A',
            $mapping->is_required ? 'Yes' : 'No',
            $mapping->is_photo_required ? 'Yes' : 'No',
            $mapping->is_facing_enabled ? 'Yes' : 'No',
            $mapping->is_shelf_enabled ? 'Yes' : 'No',
            $mapping->is_depth_enabled ? 'Yes' : 'No',
            $mapping->is_stack_enabled ? 'Yes' : 'No',
            $mapping->is_shelf_product_edit_enabled ? 'Yes' : 'No',
            $mapping->creator ? $mapping->creator->code : 'N/A',
            $mapping->created_at instanceof Carbon ? $mapping->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $mapping->modifier ? $mapping->modifier->code : 'N/A',
            $mapping->updated_at instanceof Carbon ? $mapping->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Category Tracking Code',
            'Category Tracking Name',
            'Product Code',
            'Product Name',
            // 'Product Order',
            'Bay1 Title',
            'Bay1 Photo',
            'Bay2 Title',
            'Bay2 Photo',
            'Bay3 Title',
            'Bay3 Photo',
            'Bay4 Title',
            'Bay4 Photo',
            'Bay5 Title',
            'Bay5 Photo',
            'Bay6 Title',
            'Bay6 Photo',
            'Is Required',
            'Is Photo Required',
            'Is Facing Enabled',
            'Is Shelf Enabled',
            'Is Depth Enabled',
            'Is Stack Enabled',
            'Is Shelf Product Edit Enabled',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }

}
