<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\EmployeeCommission;
use Illuminate\Http\Request;

class EmployeeEarningController extends AdminController
{
    public function getIndex()
    {
        return view('admin.employee_earnings.index');
    }

    public function getList(Request $request)
    {
        $list = EmployeeCommission::with([
            'employee',
            'order',
            'coupon'
        ])
        ->when(
            auth()->user()->type == 'employee',
            fn($q) => $q->where(
                'employee_id',
                auth()->id()
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

            ->addColumn('employee', function ($row) {

                return $row->employee?->name ?? '-';

            })

            ->addColumn('order_number', function ($row) {

                return $row->order?->order_number ?? '-';

            })

            ->addColumn('coupon_code', function ($row) {

                return $row->coupon?->code ?? '-';

            })

            ->addColumn('order_amount', function ($row) {

                return '₹ ' .
                    number_format(
                        $row->order_amount,
                        2
                    );

            })

            ->addColumn('commission_percentage', function ($row) {

                return $row->commission_percentage.'%';

            })

            ->addColumn('commission_amount', function ($row) {

                return '₹ ' .
                    number_format(
                        $row->commission_amount,
                        2
                    );

            })

            ->addColumn('status_badge', function ($row) {

                return $row->status == 'paid'
                    ? '<span class="badge bg-success">Paid</span>'
                    : '<span class="badge bg-warning">Pending</span>';

            })

            ->addColumn('action', function ($row) {

                return '
                    <a href="'.
                        route(
                            'admin.employee_earnings.view',
                            $row->id
                        )
                    .'"
                    class="btn btn-sm btn-primary">
                        View
                    </a>';
            })

            ->rawColumns([
                'status_badge',
                'action'
            ])

            ->make(true);
    }

    public function getView(Request $request, $id)
    {
        $earning = EmployeeCommission::with([
            'employee',
            'order',
            'coupon'
        ])->findOrFail($id);

        if (
            auth()->user()->type == 'employee'
            &&
            $earning->employee_id != auth()->id()
        ) {
            abort(403);
        }

        return view(
            'admin.employee_earnings.view',
            compact('earning')
        );
    }
}