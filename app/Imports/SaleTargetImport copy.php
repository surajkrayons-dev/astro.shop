<?php

namespace App\Imports;

use App\Models\SaleTarget;
use App\Models\Store;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class SaleTargetImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $storeCode      = trim(strtolower($row['store_code']));
            $targetType     = trim($row['target_type'] ?? '');
            $overallTarget  = trim($row['overall_target'] ?? '');
            $month          = (int) $row['month'];
            $year           = (int) $row['year'];

            $uniqueKey = strtolower("{$storeCode}|{$targetType}|{$overallTarget}|{$month}|{$year}");

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Store: '{$storeCode}', "."Target Type: '{$targetType}', "."Overall Target: '{$overallTarget}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            // $store = Store::where('name', $row['store'])->first();
            $storeCode = trim(strtolower($row['store_code']));
            $store = Store::whereRaw('LOWER(TRIM(code)) = ?', [$storeCode])->first();
            if (!$store) {
                throw new \Exception("Store not found for '{$row['store_code']}'");
            }

            $attributes = [
                'store_id'         => $store->id,
                'target_type'      => $row['target_type'],
                'overall_target'   => $row['overall_target'],
                'month'            => (int) $row['month'],
                'year'             => (int) $row['year'],
                'focus_product1_target' => $row['focus_product1_target'] ?? null,
                'focus_product2_target' => $row['focus_product2_target'] ?? null,
                'focus_product3_target' => $row['focus_product3_target'] ?? null,
            ];

            $existing = SaleTarget::where('store_id', $store->id)
                ->where('target_type', $row['target_type'])
                ->where('overall_target', $row['overall_target'])
                ->where('month', $month)
                ->where('year', $year)
                ->first();

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                return SaleTarget::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }

        return null;
    }

    public function rules(): array
    {
        return [
            '*.store_code'                  => 'required|max:200',
            '*.month'                       => 'required|between:1,12',
            '*.year'                        => 'required|integer|min:1900|max:9999',
            '*.target_type'                 => 'required|max:200',
            '*.overall_target'              => 'required|max:200',
            '*.focus_product1_target'       => 'nullable|max:200',
            '*.focus_product2_target'       => 'nullable|max:200',
            '*.focus_product3_target'       => 'nullable|max:200',
        ];
    }
}
