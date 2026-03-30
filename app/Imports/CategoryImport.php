<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class CategoryImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {

            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $code     = trim($row['code'] ?? '');

            $uniqueKey = strtolower($code);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Code: '{$code}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $value = $row['category_type']; // Example input
            $type_key = array_search($value, config('system.category_types'));

            if ($type_key === false) {
                throw new \Exception("Row #{$rowNumber}: Invalid category type value '{$value}'");
            }

            // 🔍 Check if existing
            $existing = Category::where([
                'code'  =>  $row['code'],

            ])->first();

            $attributes = [
                'name'       => $row['name'],
                'category_type'       => $type_key,
                'status'     => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                // ✅ If record exists, update it and set modified_by
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                // ✅ If new, set created_by
                $attributes['created_by'] = auth()->id();
                $attributes['code']       = $row['code'];
                return \App\Models\Category::create($attributes);
            }

            return $category_type;
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
            '*.code' => 'required|max:200',
            '*.name' => 'required|max:200',
            '*.category_type' => 'required|max:200',
            '*.status' => 'required',
        ];
    }
}
