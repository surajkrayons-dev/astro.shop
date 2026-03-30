<?php

namespace App\Imports;

use App\Models\Region;
use App\Models\Format;
use App\Models\PromotionProduct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class PromotionProductImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $code = trim($row['promotion_product_code'] ?? '');
            $uniqueKey = strtolower($code);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Code: '{$code}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $attributes = [
                'code'   => $code,
                'name'   => trim($row['promotion_product_name'] ?? ''),
                'status' => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            $existing = PromotionProduct::where('code', $attributes['code'])->first();

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                return new PromotionProduct($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.promotion_product_code' => 'required|max:200',
            '*.promotion_product_name' => 'required|max:200',
            '*.status' => 'required',
        ];
    }
}
