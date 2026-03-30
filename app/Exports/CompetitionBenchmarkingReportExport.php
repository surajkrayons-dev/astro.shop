<?php

namespace App\Exports;

use App\Models\CompetitionBenchmarkingReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class CompetitionBenchmarkingReportExport implements FromCollection, WithHeadings, WithMapping
{
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

        $allowedClientIds = \App\Models\User::whereIn('id', $allowedUserIds)
            ->pluck('client_id')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->unique()
            ->toArray();

        $query = \App\Models\CompetitionBenchmarkingReport::with([
            'promoter:id,code,name',
            'store:id,code,name',
            'competitionProduct:id,code,name'
        ])
        ->select('competition_benchmarking_reports.*')
        ->leftJoin('stores', 'competition_benchmarking_reports.store_id', '=', 'stores.id')
        ->leftJoin('competition_products', 'competition_benchmarking_reports.competition_product_id', '=', 'competition_products.id');

        // Filter for non-admin users
        if ($authUser->role_id != 1) {
            $query->whereIn('stores.client_id', $allowedClientIds)
                ->whereIn('competition_products.created_by', $allowedUserIds);
        }

        // Apply export filters
        if (!empty($this->filters['promoter_id'])) {
            $query->where('promoter_id', $this->filters['promoter_id']);
        }

        if (!empty($this->filters['store_id'])) {
            $query->where('store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['competition_product_id'])) {
            $query->where('competition_product_id', $this->filters['competition_product_id']);
        }

        if (isset($this->filters['is_stock']) && $this->filters['is_stock'] !== '') {
            $query->where('is_stock', $this->filters['is_stock']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(competition_benchmarking_reports.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        // Order and return
        return $query
            ->orderByDesc('competition_benchmarking_reports.created_at')
            ->get();
    }

    public function map($row): array
    {
        return [
            $row->promoter?->code ?? 'N/A',
            $row->promoter?->name ?? 'N/A',
            $row->store?->code ?? 'N/A',
            $row->store?->name ?? 'N/A',
            $row->competitionProduct?->code ?? 'N/A',
            $row->competitionProduct?->name ?? 'N/A',
            $row->is_stock ? 'In Stock' : 'Out Of Stock',
            $row->regular_price ?? 'N/A',
            $row->selling_price ?? 'N/A',
            $row->count_of_facing ?? 'N/A',
            $row->promo_running ?? 'N/A',
            $row->no_permission_reason ?? 'N/A',
            $row->created_at?->format('d-m-Y, H:i:s') ?? 'N/A',
            $row->updated_at?->format('d-m-Y, H:i:s') ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Promoter User ID',
            'Promoter Name',
            'Store Code',
            'Store Name',
            'Competition Product Code',
            'Competition Product Name',
            'Is Stock',
            'Regular Price',
            'Selling Price',
            'Count Of Facing',
            'Promo Running',
            'No Permission Reason',
            'Created Date & Time',
            'Modified Date & Time',
        ];
    }
}
