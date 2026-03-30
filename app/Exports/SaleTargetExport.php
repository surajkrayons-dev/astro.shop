<?php

namespace App\Exports;

use App\Models\SaleTarget;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SaleTargetExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();
        $filters = $this->filters;

        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = function_exists('getAllSubordinateIds')
            ? getAllSubordinateIds($authUser->id, $clientIds)
            : [];
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $query = SaleTarget::with(['store', 'createdBy', 'modifiedBy'])
            ->select('store_id', 'month', 'year', 'target_type', 'overall_sale_target', 'focus_product1_target', 'focus_product2_target', 'focus_product3_target', 'created_by', 'modified_by', 'created_at', 'updated_at');

        $query->when(!empty($filters['store_id']), function ($q) use ($filters) {
            $q->where('store_id', $filters['store_id']);
        });
        $query->when(!empty($filters['target_type']), function ($q) use ($filters) {
            $q->where('target_type', 'like', '%' . $filters['target_type'] . '%');
        });
        $query->when(!empty($filters['overall_sale_target']), function ($q) use ($filters) {
            $q->where('overall_sale_target', 'like', '%' . $filters['overall_sale_target'] . '%');
        });
        $query->when(!empty($filters['month']), function ($q) use ($filters) {
            $q->where('month', $filters['month']);
        });
        $query->when(!empty($filters['year']), function ($q) use ($filters) {
            $q->where('year', $filters['year']);
        });    

        if ($authUser->role_id != 1) {
            $query->whereHas('store', function ($subQ) use ($clientIds) {
                $subQ->whereIn('client_id', $clientIds);
            });
            $query->whereIn('created_by', $allowedUserIds);
        }

        return $query->get()->map(function ($mapping) {
            $mapping->store_code = $mapping->store->code ?? 'N/A';
            $mapping->store_name = $mapping->store->name ?? 'N/A';
            return $mapping;
        });
    }


    /**
     * Map data for each row to show user name instead of id
     */
    public function map($mapping): array
    {
        return [
            $mapping->store_code,
            $mapping->store_name,
            $mapping->month ?? 'N/A',
            $mapping->year ?? 'N/A',
            $mapping->target_type ?? 'N/A',
            $mapping->overall_sale_target ?? 'N/A',
            $mapping->focus_product1_target ?? 'N/A',
            $mapping->focus_product2_target ?? 'N/A',
            $mapping->focus_product3_target ?? 'N/A',
            $mapping->createdBy ? $mapping->createdBy->code : 'N/A',
            $mapping->created_at instanceof Carbon ? $mapping->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $mapping->modifiedBy ? $mapping->modifiedBy->code : 'N/A',
            $mapping->updated_at instanceof Carbon ? $mapping->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Store Code',
            'Store Name',
            'Month',
            'Year',
            'Target Type',
            'Overall Sale Target',
            'Focus Product1 Target',
            'Focus Product2 Target',
            'Focus Product3 Target',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }

}
