<?php

namespace App\Exports;

use App\Models\StoreUserModuleMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreUserModuleMappingExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Fetch all StoreUserModuleMapping rows with relationships.
     */
    public function collection()
    {
        $authUser = auth()->user();

        $query = StoreUserModuleMapping::with(['store', 'user', 'createdBy', 'modifiedBy'])
            ->leftJoin('stores', 'stores.id', '=', 'store_user_module_mappings.store_id');

        if ($authUser->role_id != 1) {
            $clientIds = array_filter(explode(',', $authUser->client_id));
            $query->whereIn('stores.client_id', $clientIds);
        }

        return $query->select('store_user_module_mappings.*')->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        $storeActionRequiredDates = $row->is_store_action_required_date ?? 'N/A';
        if ($storeActionRequiredDates) {
            $decoded = json_decode($storeActionRequiredDates, true);
            if (is_array($decoded)) {
                $storeActionRequiredDates = implode(', ', $decoded);
            }
        } else {
            $storeActionRequiredDates = '';
        }

        return [
            $row->store->code ?? 'N/A',
            $row->store->name ?? 'N/A',
            $row->user->code ?? 'N/A',
            $row->user->name ?? 'N/A',
            $row->location ?? 'N/A',
            $row->store_action ?? 'N/A',
            $storeActionRequiredDates,
            $row->store_action_section ?? 'N/A',
            $row->store_action_section_older ?? 'N/A',
            $row->is_location_required ? 'Yes' : 'No',
            $row->is_store_action_required ? 'Yes' : 'No',
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
            'Store Code',
            'Store Name',
            'Promoter Code',
            'Promoter Name',
            'Location',
            'Store Action',
            'Store Action Required Dates',
            'Store Action Section',
            'Store Action Section Older',
            'Is Location Required',
            'Is Store Action Required',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
