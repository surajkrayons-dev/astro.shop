<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class ProductImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $code     = trim($row['product_code'] ?? '');

            $uniqueKey = strtolower($code);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Product Code: '{$code}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $client = User::where('code', $row['client_code'])->first();
            if (!$client) {
                throw new \Exception("Row #{$rowNumber}: Client Code '{$row['client_code']}' does not exist.");
            }

            $category = Category::where('code', $row['category_code'])->first();
            if (!$category) {
                throw new \Exception("Category Code '{$row['category_code']}' does not exist.");
            }

            $brand = Brand::where('code', $row['brand_code'])->first();
            if (!$brand) {
                throw new \Exception("Brand Code '{$row['brand_code']}' does not exist.");
            }

            $price = filter_var($row['price'], FILTER_VALIDATE_FLOAT);
            if ($price === false || $price < 0) {
                throw new \Exception("Invalid price value '{$row['price']}'. It must be a non-negative number.");
            }

            $order = is_numeric($row['order']) ? (int) $row['order'] : null;

            $existing = Product::where([
                'code'  =>  $row['product_code'],

            ])->first();

            $attributes = [
                'client_id'          => $client->id,
                'name'               => $row['product_name'],
                'product_category_id'=> $category->id,
                'product_brand_id'   => $brand->id,
                'price'              => $price,
                'description'        => $row['description'] ?? null,
                'order'              => $order,
                'status'             => strtolower(trim($row['status'] ?? '')) == 'yes' ? 1 : 0,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                $attributes['code']       = $row['product_code'];
                return \App\Models\Product::create($attributes);
            }

            return $product;

        } catch (\Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }

        return null;
    }

    public function rules(): array
    {
        return [
            '*.client_code'         => 'required|exists:users,code',
            '*.product_code'        => 'required|max:200',
            '*.product_name'        => 'required|max:200',
            '*.category_code'       => 'required|exists:categories,code',
            '*.brand_code'          => 'required|exists:brands,code',
            '*.price'               => 'required|min:0',
            '*.description'         => 'nullable|string|max:1000',
            '*.order'               => 'nullable',
            '*.status'              => 'required',
        ];
    }

}
