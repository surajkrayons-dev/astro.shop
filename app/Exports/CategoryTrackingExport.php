<?php

namespace App\Exports;

use App\Models\CategoryTracking;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CategoryTrackingExport implements FromCollection, WithHeadings, WithMapping
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

        // Base Query
        $query = CategoryTracking::with(['createdBy', 'modifiedBy'])
            ->select(
                'code', 'name', 'count_of', 'target_format',
                'is_question_available', 'is_category_available',
                'status', 'created_by', 'modified_by',
                'created_at', 'updated_at'
            )
            ->when($authUser->role_id != 1, function ($q) use ($allowedUserIds) {
                // Sirf apne aur subordinate ke created_by records
                $q->whereIn('category_trackings.created_by', $allowedUserIds);
            });

        //  Apply filters
        if (!empty($this->filters['code'])) {
            $query->where('category_trackings.code', 'like', '%' . $this->filters['code'] . '%');
        }

        if (!empty($this->filters['name'])) {
            $query->where('category_trackings.name', 'like', '%' . $this->filters['name'] . '%');
        }

        if (!empty($this->filters['count_of'])) {
            $query->where('category_trackings.count_of', $this->filters['count_of']);
        }

        if (!empty($this->filters['target_format'])) {
            $query->where('category_trackings.target_format', $this->filters['target_format']);
        }

        return $query->orderByDesc('category_trackings.updated_at')->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($categoryTracking): array
    {
        $countOf = config('system.count_of')[$categoryTracking->count_of] ?? 'N/A';
        $targetFormat = config('system.target_format')[$categoryTracking->target_format] ?? 'Unknown';

        return [
            $categoryTracking->code,
            $categoryTracking->name,
            $countOf,
            $targetFormat,
            $categoryTracking->is_question_available ? 'Yes' : 'No',
            $categoryTracking->is_category_available ? 'Yes' : 'No',
            $categoryTracking->status ? 'Yes' : 'No',
            $categoryTracking->createdBy ? $categoryTracking->createdBy->code : 'N/A',
            $categoryTracking->created_at instanceof Carbon ? $categoryTracking->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $categoryTracking->modifiedBy ? $categoryTracking->modifiedBy->code : 'N/A',
            $categoryTracking->updated_at instanceof Carbon ? $categoryTracking->updated_at->format('d-m-Y, H:i:s') : 'N/A',
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
            'Count of Facing / Shelf',
            'Target Format',
            'Is Question Available',
            'Is Category Available',
            'Status',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }

}
