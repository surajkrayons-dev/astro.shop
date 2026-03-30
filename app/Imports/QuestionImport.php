<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;
use Log;

class QuestionImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $currentRow = 2;
    protected array $seen = [];

    public function model(array $row)
    {
        try {
            $rowNumber = $this->currentRow;
            $this->currentRow++;

            $question     = trim($row['question'] ?? '');
            $questionType = trim($row['question_type'] ?? '');
            $inputType    = trim($row['input_type'] ?? '');
            $options      = trim($row['options'] ?? '');
            $order        = isset($row['order']) && is_numeric($row['order']) ? (int)$row['order'] : 1;
            $isRequired   = strtolower(trim($row['is_required'] ?? '')) === 'yes' ? 1 : 0;
            $status       = strtolower(trim($row['status'] ?? '')) === 'yes' ? 1 : 0;

            if (empty($question)) {
                throw new \Exception("Question is empty");
            }

            $uniqueKey = strtolower($question);
            if (isset($this->seen[$uniqueKey])) {
                $previousRow = $this->seen[$uniqueKey];
                throw new \Exception("Row #{$rowNumber}: Duplicate (question: '{$question}') also found on row #{$previousRow}.");
            }
            $this->seen[$uniqueKey] = $rowNumber;

            $typeKey = null;
            foreach (config('system.question_type') as $key => $value) {
                if (strtolower($value) === strtolower($questionType) || strtolower($key) === strtolower($questionType)) {
                    $typeKey = $key;
                    break;
                }
            }
            if ($typeKey === null) {
                throw new \Exception("Row #{$rowNumber}: Invalid question_type '{$questionType}'");
            }

            $allowedInputTypes = ['text', 'radio', 'checkbox', 'select', 'image'];
            $matchedInputType = null;

            foreach ($allowedInputTypes as $allowed) {
                if (strtolower($inputType) === strtolower($allowed)) {
                    $matchedInputType = $allowed;
                    break;
                }
            }

            if ($matchedInputType === null) {
                throw new \Exception("Row #{$rowNumber}: Invalid input_type '{$inputType}'");
            }

            if (in_array($matchedInputType, ['radio', 'checkbox', 'select'])) {
                if (empty($options)) {
                    throw new \Exception("Row #{$rowNumber}: Options are required for input_type '{$matchedInputType}'");
                }
                $options = implode(',', array_map('trim', explode(',', $options)));
            } else {
                $options = null;
            }

            $existing = Question::where('question', $question)->first();

            $attributes = [
                'question_type' => $typeKey,
                'input_type'    => $matchedInputType,
                'options'       => $options,
                'order'         => $order,
                'is_required'   => $isRequired,
                'status'        => $status,
            ];

            if ($existing) {
                $attributes['modified_by'] = auth()->id();
                $existing->update($attributes);
                return $existing;
            } else {
                $attributes['created_by'] = auth()->id();
                $attributes['question']   = $question;
                return Question::create($attributes);
            }

        } catch (Throwable $th) {
            Log::error('Import Error Row #' . $this->currentRow . ': ' . json_encode($row) . ' | ' . $th->getMessage());
            throw new \Exception("❌ Import failed at row #{$rowNumber}: " . $th->getMessage());
        }
    }

    public function rules(): array
    {
        return [
            '*.question'      => 'required|string|max:500',
            '*.question_type' => 'required|string|max:100',
            '*.input_type'    => 'required|string|in:text,radio,checkbox,select,image',
            '*.options'       => 'nullable|string|max:1000',
            '*.order'         => 'nullable|integer|min:1|max:255',
            '*.is_required'   => 'required|string|in:Yes,No,yes,no',
            '*.status'        => 'required|string|in:Yes,No,yes,no',
        ];
    }
}
