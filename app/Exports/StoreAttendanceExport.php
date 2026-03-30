<?php

namespace App\Exports;

use App\Models\StoreAttendance;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StoreAttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $authUser = Auth::user();

        $clientIds = array_filter(explode(',', $authUser->client_id ?? ''));
        $subordinateIds = getAllSubordinateIds($authUser->id, $clientIds);
        $allowedUserIds = array_merge([$authUser->id], $subordinateIds);

        // ✅ Base query
        $query = StoreAttendance::with(['store', 'user', 'modifier'])
            ->select('store_attendances.*');

        // ✅ Apply filters
        if (!empty($this->filters['employee_id'])) {
            $query->whereHas('user', function ($q) {
                $q->where('code', 'like', '%' . $this->filters['employee_id'] . '%')
                  ->orWhere('name', 'like', '%' . $this->filters['employee_id'] . '%');
            });
        }

        if (!empty($this->filters['store_id'])) {
            $query->whereHas('store', function ($q) {
                $q->where('code', 'like', '%' . $this->filters['store_id'] . '%')
                  ->orWhere('name', 'like', '%' . $this->filters['store_id'] . '%');
            });
        }

        if (!is_null($this->filters['is_audited'])) {
            $query->where('is_audited', $this->filters['is_audited']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(store_attendances.created_at)'), [
                $this->filters['start_date'],
                $this->filters['end_date']
            ]);
        }

        // ✅ Role-based access
        if ($authUser->role_id != 1) {
            $query->where(function ($q) use ($clientIds, $allowedUserIds) {
                $q->whereHas('store', function ($subQ) use ($clientIds, $allowedUserIds) {
                    $subQ->whereIn('client_id', $clientIds)
                         ->orWhereIn('created_by', $allowedUserIds);
                })
                ->orWhereIn('store_attendances.employee_id', $allowedUserIds);
            });
        }

        return $query->orderByDesc('store_attendances.updated_at')->get();
    }

    public function map($attendance): array
    {
        $employeeCode = User::find($attendance->employee_id)?->code ?? 'N/A';
        $employeeName = User::find($attendance->employee_id)?->name ?? 'N/A';

        $checkInImage = $attendance->store_check_in_image ? url('storage/attendance/' . $attendance->store_check_in_image) : 'N/A';

        $checkOutImage = $attendance->store_check_out_image ? url('storage/attendance/' . $attendance->store_check_out_image) : 'N/A';

        $noPermissionReasonImage = $attendance->no_permission_reason_image ? url('storage/attendance/' . $attendance->no_permission_reason_image) : 'N/A';

        return [
            $employeeCode,
            $employeeName,
            $attendance->store?->code ?? 'N/A',
            $attendance->store?->name ?? 'N/A',
            $attendance->date ?? 'N/A',
            $attendance->check_in_time ?? 'N/A',
            $attendance->in_latitude ?? 'N/A',
            $attendance->in_longitude ?? 'N/A',
            $checkInImage,
            $attendance->check_out_time ?? 'N/A',
            $attendance->out_latitude ?? 'N/A',
            $attendance->out_longitude ?? 'N/A',
            $checkOutImage,
            $attendance->time_duration ?? 'N/A',
            $attendance->no_permission_reason ?? 'N/A',
            $noPermissionReasonImage,
            $attendance->merchandising_status === null ? 'N/A' : ($attendance->merchandising_status == 1 ? 'Yes' : 'No'),
            $attendance->covered_status === null ? 'N/A' : ($attendance->covered_status == 1 ? 'Yes' : 'No'),
            $attendance->is_audited ?? 'N/A',
            // $modifiedBy,
            $attendance->modifier ? $attendance->modifier->code : 'N/A',
            $attendance->updated_at instanceof Carbon ? $attendance->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Promoter Code',
            'Promoter Name',
            'Store Code',
            'Store Name',
            'Date',
            'Check-In Time',
            'In Latitude',
            'In Longitude',
            'Store Check-In Image',
            'Check-Out Time',
            'Out Latitude',
            'Out Longitude',
            'Store Check-Out Image',
            'Time Duration',
            'No Permission Reason',
            'No Permission Reason Image',
            'Merchandising Status',
            'Covered Status',
            'Audited',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
