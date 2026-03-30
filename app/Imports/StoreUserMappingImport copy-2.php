<?php

namespace App\Imports;

use App\Models\StoreUserMapping;
use App\Models\Store;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class StoreUserMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $maxDays = 31;

    protected $currentRow = 2;

    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $storeCode  = trim($row['store_code'] ?? '');
            $userCode   = trim($row['promoter_code'] ?? '');
            $clientCode = trim($row['client_code'] ?? '');
            $month      = $row['month'] ?? null;
            $year       = $row['year'] ?? null;

            // Include month & year in uniqueness key
            $uniqueKey = strtolower($storeCode . '|' . $userCode . '|' . $clientCode . '|' . $month . '|' . $year);
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate combination (Store: '{$storeCode}', User: '{$userCode}', Client: '{$clientCode}', Month: '{$month}', Year: '{$year}') also found on row #{$previousRow}."
                );
            }
            $this->seen[$uniqueKey] = $rowNumber;

            $client = User::where('code', $clientCode)->first();
            if (!$client) {
                throw new \Exception("Row #{$rowNumber}: Client with code '{$clientCode}' does not exist in DB.");
            }

            $store = Store::where('code', $storeCode)
                ->where('client_id', $client->id)
                ->where('status', 1)
                ->first();
            if (!$store) {
                throw new \Exception("Row #{$rowNumber}: Store '{$storeCode}' is not mapped to Client '{$clientCode}', or store is inactive.");
            }

            $user = User::where('code', $userCode)
                ->where('role_id', 3)
                ->first();
            if (!$user) {
                throw new \Exception("Row #{$rowNumber}: User with code '{$userCode}' does not exist in DB.");
            }

            if ($month < 1 || $month > 12) {
                throw new \Exception("Row #{$rowNumber}: Invalid month '{$month}'. Must be 1-12.");
            }
            if ($year < 1900 || $year > 9999) {
                throw new \Exception("Row #{$rowNumber}: Invalid year '{$year}'. Must be between 1900-9999.");
            }

            $dateValue = [];
            for ($i = 1; $i <= $this->maxDays; $i++) {
                $dayKey = 'day'.$i;
                if (!empty($row[$dayKey])) {
                    $dateValue[$dayKey] = [$row[$dayKey]];
                }
            }

            $existing = StoreUserMapping::where([
                'store_id'  => $store->id,
                'user_id'   => $user->id,
                'client_id' => $client->id,
                'month'     => $month,
                'year'      => $year,
            ])->first();

            $attributes = [
                'month'  => $month,
                'year'   => $year,
                'date'   => $dateValue,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['store_id']   = $store->id;
                $attributes['user_id']    = $user->id;
                $attributes['client_id']  = $client->id;
                $attributes['created_by'] = auth()->id();
                return StoreUserMapping::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $rules = [
            '*.store_code'     => 'required|exists:stores,code|max:200',
            '*.promoter_code'  => 'required|exists:users,code|max:200',
            '*.client_code'    => 'required|exists:users,code|max:200',
            '*.month'          => 'required|between:1,12',
            '*.year'           => 'required|integer|between:1900,9999',
        ];

        for ($i = 1; $i <= $this->maxDays; $i++) {
            $dayKey = 'day'.$i;
            $rules['*.' . $dayKey] = 'nullable|string|max:50';
        }

        return $rules;
    }
}
