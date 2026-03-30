<?php

namespace App\Exports;

use App\Models\CategoryTrackingTargetMapping;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CategoryTrackingTargetMappingExport implements FromCollection, WithHeadings, WithMapping
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

        $query = CategoryTrackingTargetMapping::with(['categoryTracking', 'store', 'createdBy', 'modifiedBy'])
            ->select('category_tracking_id', 'store_id', 'region_id', 'format_id', 'target', 'frequency', 'month', 'year', 'is_required', 'is_photo_required', 'is_facing_enabled', 'created_by', 'modified_by', 'created_at', 'updated_at');

        //  Filters apply
        $query->when(!empty($filters['category_tracking_id']), function ($q) use ($filters) {
            $q->where('category_tracking_id', $filters['category_tracking_id']);
        });
        $query->when(!empty($filters['store_id']), function ($q) use ($filters) {
            $q->where('store_id', $filters['store_id']);
        });
        $query->when(!empty($filters['region_id']), function ($q) use ($filters) {
            $q->where('region_id', $filters['region_id']);
        });
        $query->when(!empty($filters['format_id']), function ($q) use ($filters) {
            $q->where('format_id', $filters['format_id']);
        });
        $query->when(!empty($filters['month']), function ($q) use ($filters) {
            $q->where('month', $filters['month']);
        });
        $query->when(!empty($filters['year']), function ($q) use ($filters) {
            $q->where('year', $filters['year']);
        });

        if ($authUser->role_id != 1) {
            $query->whereHas('categoryTracking', function ($subQ) use ($allowedUserIds) {
                $subQ->whereIn('created_by', $allowedUserIds);
            })->whereHas('store', function ($subQ) use ($clientIds) {
                $subQ->whereIn('client_id', $clientIds);
            });
        }

        return $query->orderByDesc('updated_at')->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($mapping): array
    {
        // $regionType = config('system.region')[$mapping->region] ?? 'N/A';
        // $formatType = config('system.format')[$mapping->format] ?? 'N/A';

        return [
            $mapping->categoryTracking->code ?? 'N/A',
            $mapping->categoryTracking->name ?? 'N/A',
            $mapping->store->code ?? 'N/A',
            $mapping->store->name ?? 'N/A',
            $mapping->region->code ?? 'N/A',
            $mapping->region->name ?? 'N/A',
            $mapping->format->code ?? 'N/A',
            $mapping->format->name ?? 'N/A',
            $mapping->target ?? 'N/A',
            $mapping->frequency ?? 'N/A',
            $mapping->month ?? 'N/A',
            $mapping->year ?? 'N/A',
            $mapping->is_required ? 'Yes' : 'No',
            $mapping->is_photo_required ? 'Yes' : 'No',
            $mapping->is_facing_enabled ? 'Yes' : 'No',
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
            'Category Tracking Code',
            'Category Tracking Name',
            'Store Code',
            'Store Name',
            'Region Code',
            'Region Name',
            'Format Code',
            'Format Name',
            'Target ',
            'Frequency ',
            'Month ',
            'Year ',
            'Is Required ',
            'Is Photo Required',
            'Is Facing Enabled',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }

}
