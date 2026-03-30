<?php

namespace App\Exports;

use App\Models\Reason;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ReasonExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Reason::with(['creator','modifier'])
            ->select('reason', 'reason_type', 'status', 'created_by','modified_by', 'created_at', 'updated_at');

        if (!empty($this->filters['reason'])) {
            $query->where('reason', 'like', '%' . $this->filters['reason'] . '%');
        }

        if (!empty($this->filters['reason_type'])) {
            $query->where('reason_type', 'like', '%' . $this->filters['reason_type'] . '%');
        }
        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }
        
        return $query->get();
    }

    /**
     * Map data for each row to show user name instead of id
     */
    public function map($reason): array
    {
        $reason_name = config('system.reason_types')[$reason->reason_type] ?? 'N/A';

        return [
            $reason->reason ?? 'N/A',
            $reason_name ?? 'N/A',
            $reason->status ? 'Yes' : 'No',
            $reason->creator ? $reason->creator->code : 'N/A',
            $reason->created_at instanceof Carbon ? $reason->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $reason->modifier ? $reason->modifier->code : 'N/A',
            $reason->updated_at instanceof Carbon ? $reason->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Add headings to the exported file
     */
    public function headings(): array
    {
        return [
            'Reason',
            'Reason Type',
            'Status',
            'Created By',
            'Created Date & time',
            'Modified By',
            'Modified Date & Time',
        ];
    }

}
