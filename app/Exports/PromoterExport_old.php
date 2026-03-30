<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PromoterExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Fetch only users with role_id = 3 (Promoters).
     */
    public function collection()
    {
        $authUser = auth()->user();

        $query = User::with(['createdBy', 'modifiedBy'])
            ->where('role_id', 3);

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('client_id', $clientIds);
        }

        return $query->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        // $region_name = config('system.region')[$row->region] ?? 'N/A';

        return [
            $row->code ?? 'N/A',
            $row->name ?? 'N/A',
            $row->username ?? 'N/A',
            $row->client->code ?? 'N/A',
            $row->client->name ?? 'N/A',
            $row->email ?? 'N/A',
            $row->mobile ?? 'N/A',
            $row->dob ? Carbon::parse($row->dob)->format('d-m-Y') : 'N/A',
            $row->gender ?? 'N/A',
            $row->profile_image ? url('storage/user/' . $row->profile_image) : 'N/A',
            $row->region->code ?? 'N/A',
            $row->region->name ?? 'N/A',
            $row->country->name ?? 'N/A',
            $row->state->name ?? 'N/A',
            $row->city->name ?? 'N/A',
            $row->pincode->pin_code ?? 'N/A',
            $row->address ?? 'N/A',
            $row->salary ?? 'N/A',
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
            'Promoter Code',
            'Promoter Name',
            'Username',
            'Client Code',
            'Client Name',
            'Email',
            'Mobile',
            'Dob',
            'Gender',
            'Profile Image',
            'Region Code',
            'Region Name',
            'Country',
            'State',
            'City',
            'Pincode',
            'Address',
            'Salary',
            'Date Of Joining',
            'Password',
            // 'Kyc Status',
            'Status',
            'Created By',
            'Created Date & time',
            'Updated By',
            'Modified Date & Time',
        ];
    }

}
