<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class staffExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $authUser = auth()->user();

        $query = User::with(['createdBy', 'modifiedBy', 'client', 'region'])
            ->select(
                'users.*',
                'roles.name as role_name',
                'countries.name as country_name',
                'states.name as state_name',
                'cities.name as city_name',
                'pin_codes.pin_code as pin_code'
            )
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->leftJoin('countries', 'countries.id', '=', 'users.country_id')
            ->leftJoin('states', 'states.id', '=', 'users.state_id')
            ->leftJoin('cities', 'cities.id', '=', 'users.city_id')
            ->leftJoin('pin_codes', 'pin_codes.id', '=', 'users.pincode_id')
            ->where('users.type', 'staff');

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('users.client_id', $clientIds);
        }

        return $query->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        return [
            $row->code ?? 'N/A',
            $row->name ?? 'N/A',
            $row->username ?? 'N/A',
            $row->client->code ?? 'N/A',
            $row->client->name ?? 'N/A',
            $row->role_name ?? 'N/A',
            $row->email ?? 'N/A',
            $row->mobile ?? 'N/A',
            $row->dob ?? 'N/A',
            $row->date_of_joining ?? 'N/A',
            $row->gender ?? 'N/A',
            $row->salary ?? 'N/A',
            $row->region->code ?? 'N/A',
            $row->region->name ?? 'N/A',
            $row->profile_image ? url('storage/user/' . $row->profile_image) : 'N/A',
            $row->address ?? 'N/A',
            $row->country_name ?? 'N/A',
            $row->state_name ?? 'N/A',
            $row->city_name ?? 'N/A',
            $row->pin_code ?? 'N/A',
            '*******',
            // $row->kyc_status ? 'True' : 'False',
            $row->status ? 'Yes' : 'No',
            $row->createdBy->code ?? 'N/A',
            $row->created_at instanceof Carbon ? $row->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $row->modifiedBy->code ?? 'N/A',
            $row->updated_at instanceof Carbon ? $row->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Headings in the same order as map().
     */
    public function headings(): array
    {
        return [
            'Staff Code',
            'Staff Name',
            'Username',
            'Client Code',
            'Client Name',
            'Role',
            'Email Id',
            'Mobile',
            'DOB',
            'Date Of Joining',
            'Gender',
            'Salary',
            'Region Code',
            'Region Name',
            'Profile Image',
            'Address',
            'Country',
            'State',
            'City',
            'Pincode',
            'Password',
            // 'Kyc Status',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
