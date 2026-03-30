<?php

namespace App\Exports;

use App\Models\VisibilityReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VisibilityReportExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Fetch all sale reports with necessary relationships
     */
    public function collection()
    {
        $authUser = Auth::user();

        $query = VisibilityReport::with(['promoter', 'store', 'posm'])
            ->select('visibility_reports.*')
            ->leftJoin('stores', 'visibility_reports.store_id', '=', 'stores.id');

        if ($authUser->type === 'client') {
            $mappedStoreIds = \App\Models\StoreUserMapping::where('client_id', $authUser->id)
                ->pluck('store_id');
            $query->whereIn('visibility_reports.store_id', $mappedStoreIds);
        }

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('stores.client_id', $clientIds);
        }

        return $query->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($report): array
    {
        return [
            $report->promoter->code ?? 'N/A',
            $report->promoter->name ?? 'N/A',
            $report->store->code ?? 'N/A',
            $report->store->name ?? 'N/A',
            $report->posm->code ?? 'N/A',
            $report->posm->name ?? 'N/A',
            $report->visibility_action ?? '',
            $report->is_adhoc_visibility_available ? 'Yes' : 'No',
            $report->stock_as_per_planogram ? 'Yes' : 'No',
            $report->is_stock_available ? 'Yes' : 'No',
            $report->branding_condition ?? 'N/A',
            $report->no_permission_reason ?? 'N/A',
            $report->photo_left_side ?? 'N/A',
            $report->photo_close_up ?? 'N/A',
            $report->photo_right_side ?? 'N/A',
            $report->created_at instanceof Carbon ? $report->created_at->format('d-m-Y H:i:s') : 'N/A',
            $report->updated_at instanceof Carbon ? $report->updated_at->format('d-m-Y H:i:s') : 'N/A',
        ];
    }

    /**
     * Excel column headings.
     */
    public function headings(): array
    {
        return [
            'Promoter Code',
            'Promoter Name',
            'Store Code',
            'Store Name',
            'Posm Code',
            'Posm Name',
            'Visibility Action',
            'Is Adhoc Visibility Available',
            'Stock as per Planogram',
            'Is Stock Available',
            'Branding Condition',
            'No Permission Reason',
            'Photo Left Side',
            'Photo Close Up',
            'Photo Right Side',
            'Created Date & time',
            'Modified Date & Time',
        ];
    }
}
