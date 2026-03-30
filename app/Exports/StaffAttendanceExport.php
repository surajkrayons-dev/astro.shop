<?php

namespace App\Exports;

use App\Models\UserAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffAttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $attendances;
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

        $query = UserAttendance::with(['user', 'modifier'])
        ->select('user_attendances.*')
            ->join('users', 'user_attendances.employee_id', '=', 'users.id');

        // 🔹 Apply filters
        if (!empty($this->filters['employee_id'])) {
            $query->where(function ($q) {
                $q->where('users.code', 'like', '%' . $this->filters['employee_id'] . '%')
                  ->orWhere('users.name', 'like', '%' . $this->filters['employee_id'] . '%');
            });
        }

        if (isset($this->filters['is_audited'])) {
            $query->where('user_attendances.is_audited', $this->filters['is_audited']);
        }

        if (!empty($this->filters['start_date']) && !empty($this->filters['end_date'])) {
            $query->whereBetween(\DB::raw('DATE(user_attendances.created_at)'), [
                $this->filters['start_date'], $this->filters['end_date']
            ]);
        }    

        if ($authUser->role_id != 1) {
            $query->where(function ($q) use ($allowedUserIds, $clientIds) {
                $q->whereIn('user_attendances.employee_id', $allowedUserIds)
                    ->orWhereIn('users.created_by', $allowedUserIds)
                    ->orWhere(function ($sub) use ($clientIds) {
                        foreach ($clientIds as $id) {
                            $sub->orWhereRaw("FIND_IN_SET(?, users.client_id)", [$id]);
                        }
                    });
            });
        }

        return $query->orderByDesc('user_attendances.updated_at')->get();
    }

    public function map($attendance): array
    {
        $employee = $attendance->user;

        $checkInImage = $attendance->user_check_in_image ? url('storage/attendance/' . $attendance->user_check_in_image) : 'N/A';

        $checkOutImage = $attendance->user_check_out_image ? url('storage/attendance/' . $attendance->user_check_out_image) : 'N/A';

        return [
            $employee->code ?? 'N/A',
            $employee->name ?? 'N/A',
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
            $attendance->is_audited ?? 'N/A',
            $attendance->modifier?->code ?? 'N/A',
            $attendance->updated_at instanceof Carbon ? $attendance->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Promoter Code',
            'Promoter Name',
            'Date',
            'Check-In Time',
            'In Latitude',
            'In Longitude',
            'Staff Check-In Image',
            'Check-Out Time',
            'Out Latitude',
            'Out Longitude',
            'Staff Check-Out Image',
            'Time Duration',
            'Is Audited',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
