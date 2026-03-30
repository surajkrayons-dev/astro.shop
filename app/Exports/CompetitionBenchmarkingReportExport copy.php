<?php

namespace App\Exports;

use App\Models\CompetitionBenchmarkingReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CompetitionBenchmarkingReportExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $authUser = Auth::user();

        $query = CompetitionBenchmarkingReport::with(['promoter:id,code,name', 'store:id,code,name', 'competitionProduct:id,code,name'])
            ->select('competition_benchmarking_reports.*')
            ->leftJoin('stores', 'competition_benchmarking_reports.store_id', '=', 'stores.id');

        if ($authUser->type === 'client') {
            $mappedStoreIds = \App\Models\StoreUserMapping::where('client_id', $authUser->id)
                ->pluck('store_id');

            $query->whereIn('competition_benchmarking_reports.store_id', $mappedStoreIds);
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
