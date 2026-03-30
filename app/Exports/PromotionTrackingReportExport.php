<?php

namespace App\Exports;

use App\Models\PromotionTrackingReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class PromotionTrackingReportExport implements FromCollection, WithHeadings, WithMapping
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

        $allowedClientIds = User::whereIn('id', $allowedUserIds)
            ->pluck('client_id')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->unique()
            ->toArray();

        $query = PromotionTrackingReport::with(['promoter:id,code,name', 'store:id,code,name', 'promotionProduct:id,code,name'])
            ->select('promotion_tracking_reports.*')
            ->leftJoin('stores', 'promotion_tracking_reports.store_id', '=', 'stores.id')
            ->leftJoin('promotion_products', 'promotion_tracking_reports.promotion_product_id', '=', 'promotion_products.id');

        if ($authUser->role_id != 1) {
            $query->whereIn('stores.client_id', $allowedClientIds)
                  ->whereIn('promotion_products.created_by', $allowedUserIds);
        }

        if (!empty($this->filters['promoter_id'])) {
            $query->where('promotion_tracking_reports.promoter_id', $this->filters['promoter_id']);
        }

        if (!empty($this->filters['store_id'])) {
            $query->where('promotion_tracking_reports.store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['promotion_product_id'])) {
            $query->where('promotion_tracking_reports.promotion_product_id', $this->filters['promotion_product_id']);
        }

        if (isset($this->filters['is_stock'])) {
            $query->where('promotion_tracking_reports.is_stock', $this->filters['is_stock']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(promotion_tracking_reports.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        return $query->orderByDesc('promotion_tracking_reports.updated_at')->get();
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
