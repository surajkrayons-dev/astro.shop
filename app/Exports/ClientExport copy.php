<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ClientExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Fetch only users with role_id = 2 (clients).
     */
    public function collection()
    {
        return User::with(['createdBy', 'modifiedBy'])
            ->where('role_id', 2)
            ->get();
    }


    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        $region_name = config('system.region')[$row->region] ?? 'N/A';

        return [
            $row->code ?? '',
            $row->username ?? 'N/A',
            $row->email ?? 'N/A',
            $row->name ?? 'N/A',
            $row->mobile ?? '',
            $row->dob ? Carbon::parse($row->dob)->format('d-m-Y') : 'N/A',
            $row->gender ?? 'N/A',
            $row->profile_image ?? 'N/A',
            // $region_name,
            // $row->country->name ?? '',
            // $row->state->name ?? '',
            // $row->city->name ?? '',
            // $row->pincode->pin_code ?? '',
            $row->address ?? 'N/A',
            // $row->salary ?? '',
            $row->date_of_joining ? Carbon::parse($row->date_of_joining)->format('d-m-Y') : 'N/A',
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
            'Client Code',
            'Username',
            'Email',
            'Name',
            'Mobile',
            'Dob',
            'Gender',
            'Profile Image',
            // 'Region',
            // 'Country',
            // 'State',
            // 'City',
            // 'Pincode',
            'Address',
            // 'Salary',
            'Date Of Joining',
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
