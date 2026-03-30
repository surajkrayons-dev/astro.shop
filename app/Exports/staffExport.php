<?php

namespace App\Exports;

use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $query = User::with(['createdBy', 'modifiedBy', 'region'])
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

        // 🔹 Apply Filters (same as getList controller)
        if (!empty($this->filters['search_staff'])) {
            $search = $this->filters['search_staff'];
            $query->where(function ($q) use ($search) {
                $q->where('users.code', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['client_id'])) {
            $clientId = $this->filters['client_id'];
            $query->whereRaw("FIND_IN_SET(?, users.client_id)", [$clientId]);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('users.status', $this->filters['status']);
        }

        // 🔹 Staff / Non-super-admin restriction (same as helper logic)
        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);

            if (empty($subordinateIds)) {
                $query->whereRaw('1=0'); // no access
            } else {
                $query->whereIn('users.id', $subordinateIds);
            }
        }

        return $query->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        $parentStaff = User::find($row->parent_id);

        // 🔹 Multiple Client Handling
        $clientIds = $row->client_id ? explode(',', $row->client_id) : [];
        $clients = User::whereIn('id', $clientIds)->where('type', 'client')->get();

        $clientCodes = $clients->pluck('code')->filter()->implode(', ') ?: 'N/A';
        $clientNames = $clients->pluck('name')->filter()->implode(', ') ?: 'N/A';

        return [
            $row->code ?? 'N/A',
            $row->name ?? 'N/A',
            $row->username ?? 'N/A',
            $clientCodes,
            $clientNames,
            $row->role_name ?? 'N/A',
            $parentStaff?->code ?? 'N/A',
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
            $row->status ? 'Yes' : 'No',
            $row->createdBy->code ?? 'N/A',
            $row->created_at instanceof Carbon ? $row->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $row->modifiedBy->code ?? 'N/A',
            $row->updated_at instanceof Carbon ? $row->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Staff Code',
            'Staff Name',
            'Username',
            'Client Codes',
            'Client Names',
            'Role',
            'Staff Parent Code',
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
            'Status',
            'Created By',
            'Created Date & Time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
