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
    /**
     * Fetch all sale reports with necessary relationships
     */
    public function collection()
    {
        $authUser = Auth::user();

        $query = SaleReport::with(['promoter', 'store', 'product'])
            ->select('sale_reports.*')
            ->leftJoin('stores', 'sale_reports.store_id', '=', 'stores.id');

        if ($authUser->type === 'client') {
            $mappedStoreIds = \App\Models\StoreUserMapping::where('client_id', $authUser->id)
                ->pluck('store_id');
            $query->whereIn('sale_reports.store_id', $mappedStoreIds);
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
