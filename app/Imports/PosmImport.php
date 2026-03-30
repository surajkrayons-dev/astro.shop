<?php

namespace App\Imports;

use App\Models\Posm;
use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class PosmImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $code = trim($row['posm_code'] ?? '');
            $brandCode = trim($row['posm_brand_code'] ?? '');

            $uniqueKey = strtolower($code);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate (Code: '{$code}') also found on row #{$previousRow}.");
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $posmBrand = Brand::where('code', $brandCode)
                ->where('brand_type', 'posm_brand')
                ->first();

            if (!$posmBrand) {
                throw new \Exception("Brand code '{$brandCode}' not found in brands table with type 'posm_brand'");
            }

            $posmData = [
                'posm_brand_id' => $posmBrand->id,
                'name'          => $row['posm_name'],
                'image'         => $row['image'],
                'status'        => strtolower($row['status']) === 'yes' ? 1 : 0,
            ];

            $result = Posm::updateOrCreate(
                ['code' => $code],
                $posmData
            );

            if ($result->wasRecentlyCreated) {
                $result->created_by = auth()->id();
            } else {
                $result->modified_by = auth()->id();
            }

            $result->save();

            return $result;
        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.posm_brand_code' => 'required|max:200',
            '*.posm_code'       => 'required|max:200',
            '*.posm_name'       => 'required|max:200',
            '*.image'           => 'nullable',
            '*.status'          => 'required',
        ];
    }
}
