@extends('layouts.master')

@section('title')
    Employee Earning Details
@endsection

@section('content')
    <div class="row">

        <div class="col-12">

            <div class="page-title-box">
                <h4 class="mb-sm-0 font-size-18">
                    Employee Earning Details
                </h4>
            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-lg-12">

            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">
                        Commission Information
                    </h4>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6">

                            <table class="table table-bordered">

                                <tr>
                                    <th width="220">
                                        Employee
                                    </th>
                                    <td>
                                        {{ $earning->employee?->name ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Employee Code
                                    </th>
                                    <td>
                                        {{ $earning->employee?->code ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Coupon Code
                                    </th>
                                    <td>
                                        {{ $earning->coupon?->code ?? '-' }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Order Number
                                    </th>
                                    <td>
                                        {{ $earning->order?->order_number ?? '-' }}
                                    </td>
                                </tr>

                            </table>

                        </div>

                        <div class="col-md-6">

                            <table class="table table-bordered">

                                <tr>
                                    <th width="220">
                                        Order Amount
                                    </th>
                                    <td>
                                        ₹ {{ number_format($earning->order_amount, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Commission %
                                    </th>
                                    <td>
                                        {{ $earning->commission_percentage }}%
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Commission Amount
                                    </th>
                                    <td>
                                        ₹ {{ number_format($earning->commission_amount, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Status
                                    </th>
                                    <td>

                                        @if ($earning->status == 'paid')
                                            <span class="badge bg-success">
                                                Paid
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                Pending
                                            </span>
                                        @endif

                                    </td>
                                </tr>

                            </table>

                        </div>

                    </div>

                    <div class="row mt-3">

                        <div class="col-md-12">

                            <table class="table table-bordered">

                                <tr>
                                    <th width="220">
                                        Created At
                                    </th>
                                    <td>
                                        {{ $earning->created_at->format('d M Y h:i A') }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>
                                        Updated At
                                    </th>
                                    <td>
                                        {{ $earning->updated_at->format('d M Y h:i A') }}
                                    </td>
                                </tr>

                            </table>

                        </div>

                    </div>

                    <a href="{{ route('admin.employee_earnings.index') }}" class="btn btn-secondary">

                        Back

                    </a>

                </div>

            </div>

        </div>

    </div>
@endsection
