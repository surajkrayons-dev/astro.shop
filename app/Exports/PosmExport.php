<?php

namespace App\Exports;

use App\Models\Posm;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PosmExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = auth()->user();

        // Client aur subordinate IDs nikaalo
        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = function_exists('getAllSubordinateIds')
            ? getAllSubordinateIds($authUser->id, $clientIds)
            : [];
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        // Base query
        $query = Posm::with(['brand', 'creator', 'modifier'])
            ->select('posm_brand_id', 'code', 'name', 'image', 'status', 'created_by', 'modified_by', 'created_at', 'updated_at');

        // Apply filters from request
        if (!empty($this->filters['posm_brand_id'])) {
            $query->where('posm_brand_id', 'like', '%' . $this->filters['posm_brand_id'] . '%');
        }

        if (!empty($this->filters['code'])) {
            $query->where('code', 'like', '%' . $this->filters['code'] . '%');
        }

        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(posms.created_at)'), [
                $this->filters['start_date'], $this->filters['end_date']
            ]);
        }

        // Restrict data for non-admin users
        if ($authUser->role_id != 1) {
            $query->whereIn('created_by', $allowedUserIds);
        }

        // Return formatted data
        return $query->get()->map(function ($posm) {
            $posm->posm_brand_id = $posm->brand->name ?? '';
            return $posm;
        });
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($posm): array
    {
        $imageUrl = $posm->image ? url('storage/posms/' . $posm->image) : 'N/A';

        return [
            $posm->brand ? $posm->brand->code : 'N/A',
            $posm->brand ? $posm->brand->name : 'N/A',
            $posm->code ?? 'N/A',
            $posm->name ?? 'N/A',
            $imageUrl,
            $posm->status ? 'Yes' : 'No',
            $posm->creator ? $posm->creator->code : 'N/A',
            $posm->created_at instanceof Carbon ? $posm->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $posm->modifier ? $posm->modifier->code : 'N/A',
            $posm->updated_at instanceof Carbon ? $posm->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Posm Brand Code',
            'Posm Brand Name',
            'Posm Code',
            'Posm Name',
            'Image',
            'Status',
            'Created By',
            'Created Date & Time',
            'Updated By',
            'Updated Date & Time',
        ];
    }

}
