<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Client;  // Assuming Client is the User model with type = 'client'
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class SubAdminExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $authUser = auth()->user();

        $query = User::with(['createdBy', 'modifiedBy', 'role', 'region', 'country', 'state', 'city', 'pincode'])
            ->where('id', '<>', 1)
            ->where('type', 'admin');

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->where(function ($q) use ($clientIds) {
                foreach ($clientIds as $clientId) {
                    $q->orWhereRaw("FIND_IN_SET(?, client_id)", [$clientId]);
                }
            });
        }

        return $query->get();
    }

    public function map($row): array
    {
        $created_at = $row->created_at ? Carbon::parse($row->created_at) : null;
        $updated_at = $row->updated_at ? Carbon::parse($row->updated_at) : null;

        $clientIds = $row->client_id ? explode(',', $row->client_id) : [];

        $clients = User::whereIn('id', $clientIds)->where('type', 'client')->get();

        $clientCodes = $clients->pluck('code')->filter()->implode(', ') ?: 'N/A';
        $clientNames = $clients->pluck('name')->filter()->implode(', ') ?: 'N/A';

        return [
            $row->code ?? 'N/A',
            $row->name,
            $row->username,
            $clientCodes,
            $clientNames,
            $row->role->name ?? 'N/A',
            $row->email,
            $row->mobile ?? 'N/A',
            $row->dob ?? 'N/A',
            $row->date_of_joining ?? 'N/A',
            $row->gender ?? 'N/A',
            $row->salary ?? 'N/A',
            $row->region->code ?? 'N/A',
            $row->region->name ?? 'N/A',
            $row->profile_image ? url('storage/user/' . $row->profile_image) : 'N/A',
            $row->address ?? 'N/A',
            $row->country->name ?? 'N/A',
            $row->state->name ?? 'N/A',
            $row->city->name ?? 'N/A',
            $row->pincode->code ?? 'N/A',
            '*******',
            $row->status ? 'Yes' : 'No',
            $row->createdBy->code ?? 'N/A',
            $created_at ? $created_at->format('d-m-Y, H:i:s') : 'N/A',
            $row->modifiedBy->code ?? 'N/A',
            $updated_at ? $updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Sub Admin code',
            'Sub Admin Name',
            'Username',
            'Client Codes',
            'Client Names',
            'Role',
            'Email Id',
            'Mobile Number',
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
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}

