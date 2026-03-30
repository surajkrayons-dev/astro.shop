<?php

namespace App\Exports;

use App\Models\StoreUserMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreUserMappingExport implements FromCollection, WithHeadings, WithMapping
{
    protected $maxDays = 31;
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Fetch all StoreUserMapping rows with relationships.
     */
    public function collection()
    {
        $authUser = auth()->user();

        $query = StoreUserMapping::with(['store', 'user', 'client', 'createdBy', 'modifiedBy'])
            ->leftJoin('stores', 'store_user_mappings.store_id', '=', 'stores.id')
            ->select('store_user_mappings.*');

        // ✅ Apply filters from frontend
        if (!empty($this->filters['store_id'])) {
            $query->where('store_user_mappings.store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['user_id'])) {
            $query->where('store_user_mappings.user_id', $this->filters['user_id']);
        }

        if (!empty($this->filters['client_id'])) {
            $query->where('store_user_mappings.client_id', $this->filters['client_id']);
        }

        if (!empty($this->filters['month'])) {
            $query->where('store_user_mappings.month', $this->filters['month']);
        }

        if (!empty($this->filters['year'])) {
            $query->where('store_user_mappings.year', $this->filters['year']);
        }

        // 🔒 Subordinate logic (non-super-admins)
        if ($authUser->role_id != 1) {
            // Step 1: Current user ke client IDs
            $clientIds = array_filter(explode(',', $authUser->client_id));

            // Step 2: Subordinates nikaalo (recursive)
            $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);

            // Step 3: Apne + subordinates ke client IDs nikaalo
            $allStaffIds = array_merge([$authUser->id], $subordinateIds);

            $relatedClientIds = User::whereIn('id', $allStaffIds)
                ->whereNotNull('client_id')
                ->pluck('client_id')
                ->toArray();

            // Step 4: Flatten IDs (comma separated strings)
            $flatClientIds = [];
            foreach ($relatedClientIds as $cids) {
                foreach (explode(',', $cids) as $cid) {
                    $cid = trim($cid);
                    if (!empty($cid)) $flatClientIds[] = $cid;
                }
            }
            $flatClientIds = array_unique($flatClientIds);

            // Step 5: Restrict by accessible clients
            if (!empty($flatClientIds)) {
                $query->whereIn('stores.client_id', $flatClientIds);
            } else {
                $query->whereRaw('1=0'); // no access
            }
        }

        return $query->get();
    }

    /**
     * Map each row to the columns defined in headings().
     */
    public function map($row): array
    {
        $baseData = [
            $row->store->code ?? 'N/A',
            $row->store->name ?? 'N/A',
            $row->user->code ?? 'N/A',
            $row->user->name ?? 'N/A',
            $row->client->code ?? 'N/A',
            $row->client->name ?? 'N/A',
            $row->month,
            $row->year,
        ];

        // Days data
        $dateArray = $row->date ?? [];
        $daysData = [];
        for ($i = 1; $i <= $this->maxDays; $i++) {
            $dayKey = 'day' . $i;
            if (array_key_exists($dayKey, $dateArray)) {
                // If it's an array (like ["yes"]), implode to get a string
                $val = is_array($dateArray[$dayKey])
                    ? implode(', ', $dateArray[$dayKey])
                    : $dateArray[$dayKey];
                $daysData[] = $val;
            } else {
                $daysData[] = ''; // or 'N/A'
            }
        }

        // Tail columns: Created By, Modified By
        $tailData = [
            $row->createdBy->code ?? 'N/A',
            $row->created_at instanceof Carbon ? $row->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $row->modifiedBy->code ?? 'N/A',
            $row->updated_at instanceof Carbon ? $row->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];

        // Merge all
        return array_merge($baseData, $daysData, $tailData);
    }


    /**
     * Headings in the order:
     */
    public function headings(): array
    {
        // Base columns
        $baseHeadings = [
            'Store Code',
            'Store Name',
            'Promoter Code',
            'Promoter Name',
            'Client Code',
            'Client Name',
            'Month',
            'Year',
        ];

        // Day1..Day31
        for ($i = 1; $i <= $this->maxDays; $i++) {
            $baseHeadings[] = 'Day' . $i;
        }

        // Tail columns
        $tailHeadings = [
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];

        // Merge all
        return array_merge($baseHeadings, $tailHeadings);
    }
}
