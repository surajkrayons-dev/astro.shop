<?php

namespace App\Imports;

use App\Models\StoreUserModuleMapping;
use App\Models\Store;
use App\Models\User;
use App\Models\StoreUserMapping;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class StoreUserModuleMappingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            // Read codes from the row
            $storeCode = trim($row['store_code'] ?? '');
            $userCode  = trim($row['promoter_code'] ?? '');

            // Duplicate check within this file
            $uniqueKey = strtolower($storeCode . '|' . $userCode);
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (store: '{$storeCode}', user: '{$userCode}') also found on row #{$previousRow}."
                );
            }
            $this->seen[$uniqueKey] = $rowNumber;

            // Fetch store by code
            $store = Store::where('code', $storeCode)->first();
            if (!$store) {
                throw new \Exception("Row #{$rowNumber}: Store with code '{$storeCode}' does not exist.");
            }

            // Fetch user by user_id with role check
            $user = User::where('code', $userCode)
                ->where('role_id', 3)
                ->first();
            if (!$user) {
                throw new \Exception("Row #{$rowNumber}: User with code '{$userCode}' does not exist or is not role_id 3.");
            }

            // Verify store-user mapping exists
            $mapping = StoreUserMapping::where([
                'store_id' => $store->id,
                'user_id'  => $user->id,
            ])->first();
            if (!$mapping) {
                throw new \Exception(
                    "Row #{$rowNumber}: No mapping found in store_user_mappings for store_code '{$storeCode}' and promoter_code '{$userCode}'."
                );
            }

            // Extract allowed 'yes' days from mapping date JSON
            $allowedDates = [];
            $mappingDates = is_string($mapping->date)
                ? json_decode($mapping->date, true)
                : $mapping->date;
            foreach ($mappingDates as $dayKey => $values) {
                if (is_array($values) && in_array('yes', array_map('strtolower', $values))) {
                    if (preg_match('/day(\d+)/', $dayKey, $match)) {
                        $allowedDates[] = (string)$match[1];
                    }
                }
            }

            // Process and validate input dates
            $dateValue = trim($row['store_action_required_dates'] ?? '');
            $date = null;
            if ($dateValue !== '') {
                $inputDates = array_map('trim', explode(',', $dateValue));
                foreach ($inputDates as $inputDay) {
                    if (!in_array($inputDay, $allowedDates)) {
                        throw new \Exception(
                            "Row #{$rowNumber}: Date '{$inputDay}' is not valid for this store-user mapping. Please enter only allowed dates."
                        );
                    }
                }
                $date = implode(', ', $inputDates);
            }

            // Build attributes
            $attributes = [
                'location'                      => $row['location'] ?? null,
                'store_action'                  => $row['store_action'] ?? null,
                'is_store_action_required_date' => $date,
                'store_action_section'          => $row['store_action_section'] ?? null,
                'store_action_section_older'    => $row['store_action_section_older'] ?? null,
                'is_location_required'          => strtolower(trim($row['is_location_required'] ?? '')) === 'yes' ? 1 : 0,
                'is_store_action_required'      => strtolower(trim($row['is_store_action_required'] ?? '')) === 'yes' ? 1 : 0,
            ];

            // Check existing record
            $existing = StoreUserModuleMapping::where([
                'store_id' => $store->id,
                'user_id'  => $user->id,
            ])->first();

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['store_id']   = $store->id;
                $attributes['user_id']    = $user->id;
                $attributes['created_by'] = auth()->id();
                return StoreUserModuleMapping::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #' . $this->currentRow . ': ' . json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.store_code'                      => 'required|exists:stores,code|max:200',
            '*.promoter_code'                   => 'required|exists:users,code|max:200',
            '*.location'                        => 'nullable|max:200',
            '*.store_action'                    => 'nullable|max:200',
            '*.store_action_required_dates'     => 'nullable|max:200',
            '*.store_action_section'            => 'nullable|max:200',
            '*.store_action_section_older'      => 'nullable|max:200',
            '*.is_location_required'            => 'required',
            '*.is_store_action_required'        => 'required',
        ];
    }
}
