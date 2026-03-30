<?php

namespace App\Exports;

use App\Models\User;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class CityExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return City::with(['creator','modifier'])->get();
    }

    public function map($city): array
    {
        $countryName = Country::find($city->country_id)?->name ?? 'N/A';
        $stateName = State::find($city->state_id)?->name ?? 'N/A';

        return [
            $city->name ?? '',
            $countryName ?? 'N/A',
            $stateName ?? 'N/A',
            $city->status == 1 ? 'True' : 'False',
            $city->creator ? $city->creator->code : 'N/A',
            $city->created_at instanceof Carbon ? $city->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $city->modifier ? $city->modifier->code : 'N/A',
            $city->updated_at instanceof Carbon ? $city->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Country',
            'State',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
