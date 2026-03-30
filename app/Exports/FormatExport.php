<?php

namespace App\Exports;

use App\Models\Format;
use App\Models\User; // Assuming you store users here
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class FormatExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Format::with(['creator','modifier'])
            ->select('code', 'name', 'status', 'created_by','modified_by', 'created_at', 'updated_at');

        if (!empty($this->filters['code'])) {
            $query->where('code', 'like', '%' . $this->filters['code'] . '%');
        }

        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }
        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }
        
        return $query->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($format): array
    {
        return [
            $format->code ?? 'N/A',
            $format->name ?? 'N/A',
            $format->status ? 'Yes' : 'No',
            $format->creator ? $format->creator->code : 'N/A',
            $format->created_at instanceof Carbon ? $format->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $format->modifier ? $format->modifier->code : 'N/A',
            $format->updated_at instanceof Carbon ? $format->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Format Code',
            'Format Name',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }

}
