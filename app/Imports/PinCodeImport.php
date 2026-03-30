<?php

namespace App\Imports;

use App\Models\PinCode;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class PinCodeImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $pin = trim($row['pin_code'] ?? '');
            $uniqueKey = strtolower($pin);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate pin_code '{$pin}' also found on row #{$previousRow}.");
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $country = Country::where('name', $row['country'])->first();
            if (!$country) {
                throw new \Exception("Row #{$rowNumber}: Country '{$row['country']}' not found.");
            }
            $state = State::where('name', $row['state'])->first();
            if (!$state) {
                throw new \Exception("Row #{$rowNumber}: State '{$row['state']}' not found.");
            }
            $city = City::where('name', $row['city'])->first();
            if (!$city) {
                throw new \Exception("Row #{$rowNumber}: City '{$row['city']}' not found.");
            }

            // if (!$country || !$state || !$city) {
            //     throw new \Exception("Row #{$rowNumber}: One of country/state/city not found.");
            // }
            
            $existing = PinCode::where([
                'pin_code'      => $pin,

            ])->first();

            $attributes = [
                'city_id' => $city->id,
                'state_id' => $state->id,
                'country_id' => $country->id,
                'status' => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                $attributes['pin_code']       = $pin;
                return \App\Models\PinCode::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing pin code row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.pin_code' => 'required|max:20',
            '*.city' => 'required|exists:cities,name',
            '*.state' => 'required|exists:states,name',
            '*.country' => 'required|exists:countries,name',
            '*.status' => 'required',
        ];
    }
}
