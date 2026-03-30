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
    public function collection()
    {
        $authUser = Auth::user();

        $query = CategoryTrackingReport::with(['promoter:id,code,name', 'store:id,code,name', 'categoryTracking:id,code,name'])
            ->select('category_tracking_reports.*')
            ->leftJoin('stores', 'category_tracking_reports.store_id', '=', 'stores.id');

        if ($authUser->type === 'client') {
            $mappedStoreIds = \App\Models\StoreUserMapping::where('client_id', $authUser->id)
                ->pluck('store_id');

            $query->whereIn('category_tracking_reports.store_id', $mappedStoreIds);
        }

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('stores.client_id', $clientIds);
        }

        return $query->get();
    }

    public function map($row): array
    {
        return [
            $row->promoter?->code ?? 'N/A',
            $row->promoter?->name ?? 'N/A',
            $row->store?->code ?? 'N/A',
            $row->store?->name ?? 'N/A',
            $row->categoryTracking?->code ?? 'N/A',
            $row->categoryTracking?->name ?? 'N/A',
            $row->is_category_available ? 'Yes' : 'No',
            $row->photo ?? 'N/A',
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
