<?php

namespace App\Imports;

use App\Models\PosmProductMapping;
use App\Models\Posm;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class PosmProductMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $posmCode        = trim($row['posm_code'] ?? '');
            $posmBrandCode   = trim($row['posm_brand_code'] ?? '');
            $productCode     = trim($row['product_code'] ?? '');

            $uniqueKey = strtolower($posmCode . '|' . $posmBrandCode . '|' . $productCode);
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate combination (POSM: '{$posmCode}', Brand: '{$posmBrandCode}', Product: '{$productCode}') also found at row #{$previousRow}.");
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $posm = Posm::where('code', $posmCode)->first();
            if (!$posm) throw new \Exception("POSM not found for '{$posmCode}'");

            $posmBrand = Brand::where('code', $posmBrandCode)->first();
            if (!$posmBrand) throw new \Exception("POSM Brand not found for '{$posmBrandCode}'");

            $product = Product::where('code', $productCode)->first();
            if (!$product) throw new \Exception("Product not found for '{$productCode}'");

            $attributes = [
                'is_paid_visibility_enabled'  => $this->parseBool($row['is_paid_visibility_enabled']),
                'is_paid_visibility_required' => $this->parseBool($row['is_paid_visibility_required']),
                'is_tot_visibility_enabled'   => $this->parseBool($row['is_tot_visibility_enabled']),
                'is_tot_visibility_required'  => $this->parseBool($row['is_tot_visibility_required']),
                'is_visibility_enabled'       => $this->parseBool($row['is_visibility_enabled']),
                'is_visibility_required'      => $this->parseBool($row['is_visibility_required']),
            ];

            $existing = PosmProductMapping::where([
                'posm_id'       => $posm->id,
                'posm_brand_id' => $posmBrand->id,
                'product_id'    => $product->id,
            ])->first();

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['posm_id']       = $posm->id;
                $attributes['posm_brand_id'] = $posmBrand->id;
                $attributes['product_id']    = $product->id;
                $attributes['created_by']    = auth()->id();
                return PosmProductMapping::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    protected function parseBool($value): int
    {
        return in_array(strtolower(trim($value)), ['yes', '1']) ? 1 : 0;
    }

    public function rules(): array
    {
        return [
            '*.posm_code'                 => 'required|max:200',
            '*.posm_brand_code'          => 'required|max:200',
            '*.product_code'             => 'required|max:200',
            '*.is_paid_visibility_enabled'  => 'required',
            '*.is_paid_visibility_required' => 'required',
            '*.is_tot_visibility_enabled'   => 'required',
            '*.is_tot_visibility_required'  => 'required',
            '*.is_visibility_enabled'       => 'required',
            '*.is_visibility_required'      => 'required',
        ];
    }

}
