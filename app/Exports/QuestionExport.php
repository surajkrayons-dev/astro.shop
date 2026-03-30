<?php

namespace App\Exports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class QuestionExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Question::with(['creator', 'modifier'])
            ->select(
                'question',
                'question_type',
                'input_type',
                'options',
                'order',
                'is_required',
                'status',
                'created_by',
                'modified_by',
                'created_at',
                'updated_at'
            );

        if (!empty($this->filters['question'])) {
            $query->where('question', 'like', '%' . $this->filters['question'] . '%');
        }
        if (!empty($this->filters['question_type'])) {
            $query->where('question_type', 'like', '%' . $this->filters['question_type'] . '%');
        }
        if (!empty($this->filters['input_type'])) {
            $query->where('input_type', 'like', '%' . $this->filters['input_type'] . '%');
        }
        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        return $query->get();
    }

    /**
     * Map each row
     */
    public function map($question): array
    {
        $questionType = config('system.question_type')[$question->question_type] ?? 'N/A';

        return [
            $question->question ?? 'N/A',
            $questionType ?? 'N/A',
            // ucfirst($question->input_type) ?? 'N/A',
            $question->input_type ?? 'N/A',
            $question->options ?? '',
            $question->order ?? 1,
            $question->is_required ? 'Yes' : 'No',
            $question->status ? 'Yes' : 'No',
            $question->creator ? $question->creator->code : 'N/A',
            $question->created_at instanceof Carbon ? $question->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $question->modifier ? $question->modifier->code : 'N/A',
            $question->updated_at instanceof Carbon ? $question->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Headings for file
     */
    public function headings(): array
    {
        return [
            'Question',
            'Question Type',
            'Input Type',
            'Options',
            'Order',
            'Is Required',
            'Status',
            'Created By',
            'Created Date & Time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
