@extends('layouts.master')

@section('title') Login Authority @endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Login Authority</h4>
                    <div class="page-title-right">
                            <a href="{{ route('admin.login_requests.export.xlsx.data') }}" class="btn btn-soft-warning waves-effect waves-light" title="Export"><i class="fas fa-file"></i> Export</a>
                    </div>
            </div>
        </div>
    </div>

    <!-- [ filter ] start -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Filter by</h4>
                    <button id="reset-filter-btn" type="button" class="btn btn-light waves-effect waves-light"><i class="fa fa-undo"></i> Reset</button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="user_id" class="form-label fw-bold">User ( Name & UserName ) :</label>
                                <input type="text" id="user_id" class="form-control" placeholder="Search Name or Username">
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group">
                                <label for="status" class="form-label fw-bold">Status :</label>
                                <select class="form-control select2-class2" id="status" data-placeholder="Choose Status">
                                    <option value=""></option>
                                    <option value="verified" selected>Verified</option>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="logged_out">Logged Out</option>
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group">
                                <label for="dmy" class="form-label fw-bold">Date Range</label>
                                <input type="text" id="date_range" name="daterange" class="form-control" placeholder="Choose Date Range" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border">
                <div class="card-body">
                    <table id="data-table" class="table table-bordered dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Date & Time</th>
                                <th width="130px;">Status</th>
                                <th width="130px;">Action</th>
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
                start: moment().subtract(29, 'days'),
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
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                    'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                }
            }, (start, end) => {
                date_range.start = start;
                date_range.end = end;
                reloadTable('#data-table');
            });

            function reloadTable(tableId) {
                if (!tableId.startsWith('#')) {
                    tableId = `#${tableId}`;
                }
                $(tableId).DataTable().ajax.reload();
            }

            $(document).on('click', '#reset-filter-btn', function(e) {
                e.preventDefault();
                $('#user_id').val('').trigger('change');
                $('#status').val('verified').trigger('change');
                $('#date_range').data('daterangepicker').setStartDate(moment().subtract(29, 'days'));
                $('#date_range').data('daterangepicker').setEndDate(moment());
                date_range.start = moment().subtract(29, 'days');
                date_range.end = moment();
                reloadTable('#data-table');
            });

            $('#data-table').DataTable({
                aaSorting: [[2, 'desc']],
                ajax: {
                    url: '{{ route("admin.login_requests.list") }}',
                    data: function(d) {
                        d.user = $('#user_id').val();
                        d.status = $('#status').val();
                        d.start_date = date_range.start.format('YYYY-MM-DD');
                        d.end_date = date_range.end.format('YYYY-MM-DD');
                    }
                },
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
                                return '<span class="badge badge-soft-warning font-size-13 border">Waiting for access</span>';
                            } else if (row.status == 'verified') {
                                return '<span class="badge badge-soft-success font-size-13 border">Verified</span>';
                            } else if (row.status == 'rejected') {
                                return '<span class="badge badge-soft-danger font-size-13 border">Rejected</span>';
                            } else if (row.status == 'logged_out') {
                                return '<span class="badge badge-soft-info font-size-13 border">Logged Out</span>';
                            }
                            return row.status;
                        }
                    },
                    {
                        data: null,
                        mRender: (data, type, row) => {
                            if (row.status == 'pending') {
                                return `
                                    <button type="button" class="btn btn-soft-success btn-sm waves-effect waves-light change-status-btn" data-id="${row.id}" data-status="verified"><i class="fa fa-check"></i> Approve</button>
                                    <button type="button" class="btn btn-soft-danger btn-sm waves-effect waves-light change-status-btn" data-id="${row.id}" data-status="rejected"><i class="fa fa-times"></i> Reject</button>
                                `;
                            } else if (row.status == 'verified') {
                                return `
                                    <button type="button" class="btn btn-soft-danger btn-sm waves-effect waves-light change-status-btn" data-id="${row.id}" data-status="logged_out"><i class="bx bx-power-off text-bold"></i> Logout</button>
                                `;
                            }
                            return '';
                        }
                    }
                ],
            });

            $('#user_id, #status').on('input change', function() {
                reloadTable('#data-table');
            });

            $('#date_range').on('apply.daterangepicker', function() {
                reloadTable('#data-table');
            });

            $(document).on('click', '.change-status-btn', function (e) {
                e.preventDefault();
                const { id, status } = $(this).data();
                const $btns = $('.change-status-btn');

                $.ajax({
                    dataType: 'json',
                    type: 'POST',
                    url: "{{ route('admin.login_requests.status.update') }}",
                    data: {
                        id,
                        status,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: () => {
                        $btns.prop('disabled', true);
                        showToastr();
                    },
                    error: (jqXHR, exception) => {
                        $btns.prop('disabled', false);
                        showToastr('error', formatErrorMessage(jqXHR, exception));
                    },
                    success: response => {
                        $btns.prop('disabled', false);
                        showToastr('success', response.message);
                        reloadTable('#data-table');
                    }
                });
            });
        });
    </script>
@endsection
