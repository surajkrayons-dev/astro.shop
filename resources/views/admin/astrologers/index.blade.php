@extends('layouts.master')

@section('title') Astrologer @endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">All Astrologers</h4>

            <div class="page-title-right">
                <a href="{{ route('admin.astrologers.create.index') }}"
                    class="btn btn-soft-info waves-effect waves-light"> <i class="fas fa-plus"></i> Create </a>
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
                <button id="reset-filter-btn" type="button" class="btn btn-light waves-effect waves-light"><i
                        class="fa fa-undo"></i> Reset</button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label class="form-label fw-bold">Astrologer</label>
                            <select id="astrologer_id" class="form-control select2-class2"
                                data-placeholder="Choose Astrologer">
                                <option value=""></option>
                                @foreach(\App\Models\User::where('role_id', 2)->get() as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->code }} - {{ $user->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col">
                        <div class="form-group">
                            <label for="status" class="form-label fw-bold">Status :</label>
                            <select class="form-control select2-class2" id="status" data-placeholder="Choose Status">
                                <option value=""></option>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    {{-- <div class="col">
                            <div class="form-group">
                                <label for="dmy" class="form-label fw-bold">Date Range</label>
                                <input type="text" id="date_range" name="daterange" class="form-control" placeholder="Choose Date Range" />
                            </div>
                        </div> --}}
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
                            <th>Astrologer Code & Name</th>
                            <th>Username</th>
                            <th>Email ID</th>
                            <th>Phone No.</th>
                            <th>Status</th>
                            <th width="100px;">Action</th>
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
    $('#data-table').on('change', '.change-status', function(e) {
        e.preventDefault();
        const {
            id
        } = $(this).data();
        const checkbox = $(this);
        checkbox.prop('disabled', true);
        $.get(`{{ route("admin.astrologers.change.status") }}/${id}`, function(response) {
            reloadTable('data-table');
            checkbox.prop('disabled', false);
        }).fail(function() {
            checkbox.prop('disabled', false);
        });
    });

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
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                .endOf('month')
            ],
            'This Year': [moment().startOf('year'), moment().endOf('year')],
            'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year')
                .endOf('year')
            ],
        }
    }, (start, end) => {
        date_range.start = start;
        date_range.end = end;
        reloadTable('#data-table');
    });

    const reloadTable = (tableId) => {
        if (!tableId.startsWith('#')) {
            tableId = `#${tableId}`;
        }
        $(tableId).DataTable().ajax.reload();
    };

    $(document).on('click', '#reset-filter-btn', function(e) {
        e.preventDefault();
        $('#astrologer_id ').val('');
        $('#status').val('1').trigger('change');
        $('#date_range').data('daterangepicker').setStartDate(moment().subtract(29, 'days'));
        $('#date_range').data('daterangepicker').setEndDate(moment());
        reloadTable('#data-table');
    });

    $('#data-table').DataTable({
        ajax: {
            url: '{{ route("admin.astrologers.list") }}',
            data: function(d) {
                d.astrologer_id = $('#astrologer_id').val();
                d.status = $('#status').val();
                d.start_date = date_range.start.format('YYYY-MM-DD');
                d.end_date = date_range.end.format('YYYY-MM-DD');
            }
        },
        @if(Can::is_accessible('astros', 'create'))
        dom: DT_DOM_OPTION,
        buttons: DT_BUTTONS_OPTION,
        @endif
        columns: [{
                data: 'code_name'
            },
            {
                data: 'username'
            },
            {
                data: 'email'
            },
            {
                data: 'mobile'
            },
            {
                data: null,
                name: 'status',
                className: 'text-center',
                mRender: (data, type, row) => {
                    return `
                                @if (Can::is_accessible('astros', 'update'))
                                    <div class="square-switch">
                                        <input type="checkbox" id="status-switch-${row.id}" class="change-status" switch="status" data-id="${row.id}" ${row.status == 1 ? 'checked' : ''} />
                                        <label for="status-switch-${row.id}" data-on-label="Yes" data-off-label="No"></label>
                                    </div>
                                @else
                                    ${row.status == 1 ? 'Active' : 'Inactive'}
                                @endif
                            `;
                }
            },
            {
                data: null,
                className: 'text-center',
                mRender: (data, type, row) => {
                    return `
                                <a href="{{ route('admin.astrologers.update.index') }}/${row.id}" class="btn btn-soft-info btn-sm waves-effect waves-light"><i class="bx bx-pencil font-size-16"></i></a>
                                <a href="javascript:void(0);" data-href="{{ route('admin.astrologers.view') }}/${row.id}" class="btn btn-soft-success btn-sm waves-effect waves-light open-remote-modal" data-target="#xxlRemoteModal"><i class="mdi mdi-eye font-size-16"></i></a>
                                <button type="button" class="btn btn-soft-danger btn-sm waves-effect waves-light delete-entry" data-href="{{ route("admin.astrologers.delete") }}/${row.id}" data-tbl="data"><i class="bx bx-trash font-size-16"></i></button>
                            `;
                },
                orderable: false,
                searchable: false
            }
        ],
    });
    $('#astrologer_id, #status').on('input change', function() {
        reloadTable('#data-table');
    });

    $('#date_range').on('apply.daterangepicker', function() {
        reloadTable('#data-table');
    });
});
</script>
@endsection