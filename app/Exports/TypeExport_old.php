<?php

namespace App\Exports;

use App\Models\Type;
use App\Models\User; // Assuming you store users here
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class TypeExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // ✅ Fetch related user in same query using Eager Loading
        return Type::with(['creator','modifier'])
            ->select('code', 'name', 'type', 'status', 'created_by','modified_by', 'created_at', 'updated_at')
            ->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($type): array
    {
        $type_name = config('system.types')[$type->type] ?? 'Unknown';

        return [
            $type->code ?? 'N/A',
            $type->name ?? 'N/A',
            $type_name ?? 'N/A',
            $type->status ? 'Yes' : 'No',
            $type->creator ? $type->creator->code : 'N/A',
            $type->created_at instanceof Carbon ? $type->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $type->modifier ? $type->modifier->code : 'N/A',
            $type->updated_at instanceof Carbon ? $type->updated_at->format('d-m-Y, H:i:s') : 'N/A',
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
            'Type',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }

}
