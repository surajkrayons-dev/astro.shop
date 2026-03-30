<?php

namespace App\Imports;

use App\Models\PayoutStore;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Carbon\Carbon;

class PayoutStoreImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        $rowNumber = $this->currentRow++;
        Log::info("Importing payout row #{$rowNumber}: " . json_encode($row));

        $storeCode   = trim($row['store_code']   ?? '');
        $clientCode  = trim($row['client_code']  ?? '');

        $store  = Store::where('code',  $storeCode)->first();
        $client = User::where('code', $clientCode)->first();

        if (!$store) {
            throw new \Exception("Row #{$rowNumber}: Store Code '{$storeCode}' not found.");
        }
        if (!$client) {
            throw new \Exception("Row #{$rowNumber}: Client Code '{$clientCode}' not found.");
        }

        $date = $this->parseDate($row['date'] ?? null);

        $uniqueKey = strtolower("{$storeCode}|{$clientCode}|{$date}");
        if (isset($this->seen[$uniqueKey])) {
            $prev = $this->seen[$uniqueKey];
            throw new \Exception("Row #{$rowNumber}: Duplicate entry also found at row #{$prev}.");
        }
        $this->seen[$uniqueKey] = $rowNumber;

        $existing = PayoutStore::where([
            'store_id'  => $store->id,
            'client_id' => $client->id,
            'date'      => $date,
        ])->first();

        $attributes = [
            'store_id'      => $store->id,
            'client_id'     => $client->id,
            'date'          => $date,
            'payout_amount' => $row['payout_amount'] ?? 0,
            // 'msg_status'    => $row['msg_status'] == 'True' ? 1 : 0,
            'comment'       => $row['comment'] ?? null,
        ];

        if ($existing) {
            $attributes['modified_by'] = auth()->id();
            $existing->update($attributes);
            return $existing;
        } else {
            $attributes['created_by'] = auth()->id();
            return new PayoutStore($attributes);
        }
    }


    /**
     * Parse date method to handle both numeric Excel date or string date.
     * Returns "d-m-Y" format (e.g. "31-03-2025") or null if empty.
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = trim($value);

        // If numeric => Excel numeric date
        if (is_numeric($value)) {
            try {
                $base = Carbon::createFromDate(1899, 12, 30)->startOfDay();
                $date = $base->addDays($value);
                return $date->format('d-m-Y');
            } catch (\Exception $e) {
                throw new \Exception("Cannot parse numeric Excel date '$value'.");
            }
        }

        // Try parsing "d-M-y" format (e.g. "31-Mar-25")
        try {
            $date = Carbon::createFromFormat('d-M-y', $value);
            return $date->format('d-m-Y');
        } catch (\Exception $e) {
            // Fallback parse (any recognized format)
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
            '*.store_code'      => 'required|exists:stores,code',
            '*.client_code'     => 'required|exists:users,code',
            '*.date'            => 'required',
            '*.payout_amount'   => 'required|numeric|min:0',
            // '*.msg_status'      => 'required',
            '*.comment'         => 'nullable|string|max:255',
        ];
    }
}
