<?php

namespace App\Exports;

use App\Models\Chain;
use App\Models\User; 
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ChainExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Chain::with(['creator','modifier'])
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
    public function map($chain): array
    {
        return [
            $chain->code ?? 'N/A',
            $chain->name ?? 'N/A',
            $chain->status ? 'Yes' : 'No',
            $chain->creator ? $chain->creator->code : 'N/A',
            $chain->created_at instanceof Carbon ? $chain->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $chain->modifier ? $chain->modifier->code : 'N/A',
            $chain->updated_at instanceof Carbon ? $chain->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }

}
