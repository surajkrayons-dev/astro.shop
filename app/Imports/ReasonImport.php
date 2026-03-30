<?php

namespace App\Imports;

use App\Models\Reason;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class ReasonImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $reason = trim($row['reason'] ?? '');
            // $reasonType = trim($row['reason_type'] ?? '');

            $uniqueKey = strtolower($reason);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    // "Row #{$rowNumber}: Duplicate (Store Category: '{$reason}', "
                    // ."Competition Product: '{$reasonType}') also found on row #{$previousRow}.",

                    "Row #{$rowNumber}: Duplicate (reason: '{$reason}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;


            $value = $row['reason_type']; // Example input
            $type_key = array_search($value, config('system.reason_types'));

            if ($type_key === false) {
                throw new \Exception("Invalid reason type value '{$value}'");
            }

            // 🔍 Check if existing
            $existing = Reason::where([
                'reason' => $row['reason'], 

            ])->first();

            $attributes = [
                'reason_type' => $type_key,
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
                $attributes['reason']       = $row['reason'];
                return \App\Models\Reason::create($attributes);
            }

            return $reason_type;
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
            '*.reason' => 'required|max:200',
            '*.reason_type' => 'required|max:200',
            '*.status' => 'required',
        ];
    }
}
