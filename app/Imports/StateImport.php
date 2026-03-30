<?php

namespace App\Imports;

use App\Models\State;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class StateImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $name     = trim($row['name'] ?? '');

            $uniqueKey = strtolower($name);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (name: '{$name}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $country = Country::where('name', $row['country'])->first();
            if (!$country) {
                throw new \Exception("Country '{$row['country']}' does not exist.");
            }

            $existing = State::where([
                'name'      => $name,

            ])->first();

            $attributes = [
                'country_id'   => $country->id,
                'status'       => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                // ✅ If record exists, update it and set modified_by
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                // ✅ If new, set created_by
                $attributes['created_by'] = auth()->id();
                $attributes['name']       = $name;
                return \App\Models\State::create($attributes);
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
            '*.name' => 'required|max:200',
            '*.country' => 'required|exists:countries,name',
            '*.status' => 'required',
        ];
    }
}
