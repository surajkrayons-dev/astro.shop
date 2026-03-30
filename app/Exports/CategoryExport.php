<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\User; // Assuming you store users here
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CategoryExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Category::with(['creator','modifier'])
            ->select('code', 'name', 'category_type', 'status', 'created_by','modified_by', 'created_at', 'updated_at');

        if (!empty($this->filters['code'])) {
            $query->where('code', 'like', '%' . $this->filters['code'] . '%');
        }

        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }
        if (!empty($this->filters['category_type'])) {
            $query->where('category_type', 'like', '%' . $this->filters['category_type'] . '%');
        }
        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        return $query->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($category): array
    {
        $category_name = config('system.category_types')[$category->category_type] ?? 'N/A';

        return [
            $category->code ?? 'N/A',
            $category->name ?? 'N/A',
            $category_name ?? 'N/A',
            $category->status ? 'Yes' : 'No',
            $category->creator ? $category->creator->code : 'N/A',
            $category->created_at instanceof Carbon ? $category->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $category->modifier ? $category->modifier->code : 'N/A',
            $category->updated_at instanceof Carbon ? $category->updated_at->format('d-m-Y, H:i:s') : 'N/A',
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
            'Category Type',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }

}
