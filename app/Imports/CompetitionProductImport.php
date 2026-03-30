<?php

namespace App\Imports;

use App\Models\CompetitionProduct;
use App\Models\Category;
use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class CompetitionProductImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $code = trim($row['code'] ?? '');
            $uniqueKey = strtolower($code);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Code: '{$code}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $category = Category::where([
                ['code', $row['competition_category_code']],
                ['category_type', 'competition_category']
            ])->first();

            if (!$category) {
                throw new \Exception("Row #{$rowNumber}: Competition Category Code '{$row['competition_category_code']}' not found.");
            }

            $brand = Brand::where([
                ['code', $row['competition_brand_code']],
                ['brand_type', 'competition_brand']
            ])->first();

            if (!$brand) {
                throw new \Exception("Row #{$rowNumber}: Competition Brand Code '{$row['competition_brand_code']}' not found.");
            }

            $mrpValue = $row['mrp'] ?? null;
            $mrp = $mrpValue !== null ? (float) str_replace(',', '', $mrpValue) : null;

            $existingProduct = CompetitionProduct::where('code', $code)->first();

            $attributes = [
                'competition_category_id' => $category->id,
                'competition_brand_id'    => $brand->id,
                'name'                    => $row['name'] ?? null,
                'mrp'                     => $mrp,
                // 'order'                   => $row['order'] ?? null,
            ];

            if ($existingProduct) {
                $attributes['modified_by'] = auth()->id();
                $existingProduct->update($attributes);
                return $existingProduct;
            } else {
                $attributes['code'] = $code;
                $attributes['created_by'] = auth()->id();
                return CompetitionProduct::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.competition_category_code' => [
                'required',
                'max:200',
                function ($attribute, $value, $fail) {
                    if (!Category::where('code', $value)->where('category_type', 'competition_category')->exists()) {
                        $fail("The {$attribute} must exist under competition_category type.");
                    }
                }
            ],
            '*.competition_brand_code' => [
                'required',
                'max:200',
                function ($attribute, $value, $fail) {
                    if (!Brand::where('code', $value)->where('brand_type', 'competition_brand')->exists()) {
                        $fail("The {$attribute} must exist under competition_brand type.");
                    }
                }
            ],
            '*.code'  => 'required|max:200',
            '*.name'  => 'required|max:200',
            '*.mrp'   => 'nullable|max:200',
            // '*.order' => 'nullable|max:200',
        ];
    }
}
