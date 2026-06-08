<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\EmployeeCommission;
use App\Models\EmployeeWithdrawRequest;
use Illuminate\Http\Request;

class EmployeeWithdrawRequestController extends AdminController
{
    public function getIndex()
    {
        return view('admin.employee_withdraw_requests.index');
    }

    public function getList(Request $request)
    {
        $list = EmployeeWithdrawRequest::with('employee')
            ->when(
                auth()->user()->type == 'employee',
                fn($q) => $q->where(
                    'employee_id',
                    auth()->id()
                )
            )
            ->when(
                $request->employee_id,
                fn($q) => $q->where(
                    'employee_id',
                    $request->employee_id
                )
            )
            ->when(
                $request->status,
                fn($q) => $q->where(
                    'status',
                    $request->status
                )
            )
            ->latest();

        return \DataTables::of($list)
            ->addColumn('employee_name', function ($row) {
                return $row->employee?->name ?? '-';

            })
            ->addColumn('amount', function ($row) {
                return '₹ ' . number_format(
                    $row->amount,
                    2
                );
            })
            ->addColumn('requested_at', function ($row) {
                return $row->requested_at
                    ? date('d M Y h:i A', strtotime($row->requested_at))
                    : '-';
            })
            ->addColumn('processed_at', function ($row) {
                return $row->processed_at
                    ? date('d M Y h:i A', strtotime($row->processed_at))
                    : '-';
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->status == 'approved') {
                    return '<span class="badge bg-success">Approved</span>';
                }
                if ($row->status == 'rejected') {
                    return '<span class="badge bg-danger">Rejected</span>';
                }
                return '<span class="badge bg-warning">Pending</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '
                    <a href="' .
                    route(
                        'admin.employee_withdraw_requests.view',
                        $row->id
                    ) .
                    '" class="btn btn-sm btn-primary">
                        View
                    </a>';
                if (
                    auth()->user()->type != 'employee'
                    &&
                    $row->status == 'pending'
                ) {
                    $btn .= '
                        <form method="POST"
                            action="' .
                            route(
                                'admin.employee_withdraw_requests.approve',
                                $row->id
                            ) .
                            '"
                            style="display:inline-block">
                            ' . csrf_field() . '
                            <button
                                class="btn btn-success btn-sm">
                                Approve
                            </button>
                        </form>';
                    $btn .= '
                        <form method="POST"
                            action="' .
                            route(
                                'admin.employee_withdraw_requests.reject',
                                $row->id
                            ) .
                            '"
                            style="display:inline-block">

                            ' . csrf_field() . '

                            <button
                                type="submit"
                                class="btn btn-danger btn-sm">
                                Reject
                            </button>

                        </form>';
                }
                return $btn;
            })
            ->rawColumns([
                'status_badge',
                'action'
            ])
        ->make(true);
    }

    public function getView($id)
    {
        $request = EmployeeWithdrawRequest::with(
        'employee'
        )->findOrFail($id);

        return view(
            'admin.employee_withdraw_requests.view',
            compact('request')
        );
    }

    public function approve($id)
    {
        if (auth()->user()->type == 'employee') {
            abort(403);
        }
        
        $withdrawRequest =
        EmployeeWithdrawRequest::findOrFail($id);
        
        if ($withdrawRequest->status != 'pending') {

            return back()->with(
                'error',
                'Request already processed.'
            );
        }

        $withdrawRequest->update([
            'status' => 'approved',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        EmployeeCommission::where(
            'employee_id',
            $withdrawRequest->employee_id
        )
        ->where(
            'is_withdraw_requested',
            1
        )
        ->where(
            'status',
            'pending'
        )
        ->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => auth()->id(),
        ]);

        return back()->with(
            'success',
            'Withdrawal approved successfully.'
        );
    }

    public function reject($id)
    {
        if (auth()->user()->type == 'employee') {
            abort(403);
        }

        $withdrawRequest =
        EmployeeWithdrawRequest::findOrFail($id);

        
        if ($withdrawRequest->status != 'pending') {

            return back()->with(
                'error',
                'Request already processed.'
            );
        }

        $withdrawRequest->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        EmployeeCommission::where(
            'employee_id',
            $withdrawRequest->employee_id
        )
        ->where(
            'is_withdraw_requested',
            1
        )
        ->where(
            'status',
            'pending'
        )
        ->update([
            'is_withdraw_requested' => 0,
            'withdraw_requested_at' => null,
        ]);

        return back()->with(
            'success',
            'Withdrawal rejected successfully.'
        );
    }

    public function store()
    {
        $employee = auth()->user();

        $alreadyPending = EmployeeWithdrawRequest::where(
            'employee_id',
            $employee->id
        )
        ->where('status', 'pending')
        ->exists();

        if ($alreadyPending) {

            return back()->with(
                'error',
                'You already have a pending withdrawal request.'
            );
        }

        $amount = EmployeeCommission::where(
            'employee_id',
            $employee->id
        )
        ->where('status', 'pending')
        ->where('is_withdraw_requested', 0)
        ->whereHas('order', function ($q) {
            $q->where(
                'delivered_at',
                '<=',
                now()->subDays(15)
            );
        })
        ->sum('commission_amount');

        if ($amount <= 0) {

            return back()->with(
                'error',
                'No commission available for withdrawal.'
            );
        }

        EmployeeWithdrawRequest::create([
            'employee_id' => $employee->id,
            'amount' => $amount,
            'requested_at' => now(),
        ]);

        EmployeeCommission::where(
            'employee_id',
            $employee->id
        )
        ->where('status', 'pending')
        ->where('is_withdraw_requested', 0)
        ->update([
            'is_withdraw_requested' => 1,
            'withdraw_requested_at' => now(),
        ]);

        return back()->with(
            'success',
            'Withdrawal request submitted successfully.'
        );
    }
}