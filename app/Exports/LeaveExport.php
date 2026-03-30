<?php

namespace App\Exports;

use App\Models\Leave;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class LeaveExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Get leave records.
     */
    public function collection()
    {
        $authUser = auth()->user();
        $hasAuditPermission = \Can::has('leaves', 'audit');

        $query = Leave::with(['employee', 'creator', 'modifier']);

        if ($authUser->role_id == 1) {
            //  Super admin: show all data
            return $query->get();
        }

        if (!$hasAuditPermission) {
            // No audit permission: only own data
            $query->where('employee_id', $authUser->id);
        } else {
            // Audit permission: own + client-mapped users
            $clientIds = [];

            if (!empty($authUser->client_id)) {
                $clientIds = explode(',', $authUser->client_id);
            }

            $query->where(function ($q) use ($authUser, $clientIds) {
                $q->where('employee_id', $authUser->id);

                if (!empty($clientIds)) {
                    $q->orWhereIn('employee_id', function ($subQuery) use ($clientIds) {
                        $subQuery->select('id')
                            ->from('users')
                            ->whereIn('client_id', $clientIds);
                    });
                }
            });
        }

        return $query->get();
    }

    /**
     * Map leave data to export columns.
     */
    public function map($leave): array
    {
        return [
            $leave->employee->code ?? 'N/A',
            $leave->employee->name ?? 'N/A',
            ucfirst($leave->leave_type ?? 'N/A'),
            $leave->start_date ? Carbon::parse($leave->start_date)->format('d-m-Y') : 'N/A',
            $leave->end_date ? Carbon::parse($leave->end_date)->format('d-m-Y') : 'N/A',
            $leave->total_days ?? 'N/A',
            $leave->reason ?? 'N/A',
            $leave->remark ?? 'N/A',
            ucfirst($leave->status ?? 'N/A'),
            $leave->creator->code ?? 'N/A',
            $leave->created_at instanceof Carbon ? $leave->created_at->format('d-m-Y, H:i:s') : 'N/A',
            $leave->modifier->code ?? 'N/A',
            $leave->updated_at instanceof Carbon ? $leave->updated_at->format('d-m-Y, H:i:s') : 'N/A',
        ];
    }

    /**
     * Headings for export.
     */
    public function headings(): array
    {
        return [
            'Employee Code',
            'Employee Name',
            'Leave Type',
            'Start Date',
            'End Date',
            'Total Days',
            'Reason',
            'Remark',
            'Status',
            'Created By',
            'Created Date & Time',
            'Modified By',
            'Modified Date & Time',
        ];
    }
}
