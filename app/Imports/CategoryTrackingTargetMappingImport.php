<?php

namespace App\Imports;

use App\Models\CategoryTracking;
use App\Models\CategoryTrackingTargetMapping;
use App\Models\Store;
use App\Models\Region;
use App\Models\Format;
use App\Models\StoreUserMapping;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class CategoryTrackingTargetMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $categoryTrackingCode = trim($row['category_tracking_code'] ?? '');
            $storeCode            = trim($row['store_code'] ?? '');
            $regioncode           = trim($row['region_code'] ?? '');
            $formatcode           = trim($row['format_code'] ?? '');
            $month                = (int) $row['month'];
            $year                 = (int) $row['year'];

            $uniqueKey = strtolower($categoryTrackingCode . '|' . $storeCode . '|' . $regioncode . '|' . $formatcode . '|' . $month . '|' . $year);
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate entry (Category Tracking Code: '{$categoryTrackingCode}', "
                    . "Store Code: '{$storeCode}', Region Code: '{$regioncode}', Format Code: '{$formatcode}', Month: '{$month}', Year: '{$year}') also found on row #{$previousRow}."
                );
            }
            $this->seen[$uniqueKey] = $rowNumber;

            $categoryTracking = CategoryTracking::where('code', $categoryTrackingCode)->first();
            if (!$categoryTracking) {
                throw new \Exception("Row #{$rowNumber}: Category Tracking not found for code '{$categoryTrackingCode}'");
            }

            $store = Store::where('code', $storeCode)->first();
            if (!$store) {
                throw new \Exception("Row #{$rowNumber}: Store not found for code '{$storeCode}'");
            }

            $latestMapping = StoreUserMapping::where('store_id', $store->id)
                ->latest('id')
                ->first();

            if (!$latestMapping) {
                throw new \Exception("Row #{$rowNumber}: No latest StoreUserMapping found for store '{$storeCode}'");
            }

            $storeDates = is_string($latestMapping->date)
                ? json_decode($latestMapping->date, true)
                : $latestMapping->date;

            $allowedDates = [];
            foreach ($storeDates as $dayKey => $values) {
                if (is_array($values) && in_array('yes', array_map('strtolower', $values))) {
                    if (preg_match('/day(\d+)/', $dayKey, $match)) {
                        $allowedDates[] = (int) $match[1];
                    }
                }
            }

            $frequency = implode(', ', $allowedDates);

            $region = $row['region_code'] ? Region::where('code', $row['region_code'])->first() : null;
            if ($row['region_code'] && !$region) {
                throw new \Exception("Row #{$rowNumber}: Region Code '{$row['region_code']}' not found.");
            }

            $format = $row['format_code'] ? Format::where('code', $row['format_code'])->first() : null;
            if ($row['format_code'] && !$format) {
                throw new \Exception("Row #{$rowNumber}: Format Code '{$row['format_code']}' not found.");
            }

            $month = $row['month'];
            if ($month < 1 || $month > 12) {
                throw new \Exception("Row #{$rowNumber}: Invalid month '{$month}'. Must be 1-12.");
            }

            $year = $row['year'];
            if ($year < 1900 || $year > 9999) {
                throw new \Exception("Row #{$rowNumber}: Invalid year '{$year}'. Must be 1900-9999.");
            }

            $mappingKeys = [
                'category_tracking_id' => $categoryTracking->id,
                'store_id'             => $store->id,
                'region_id'            => $region?->id,
                'format_id'            => $format?->id,
                'month'                => $month,
                'year'                 => $year,
            ];

            $attributes = [
                'target'             => $row['target'],
                'frequency'          => $frequency,
                'month'              => $month,
                'year'               => $year,
                'is_required'        => strtolower(trim($row['is_required'])) === 'yes' ? 1 : 0,
                'is_photo_required'  => strtolower(trim($row['is_photo_required'])) === 'yes' ? 1 : 0,
                'is_facing_enabled'  => strtolower(trim($row['is_facing_enabled'])) === 'yes' ? 1 : 0,
            ];

            $existing = CategoryTrackingTargetMapping::where($mappingKeys)->first();

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes = array_merge($mappingKeys, $attributes, [
                    'created_by' => auth()->id(),
                ]);
                return CategoryTrackingTargetMapping::create($attributes);
            }
        } catch (Throwable $th) {
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.category_tracking_code' => 'required|max:200',
            '*.store_code'             => 'required|max:200',
            '*.region_code'            => 'required|exists:regions,code',
            '*.format_code'            => 'required|exists:formats,code',
            '*.target'                 => 'required|max:200',
            '*.month'                  => 'required|between:1,12',
            '*.year'                   => 'required|integer|min:1900|max:9999',
            '*.is_required'            => 'required',
            '*.is_photo_required'      => 'required',
            '*.is_facing_enabled'      => 'required',
        ];
    }
}
