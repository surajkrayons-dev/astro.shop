@extends('layouts.master')

@section('title') Login Reports @endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">All Login Reports</h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Filter by</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="staff" class="form-label fw-bold">Staff :</label>
                                <select class="form-control select2-class2" id="staff" name="user_id" data-placeholder="Choose Staff">
                                    <option value=""></option>
                                    @if ($users->isNotEmpty())
                                        @foreach ($users as $row)
                                            <option value="{{ $row->id }}">{{ "{$row->name} ($row->username)" }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="status" class="form-label fw-bold">Status :</label>
                                <select class="form-control select2-class2" id="status" name="status" data-placeholder="Choose Status">
                                    <option value=""></option>
                                    <option value="pending">Pending</option>
                                    <option value="verified">Verified</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="logged_out">Logged Out</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label for="date_range" class="form-label fw-bold">Date Range :</label>
                                <input type="text" id="date_range" name="daterange" class="form-control" placeholder="Choose Date Range" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border">
                <div class="card-body">
                    <table id="data-table" class="table table-bordered dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Date & Time</th>
                                <th width="120px;">Status</th>
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
    <script type="text/javascript">
        $(function() {
            const date_range = {
                start: moment(),
                end: moment()
            };

            $('#date_range').daterangepicker({
                startDate: date_range.start,
                endDate: date_range.end,
                locale: {
                    format: 'DD MMM, YYYY',
                    applyLabel: 'Apply',
                    cancelLabel: 'Cancel',
                    customRangeLabel: 'Custom',
                },
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 15 Days': [moment().subtract(15, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                },
            }, (start, end) => {
                date_range.start = start;
                date_range.end = end;

                reloadTable('data-table');
            });

            $(document).on('change', '[name="user_id"],[name="status"]', () => reloadTable('data-table'));

            $('#data-table').DataTable({
                ajax: {
                    type: 'POST',
                    url: '{{ route("admin.login_requests.reports.list") }}',
                    data: params => {
                        params.user_id = $('[name="user_id"]').val();
                        params.status = $('[name="status"]').val();
                        params.start_date = date_range.start.format('YYYY-MM-DD');
                        params.end_date = date_range.end.format('YYYY-MM-DD');
                        params._token = '{{ csrf_token() }}';
                        return params;
                    }
                },
                aaSorting: [[2, 'desc']],
                dom: DT_DOM_OPTION,
                buttons: DT_BUTTONS_OPTION,
                columns : [
                    { data: 'name', name: 'users.name' },
                    { data: 'username', name: 'users.username' },
                    { data: 'created_at', mRender: data => formatDate(data) },
                    {
                        data: null,
                        name: 'status',
                        mRender: (data, type, row) => {
                            if (row.status == 'pending') {
                                return '<span class="badge badge-soft-warning font-size-13 border">Pending</span>';
                            } else if (row.status == 'verified') {
                                return '<span class="badge badge-soft-success font-size-13 border">Verified</span>';
                            } else if (row.status == 'rejected') {
                                return '<span class="badge badge-soft-danger font-size-13 border">Rejected</span>';
                            } else if (row.status == 'logged_out') {
                                return '<span class="badge badge-soft-info font-size-13 border">Logged Out</span>';
                            }

                            return row.status;
                        }
                    }
                ],
            });
        });
    </script>
@endsection
