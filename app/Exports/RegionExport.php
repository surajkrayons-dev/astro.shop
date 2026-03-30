<?php

namespace App\Exports;

use App\Models\Region;
use App\Models\User; // Assuming you store users here
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class RegionExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Region::with(['creator','modifier'])
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
    public function map($region): array
    {
        return [
            $region->code ?? 'N/A',
            $region->name ?? 'N/A',
            $region->status ? 'Yes' : 'No',
            $region->creator ? $region->creator->code : 'N/A',
            $region->created_at instanceof Carbon ? $region->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $region->modifier ? $region->modifier->code : 'N/A',
            $region->updated_at instanceof Carbon ? $region->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Region Code',
            'Region Name',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }

}
