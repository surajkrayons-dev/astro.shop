<?php

namespace App\Exports;

use App\Models\Store;
use App\Models\StoreUserMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $authUser = Auth::user();

        $query = Store::with(['creator', 'city', 'state', 'country', 'chain', 'client'])
            ->select(
                'client_id', 'client_store_code', 'code', 'name', 'distributor_code',
                'distributor_name', 'store_type', 'format_id', 'chain_id',
                'region_id', 'latitude', 'longitude', 'distance', 'address', 'city_id', 'state_id', 'country_id', 'created_by',
                'modified_by', 'status', 'created_at', 'updated_at'
            );

        if ($authUser->type === 'client') {
            $query->where('client_id', $authUser->id);
        } elseif ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('client_id', $clientIds);
        }

        return $query->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($store): array
    {
        $client_name   = $store->client ? $store->client->code : 'N/A';
        $chain_code         = $store->chain ? $store->chain->code : 'N/A';
        $chain_name         = $store->chain ? $store->chain->name : 'N/A';
        $city_name     = $store->city ? $store->city->name : 'N/A';
        $state_name    = $store->state ? $store->state->name : 'N/A';
        $country_name  = $store->country ? $store->country->name : 'N/A';

        // $region_name   = config('system.region')[$store->region] ?? 'N/A';
        // $format_name   = config('system.format')[$store->format] ?? 'N/A';

        $region_code         = $store->region ? $store->region->code : 'N/A';
        $region_name         = $store->region ? $store->region->name : 'N/A';
        $format_code         = $store->format ? $store->format->code : 'N/A';
        $format_name         = $store->format ? $store->format->name : 'N/A';

        $status_name   = $store->status ? 'Yes' : 'No';

        $creator_name  = $store->creator ? $store->creator->code : 'N/A';
        $modifier_name = $store->modified_by ? $store->modifier->code : 'N/A';;

        return [
            $store->client->code ?? 'N/A',
            $store->client->name ?? 'N/A',
            $store->client_store_code ?? 'N/A',
            $store->code ?? 'N/A',
            $store->name ?? 'N/A',
            $store->distributor_code ?? 'N/A',
            $store->distributor_name ?? 'N/A',
            // $kyc_status,
            $chain_code,
            $chain_name,
            $store->store_type ?? 'N/A',
            $format_code,
            $format_name,
            $region_code,
            $region_name,
            $store->latitude ?? 'N/A',
            $store->longitude ?? 'N/A',
            $store->distance ?? 'N/A',
            $store->address ?? 'N/A',
            $city_name ?? 'N/A',
            $state_name ?? 'N/A',
            $country_name ?? 'N/A',
            $status_name ?? 'N/A',
            $creator_name,
            $store->created_at instanceof Carbon ? $store->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $modifier_name,
            $store->updated_at instanceof Carbon ? $store->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Client Code',
            'Client Name',
            'Client Store Code',
            'Store Code',
            'Store Name',
            'Distributor Code',
            'Distributor Name',
            // 'KYC Status',
            'Store Chain Code',
            'Store Chain Name',
            'Store Type',
            'Format Code',
            'Format Name',
            'Region Code',
            'Region Name',
            'Latitude',
            'Longitude',
            'Distance',
            'Address',
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
