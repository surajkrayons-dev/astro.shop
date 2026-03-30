<?php

namespace App\Exports;

use App\Models\CompetitionProductMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CompetitionProductMappingExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = function_exists('getAllSubordinateIds')
            ? getAllSubordinateIds($authUser->id, $clientIds)
            : [];
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $query = CompetitionProductMapping::with([
            'chain', 'competitionProduct', 'region', 'format', 'creator', 'modifier'
        ]);

        $query->when(!empty($this->filters['chain_id']), function ($q) {
            $q->where('chain_id', $this->filters['chain_id']);
        });
        $query->when(!empty($this->filters['competition_product_id']), function ($q) {
            $q->where('competition_product_id', $this->filters['competition_product_id']);
        });
        $query->when(!empty($this->filters['region_id']), function ($q) {
            $q->where('region_id', $this->filters['region_id']);
        });
        $query->when(!empty($this->filters['format_id']), function ($q) {
            $q->where('format_id', $this->filters['format_id']);
        });
        $query->when(!empty($this->filters['month']), function ($q) {
            $q->where('month', $this->filters['month']);
        });
        $query->when(!empty($this->filters['year']), function ($q) {
            $q->where('year', $this->filters['year']);
        });

        if ($authUser->role_id != 1) {
            $query->whereHas('competitionProduct', function ($subQ) use ($allowedUserIds) {
                $subQ->whereIn('created_by', $allowedUserIds);
            });
        }

        return $query->get();
    }

    /**
     * Map each row to match the same columns used in your import.
     */
    public function map($row): array
    {
        // $region_name = config('system.region')[$row->region] ?? 'N/A';
        // $format_name = config('system.format')[$row->format] ?? 'N/A';

        return [
            $row->chain->code ?? 'N/A',
            $row->chain->name ?? 'N/A',
            $row->competitionProduct->code ?? 'N/A',
            $row->competitionProduct->name ?? 'N/A',
            $row->region->code ?? 'N/A',
            $row->region->name ?? 'N/A',
            $row->format->code ?? 'N/A',
            $row->format->name ?? 'N/A',
            $row->month ?? 'N/A',
            $row->year ?? 'N/A',
            $row->is_required ? 'Yes' : 'No',
            $row->is_mrp_enabled ? 'Yes' : 'No',
            $row->is_selling_price_enabled ? 'Yes' : 'No',
            $row->is_mdf_enabled ? 'Yes' : 'No',
            $row->is_facing_enabled ? 'Yes' : 'No',
            $row->is_display_available ? 'Yes' : 'No',
            $row->is_promo_running_enabled ? 'Yes' : 'No',
            $row->is_night_enabled ? 'Yes' : 'No',
            $row->is_pack_enabled ? 'Yes' : 'No',
            $row->is_stock_on_display_enabled ? 'Yes' : 'No',
            $row->is_photo_enabled ? 'Yes' : 'No',
            $row->is_photo_required ? 'Yes' : 'No',
            $row->creator->code ?? 'N/A',
            $row->created_at instanceof Carbon ? $row->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $row->modifier->code ?? 'N/A',
            $row->updated_at instanceof Carbon ? $row->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * The headings in the same order as map().
     */
    public function headings(): array
    {
        return [
            'Chain code',
            'Chain Name',
            'competition Product Code',
            'competition Product Name',
            'Region Code',
            'Region Name',
            'Format Code',
            'Format Name',
            'Month',
            'Year',
            'Is Required',
            'Is MRP Enabled',
            'Is Selling Price Enabled',
            'Is MDF Enabled',
            'Is Facing Enabled',
            'Is Display Available',
            'Is Promo Running Enabled',
            'Is Night Enabled',
            'Is Pack Enabled',
            'Is Stock On Display Enabled',
            'Is Photo Enabled',
            'Is Photo Required',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }
}
