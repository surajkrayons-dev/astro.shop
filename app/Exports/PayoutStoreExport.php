<?php

namespace App\Exports;

use App\Models\PayoutStore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class PayoutStoreExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        // Get client & subordinate hierarchy
        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        // Get all allowed clients (from user & subordinate)
        $allowedClientIds = User::whereIn('id', $allowedUserIds)
            ->pluck('client_id')
            ->filter()
            ->flatMap(fn($ids) => explode(',', $ids))
            ->unique()
            ->toArray();

        // Build query
        $query = PayoutStore::with(['store', 'client', 'createdBy', 'modifiedBy'])
            ->leftJoin('stores', 'stores.id', '=', 'payout_stores.store_id')
            ->leftJoin('users as clients', 'clients.id', '=', 'payout_stores.client_id')
            ->select('payout_stores.*');

        // Apply hierarchy filter (staff/subordinate only see their clients' stores)
        if ($authUser->role_id != 1) {
            if (!empty($allowedClientIds)) {
                $query->whereIn('stores.client_id', $allowedClientIds);
            } else {
                // no allowed clients → no data
                $query->whereRaw('1=0');
            }
        }

        // Apply optional filters
        if (!empty($this->filters['store_id'])) {
            $query->where('payout_stores.store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['client_id'])) {
            $query->where('payout_stores.client_id', $this->filters['client_id']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(payout_stores.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        return $query->orderByDesc('payout_stores.created_at')->get();
    }

    /**
     * Map data for each row to include both code & name for related entities.
     */
    public function map($payout): array
    {
        return [
            $payout->store->code ?? 'N/A',
            $payout->store->name ?? 'N/A',
            $payout->client->code ?? 'N/A',
            $payout->client->name ?? 'N/A',
            $payout->date ?? 'N/A',
            $payout->payout_amount ?? 0,
            // $payout->msg_status ? 'True' : 'False',
            $payout->comment ?? 'N/A',
            $payout->createdBy?->code ?? 'N/A',
            $payout->created_at instanceof Carbon ? $payout->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $payout->modifiedBy?->code ?? '',
            $payout->updated_at instanceof Carbon ? $payout->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Define headings for the exported file.
     */
    public function headings(): array
    {
        return [
            'Store Code',
            'Store Name',
            'Client Code',
            'Client Name',
            'Date',
            'Payout Amount',
            // 'Msg Status',
            'Comment',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }
}
