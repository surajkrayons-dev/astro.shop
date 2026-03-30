<?php

namespace App\Imports;

use App\Models\Chain;
use App\Models\Store;
use App\Models\Region;
use App\Models\Format;
use App\Models\Product;
use App\Models\ProductMapping;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class ProductMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $productCode = trim($row['product_code'] ?? '');
            $chainCode   = trim($row['chain_code'] ?? '');
            $month       = (int)($row['month'] ?? 0);
            $year        = (int)($row['year'] ?? 0);

            // Uniqueness check for (product_code, chain_code, month, year) in current import
            $uniqueKey = strtolower($productCode.'|'.$chainCode.'|'.$month.'|'.$year);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Product Code: '{$productCode}', "
                    ."Chain Code: '{$chainCode}', Month: '{$month}', Year: '{$year}') also found on row #{$previousRow}."
                );
            }
            $this->seen[$uniqueKey] = $rowNumber;

            $product = Product::where('code', $productCode)->first();
            if (!$product) {
                throw new \Exception("Product Code '{$productCode}' does not exist.");
            }

            $chain = Chain::where('code', $chainCode)->first();
            if (!$chain) {
                throw new \Exception("Chain Code '{$chainCode}' does not exist.");
            }

            // $region = Region::where('code', $row['region_code'] ?? null)->first();
            // $format = Format::where('code', $row['format_code'] ?? null)->first();

            $region = $row['region_code'] ? Region::where('code', $row['region_code'])->first() : null;
            if ($row['region_code'] && !$region) {
                throw new \Exception("Row #{$rowNumber}: Region Code '{$row['region_code']}' not found.");
            }

            $format = $row['format_code'] ? Format::where('code', $row['format_code'])->first() : null;
            if ($row['format_code'] && !$format) {
                throw new \Exception("Row #{$rowNumber}: Format Code '{$row['format_code']}' not found.");
            }

            if ($month < 1 || $month > 12) {
                throw new \Exception("Invalid month value '{$month}'. Must be 1-12.");
            }
            if ($year < 1900 || $year > 9999) {
                throw new \Exception("Invalid year value '{$year}'. Must be 1900-9999.");
            }

            $mdfDate       = $this->parseDate($row['mdf_date']       ?? null);
            $expiryDate    = $this->parseDate($row['expiry_date']    ?? null);
            $damageQtyDate = $this->parseDate($row['damage_qty_date']?? null);

            $alreadyMapped = ProductMapping::where('product_id', $product->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('chain_id', '!=', $chain->id)
                ->first();
            if ($alreadyMapped) {
                $otherChain = Chain::find($alreadyMapped->chain_id);
                throw new \Exception(
                    "Row #{$rowNumber}: Product '{$productCode}' is already mapped to chain '{$otherChain?->code}' for month '{$month}' and year '{$year}'. "
                    ."A product can only be mapped to one chain per month."
                );
            }

            // Check if mapping already exists for (product_id, chain_id, month, year)
            $existing = ProductMapping::where([
                'product_id' => $product->id,
                'chain_id'   => $chain->id,
                'month'      => $month,
                'year'       => $year,
            ])->first();

            $attributes = [
                'product_id'         => $product->id,
                'chain_id'           => $chain->id,
                'region_id'          => $region?->id,
                'format_id'          => $format?->id,
                'maq'                => $row['maq'] ?? null,
                'focus_product'      => $row['focus_product'] ?? null,

                'is_msl_enabled'              => strtolower(trim($row['is_msl_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_primary_shelf_enabled'    => strtolower(trim($row['is_primary_shelf_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_primary_shelf_required'   => strtolower(trim($row['is_primary_shelf_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_mfd_in_ps_enabled'        => strtolower(trim($row['is_mfd_in_ps_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_mfd_in_ps_required'       => strtolower(trim($row['is_mfd_in_ps_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_expiry_date_in_ps_enabled'=> strtolower(trim($row['is_expiry_date_in_ps_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_expiry_date_in_ps_required'=>strtolower(trim($row['is_expiry_date_in_ps_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_damage_qty_in_ps_enabled' => strtolower(trim($row['is_damage_qty_in_ps_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_damage_qty_in_ps_required'=> strtolower(trim($row['is_damage_qty_in_ps_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_sales_enabled'            => strtolower(trim($row['is_sales_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_sales_required'           => strtolower(trim($row['is_sales_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_sales_return_enabled'     => strtolower(trim($row['is_sales_return_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_stock_transfer_enabled'   => strtolower(trim($row['is_stock_transfer_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_ba_enabled'               => strtolower(trim($row['is_ba_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_ba_required'              => strtolower(trim($row['is_ba_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_adhoc_enabled'            => strtolower(trim($row['is_adhoc_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_adhoc_required'           => strtolower(trim($row['is_adhoc_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_primary_shelf_csp_enabled'=> strtolower(trim($row['is_primary_shelf_csp_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_primary_shelf_csp_required'=>strtolower(trim($row['is_primary_shelf_csp_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_sales_csp_enabled'        => strtolower(trim($row['is_sales_csp_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_sales_csp_required'       => strtolower(trim($row['is_sales_csp_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_ba_csp_enabled'           => strtolower(trim($row['is_ba_csp_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_ba_csp_required'          => strtolower(trim($row['is_ba_csp_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_sampling_sales_enabled'   => strtolower(trim($row['is_sampling_sales_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_sampling_sales_required'  => strtolower(trim($row['is_sampling_sales_required'] ?? '')) === 'yes' ? 1 : 0,

                'month'            => $month,
                'year'             => $year,
                'mdf_date'         => $mdfDate,
                'expiry_date'      => $expiryDate,
                'damage_qty_date'  => $damageQtyDate,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                $attributes['product_id']       = $product->id;
                return \App\Models\ProductMapping::create($attributes);
            }

        } catch (\Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }

        return null;
    }

    /**
    * parseDate method: handles numeric Excel date or string date
    * Returns "d-m-Y" format (e.g. "31-03-2025") or null if empty
    */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = trim($value);

        // (A) If numeric => Excel numeric date
        if (is_numeric($value)) {
            try {
                // base date 1899-12-30
                $base = Carbon::createFromDate(1899, 12, 30)->startOfDay();
                // No offset subtracted:
                $date = $base->addDays($value);
                return $date->format('d-m-Y');
            } catch (\Exception $e) {
                throw new \Exception("Cannot parse numeric Excel date '$value'.");
            }
        }

        // (B) Try "d-M-y" (e.g. "31-Mar-25")
        try {
            $date = Carbon::createFromFormat('d-M-y', $value);
            return $date->format('d-m-Y');
        } catch (\Exception $e) {
            // (C) Fallback parse (any recognized format)
            try {
                $date = Carbon::parse($value);
                return $date->format('d-m-Y');
            } catch (\Exception $ex) {
                throw new \Exception("Cannot parse date '$value'.");
            }
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            '*.product_code'   => 'required|exists:products,code',
            '*.chain_code'     => 'required|exists:chains,code',
            '*.region_code'    => 'nullable|exists:regions,code',
            '*.format_code'    => 'nullable|exists:formats,code',
            '*.maq'            => 'required|max:200',
            '*.focus_product'  => 'nullable|max:200',

            // If you truly want no strict check for True/False, "required" is enough
            '*.is_msl_enabled' => 'required',
            '*.is_primary_shelf_enabled' => 'required',
            '*.is_primary_shelf_required' => 'required',
            '*.is_mfd_in_ps_enabled' => 'required',
            '*.is_mfd_in_ps_required' => 'required',
            '*.is_expiry_date_in_ps_enabled' => 'required',
            '*.is_expiry_date_in_ps_required' => 'required',
            '*.is_damage_qty_in_ps_enabled' => 'required',
            '*.is_damage_qty_in_ps_required' => 'required',
            '*.is_sales_enabled' => 'required',
            '*.is_sales_required' => 'required',
            '*.is_sales_return_enabled' => 'required',
            '*.is_stock_transfer_enabled' => 'required',
            '*.is_ba_enabled' => 'required',
            '*.is_ba_required' => 'required',
            '*.is_adhoc_enabled' => 'required',
            '*.is_adhoc_required' => 'required',
            '*.is_primary_shelf_csp_enabled' => 'required',
            '*.is_primary_shelf_csp_required' => 'required',
            '*.is_sales_csp_enabled' => 'required',
            '*.is_sales_csp_required' => 'required',
            '*.is_ba_csp_enabled' => 'required',
            '*.is_ba_csp_required' => 'required',
            '*.is_sampling_sales_enabled' => 'required',
            '*.is_sampling_sales_required' => 'required',

            '*.month' => 'required|integer|between:1,12',
            '*.year'  => 'required|integer|min:1900|max:9999',

            '*.mdf_date'        => 'nullable',
            '*.expiry_date'     => 'nullable',
            '*.damage_qty_date' => 'nullable',
        ];
    }
}
