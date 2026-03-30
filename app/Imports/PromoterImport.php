<?php

namespace App\Imports;

use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\PinCode;
use App\Models\Region;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Throwable;
use Log;

class PromoterImport implements ToModel, WithHeadingRow, WithValidation
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
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $code = trim($row['promoter_code'] ?? '');
            $clientCode = trim($row['client_code'] ?? '');
            $uniqueKey = strtolower($code);

            if (empty($code)) {
                throw new \Exception("Row #{$rowNumber}: 'Promoter Code' is required.");
            }

            $clientId = null;
            if (!empty($clientCode)) {
                $client = User::where('code', $clientCode)->where('type', 'client')->first();
                if (!$client) {
                    throw new \Exception("Row #{$rowNumber}: Invalid Client Code '{$clientCode}' – no matching client found.");
                }
                $clientId = $client->id;
            }

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate Promoter Code '{$code}' also found on row #{$previousRow}.");
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $existing = User::where('code', $code)->first();

            $username = trim($row['username'] ?? '');
            $email = trim($row['email'] ?? '');
            $mobile = trim($row['mobile'] ?? '');

            if (!$existing) {
                if (!empty($username) && User::where('username', $username)->exists()) {
                    throw new \Exception("Row #{$rowNumber}: Username '{$username}' already exists.");
                }

                if (!empty($email) && User::where('email', $email)->exists()) {
                    throw new \Exception("Row #{$rowNumber}: Email '{$email}' already exists.");
                }

                if (!empty($mobile) && User::where('mobile', $mobile)->exists()) {
                    throw new \Exception("Row #{$rowNumber}: Mobile number '{$mobile}' already exists.");
                }
            } else {
                if (!empty($username) && User::where('username', $username)->where('id', '!=', $existing->id)->exists()) {
                    throw new \Exception("Row #{$rowNumber}: Username '{$username}' already exists.");
                }

                if (!empty($email) && User::where('email', $email)->where('id', '!=', $existing->id)->exists()) {
                    throw new \Exception("Row #{$rowNumber}: Email '{$email}' already exists.");
                }

                if (!empty($mobile) && User::where('mobile', $mobile)->where('id', '!=', $existing->id)->exists()) {
                    throw new \Exception("Row #{$rowNumber}: Mobile number '{$mobile}' already exists.");
                }
            }

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

            $region = null;
            if (!empty($row['region_code'])) {
                $region = Region::where('code', $row['region_code'])->first();
                if (!$region) {
                    throw new \Exception("Row #{$rowNumber}: Region Code '{$row['region_code']}' does not exist.");
                }
            }

            $pinCode = $row['pincode'] ? PinCode::where('pin_code', $row['pincode'])->first() : null;
            if ($row['pincode'] && !$pinCode) {
                throw new \Exception("Row #{$rowNumber}: PinCode '{$row['pincode']}' not found.");
            }

            // $regionValue = strtolower($row['region'] ?? '');
            // $regionKey = array_search(ucwords($regionValue), config('system.region'));

            // if ($regionKey === false) {
            //     $regionKey = null;
            // }

            $dob = $this->parseDate($row['dob'] ?? null);
            $date_of_joining  = $this->parseDate($row['date_of_joining'] ?? null);


            $attributes = [
                'code'          => $code,
                'type'          => 'promoter',
                'role_id'       => 3,
                'client_id'     => $clientId,
                'name'          => $row['promoter_name'],
                'username'      => $username,
                'email'         => $email ?? null,
                'mobile'        => $row['mobile'] ?? null,
                'dob'           => $dob,
                'gender'        => $row['gender'] ?? null,
                'region_id'        => $region?->id ?? null,
                'country_id'     => $country?->id,
                'state_id'       => $state?->id,
                'city_id'        => $city?->id,
                'pincode_id'     => $pinCode?->id ?? null,
                // 'pincode_id'     => $row['pincode_id'] ?? null,
                'address'       => $row['address'] ?? null,
                'salary'        => $row['salary'] ?? null,
                'date_of_joining' => $date_of_joining,
                'profile_image' => $row['profile_image'] ?? null,
                'password'      => Hash::make($row['password']),
                // 'kyc_status'    => $row['kyc_status'] == 'True' ? 1 : 0,
                'status'        => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                return User::create($attributes);
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

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = trim($value);

        // (A) If numeric => Excel numeric date
        if (is_numeric($value)) {
            try {
                // base date 1899-12-30
                $base = Carbon::createFromDate(1899, 12, 30)->startOfDay();
                // No offset subtracted:
                $date = $base->addDays($value);
                return $date->format('d-m-Y');
            } catch (\Exception $e) {
                throw new \Exception("Cannot parse numeric Excel date '$value'.");
            }
        }

        // (B) Try "d-M-y" (e.g. "31-Mar-25")
        try {
            $date = Carbon::createFromFormat('d-M-y', $value);
            return $date->format('d-m-Y');
        } catch (\Exception $e) {
            // (C) Fallback parse (any recognized format)
            try {
                $date = Carbon::parse($value);
                return $date->format('d-m-Y');
            } catch (\Exception $ex) {
                throw new \Exception("Cannot parse date '$value'.");
            }
        }
    }

    /**
     * Validation rules (Maatwebsite-Excel).
     */
    public function rules(): array
    {
        return [
            '*.client_code' => 'required|exists:users,code',
            '*.promoter_code' => 'required',
            '*.username' => 'required|max:200',
            '*.promoter_name' => 'required|max:200',
            '*.email' => 'required|email|max:200',
            '*.mobile' => 'required|numeric|digits:10',
            '*.dob' => 'nullable',
            '*.gender' => 'nullable',
            '*.profile_image' => 'nullable|string|max:255',
            '*.region_code'     => 'nullable|exists:regions,code',
            '*.country' => 'nullable|string|max:100',
            '*.state' => 'nullable|string|max:100',
            '*.city' => 'nullable|string|max:100',
            '*.pincode' => 'nullable',
            '*.address' => 'nullable|string|max:2000',
            '*.salary' => 'nullable',
            '*.date_of_joining' => 'nullable',
            '*.password' => 'required|min:6',
            // '*.kyc_status' => 'required',
            '*.status' => 'required',
        ];
    }
}
