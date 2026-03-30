<?php

namespace App\Imports;

use App\Models\CategoryTracking;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class CategoryTrackingImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $code = trim($row['category_tracking_code'] ?? '');
            $uniqueKey = strtolower($code);

            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception(
                    "Row #{$rowNumber}: Duplicate (Code: '{$code}') also found on row #{$previousRow}."
                );
            }

            $this->seen[$uniqueKey] = $rowNumber;

            $countOf = trim($row['count_of_facing_shelf']);
            $targetFormat = trim($row['target_format']);

            $countOf_key = array_search($countOf, config('system.count_of'));
            if ($countOf_key === false) {
                throw new \Exception("Row #{$rowNumber}: Invalid value for 'count_of_facing_shelf': '{$countOf}'");
            }

            $targetFormat_key = array_search($targetFormat, config('system.target_format'));
            if ($targetFormat_key === false) {
                throw new \Exception("Row #{$rowNumber}: Invalid value for 'target_format': '{$targetFormat}'");
            }

            $attributes = [
                'name'                   => $row['category_tracking_name'],
                'count_of'               => $countOf_key,
                'target_format'          => $targetFormat_key,
                'is_question_available'  => strtolower(trim($row['is_question_available'] ?? '')) === 'yes' ? 1 : 0,
                'is_category_available'  => strtolower(trim($row['is_category_available'] ?? '')) === 'yes' ? 1 : 0,
                'status'                 => strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0,
            ];

            $existing = CategoryTracking::where('code', $code)->first();

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['code'] = $code;
                $attributes['created_by'] = auth()->id();
                return CategoryTracking::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Error importing row #'.$this->currentRow.': '. json_encode($row) . ' | Error: ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}.\n👉 " . $th->getMessage());
        }

        return null;
    }

    public function rules(): array
    {
        return [
            '*.category_tracking_code'   => 'required|max:200',
            '*.category_tracking_name'   => 'required|max:200',
            '*.count_of_facing_shelf'    => 'required|max:200',
            '*.target_format'            => 'required|max:200',
            '*.is_question_available'    => 'required',
            '*.is_category_available'    => 'required',
            '*.status'                   => 'required',
        ];
    }
}
