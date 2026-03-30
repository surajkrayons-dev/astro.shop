<?php

namespace App\Imports;

use App\Models\Store;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\User;
use App\Models\Region;
use App\Models\Format;
use App\Models\Chain;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class StoreImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * The first row of the sheet is the heading row (#1),
     * so data starts from row #2.
     */
    protected $currentRow = 2;

    /**
     * Track duplicates in THIS Excel file.
     * Key = "code" (lowercased and trimmed).
     * Value = row number where it was first seen.
     */
    protected array $seen = [];

    /**
     * Called for each row of data.
     */
    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            // Extract the 'code' field and normalize it
            $code = trim($row['store_code'] ?? '');

            $uniqueKey = strtolower($code);

            // Duplicate check: if code already seen, throw exception
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Code: '{$code}') also found on row #{$previousRow}."
                );
            }
            $this->seen[$uniqueKey] = $rowNumber;

            $client = User::where('code', $row['client_code'])->first();
            if (!$client) {
                throw new \Exception("Row #{$rowNumber}: Client Code '{$row['client_code']}' does not exist.");
            }

            $chain = Chain::where('code', trim($row['store_chain_code']))->first();
            if (!$chain) {
                throw new \Exception("Store Chain Code '{$row['store_chain_code']}' does not exist.");
            }
            
            $format = Format::where('code', trim($row['format_code']))->first();
            if (!$format) {
                throw new \Exception("Format Code '{$row['format_code']}' does not exist.");
            }
            
            $region = Region::where('code', trim($row['region_code']))->first();
            if (!$region) {
                throw new \Exception("Region Code '{$row['region_code']}' does not exist.");
            }
            
            // $regionValue = $row['region'] ?? null;
            // $regionKey = array_search($regionValue, config('system.region'));
            // if ($regionKey === false) {
            //     $regionKey = null;
            // }

            // $formatValue = $row['format'] ?? null;
            // $formatKey = array_search($formatValue, config('system.format'));
            // if ($formatKey === false) {
            //     $formatKey = null;
            // }

            // $cityId = \App\Models\City::where('name', $row['city'] ?? '')->value('id') ?? null;
            // $stateId = \App\Models\State::where('name', $row['state'] ?? '')->value('id') ?? null;
            // $countryId = \App\Models\Country::where('name', $row['country'] ?? '')->value('id') ?? null;
            $city = $row['city'] ? City::where('name', $row['city'])->first() : null;
            if ($row['city'] && !$city) {
                throw new \Exception("Row #{$rowNumber}: City '{$row['city']}' not found.");
            }

            $state = $row['state'] ? State::where('name', $row['state'])->first() : null;
            if ($row['state'] && !$state) {
                throw new \Exception("Row #{$rowNumber}: State '{$row['state']}' not found.");
            }

            $country = $row['country'] ? Country::where('name', $row['country'])->first() : null;
            if ($row['country'] && !$country) {
                throw new \Exception("Row #{$rowNumber}: Country '{$row['country']}' not found.");
            }

            // 🔍 Check if existing
            $existing = Store::where([
                'code'  =>  $code,

            ])->first();

            $attributes = [
                'client_id'         => $client->id,
                'client_store_code' => $row['client_store_code'],
                'name'              => $row['store_name'],
                'distributor_code'  => $row['distributor_code'],
                'distributor_name'  => $row['distributor_name'],
                // 'kyc_status'        => $row['kyc_status'] == 'True' ? 1 : 0,
                'store_type'        => $row['store_type'] ?? null,
                'chain_id'          => $chain->id,
                'format_id'         => $format ? $format->id : null,
                'region_id'         => $region ? $region->id : null,
                'latitude'          => $row['latitude'] ?? null,
                'longitude'         => $row['longitude'] ?? null,
                'distance'          => $row['distance'] ?? null,
                'address'           => $row['address'] ?? null,
                'state_id'          => $state ? $state->id : null,
                'city_id'           => $city ? $city->id : null,
                'country_id'        => $country ? $country->id : null,
                'status'            => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                $attributes['code']       =  $code;
                return \App\Models\Store::create($attributes);
            }
        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());

            $errorMessage = $th->getMessage();

            if (str_contains($errorMessage, 'Integrity constraint violation') && str_contains($errorMessage, 'Duplicate entry')) {
                throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 This data already exists in DB.");
            }

            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $errorMessage);
        }
    }

    /**
     * Validation rules for Maatwebsite-Excel.
     */
    public function rules(): array
    {
        return [
            '*.client_code'             => 'required|max:200',
            '*.client_store_code'       => 'required|max:200',
            '*.store_code'              => 'required|max:200',
            '*.store_name'              => 'required|max:200',
            '*.distributor_code'        => 'nullable|max:200',
            '*.distributor_name'        => 'nullable|max:200',
            '*.store_type'              => 'nullable|max:200',
            '*.store_chain_code'        => 'required|max:200',
            '*.format_code'             => 'nullable|max:200',
            '*.region_code'             => 'nullable|max:200',
            '*.latitude'                => 'nullable',
            '*.longitude'               => 'nullable',
            '*.distance'                => 'nullable',
            '*.address'                 => 'nullable',
            '*.city'                    => 'nullable|string|max:200',
            '*.state'                   => 'nullable|string|max:200',
            '*.country'                 => 'nullable|string|max:200',
            // '*.kyc_status'              => 'nullable',
            '*.status'                  => 'required',
        ];
    }
}
