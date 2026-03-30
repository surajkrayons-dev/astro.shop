<?php

namespace App\Exports;

use App\Models\SaleReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SaleReportExport implements FromCollection, WithHeadings, WithMapping
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

        // Get allowed client IDs (login + subordinate)
        $allowedClientIds = \App\Models\User::whereIn('id', $allowedUserIds)
            ->pluck('client_id')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->unique()
            ->toArray();

        $query = SaleReport::with(['promoter', 'store', 'product'])
            ->select('sale_reports.*')
            ->leftJoin('stores', 'sale_reports.store_id', '=', 'stores.id');

        // if ($authUser->type === 'client') {
        //     $query->where('stores.client_id', $authUser->id);
        // } elseif ($authUser->role_id != 1) {
        //     $clientIds = array_filter(explode(',', $authUser->client_id));
        //     $query->whereIn('stores.client_id', $clientIds);
        // }

        if ($authUser->role_id != 1) {
            if (!empty($allowedClientIds)) {
                $query->whereIn('stores.client_id', $allowedClientIds);
            } else {
                $query->whereRaw('1=0'); // no clients => no data
            }
        }

        // Apply filters
        if (!empty($this->filters['promoter_id'])) {
            $query->where('sale_reports.promoter_id', $this->filters['promoter_id']);
        }

        if (!empty($this->filters['store_id'])) {
            $query->where('sale_reports.store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['product_id'])) {
            $query->where('sale_reports.product_id', $this->filters['product_id']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(sale_reports.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        // Fix: remove groupBy, use distinct instead
        $query->distinct('sale_reports.id');

        return $query->orderByDesc('sale_reports.updated_at')->get();
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
            $report->product->code ?? 'N/A',
            $report->product->name ?? 'N/A',
            $report->product_price !== null ? number_format($report->product_price, 2) : 'N/A',
            $report->available_stock ?? 'N/A',
            $report->replenishment_stock ?? 'N/A',
            $report->closing_stock ?? 'N/A',
            $report->total_sale !== null ? number_format($report->total_sale, 2) : 'N/A',
            $report->customers_approached ?? 'N/A',
            $report->customers_converted ?? 'N/A',
            $report->no_permission_reason ?? 'N/A',
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
            'Product Code',
            'Product Name',
            'Product Price',
            'Available Stock',
            'Replenishment Stock',
            'Closing Stock',
            'Total Sale',
            'Customers Approached',
            'Customers Converted',
            'No Permission Reason',
            'Created Date & Time',
            'Modified Date & Time',
        ];
    }
}
