<?php

namespace App\Imports;

use App\Models\CategoryTracking;
use App\Models\CategoryTrackingProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class CategoryTrackingProductImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $productCode = trim($row['product_code'] ?? '');
            $categoryTrackingCode = trim($row['category_tracking_code'] ?? '');
            $uniqueKey = strtolower($categoryTrackingCode . '|' . $productCode);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate found for CategoryTracking '{$categoryTrackingCode}' with Product '{$productCode}' (also on row #{$previousRow}).");
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $categoryTracking = CategoryTracking::where('code', $row['category_tracking_code'] ?? '')->first();
            if (!$categoryTracking) {
                throw new \Exception("Row #{$rowNumber}: Category Tracking not found for code '{$row['category_tracking_code']}'.");
            }

            $product = Product::where('code', $productCode)->first();
            if (!$product) {
                throw new \Exception("Product Code '{$productCode}' does not exist.");
            }

            $attributes = [
                'category_tracking_id'              => $categoryTracking->id,
                'product_id'                        => $product->id,
                // 'order'                             => $row['product_order'],
                'bay1_title'                        => $row['bay1_title'],
                'bay1_photo'                        => $row['bay1_photo'],
                'bay2_title'                        => $row['bay2_title'],
                'bay2_photo'                        => $row['bay2_photo'],
                'bay3_title'                        => $row['bay3_title'],
                'bay3_photo'                        => $row['bay3_photo'],
                'bay4_title'                        => $row['bay4_title'],
                'bay4_photo'                        => $row['bay4_photo'],
                'bay5_title'                        => $row['bay5_title'],
                'bay5_photo'                        => $row['bay5_photo'],
                'bay6_title'                        => $row['bay6_title'],
                'bay6_photo'                        => $row['bay6_photo'],
                'is_required'                       => strtolower(trim($row['is_required'])) === 'yes' ? 1 : 0,
                'is_photo_required'                 => strtolower(trim($row['is_photo_required'])) === 'yes' ? 1 : 0,
                'is_facing_enabled'                 => strtolower(trim($row['is_facing_enabled'])) === 'yes' ? 1 : 0,
                'is_shelf_enabled'                  => strtolower(trim($row['is_shelf_enabled'])) === 'yes' ? 1 : 0,
                'is_depth_enabled'                  => strtolower(trim($row['is_depth_enabled'])) === 'yes' ? 1 : 0,
                'is_stack_enabled'                  => strtolower(trim($row['is_stack_enabled'])) === 'yes' ? 1 : 0,
                'is_shelf_product_edit_enabled'     => strtolower(trim($row['is_shelf_product_edit_enabled'] ?? '')) === 'yes' ? 1 : 0,
            ];

            $existing = CategoryTrackingProduct::where('category_tracking_id', $categoryTracking->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $existing->update(array_merge($attributes, [
                    'modified_by' => auth()->id(),
                ]));
                return $existing;
            } else {
                return CategoryTrackingProduct::create(array_merge($attributes, [
                    'created_by'  => auth()->id(),
                ]));
            }
        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.category_tracking_code'              => 'required|max:200',
            '*.product_code'                        => 'required|exists:products,code',
            // '*.product_order'                       => 'nullable|max:200',
            '*.bay1_title'                          => 'nullable|string|max:200',
            '*.bay1_photo'                          => 'nullable',
            '*.bay2_title'                          => 'nullable|string|max:200',
            '*.bay2_photo'                          => 'nullable',
            '*.bay3_title'                          => 'nullable|string|max:200',
            '*.bay3_photo'                          => 'nullable',
            '*.bay4_title'                          => 'nullable|string|max:200',
            '*.bay4_photo'                          => 'nullable',
            '*.bay5_title'                          => 'nullable|string|max:200',
            '*.bay5_photo'                          => 'nullable',
            '*.bay6_title'                          => 'nullable|string|max:200',
            '*.bay6_photo'                          => 'nullable',
            '*.is_required'                         => 'required',
            '*.is_photo_required'                   => 'required',
            '*.is_facing_enabled'                   => 'required',
            '*.is_shelf_enabled'                    => 'required',
            '*.is_depth_enabled'                    => 'required',
            '*.is_stack_enabled'                    => 'required',
            '*.is_shelf_product_edit_enabled'       => 'required',
        ];
    }
}
