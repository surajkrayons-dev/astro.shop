<?php

namespace App\Exports;

use App\Models\Type;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class TypeExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Type::with(['creator','modifier'])
            ->select('code', 'name', 'type', 'status', 'created_by','modified_by', 'created_at', 'updated_at');

        // Filters
        if (!empty($this->filters['code'])) {
            $query->where('code', 'like', '%' . $this->filters['code'] . '%');
        }

        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }

        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date'])
                  ->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        return $query->get();
    }

    public function map($type): array
    {
        $type_name = config('system.types')[$type->type] ?? 'Unknown';

        return [
            $type->code ?? 'N/A',
            $type->name ?? 'N/A',
            $type_name,
            $type->status ? 'Yes' : 'No',
            $type->creator->code ?? 'N/A',
            $type->created_at ? $type->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $type->modifier->code ?? 'N/A',
            $type->updated_at ? $type->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Type',
            'Status',
            'Created By',
            'Created Date & Time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
