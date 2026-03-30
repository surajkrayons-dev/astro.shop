<?php

namespace App\Imports;

use App\Models\CompetitionProductMapping;
use App\Models\CompetitionProduct;
use App\Models\Store;
use App\Models\Chain;
use App\Models\Region;
use App\Models\Format;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class CompetitionProductMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $storeCode = trim($row['store_code'] ?? '');
            $chainCode = trim($row['chain_code'] ?? '');
            $competitionProductCode = trim($row['competition_product_code'] ?? '');
            $month = (int) $row['month'];
            $year = (int) $row['year'];

            $uniqueKey = strtolower("{$storeCode}|{$chainCode}|{$competitionProductCode}|{$month}|{$year}");

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Store Code: '{$storeCode}', Chain Code: '{$chainCode}', Competition Product Code: '{$competitionProductCode}, Month: '{$month}, Year: '{$year}') also found on row #{$previousRow}."
                );
            }
            $this->seen[$uniqueKey] = $rowNumber;

            $store = Store::where('code', $storeCode)->first();
            if (!$store) {
                throw new \Exception("Store not found for code '{$storeCode}'");
            }

            $chain = Chain::where('code', $chainCode)->first();
            if (!$chain) {
                throw new \Exception("Row #{$rowNumber}: Chain with code '{$chainCode}' does not exist in DB.");
            }

            $product = CompetitionProduct::where('code', $competitionProductCode)->first();
            if (!$product) {
                throw new \Exception("Row #{$rowNumber}: Competition Product with code '{$competitionProductCode}' does not exist in DB.");
            }

            $region = Region::where('code', $row['region_code'])->first();
            if (!$region) {
                throw new \Exception("Region Code '{$row['region_code']}' does not exist.");
            }

            $format = Format::where('code', $row['format_code'])->first();
            if (!$format) {
                throw new \Exception("Format Code '{$row['format_code']}' does not exist.");
            }

            $month = $row['month'];
            if ($month < 1 || $month > 12) {
                throw new \Exception("Row #{$rowNumber}: Invalid month '{$month}'. Must be 1-12.");
            }

            $year = $row['year'];
            if ($year < 1900 || $year > 9999) {
                throw new \Exception("Row #{$rowNumber}: Invalid year '{$year}'. Must be 1900-9999.");
            }

            $attributes = [
                'format_id'                   => $format->id,
                'region_id'                   => $region->id,
                'month'                       => $month,
                'year'                        => $year,
                'is_required'                 => ($row['is_required'] ?? '') === 'Yes' ? 1 : 0,
                'is_mrp_enabled'              => ($row['is_mrp_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_selling_price_enabled'    => ($row['is_selling_price_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_mdf_enabled'              => ($row['is_mdf_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_facing_enabled'           => ($row['is_facing_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_display_available'        => ($row['is_display_available'] ?? '') === 'Yes' ? 1 : 0,
                'is_promo_running_enabled'    => ($row['is_promo_running_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_night_enabled'            => ($row['is_night_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_pack_enabled'             => ($row['is_pack_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_stock_on_display_enabled' => ($row['is_stock_on_display_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_photo_enabled'            => ($row['is_photo_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_photo_required'           => ($row['is_photo_required'] ?? '') === 'Yes' ? 1 : 0,
            ];

            $existingMapping = CompetitionProductMapping::where('chain_id', $chain->id)
                ->where('store_id', $store->id)
                ->where('competition_product_id', $product->id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingMapping) {
                $attributes['modified_by'] = auth()->id();
                $existingMapping->update($attributes);
                return $existingMapping;
            } else {
                $attributes['store_id'] = $store->id;
                $attributes['chain_id'] = $chain->id;
                $attributes['competition_product_id'] = $product->id;
                $attributes['created_by'] = auth()->id();
                return CompetitionProductMapping::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.store_code'                     => 'required|exists:stores,code',
            '*.chain_code'                     => 'required|exists:chains,code',
            '*.competition_product_code'       => 'required|exists:competition_products,code',
            '*.region_code'                    => 'required|exists:regions,code',
            '*.format_code'                    => 'required|exists:formats,code',
            '*.month'                          => 'required|between:1,12',
            '*.year'                           => 'required|integer|min:1900|max:9999',
            '*.is_required'                    => 'required',
            '*.is_mrp_enabled'                 => 'required',
            '*.is_selling_price_enabled'       => 'required',
            '*.is_mdf_enabled'                 => 'required',
            '*.is_facing_enabled'              => 'required',
            '*.is_display_available'           => 'required',
            '*.is_promo_running_enabled'       => 'required',
            '*.is_night_enabled'               => 'required',
            '*.is_pack_enabled'                => 'required',
            '*.is_stock_on_display_enabled'    => 'required',
            '*.is_photo_enabled'               => 'required',
            '*.is_photo_required'              => 'required',
        ];
    }
}
