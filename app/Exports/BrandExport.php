<?php

namespace App\Exports;

use App\Models\Brand;
use App\Models\User; // Assuming you store users here
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class BrandExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Brand::with(['creator','modifier'])
        ->select('code', 'name', 'brand_type', 'status', 'created_by','modified_by', 'created_at', 'updated_at');

        if (!empty($this->filters['code'])) {
            $query->where('code', 'like', '%' . $this->filters['code'] . '%');
        }

        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }
        if (!empty($this->filters['brand_type'])) {
            $query->where('brand_type', 'like', '%' . $this->filters['brand_type'] . '%');
        }
        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }
        
        return $query->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($brand): array
    {
        $brand_name = config('system.brand_types')[$brand->brand_type] ?? 'N/A';

        return [
            $brand->code ?? 'N/A',
            $brand->name ?? 'N/A',
            $brand_name ?? 'N/A',
            $brand->status ? 'Yes' : 'No',
            $brand->creator ? $brand->creator->code : 'N/A',
            $brand->created_at instanceof Carbon ? $brand->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $brand->modifier ? $brand->modifier->code : 'N/A' ,
            $brand->updated_at instanceof Carbon ? $brand->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Brand Code',
            'Brand Name',
            'brand Type',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }

}
