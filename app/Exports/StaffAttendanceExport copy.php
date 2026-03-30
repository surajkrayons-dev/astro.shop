<?php

namespace App\Exports;

use App\Models\User;
use App\Models\UserAttendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class StaffAttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return UserAttendance::with(['user', 'modifier'])->get();
    }

    public function map($attendance): array
    {
        $employeeName = User::find($attendance->employee_id)?->code ?? 'N/A';

        return [
            $employeeName,
            $attendance->date ?? 'N/A',
            $attendance->check_in_time ?? 'N/A',
            $attendance->in_latitude ?? 'N/A',
            $attendance->in_longitude ?? 'N/A',
            $attendance->user_check_in_image ?? 'N/A',
            $attendance->check_out_time ?? 'N/A',
            $attendance->out_latitude ?? 'N/A',
            $attendance->out_longitude ?? 'N/A',
            $attendance->user_check_out_image ?? 'N/A',
            $attendance->time_duration ?? 'N/A',
            $attendance->is_audited ?? 'N/A',
            $attendance->modifier ? $attendance->modifier->code : 'N/A',
            $attendance->updated_at instanceof Carbon ? $attendance->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Promoter Code',
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
