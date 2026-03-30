@extends('layouts.master')

@section('title')
    Visibility Report
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Visibility Report</h4>
                <div class="page-title-right">
                    <a href="{{ route('client.visibility_reports.export.xlsx.data') }}"
                        class="btn btn-soft-warning waves-effect waves-light" title="Export"><i class="fas fa-file"></i>
                        Export</a>
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
                                <label for="promoter_id" class="form-label">Promoter :</label>
                                <select name="promoter_id" id="promoter_id" class="form-control select2-class2"
                                    data-placeholder="Choose Promoter">
                                    <option value="">Choose Promoter</option>
                                    @foreach ($promoters as $promoter)
                                        <option value="{{ $promoter->id }}">[{{ $promoter->code }}]-{{ $promoter->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="store_id" class="form-label">Store :</label>
                                <select name="store_id" id="store_id" class="form-control select2-class2"
                                    data-placeholder="Choose Store">
                                    <option value="">Choose Store</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}">[{{ $store->code }}]-{{ $store->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="posm_id" class="form-label">POSM :</label>
                                <select name="posm_id" id="posm_id" class="form-control select2-class2"
                                    data-placeholder="Choose POSM">
                                    <option value="">Choose POSM</option>
                                    @foreach ($posms as $posm)
                                        <option value="{{ $posm->id }}">[{{ $posm->code }}]-{{ $posm->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="visibility_action" class="form-label fw-bold">Visibility Action :</label>
                                <select name="visibility_action" class="form-control select2-class2" id="visibility_action"
                                    data-placeholder="Choose Visibility Action">
                                    <option value="">Choose Visibility Action</option>
                                    <option value="PaidVisibility">Paid Visibility</option>
                                    <option value="TOTVisibility">TOT Visibility</option>
                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label for="date_range" class="form-label fw-bold">Date Range</label>
                                <input type="text" id="date_range" name="daterange" class="form-control"
                                    placeholder="Choose Date Range" />
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
                                <th>Promoter</th>
                                <th>Store</th>
                                <th>POSM</th>
                                <th>Visibility Action</th>
                                <th width="180px;">Action</th>
                            </tr>
                        </thead>
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
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
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
                $(tableId).DataTable().ajax.reload();
            };

            $(document).on('click', '#reset-filter-btn', function(e) {
                e.preventDefault();
                $('#promoter_id').val('').trigger('change');
                $('#store_id').val('').trigger('change');
                $('#posm_id').val('').trigger('change');
                $('#visibility_action').val('').trigger('change');
                $('#date_range').data('daterangepicker').setStartDate(moment().subtract(29, 'days'));
                $('#date_range').data('daterangepicker').setEndDate(moment());
                reloadTable('#data-table');
            });

            $('#data-table').DataTable({
                ajax: {
                    url: '{{ route('client.visibility_reports.list') }}',
                    data: function(d) {
                        d.store_id = $('#store_id').val();
                        d.promoter_id = $('#promoter_id').val();
                        d.posm_id = $('#posm_id').val();
                        d.visibility_action = $('#visibility_action').val();
                        d.start_date = date_range.start.format('YYYY-MM-DD');
                        d.end_date = date_range.end.format('YYYY-MM-DD');
                    }
                },
                columns: [{
                        data: 'promoter',
                        name: 'promoter'
                    },
                    {
                        data: 'store',
                        name: 'store'
                    },
                    {
                        data: 'posm',
                        name: 'posm'
                    },
                    {
                        data: 'visibility_action',
                        name: 'visibility_action'
                    },
                    {
                        data: null,
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            return `
                                <a href="javascript:void(0);" data-href="{{ route('client.visibility_reports.view') }}/${row.id}" class="btn btn-soft-success btn-sm waves-effect waves-light open-remote-modal" data-target="#xlRemoteModal"><i class="mdi mdi-eye font-size-16"></i></a>
                            `;
                        },
                        orderable: false,
                        searchable: false
                    }
                ],
            });
            $('#store_id, #promoter_id, #posm_id, #visibility_action').on('input change', function() {
                reloadTable('#data-table');
            });

            $('#date_range').on('apply.daterangepicker', function() {
                reloadTable('#data-table');
            });
        });
    </script>
@endsection
