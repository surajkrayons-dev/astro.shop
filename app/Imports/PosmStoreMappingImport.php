<?php

namespace App\Imports;

use App\Models\PosmStoreMapping;
use App\Models\Posm;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class PosmStoreMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $posmCode = trim($row['posm_code'] ?? '');
            $storeCode = trim($row['store_code'] ?? '');
            $storeAction = trim($row['store_action'] ?? '');
            $month = (int) $row['month'];
            $year = (int) $row['year'];

            $uniqueKey = strtolower("{$posmCode}|{$storeCode}|{$storeAction}|{$month}|{$year}");

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Posm: '{$posmCode}', Store: '{$storeCode}', Store Action: '{$storeAction}', Month: {$month}, Year: {$year}) also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $posm = Posm::where('code', $posmCode)->first();
            if (!$posm) {
                throw new \Exception("Row #{$rowNumber}: Posm '{$posmCode}' does not exist in DB.");
            }

            $store = Store::where('code', $storeCode)->first();
            if (!$store) {
                throw new \Exception("Row #{$rowNumber}: Store '{$storeCode}' does not exist in DB.");
            }

            $startDate = $this->parseDate($row['start_date'] ?? null);
            $endDate = $this->parseDate($row['end_date'] ?? null);

            $month = $row['month'];
            if ($month < 1 || $month > 12) {
                throw new \Exception("Row #{$rowNumber}: Invalid month '{$month}'. Must be 1-12.");
            }

            $year = $row['year'];
            if ($year < 1900 || $year > 9999) {
                throw new \Exception("Row #{$rowNumber}: Invalid year '{$year}'. Must be 1900-9999.");
            }

            $type_key_original = trim($row['store_action']);
            $visibilityTypes = config('system.visibility_types');

            $matchedKey = null;
            foreach ($visibilityTypes as $key => $label) {
                if (strtolower($label) === strtolower($type_key_original)) {
                    $matchedKey = $key;
                    break;
                }
            }

            if (!$matchedKey) {
                foreach ($visibilityTypes as $key => $label) {
                    similar_text(strtolower($type_key_original), strtolower($label), $percent);
                    if ($percent > 80) {
                        $matchedKey = $key;
                        break;
                    }
                }
            }

            if (!$matchedKey) {
                throw new \Exception("Row #{$rowNumber}: Invalid Store Action value '{$type_key_original}'");
            }

            $type_key = $matchedKey;


            $attributes = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'month' => $month,
                'year' => $year,
                'store_action' => $type_key,
                'is_required' => ($row['is_required'] ?? '') === 'Yes' ? 1 : 0,
                'is_photo_required' => ($row['is_photo_required'] ?? '') === 'Yes' ? 1 : 0,
                'is_photo1_required' => ($row['is_photo1_required'] ?? '') === 'Yes' ? 1 : 0,
                'is_photo2_required' => ($row['is_photo2_required'] ?? '') === 'Yes' ? 1 : 0,
                'is_photo3_required' => ($row['is_photo3_required'] ?? '') === 'Yes' ? 1 : 0,
                'is_scanner_enabled' => ($row['is_scanner_enabled'] ?? '') === 'Yes' ? 1 : 0,
                'is_scanner_required' => ($row['is_scanner_required'] ?? '') === 'Yes' ? 1 : 0,
            ];

            $existingMapping = PosmStoreMapping::where('posm_id', $posm->id)
                ->where('store_id', $store->id)
                ->where('store_action', $type_key)
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existingMapping) {
                $attributes['modified_by'] = auth()->id();
                $existingMapping->update($attributes);
                return $existingMapping;
            } else {
                $attributes['posm_id'] = $posm->id;
                $attributes['store_id'] = $store->id;
                $attributes['created_by'] = auth()->id();
                return PosmStoreMapping::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    /**
     * Parses Excel cell value to a standard date format.
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // Excel numeric date
        if (is_numeric($value)) {
            try {
                return Carbon::createFromDate(1899, 12, 30)->addDays($value)->format('d-m-Y');
            } catch (\Exception $e) {
                throw new \Exception("Cannot parse numeric Excel date '$value'.");
            }
        }

        // Try standard formats
        try {
            return Carbon::createFromFormat('d-M-y', $value)->format('d-m-Y');
        } catch (\Exception $e) {
            try {
                return Carbon::parse($value)->format('d-m-Y');
            } catch (\Exception $ex) {
                throw new \Exception("Cannot parse date '$value'.");
            }
        }
    }

    /**
     * Validation rules for each row.
     */
    public function rules(): array
    {
        return [
            '*.posm_code' => 'required|exists:posms,code',
            '*.store_code' => 'required|exists:stores,code',
            '*.start_date' => 'nullable',
            '*.end_date' => 'nullable',
            '*.month' => 'required|between:1,12',
            '*.year' => 'required|integer|min:1900|max:9999',
            '*.store_action' => 'required|max:200',
            '*.is_required' => 'required',
            '*.is_photo_required' => 'required',
            '*.is_photo1_required' => 'required',
            '*.is_photo2_required' => 'required',
            '*.is_photo3_required' => 'required',
            '*.is_scanner_enabled' => 'required',
            '*.is_scanner_required' => 'required',
        ];
    }
}
