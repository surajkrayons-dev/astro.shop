<?php

namespace App\Imports;

use App\Models\StoreKycMapping;
use App\Models\Store;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class StoreKycMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $storeCode = trim($row['store_code'] ?? '');

            $uniqueKey = strtolower($storeCode.'|'.$row['month'].'|'.$row['year']);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Store Code: '{$storeCode}', Month: '{$row['month']}', Year: '{$row['year']}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            Log::info("Importing row: " . json_encode($row));

            $store = Store::where('code', $storeCode)->first();
            if (!$store) {
                throw new \Exception("Store Code '{$storeCode}' does not exist.");
            }

            $existing = StoreKycMapping::where([
                'store_id' => $store->id,
                'month'    => (int) $row['month'],
                'year'     => (int) $row['year'],
            ])->first();

            $attributes = [
                'store_id'      => $store->id,
                'month'         => (int) $row['month'],
                'year'          => (int) $row['year'],
                'is_required'   => strtolower(trim($row['is_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_acc_passbook_required'       => strtolower(trim($row['is_acc_passbook_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_pan_card_required'           => strtolower(trim($row['is_pan_card_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_aadhaar_card_required'       => strtolower(trim($row['is_aadhaar_card_required'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                return StoreKycMapping::create($attributes);
            }
        } catch (\Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }

        return null;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            '*.store_code'  => 'required|exists:stores,code',
            '*.month'       => 'required|between:1,12',
            '*.year'        => 'required|integer|min:1900|max:9999',
            '*.is_required' => 'required',
            '*.is_acc_passbook_required'     => 'required',
            '*.is_pan_card_required'         => 'required',
            '*.is_aadhaar_card_required'     => 'required',
        ];
    }
}
