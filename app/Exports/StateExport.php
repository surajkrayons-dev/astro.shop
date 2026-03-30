<?php

namespace App\Exports;

use App\Models\User;
use App\Models\State;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class StateExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return State::with(['creator','modifier'])->get();
    }

    public function map($state): array
    {
        $countryName = Country::find($state->country_id)?->name ?? 'N/A';

        return [
            $state->name ?? 'N/A',
            $countryName,
            $state->status == 1 ? 'Yes' : 'No',
            $state->creator ? $state->creator->code : 'N/A',
            $state->created_at instanceof Carbon ? $state->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $state->modifier ? $state->modifier->code : 'N/A',
            $state->updated_at instanceof Carbon ? $state->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Country',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
