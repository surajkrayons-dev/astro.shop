<?php

namespace App\Exports;

use App\Models\User;
use App\Models\PinCode;
use App\Models\City;
use App\Models\State;
use App\Models\Country;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PinCodeExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return PinCode::with(['creator','modifier'])->get();
    }

    public function map($pincode): array
    {
        $countryName = Country::find($pincode->country_id)?->name ?? 'N/A';
        $stateName = State::find($pincode->state_id)?->name ?? 'N/A';
        $cityName = City::find($pincode->city_id)?->name ?? 'N/A';

        return [
            $pincode->pin_code ?? 'N/A',
            $cityName ?? 'N/A',
            $stateName ?? 'N/A',
            $countryName ?? 'N/A',
            $pincode->status == 1 ? 'Yes' : 'No',
            $pincode->creator ? $pincode->creator->code : 'N/A',
            $pincode->created_at instanceof Carbon ? $pincode->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $pincode->modifier ? $pincode->modifier->code : 'N/A',
            $pincode->updated_at instanceof Carbon ? $pincode->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Pin Code',
            'City',
            'State',
            'Country',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
