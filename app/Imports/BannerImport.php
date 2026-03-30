<?php

namespace App\Imports;

use App\Models\Banner;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class BannerImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $name = trim($row['banner_name'] ?? '');

            $uniqueKey = strtolower($name);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (name: '{$name}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            // Find existing record
            $existing = Banner::where([
                'name'      => $row['banner_name'],
                
            ])->first();

            $attributes = [
                'order'             => $row['banner_order'],
                'url'               => $row['banner_link'],
                'image'             => $row['image'],
                'description'       => $row['description'],
                'status'            => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['name']       = $row['banner_name'];
                $attributes['created_by']    = auth()->id();
                return Banner::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.banner_name'     => 'required|max:200',
            '*.banner_order'    => 'nullable|integer|min:1',
            '*.status'          => 'required',
            '*.banner_link'     => 'nullable|max:200',
            '*.image'           => 'nullable',
            '*.description'     => 'nullable|max:200',
        ];
    }
}
