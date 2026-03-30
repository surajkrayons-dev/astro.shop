<?php

namespace App\Imports;

use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use App\Models\PinCode;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Throwable;
use Log;

class ClientImport implements ToModel, WithHeadingRow, WithValidation
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

            $code = trim($row['client_code'] ?? '');
            $uniqueKey = strtolower($code);

            if (empty($code)) {
                throw new \Exception("Row #{$rowNumber}: 'Client Code' is required.");
            }

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate Client Code '{$code}' also found on row #{$previousRow}.");
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $existing = User::where('code', $code)->first();

            $username = trim($row['username'] ?? '');
            $email = trim($row['email'] ?? '');
            $mobile = trim($row['mobile'] ?? '');

            if (!$existing) {
                // Create scenario: username/email/mobile must not exist at all
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

            $dob = $this->parseDate($row['dob'] ?? null);
            $doj = $this->parseDate($row['date_of_joining'] ?? null);

            $attributes = [
                'code'            => $code,
                'type'            => 'client',
                'role_id'         => 2,
                'name'            => $row['name'],
                'username'        => $username,
                'email'           => $email ?? null,
                'mobile'          => $row['mobile'] ?? null,
                'dob'             => $dob,
                'gender'          => $row['gender'] ?? null,
                'address'         => $row['address'] ?? null,
                'date_of_joining' => $doj,
                'profile_image'   => $row['profile_image'] ?? null,
                'password'        => Hash::make($row['password']),
                'status'          => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
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

    /**
    * parseDate method: handles numeric Excel date or string date
    * Returns "d-m-Y" format (e.g. "31-03-2025") or null if empty
    */
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
            '*.client_code' => 'required',
            '*.username' => 'required|max:200',
            '*.name' => 'required|max:200',
            '*.email' => 'required|email|max:200',
            '*.mobile' => 'nullable|numeric|digits:10',
            '*.dob' => 'nullable',
            '*.gender' => 'nullable',
            '*.profile_image' => 'nullable|string|max:255',
            // '*.region' => 'nullable|string|max:200',
            // '*.country' => 'nullable|string|max:100',
            // '*.state' => 'nullable|string|max:100',
            // '*.city' => 'nullable|string|max:100',
            // '*.pincode' => 'nullable',
            '*.address' => 'nullable|string|max:2000',
            // '*.salary' => 'nullable',
            '*.date_of_joining' => 'nullable',
            '*.password' => 'required|min:6',
            // '*.kyc_status' => 'required',
            '*.status' => 'required',
        ];
    }
}
