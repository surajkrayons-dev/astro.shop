<?php

namespace App\Exports;

use App\Models\PosmStoreMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PosmStoreMappingExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        $query = PosmStoreMapping::with(['posm', 'store', 'createdBy', 'modifiedBy'])
            ->select('posm_store_mappings.*')
            ->leftJoin('stores', 'stores.id', '=', 'posm_store_mappings.store_id')
            ->leftJoin('posms', 'posms.id', '=', 'posm_store_mappings.posm_id');

        // 🔹 Filters
        if (!empty($this->filters['posm_id'])) {
            $query->where('posm_store_mappings.posm_id', $this->filters['posm_id']);
        }

        if (!empty($this->filters['store_id'])) {
            $query->where('posm_store_mappings.store_id', $this->filters['store_id']);
        }

        if (!empty($this->filters['store_action'])) {
            $query->where('posm_store_mappings.store_action', $this->filters['store_action']);
        }

        if (!empty($this->filters['month'])) {
            $query->where('posm_store_mappings.month', $this->filters['month']);
        }

        if (!empty($this->filters['year'])) {
            $query->where('posm_store_mappings.year', $this->filters['year']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(posm_store_mappings.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        // 🔹 Restrict for non-admins (only self + subordinates + their clients)
        if ($authUser->role_id != 1) {
            $query->whereIn('posm_store_mappings.created_by', $allowedUserIds)
                ->whereHas('store', function ($s) use ($clientIds) {
                    $s->whereIn('client_id', $clientIds);
                });
        }

        return $query->get();
    }

    /**
     * Map each row according to headings.
     */
    public function map($row): array
    {
        $store_action = config('system.visibility_types')[$row->store_action] ?? 'N/A';

        return [
            $row->posm->code ?? 'N/A',
            $row->posm->name ?? 'N/A',
            $row->store->code ?? 'N/A',
            $row->store->name ?? 'N/A',
            $row->start_date ? Carbon::parse($row->start_date)->format('d-m-Y') : 'N/A',
            $row->end_date ? Carbon::parse($row->end_date)->format('d-m-Y') : 'N/A',
            $row->month ?? 'N/A',
            $row->year ?? 'N/A',
            $store_action ?? 'N/A',
            $row->is_required ? 'Yes' : 'No',
            $row->is_photo_required ? 'Yes' : 'No',
            $row->is_photo1_required ? 'Yes' : 'No',
            $row->is_photo2_required ? 'Yes' : 'No',
            $row->is_photo3_required ? 'Yes' : 'No',
            $row->is_scanner_enabled ? 'Yes' : 'No',
            $row->is_scanner_required ? 'Yes' : 'No',
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
            'Posm Code',
            'Posm Name',
            'Store Code',
            'Store Name',
            'Start Date',
            'End Date',
            'Month',
            'Year',
            'Store Action',
            'Is Required',
            'Is Photo Required',
            'Is Photo1 Required',
            'Is Photo2 Required',
            'Is Photo3 Required',
            'Is Scanner Enabled',
            'Is Scanner Required',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }
}
