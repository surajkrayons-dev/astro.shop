<?php

namespace App\Imports;

use App\Models\SaleTarget;
use App\Models\Store;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class SaleTargetImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow++;
            $storeCode = trim(strtolower($row['store_code'] ?? ''));
            $targetType = trim($row['target_type'] ?? '');
            $overallSaleTarget = trim($row['overall_sale_target'] ?? '');
            $month = (int) $row['month'];
            $year = (int) $row['year'];

            $uniqueKey = "{$storeCode}|{$targetType}|{$overallSaleTarget}|{$month}|{$year}";
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate (Store: '{$storeCode}', Target Type: '{$targetType}, Month: '{$month}, Year: '{$year}') also found on row #{$previousRow}.");
            }
            $this->seen[$uniqueKey] = $rowNumber;

            $store = Store::whereRaw('LOWER(TRIM(code)) = ?', [$storeCode])->first();
            if (!$store) {
                throw new \Exception("Store not found for '{$row['store_code']}'");
            }

            $attributes = [
                'store_id'              => $store->id,
                'target_type'           => $targetType,
                'overall_sale_target'   => $overallSaleTarget ?: null,
                'month'                 => $month,
                'year'                  => $year,
                'focus_product1_target' => $row['focus_product1_target'] ?? null,
                'focus_product2_target' => $row['focus_product2_target'] ?? null,
                'focus_product3_target' => $row['focus_product3_target'] ?? null,
            ];

            $existing = SaleTarget::where('store_id', $store->id)
                ->where('target_type', $targetType)
                ->where('month', $month)
                ->where('year', $year)
                ->when(!blank($overallSaleTarget), function ($query) use ($overallSaleTarget) {
                    return $query->where('overall_sale_target', $overallSaleTarget);
                }, function ($query) {
                    return $query->whereNull('overall_sale_target');
                })
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
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.store_code'              => 'required|max:200',
            '*.month'                   => 'required|between:1,12',
            '*.year'                    => 'required|integer|min:1900|max:9999',
            '*.target_type'             => 'nullable|max:200',
            '*.overall_sale_target'     => 'required|max:200',
            '*.focus_product1_target'   => 'nullable|max:200',
            '*.focus_product2_target'   => 'nullable|max:200',
            '*.focus_product3_target'   => 'nullable|max:200',
        ];
    }
}
