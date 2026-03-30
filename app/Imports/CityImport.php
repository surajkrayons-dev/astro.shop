<?php

namespace App\Imports;

use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class CityImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $name = trim($row['name'] ?? '');
            $stateName = trim($row['state'] ?? '');
            $countryName = trim($row['country'] ?? '');

            $uniqueKey = strtolower($name . '|' . $stateName . '|' . $countryName);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (name: '{$name}', state: '{$stateName}', country: '{$countryName}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $country = Country::where('name', $countryName)->first();
            if (!$country) {
                throw new \Exception("Country '{$countryName}' does not exist.");
            }

            $state = State::where('name', $stateName)->where('country_id', $country->id)->first();
            if (!$state) {
                throw new \Exception("State '{$stateName}' with country '{$countryName}' does not exist.");
            }

            $existing = City::where([
                'name'      => $name,

            ])->first();

            $attributes = [
                'state_id'  => $state->id,
                'country_id'=> $country->id,
                'status'    => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                $attributes['name']       = $name;
                return \App\Models\City::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.name'    => 'required|max:200',
            '*.state'   => 'required|exists:states,name',
            '*.country' => 'required|exists:countries,name',
            '*.status'  => 'required',
        ];
    }
}
