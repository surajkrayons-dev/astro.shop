<?php

namespace App\Exports;

use App\Models\PromotionTrackingReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PromotionTrackingReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $authUser = Auth::user();

        $query = PromotionTrackingReport::with(['promoter:id,code,name', 'store:id,code,name', 'promotionProduct:id,code,name'])
            ->select('promotion_tracking_reports.*')
            ->leftJoin('stores', 'promotion_tracking_reports.store_id', '=', 'stores.id');

        if ($authUser->type === 'client') {
            $mappedStoreIds = \App\Models\StoreUserMapping::where('client_id', $authUser->id)
                ->pluck('store_id');
            $query->whereIn('promotion_tracking_reports.store_id', $mappedStoreIds);
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
            $row->promotionProduct?->code ?? 'N/A',
            $row->promotionProduct?->name ?? 'N/A',
            $row->is_same_promotion_running ? 'Yes' : 'No',
            $row->promotion_running_reason ?? 'N/A',
            $row->is_same_on_pos ? 'Yes' : 'No',
            $row->pos_reason ?? 'N/A',
            $row->is_same_self_talker ? 'Yes' : 'No',
            $row->self_talker_reason ?? 'N/A',
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
            'Promotion Product Code',
            'Promotion Product Name',
            'Same Promotion Running',
            'Promotion Running Reason',
            'Same on POS',
            'POS Reason',
            'Same Self Talker',
            'Self Talker Reason',
            'Created Date & Time',
            'Modified Date & Time',
        ];
    }
}
