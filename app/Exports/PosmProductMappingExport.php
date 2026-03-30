<?php

namespace App\Exports;

use App\Models\PosmProductMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PosmProductMappingExport implements FromCollection, WithHeadings, WithMapping
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

        $query = PosmProductMapping::with([
            'posm', 'posmBrand', 'product', 'productCategory', 'productBrand', 'createdBy', 'modifiedBy'
        ]);

        if ($authUser->role_id != 1) {
            $query->where(function ($q) use ($allowedUserIds, $clientIds) {
                $q->whereIn('created_by', $allowedUserIds)
                  ->orWhereHas('posm', function ($sub) use ($allowedUserIds) {
                      $sub->whereIn('created_by', $allowedUserIds);
                  });
            });

            // Product-level restriction: only client-linked products
            $query->whereHas('product', function ($q) use ($clientIds) {
                $q->whereIn('client_id', $clientIds);
            });
        }

        // Filters
        if (!empty($this->filters['posm_id'])) {
            $query->where('posm_id', $this->filters['posm_id']);
        }
        if (!empty($this->filters['posm_brand_id'])) {
            $query->where('posm_brand_id', $this->filters['posm_brand_id']);
        }
        if (!empty($this->filters['product_id'])) {
            $query->where('product_id', $this->filters['product_id']);
        }
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date'])
                  ->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        return $query->get();
    }

    public function map($ppm): array
    {
        return [
            $ppm->posm->code ?? 'N/A',
            $ppm->posm->name ?? 'N/A',
            $ppm->posmBrand->code ?? 'N/A',
            $ppm->posmBrand->name ?? 'N/A',
            $ppm->product->code ?? 'N/A',
            $ppm->product->name ?? 'N/A',
            // $ppm->productCategory->code ?? 'N/A',
            // $ppm->productCategory->name ?? 'N/A',
            // $ppm->productBrand->code ?? 'N/A',
            // $ppm->productBrand->name ?? 'N/A',
            // $ppm->mrp ?? 'N/A',
            $ppm->is_paid_visibility_enabled ? 'Yes' : 'No',
            $ppm->is_paid_visibility_required ? 'Yes' : 'No',
            $ppm->is_tot_visibility_enabled ? 'Yes' : 'No',
            $ppm->is_tot_visibility_required ? 'Yes' : 'No',
            $ppm->is_visibility_enabled ? 'Yes' : 'No',
            $ppm->is_visibility_required ? 'Yes' : 'No',
            $ppm->createdBy->code ?? 'N/A',
            $ppm->created_at instanceof Carbon ? $ppm->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $ppm->modifiedBy->code ?? 'N/A',
            $ppm->updated_at instanceof Carbon ? $ppm->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'POSM Code',
            'POSM Name',
            'POSM Brand Code',
            'POSM Brand Name',
            'Product Code',
            'Product Name',
            // 'Product Category Code',
            // 'Product Category Name',
            // 'Product Brand Code',
            // 'Product Brand Name',
            // 'MRP',
            'Is Paid Visibility Enabled',
            'Is Paid Visibility Required',
            'Is Tot Visibility Enabled',
            'Is Tot Visibility Required',
            'Is Visibility Enabled',
            'Is Visibility Required',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }
}
