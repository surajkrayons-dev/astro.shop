<?php

namespace App\Imports;

use App\Models\Region;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class RegionImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * The first row of the sheet is the heading row (#1),
     * so the first data row is #2.
     */
    protected $currentRow = 2;

    /**
     * Track duplicates in THIS Excel file.
     * Key = "storeCategory|competitionProduct"
     * Value = row number where it was first seen.
     */
    protected array $seen = [];

    /**
     * Called for each row of data.
     */
    public function model(array $row)
    {
        try {
            // 1) Determine the row number and increment for next
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            // 2) Extract code
            $code     = trim($row['region_code'] ?? '');

            // 3) Build a key to detect duplicates within this Excel
            //    (lowercase to avoid case-sensitivity).
            $uniqueKey = strtolower($code);

            // 4) Check if we've already seen this combo in THIS file
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Region Code: '{$code}') also found on row #{$previousRow}."
                );
            }

            // If not seen, mark it as seen
            $this->seen[$uniqueKey] = $rowNumber;

            // 🔍 Check if existing
            $existing = Region::where([
                'code'  =>  $row['region_code'],

            ])->first();

            $attributes = [
                'name'       => $row['region_name'],
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
                $attributes['code']       = $row['region_code'];
                return \App\Models\Region::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    /**
     * Validation rules (Maatwebsite-Excel).
     */
    public function rules(): array
    {
        return [
            '*.region_code' => 'required|max:200',
            '*.region_name' => 'required|max:200',
            '*.status' => 'required',
        ];
    }
}
