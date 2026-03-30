<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ClientExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Fetch only users with role_id = 2 (clients).
     */
    public function collection()
    {
        $authUser = auth()->user();

        $query = User::with(['createdBy', 'modifiedBy'])
            ->where('role_id', 2);

        // 🔹 Apply filters
        if (!empty($this->filters['search_client'])) {
            $search = $this->filters['search_client'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('id', $clientIds);
        }

        return $query->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        // $region_name = config('system.region')[$row->region] ?? 'N/A';

        $configServices = config('system.service_types');

        $servicesList = $row->services->pluck('services')->map(function ($serviceKey) use ($configServices) {
            $normalizedKey = strtolower(trim($serviceKey));
            return $configServices[$normalizedKey] ?? ucfirst(str_replace('_', ' ', $serviceKey));
        });

        $serviceTypes = $servicesList->isNotEmpty() ? $servicesList->implode(', ') : 'N/A';

        $serviceCost = $row->services->sum('service_cost');
        $serviceCost = $serviceCost > 0 ? $serviceCost : 'N/A';

        return [
            $row->code ?? '',
            $row->username ?? 'N/A',
            $row->email ?? 'N/A',
            $row->name ?? 'N/A',
            $row->mobile ?? '',
            $row->dob ? Carbon::parse($row->dob)->format('d-m-Y') : 'N/A',
            $row->gender ?? 'N/A',
            $row->profile_image ? url('storage/user/' . $row->profile_image) : 'N/A',
            $row->address ?? 'N/A',
            $row->date_of_joining ? Carbon::parse($row->date_of_joining)->format('d-m-Y') : 'N/A',
            '*******',
            $row->status ? 'Yes' : 'No',
            $serviceTypes,
            $serviceCost,
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
            'Address',
            'Date Of Joining',
            'Password',
            'Status',
            'Services',
            'Service Cost',
            'Created By',
            'Created Date & Time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
