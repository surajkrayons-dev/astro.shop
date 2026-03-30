<?php

namespace App\Imports;

use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\PinCode;
use App\Models\Region;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Throwable;
use Log;

class StaffImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $code = trim($row['staff_code'] ?? '');
            $clientCodeRaw = trim($row['client_codes'] ?? '');
            $uniqueKey = strtolower($code);

            if (empty($code)) {
                throw new \Exception("Row #{$rowNumber}: 'Staff Code' is required.");
            }

            // ✅ Multiple client code handle
            $clientIds = [];
            if (!empty($clientCodeRaw)) {
                $clientCodes = array_map('trim', explode(',', $clientCodeRaw));
                foreach ($clientCodes as $cCode) {
                    $client = User::where('code', $cCode)->where('type', 'client')->first();
                    if (!$client) {
                        throw new \Exception("Row #{$rowNumber}: Invalid Client Code '{$cCode}' - no matching client found.");
                    }
                    $clientIds[] = $client->id;
                }
            }

            // ✅ parent staff
            $parentStaffId = null;
            if (!empty($row['staff_parent_code'])) {
                $parent = User::where('code', trim($row['staff_parent_code']))->where('type','staff')->first();
                if (!$parent) {
                    throw new \Exception("Row #{$rowNumber}: Invalid Staff Parent Code '{$row['staff_parent_code']}' - no matching staff found.");
                }
                $parentStaffId = $parent->id;
            }

            // ✅ Duplicate staff code check
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate Staff Code '{$code}' also found on row #{$previousRow}.");
            }
            $this->seen[$uniqueKey] = $rowNumber;

            $existing = User::where('code', $code)->first();

            $username = trim($row['username'] ?? '');
            $email = trim($row['email_id'] ?? '');
            $mobile = trim($row['mobile'] ?? '');

            // ✅ Duplicate username / email / mobile check
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

            // ✅ Location validations
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

            $pinCode = $row['pincode'] ? PinCode::where('pin_code', $row['pincode'])->first() : null;
            if ($row['pincode'] && !$pinCode) {
                throw new \Exception("Row #{$rowNumber}: PinCode '{$row['pincode']}' not found.");
            }

            $region = Region::where('code', $row['region_code'])->first();
            if (!$region) {
                throw new \Exception("Row #{$rowNumber}: Region Code '{$row['region_code']}' does not exist.");
            }

            $dateOfJoining = $this->parseDate($row['date_of_joining'] ?? null);
            $dob = $this->parseDate($row['dob'] ?? null);

            $roleId = $this->getRoleId($row['role']);
            if (!$roleId) {
                throw new \Exception("Invalid role '{$row['role']}' provided on row #{$rowNumber}. No matching role found in DB.");
            }

            // ✅ Prepare data for insert/update
            $attributes = [
                'username'        => $username,
                'email'           => $email,
                'code'            => $code,
                'name'            => $row['staff_name'],
                'parent_id'       => $parentStaffId,
                'mobile'          => $mobile,
                'dob'             => $dob,
                'date_of_joining' => $dateOfJoining,
                'gender'          => $row['gender'] ?? null,
                'salary'          => $row['salary'] ?? null,
                'region_id'       => $region?->id ?? null,
                'profile_image'   => $row['profile_image'] ?? null,
                'address'         => $row['address'] ?? null,
                'password'        => Hash::make($row['password']),
                'country_id'      => $country?->id,
                'state_id'        => $state?->id,
                'city_id'         => $city?->id,
                'pincode_id'      => $pinCode?->id ?? null,
                'role_id'         => $roleId,
                'status'          => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
                'type'            => 'staff',
            ];

            // ✅ Update or Create staff
            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                $user = $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                $user = User::create($attributes);
            }

            // ✅ Update client IDs (comma separated)
            if (!empty($clientIds)) {
                $user->update(['client_id' => implode(',', $clientIds)]);
            }

            return $user;

        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    private function getRoleId($roleName)
    {
        $role = Role::where('name', trim($roleName))->first();
        if (!$role) {
            throw new \Exception("Role '{$roleName}' not found.");
        }
        return $role->id;
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        // 🧮 Excel numeric date format
        if (is_numeric($value)) {
            try {
                $base = Carbon::createFromDate(1899, 12, 30)->startOfDay();
                return $base->addDays($value)->format('d-m-Y');
            } catch (\Exception $e) {
                throw new \Exception("Cannot parse numeric Excel date '$value'.");
            }
        }

        // 🧾 Try known formats
        foreach (['d-M-y', 'd/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('d-m-Y');
            } catch (\Exception $e) {
                continue;
            }
        }

        // 🧠 Fallback auto-parse
        try {
            return Carbon::parse($value)->format('d-m-Y');
        } catch (\Exception $ex) {
            throw new \Exception("Cannot parse date '$value'.");
        }
    }

    public function rules(): array
    {
        return [
            '*.staff_code'    => 'required',
            '*.username'      => 'required|max:200',
            '*.client_codes'   => 'nullable|string',
            '*.email_id'      => 'required|email|max:200',
            '*.staff_name'    => 'required|max:200',
            '*.staff_parent_code' => 'nullable|exists:users,code',
            '*.mobile'        => 'nullable|numeric|digits:10',
            '*.dob'           => 'nullable',
            '*.date_of_joining' => 'nullable',
            '*.gender'        => 'nullable',
            '*.salary'        => 'nullable',
            '*.region_code'   => 'nullable|exists:regions,code',
            '*.profile_image' => 'nullable',
            '*.address'       => 'nullable|string|max:2000',
            '*.password'      => 'required|min:6',
            '*.country'       => 'nullable|string|max:255',
            '*.state'         => 'nullable|string|max:255',
            '*.city'          => 'nullable|string|max:255',
            '*.pincode'       => 'nullable',
            '*.role'          => 'required',
            '*.status'        => 'required',
        ];
    }
}
