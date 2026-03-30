<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserService;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;
use Throwable;
use Log;

class ClientImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow++;
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
                'mobile'          => $mobile ?? null,
                'dob'             => $dob,
                'gender'          => $row['gender'] ?? null,
                'address'         => $row['address'] ?? null,
                'date_of_joining' => $doj,
                'profile_image'   => $row['profile_image'] ?? null,
                'password'        => Hash::make($row['password']),
                'status'          => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            $user = null;
            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                $user = $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                $user = User::create($attributes);
            }

            // Process services
            $services = strtolower(trim($row['services'] ?? ''));
            $serviceCost = intval($row['service_cost'] ?? 0);

            if (!empty($services)) {
                $configServices = config('system.service_types');
                $normalizedMap = [];

                foreach ($configServices as $key => $value) {
                    $normalizedKey = strtolower(str_replace(['_', ' '], '', $key));
                    $normalizedValue = strtolower(str_replace(['_', ' '], '', $value));
                    $normalizedMap[$normalizedKey] = $key;
                    $normalizedMap[$normalizedValue] = $key;
                }

                $inputServices = collect(explode(',', $services))
                    ->map(fn($s) => strtolower(str_replace(['_', ' '], '', trim($s))))
                    ->filter()
                    ->values()
                    ->all();

                $finalServices = [];

                foreach ($inputServices as $serviceKey) {
                    if (!array_key_exists($serviceKey, $normalizedMap)) {
                        throw new \Exception("Row #{$rowNumber}: Invalid service type '{$serviceKey}'. Allowed: " . implode(', ', array_values($configServices)));
                    }

                    $finalServices[] = $normalizedMap[$serviceKey];
                }

                UserService::updateOrCreate(
                    ['client_id' => $user->id],
                    [
                        'services' => implode(', ', $finalServices),
                        'service_cost' => $serviceCost
                    ]
                );
            }

            return $user;
        } catch (Throwable $th) {
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());

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

        if (is_numeric($value)) {
            try {
                $base = Carbon::createFromDate(1899, 12, 30)->startOfDay();
                $date = $base->addDays($value);
                return $date->format('d-m-Y');
            } catch (\Exception $e) {
                throw new \Exception("Cannot parse numeric Excel date '$value'.");
            }
        }

        try {
            $date = Carbon::createFromFormat('d-M-y', $value);
            return $date->format('d-m-Y');
        } catch (\Exception $e) {
            try {
                $date = Carbon::parse($value);
                return $date->format('d-m-Y');
            } catch (\Exception $ex) {
                throw new \Exception("Cannot parse date '$value'.");
            }
        }
    }

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
            '*.address' => 'nullable|string|max:2000',
            '*.date_of_joining' => 'nullable',
            '*.password' => 'required|min:6',
            '*.status' => 'required',
            '*.services' => 'nullable',
            '*.service_cost' => 'nullable|numeric',
        ];
    }
}
