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
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $authUser = auth()->user();

        $query = StoreKycMapping::with(['store', 'createdBy', 'modifiedBy'])
            ->leftJoin('stores', 'stores.id', '=', 'store_kyc_mappings.store_id');

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('stores.client_id', $clientIds);
        }

        return $query->select('store_kyc_mappings.*')->get();
    }

    /**
     * Map data for each row to include both code & name for related entities.
     */
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
