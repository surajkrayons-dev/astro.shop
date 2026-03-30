<?php

namespace App\Exports;

use App\Models\CategoryTrackingReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CategoryTrackingReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = Auth::user();

        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $allowedClientIds = \App\Models\User::whereIn('id', $allowedUserIds)
            ->pluck('client_id')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->unique()
            ->toArray();

        $query = CategoryTrackingReport::with(['promoter:id,code,name', 'store:id,code,name', 'categoryTracking:id,code,name'])
            ->select('category_tracking_reports.*')
            ->leftJoin('stores', 'category_tracking_reports.store_id', '=', 'stores.id')
            ->leftJoin('category_trackings', 'category_tracking_reports.category_tracking_id', '=', 'category_trackings.id');

        // if ($authUser->type === 'client') {
        //     $query->where('stores.client_id', $authUser->id);
        // } elseif ($authUser->role_id != 1) {
        //     $clientIds = array_filter(explode(',', $authUser->client_id));
        //     $query->whereIn('stores.client_id', $clientIds);
        // }
        if ($authUser->role_id != 1) {
            $query->whereIn('stores.client_id', $allowedClientIds)
                ->whereIn('category_trackings.created_by', $allowedUserIds);
        }

        if (!empty($this->filters['promoter_id'])) {
            $query->where('category_tracking_reports.promoter_id', $this->filters['promoter_id']);
        }
        if (!empty($this->filters['store_id'])) {
            $query->where('category_tracking_reports.store_id', $this->filters['store_id']);
        }
        if (!empty($this->filters['category_tracking_id'])) {
            $query->where('category_tracking_reports.category_tracking_id', $this->filters['category_tracking_id']);
        }
        if (isset($this->filters['is_stock'])) {
            $query->where('category_tracking_reports.is_stock', $this->filters['is_stock']);
        }
        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(category_tracking_reports.created_at)'), [$this->filters['start_date'], $this->filters['end_date']]);   
        }

        return $query->orderByDesc('category_tracking_reports.updated_at')->get();
    }

    public function map($row): array
    {
        $baseUrl = url('storage/category_tracking_reports');
        $photo = $row->photo ? $baseUrl . '/' . $row->photo : 'N/A';

        return [
            $row->promoter?->code ?? 'N/A',
            $row->promoter?->name ?? 'N/A',
            $row->store?->code ?? 'N/A',
            $row->store?->name ?? 'N/A',
            $row->categoryTracking?->code ?? 'N/A',
            $row->categoryTracking?->name ?? 'N/A',
            $row->is_category_available ? 'Yes' : 'No',
            $photo,
            $row->count_of_facing ?? 'N/A',
            $row->total_category_facing ?? 'N/A',
            $row->achievement_sos ?? 'N/A',
            $row->target_sos ?? 'N/A',
            $row->remaining_target_value ?? 'N/A',
            $row->no_permission_reason ?? 'N/A',
            $row->created_at?->format('d-m-Y, H:i:s') ?? 'N/A',
            $row->updated_at?->format('d-m-Y, H:i:s') ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Promoter Code',
            'Promoter Name',
            'Store Code',
            'Store Name',
            'Category Tracking Code',
            'Category Tracking Name',
            'Is Category Available',
            'Photo',
            'Count Of Facing',
            'Total Category Facing',
            'Achievement SOS',
            'Target SOS',
            'Remaining Target Value',
            'No Permission Reason',
            'Created Date & Time',
            'Modified Date & Time',
        ];
    }
}
