<?php

namespace App\Exports;

use App\Models\ProductMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ProductMappingExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $query = ProductMapping::with(['product', 'store', 'chain', 'format', 'region', 'createdBy', 'modifiedBy'])
            ->leftJoin('products', 'products.id', '=', 'product_mappings.product_id');

        // ✅ Apply filters
        if (!empty($this->filters['product_id'])) {
            $query->where('product_mappings.product_id', $this->filters['product_id']);
        }
        if (!empty($this->filters['chain_id'])) {
            $query->where('product_mappings.chain_id', $this->filters['chain_id']);
        }
        if (!empty($this->filters['region_id'])) {
            $query->where('product_mappings.region_id', $this->filters['region_id']);
        }
        if (!empty($this->filters['format_id'])) {
            $query->where('product_mappings.format_id', $this->filters['format_id']);
        }
        if (!empty($this->filters['month'])) {
            $query->where('product_mappings.month', 'like', '%' . $this->filters['month'] . '%');
        }
        if (!empty($this->filters['year'])) {
            $query->where('product_mappings.year', 'like', '%' . $this->filters['year'] . '%');
        }

        // 🔐 Subordinate access restriction
        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
            $allStaffIds = array_merge([$authUser->id], $subordinateIds);
            $relatedClientIds = \App\Models\User::whereIn('id', $allStaffIds)
                ->whereNotNull('client_id')
                ->pluck('client_id')->toArray();
            // Flatten comma-separated client IDs
            $flatClientIds = [];
            foreach ($relatedClientIds as $cids) {
                foreach (explode(',', $cids) as $cid) {
                    $cid = trim($cid);
                    if ($cid !== '') {
                        $flatClientIds[] = $cid;
                    }
                }
            }
            $flatClientIds = array_unique($flatClientIds);

            if (!empty($flatClientIds)) {
                // ✅ Restrict based on products' client_id
                $query->whereIn('products.client_id', $flatClientIds);
            } else {
                $query->whereRaw('1=0'); // No access
            }
        }
        
        return $query->select('product_mappings.*')->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        // Region & Format mapping from config
        // $region_name = config('system.region')[$row->region] ?? 'N/A';
        // $format_name = config('system.format')[$row->format] ?? 'N/A';

        return [
            $row->product->code ?? 'N/A',
            $row->product->name ?? 'N/A',
            // $row->store->code ?? 'N/A',
            // $row->store->name ?? 'N/A',
            $row->chain->code ?? 'N/A',
            $row->chain->name ?? 'N/A',
            $row->region->code ?? 'N/A',
            $row->region->name ?? 'N/A',
            $row->format->code ?? 'N/A',
            $row->format->name ?? 'N/A',
            // $region_name ?? 'N/A',
            // $format_name ?? 'N/A',
            $row->is_msl_enabled ? 'Yes' : 'No',
            $row->maq ?? 'N/A',
            $row->is_primary_shelf_enabled ? 'Yes' : 'No',
            $row->is_primary_shelf_required ? 'Yes' : 'No',
            $row->is_mfd_in_ps_enabled ? 'Yes' : 'No',
            $row->is_mfd_in_ps_required ? 'Yes' : 'No',
            $row->mdf_date ?? 'N/A',
            $row->is_expiry_date_in_ps_enabled ? 'Yes' : 'No',
            $row->is_expiry_date_in_ps_required ? 'Yes' : 'No',
            $row->expiry_date ?? 'N/A',
            $row->is_damage_qty_in_ps_enabled ? 'Yes' : 'No',
            $row->is_damage_qty_in_ps_required ? 'Yes' : 'No',
            $row->damage_qty_date ?? 'N/A',
            $row->is_sales_enabled ? 'Yes' : 'No',
            $row->is_sales_required ? 'Yes' : 'No',
            $row->is_sales_return_enabled ? 'Yes' : 'No',
            $row->is_stock_transfer_enabled ? 'Yes' : 'No',
            $row->focus_product ?? 'N/A',
            $row->is_ba_enabled ? 'Yes' : 'No',
            $row->is_ba_required ? 'Yes' : 'No',
            $row->is_adhoc_enabled ? 'Yes' : 'No',
            $row->is_adhoc_required ? 'Yes' : 'No',
            $row->is_primary_shelf_csp_enabled ? 'Yes' : 'No',
            $row->is_primary_shelf_csp_required ? 'Yes' : 'No',
            $row->is_sales_csp_enabled ? 'Yes' : 'No',
            $row->is_sales_csp_required ? 'Yes' : 'No',
            $row->is_ba_csp_enabled ? 'Yes' : 'No',
            $row->is_ba_csp_required ? 'Yes' : 'No',
            $row->is_sampling_sales_enabled ? 'Yes' : 'No',
            $row->is_sampling_sales_required ? 'Yes' : 'No',
            $row->month ?? 'N/A',
            $row->year ?? 'N/A',
            $row->createdBy->code ?? 'N/A',
            $row->created_at instanceof Carbon ? $row->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $row->modifiedBy->code ?? 'N/A',
            $row->updated_at instanceof Carbon ? $row->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Headings in the same order as map().
     */
    public function headings(): array
    {
        return [
            'Product Code',
            'Product Name',
            'Chain Code',
            'Chain Name',
            'Region Code',
            'Region Name',
            'Format Code',
            'Format Name',
            'Is MSL Enabled',
            'MAQ',
            'Is Primary Shelf Enabled',
            'Is Primary Shelf Required',
            'Is MFD in PS Enabled',
            'Is MFD in PS Required',
            'MDF Date',
            'Is Expiry Date in PS Enabled',
            'Is Expiry Date in PS Required',
            'Expiry Date',
            'Is Damage Qty in PS Enabled',
            'Is Damage Qty in PS Required',
            'Damage Qty Date',
            'Is Sales Enabled',
            'Is Sales Required',
            'Is Sales Return Enabled',
            'Is Stock Transfer Enabled',
            'Focus Product',
            'Is BA Enabled',
            'Is BA Required',
            'Is Adhoc Enabled',
            'Is Adhoc Required',
            'Is Primary Shelf CSP Enabled',
            'Is Primary Shelf CSP Required',
            'Is Sales CSP Enabled',
            'Is Sales CSP Required',
            'Is BA CSP Enabled',
            'Is BA CSP Required',
            'Is Sampling Sales Enabled',
            'Is Sampling Sales Required',
            'Month',
            'Year',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
