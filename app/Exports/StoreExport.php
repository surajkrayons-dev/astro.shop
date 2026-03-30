<?php

namespace App\Exports;

use App\Models\Store;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = Auth::user();

        $query = Store::with(['creator', 'modifier', 'client', 'chain', 'format', 'region', 'city', 'state', 'country'])
            ->select('stores.*');

        /** 🔹 Filters apply karo **/
        if (!empty($this->filters['search_store'])) {
            $search = $this->filters['search_store'];
            $query->where(function ($q) use ($search) {
                $q->where('stores.code', 'like', "%{$search}%")
                  ->orWhere('stores.name', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['client_id'])) {
            $query->where('stores.client_id', 'like', "%{$this->filters['client_id']}%");
        }

        if (!empty($this->filters['format_id'])) {
            $query->where('stores.format_id', $this->filters['format_id']);
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('stores.status', $this->filters['status']);
        }

        /** 🔹 Access Control Logic **/
        if ($authUser->role_id != 1) {
            // Step 1: user ke client_ids nikalo
            $clientIds = array_filter(explode(',', $authUser->client_id));

            // Step 2: subordinates ke client ids bhi include karo
            $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
            $allStaffIds = array_merge([$authUser->id], $subordinateIds);

            // Step 3: un sab staff ke client_ids nikalo
            $relatedClientIds = User::whereIn('id', $allStaffIds)
                ->whereNotNull('client_id')
                ->pluck('client_id')
                ->toArray();

            // Step 4: flatten karo
            $flatClientIds = [];
            foreach ($relatedClientIds as $cids) {
                foreach (explode(',', $cids) as $cid) {
                    $cid = trim($cid);
                    if (!empty($cid)) $flatClientIds[] = $cid;
                }
            }
            $flatClientIds = array_unique($flatClientIds);

            // Step 5: stores filter karo sirf allowed clients ke base par
            if (!empty($flatClientIds)) {
                $query->whereIn('stores.client_id', $flatClientIds);
            } else {
                $query->whereRaw('1=0'); // No access
            }
        }

        return $query->get();
    }

    public function map($store): array
    {
        return [
            $store->client->code ?? 'N/A',
            $store->client->name ?? 'N/A',
            $store->client_store_code ?? 'N/A',
            $store->code ?? 'N/A',
            $store->name ?? 'N/A',
            $store->distributor_code ?? 'N/A',
            $store->distributor_name ?? 'N/A',
            $store->chain->code ?? 'N/A',
            $store->chain->name ?? 'N/A',
            $store->store_type ?? 'N/A',
            $store->format->code ?? 'N/A',
            $store->format->name ?? 'N/A',
            $store->region->code ?? 'N/A',
            $store->region->name ?? 'N/A',
            $store->latitude ?? 'N/A',
            $store->longitude ?? 'N/A',
            $store->distance ?? 'N/A',
            $store->address ?? 'N/A',
            $store->city->name ?? 'N/A',
            $store->state->name ?? 'N/A',
            $store->country->name ?? 'N/A',
            $store->status ? 'Yes' : 'No',
            $store->creator->code ?? 'N/A',
            $store->created_at instanceof Carbon ? $store->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $store->modifier->code ?? 'N/A',
            $store->updated_at instanceof Carbon ? $store->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

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
            'Created Date & Time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
