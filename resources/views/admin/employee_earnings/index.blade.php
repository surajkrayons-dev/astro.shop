@extends('layouts.master')

@section('title')
    Employee Earnings
@endsection

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">
                    Employee Earnings
                </h4>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        Filter Earnings
                    </h4>

                    <button id="reset-filter-btn" type="button" class="btn btn-light">

                        <i class="fa fa-undo"></i>
                        Reset

                    </button>
                </div>

                <div class="card-body">

                    <div class="row">

                        @if (auth()->user()->type != 'employee')
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Employee
                                </label>

                                <select id="employee_id" class="form-control select2-class2"
                                    data-placeholder="Choose Employee">

                                    <option value=""></option>

                                    @foreach (\App\Models\User::where('type', 'employee')->orderBy('name')->get() as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                Status
                            </label>

                            <select id="status" class="form-control select2-class2" data-placeholder="Choose Status">

                                <option value=""></option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>

                            </select>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Listing --}}
    <div class="row">
        <div class="col-12">
            <div class="card border">

                <div class="card-body">

                    <table id="data-table" class="table table-bordered dt-responsive nowrap w-100">

                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Order No.</th>
                                <th>Coupon</th>
                                <th>Order Amount</th>
                                <th>Commission %</th>
                                <th>Commission Amount</th>
                                <th>Status</th>
                                <th width="100">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody></tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(function() {

            const table = $('#data-table').DataTable({

                processing: true,
                serverSide: true,

                ajax: {

                    url: '{{ route('admin.employee_earnings.list') }}',

                    data: function(d) {

                        d.employee_id =
                            $('#employee_id').val();

                        d.status =
                            $('#status').val();
                    }
                },

                columns: [

                    {
                        data: 'employee',
                        name: 'employee'
                    },

                    {
                        data: 'order_number',
                        name: 'order_number'
                    },

                    {
                        data: 'coupon_code',
                        name: 'coupon_code'
                    },

                    {
                        data: 'order_amount',
                        name: 'order_amount'
                    },

                    {
                        data: 'commission_percentage',
                        name: 'commission_percentage'
                    },

                    {
                        data: 'commission_amount',
                        name: 'commission_amount'
                    },

                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },

                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }

                ]

            });

            $('#employee_id, #status').on(
                'change',
                function() {

                    table.ajax.reload();

                }
            );

            $('#reset-filter-btn').on(
                'click',
                function() {

                    $('#employee_id')
                        .val('')
                        .trigger('change');

                    $('#status')
                        .val('')
                        .trigger('change');

                    table.ajax.reload();
                }
            );

        });
    </script>
@endsection
