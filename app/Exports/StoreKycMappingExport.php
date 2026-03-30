<?php

namespace App\Exports;

use App\Models\StoreKycMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreKycMappingExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $query = StoreKycMapping::with(['store', 'createdBy', 'modifiedBy'])
            ->leftJoin('stores', 'stores.id', '=', 'store_kyc_mappings.store_id');

        // 🔹 Apply filters
        if (!empty($this->filters['store_id'])) {
            $query->where('store_kyc_mappings.store_id', $this->filters['store_id']);
        }
        if (!empty($this->filters['month'])) {
            $query->where('store_kyc_mappings.month', 'like', '%' . $this->filters['month'] . '%');
        }
        if (!empty($this->filters['year'])) {
            $query->where('store_kyc_mappings.year', 'like', '%' . $this->filters['year'] . '%');
        }
        if (isset($this->filters['is_required']) && $this->filters['is_required'] !== '') {
            $query->where('store_kyc_mappings.is_required', $this->filters['is_required']);
        }

        // 🔹 Subordinate access for non-super-admin users
        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
            $allStaffIds = array_merge([$authUser->id], $subordinateIds);

            $relatedClientIds = \App\Models\User::whereIn('id', $allStaffIds)
                ->whereNotNull('client_id')
                ->pluck('client_id')->toArray();

            // Flatten comma-separated client IDs
            $flatClientIds = [];
            foreach ($relatedClientIds as $cids) {
                foreach (explode(',', $cids) as $cid) {
                    $cid = trim($cid);
                    if (!empty($cid)) $flatClientIds[] = $cid;
                }
            }
            $flatClientIds = array_unique($flatClientIds);

            if (!empty($flatClientIds)) {
                $query->whereIn('stores.client_id', $flatClientIds);
            } else {
                $query->whereRaw('1=0'); // no access
            }
        }

        return $query->select('store_kyc_mappings.*')->get();
    }

    public function map($mapping): array
    {
        return [
            $mapping->store->code ?? 'N/A',
            $mapping->store->name ?? 'N/A',
            $mapping->month ?? 'N/A',
            $mapping->year ?? 'N/A',
            $mapping->is_required ? 'Yes' : 'No',
            $mapping->is_acc_passbook_required ? 'Yes' : 'No',
            $mapping->is_pan_card_required ? 'Yes' : 'No',
            $mapping->is_aadhaar_card_required ? 'Yes' : 'No',
            $mapping->createdBy ? $mapping->createdBy->code : 'N/A',
            $mapping->created_at instanceof Carbon ? $mapping->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $mapping->modifiedBy ? $mapping->modifiedBy->code : 'N/A',
            $mapping->updated_at instanceof Carbon ? $mapping->updated_at->format('d-m-Y, H:i:s') : 'N/A',
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
            'Month',
            'Year',
            'Is Required',
            'Is Acc Passbook Required',
            'Is Pan Card Required',
            'Is Aadhaar Card Required',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }
}
