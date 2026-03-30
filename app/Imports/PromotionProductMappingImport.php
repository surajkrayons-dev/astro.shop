<?php

namespace App\Imports;

use App\Models\PromotionProduct;
use App\Models\Store;
use App\Models\Chain;
use App\Models\Category;
use App\Models\Format;
use App\Models\Region;
use App\Models\Brand;
use App\Models\State;
use App\Models\PromotionProductMapping;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class PromotionProductMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $promotionProductCode = trim($row['promotion_product_code'] ?? '');
            $storeCode            = trim($row['store_code'] ?? '');
            $chainCode            = trim($row['chain_code'] ?? '');
            $month                = (int) $row['month'];
            $year                 = (int) $row['year'];

            $uniqueKey = strtolower("{$promotionProductCode}|{$storeCode}|{$chainCode}|{$month}|{$year}");

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Promotion Product Code: '{$promotionProductCode}', Store Code: '{$storeCode}', Chain Code: '{$chainCode}', Month: '{$month}', Year: '{$year}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $promotionProduct = PromotionProduct::where('code', $promotionProductCode)->first();
            if (!$promotionProduct) {
                throw new \Exception("Promotion product not found for code '{$promotionProductCode}'");
            }

            $store = Store::where('code', $storeCode)->first();
            if (!$store) {
                throw new \Exception("Store not found for code '{$storeCode}'");
            }

            $chain = Chain::where('code', $chainCode)->first();
            if (!$chain) {
                throw new \Exception("Chain not found for code '{$chainCode}'");
            }

            $productCategory = Category::where('code', $row['product_category_code'] ?? '')->first();
            if (!$productCategory) {
                throw new \Exception("Product category not found for code '{$row['product_category_code']}'");
            }

            $productBrand = Brand::where('code', $row['product_brand_code'] ?? '')->first();
            if (!$productBrand) {
                throw new \Exception("Product brand not found for code '{$row['product_brand_code']}'");
            }

            $state = null;
            $stateName = trim($row['state'] ?? '');
            if ($stateName !== '') {
                $state = \App\Models\State::where('name', $stateName)->value('id');
            }

            $region = $row['region_code'] ? Region::where('code', $row['region_code'])->first() : null;
            if ($row['region_code'] && !$region) {
                throw new \Exception("Row #{$rowNumber}: Region Code '{$row['region_code']}' not found.");
            }

            $format = $row['format_code'] ? Format::where('code', $row['format_code'])->first() : null;
            if ($row['format_code'] && !$format) {
                throw new \Exception("Row #{$rowNumber}: Format Code '{$row['format_code']}' not found.");
            }

            $offerTypeInput = strtolower(trim($row['offer_type']));
            $allowedKeys = array_keys(config('system.target_format'));

            if (!in_array($offerTypeInput, $allowedKeys)) {
                throw new \Exception(
                    "Row #{$rowNumber}: Invalid value for 'offer_type': '{$row['offer_type']}'. Allowed values: " . implode(', ', $allowedKeys)
                );
            }

            $attributes = [
                'product_category_id'       => $productCategory->id,
                'chain_id'                  => $chain->id,
                'product_brand_id'          => $productBrand->id,
                'region_id'                 => $region?->id,
                'format_id'                 => $format?->id,
                'offer_type'                => $offerTypeInput,
                'offer'                     => $row['offer'] ?? null,
                'state_id'                  => $state ?? null,
                'month'                     => (int) $row['month'],
                'year'                      => (int) $row['year'],
                'is_required'               => strtolower(trim($row['is_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_photo_required'         => strtolower(trim($row['is_photo_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_enter_stock_enabled'    => strtolower(trim($row['is_enter_stock_enabled'] ?? '')) === 'yes' ? 1 : 0,
                'is_enter_stock_required'   => strtolower(trim($row['is_enter_stock_required'] ?? '')) === 'yes' ? 1 : 0,
            ];
            // dd($attributes);

            $existing = PromotionProductMapping::where('promotion_product_id', $promotionProduct->id)
                ->where('store_id', $store->id)
                ->where('chain_id', $chain->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['promotion_product_id'] = $promotionProduct->id;
                $attributes['store_id'] = $store->id;
                $attributes['chain_id'] = $chain->id;
                $attributes['created_by'] = auth()->id();
                return new PromotionProductMapping($attributes);
            }
        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }


    public function rules(): array
    {
        return [
            '*.promotion_product_code'    => 'required|max:200',
            '*.store_code'                => 'required|max:200',
            '*.chain_code'                => 'required|max:200',
            '*.product_category_code'     => 'required|max:200',
            '*.product_brand_code'        => 'required|max:200',
            '*.region_code'               => 'nullable|max:200',
            '*.format_code'               => 'nullable|max:200',
            '*.offer_type'                => 'required|max:200',
            '*.offer'                     => 'required|max:200',
            '*.state'                     => 'nullable|max:200',
            '*.month'                     => 'required|between:1,12',
            '*.year'                      => 'required|integer|min:1900|max:9999',
            '*.is_required'               => 'required',
            '*.is_photo_required'         => 'required',
            '*.is_enter_stock_enabled'    => 'required',
            '*.is_enter_stock_required'   => 'required',
        ];
    }
}
