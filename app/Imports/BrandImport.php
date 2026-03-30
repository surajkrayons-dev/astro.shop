<?php

namespace App\Imports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class BrandImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $brandCode = trim($row['brand_code'] ?? '');

            if (isset($this->seen[$brandCode])) {
                $previousRow = $this->seen[$brandCode];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate Brand Code '{$brandCode}' also found on row #{$previousRow}."
                );
            }

            $this->seen[$brandCode] = $rowNumber;

            $typeKey = array_search($row['brand_type'], config('system.brand_types'));
            if ($typeKey === false) {
                throw new \Exception("Invalid brand type value '{$row['brand_type']}'");
            }

            // 🔍 Check if existing
            $existing = Brand::where([
                'code'  =>  $row['brand_code'],

            ])->first();

            $attributes = [
                'name'          => $row['brand_name'],
                'brand_type'    => $typeKey,
                'status'        => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                // ✅ If record exists, update it and set modified_by
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                // ✅ If new, set created_by
                $attributes['created_by'] = auth()->id();
                $attributes['code']       = $row['brand_code'];
                return \App\Models\Brand::create($attributes);
            }

            return $brand_type;
        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }

        return null;
    }

    // Validation rules for each row
    public function rules(): array
    {
        return [
            '*.brand_code' => 'required|max:200',
            '*.brand_name' => 'required|max:200',
            '*.brand_type' => 'required|max:200',
            '*.status'     => 'required',
        ];
    }
}
