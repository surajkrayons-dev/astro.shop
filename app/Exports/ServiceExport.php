<?php

namespace App\Exports;

use App\Models\User;
use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ServiceExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $authUser = Auth::user();

        $query = Service::with(['client', 'creator', 'modifier'])
            ->select(
                'client_id', 'code', 'name', 'service_type',
                'service_cost', 'status', 'created_by', 'modified_by',
                'created_at', 'updated_at'
            );

        // Client-wise restriction
        if ($authUser->type === 'client') {
            $query->where('client_id', $authUser->id);
        }

        return $query->get();
    }

    public function map($service): array
    {
        $client_code     = $service->client->code ?? 'N/A';
        $client_name     = $service->client->name ?? 'N/A';
        $service_type    = implode(', ', $service->getServiceTypeArrayAttribute());
        $cost            = '₹ ' . number_format($service->service_cost, 2);
        $status_name     = $service->status ? 'Yes' : 'No';

        $creator_code    = $service->creator->code ?? 'N/A';
        $modifier_code   = $service->modifier->code ?? 'N/A';

        return [
            $service->cod ?? 'N/A',
            $service->name ?? 'N/A',
            $client_code ?? 'N/A',
            $client_name ?? 'N/A',
            $service_type ?? 'N/A',
            $cost ?? 'N/A',
            $status_name ?? 'N/A',
            $creator_code ?? 'N/A',
            $service->created_at instanceof Carbon ? $service->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $modifier_code,
            $service->updated_at instanceof Carbon ? $service->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Service Code',
            'Service Name',
            'Client Code',
            'Client Name',
            'Service Types',
            'Service Cost',
            'Status',
            'Created By',
            'Created Date & Time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
