@extends('layouts.master')

@section('title')
    Competition Benchmarking Reports
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">All Competition Benchmarking Reports</h4>
                <div class="page-title-right">
                    <a href="{{ route('client.competition_benchmarking_reports.export.xlsx.data') }}"
                        class="btn btn-soft-warning waves-effect waves-light" title="Export"><i class="fas fa-file"></i>
                        Export</a>
                </div>
            </div>
        </div>
    </div>

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
                                    @foreach ($promoters as $promoters)
                                        <option value="{{ $promoters->id }}">[{{ $promoters->code }}]-{{ $promoters->name }}
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
                                <label for="competition_product_id" class="form-label">Competition Product :</label>
                                <select name="competition_product_id" id="competition_product_id"
                                    class="form-control select2-class2" data-placeholder="Choose Competition Product">
                                    <option value="">Choose Competition Product</option>
                                    @foreach ($competitionProducts as $competitionProduct)
                                        <option value="{{ $competitionProduct->id }}">
                                            [{{ $competitionProduct->code }}]-{{ $competitionProduct->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group">
                                <label for="is_stock" class="form-label fw-bold">Is Stock :</label>
                                <select class="form-control select2-class2" id="is_stock" data-placeholder="Choose Stock">
                                    <option value=""></option>
                                    <option value="1" selected>In Stock</option>
                                    <option value="0">Out of Stock</option>
                                </select>
                            </div>
                        </div>

                        <div class="col">
                            <div class="form-group">
                                <label for="dmy" class="form-label fw-bold">Date Range</label>
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
                                <th>Competition Product</th>
                                <th>Is Stock</th>
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
                $('#competition_product_id').val('').trigger('change');
                $('#is_stock').val('1').trigger('change');
                $('#date_range').data('daterangepicker').setStartDate(moment().subtract(29, 'days'));
                $('#date_range').data('daterangepicker').setEndDate(moment());
                reloadTable('#data-table');
            });

            $('#data-table').DataTable({
                ajax: {
                    url: '{{ route('client.competition_benchmarking_reports.list') }}',
                    data: function(d) {
                        d.store_id = $('#store_id').val();
                        d.promoter_id = $('#promoter_id').val();
                        d.competition_product_id = $('#competition_product_id').val();
                        d.is_stock = $('#is_stock').val();
                        d.start_date = date_range.start.format('YYYY-MM-DD');
                        d.end_date = date_range.end.format('YYYY-MM-DD');
                    }
                },
                columns: [{
                        data: 'promoter_info',
                        name: 'promoter_info'
                    },
                    {
                        data: 'store_info',
                        name: 'store_info'
                    },
                    {
                        data: 'competition_product_info',
                        name: 'competition_product_info'
                    },
                    {
                        data: 'is_stock',
                        name: 'is_stock',
                        render: function(data, type, row) {
                            return data == 1 ? 'In Stock' : 'Out Of Stock';
                        }
                    },
                    {
                        data: null,
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            return `
                                <a href="javascript:void(0);" data-href="{{ route('client.competition_benchmarking_reports.view') }}/${row.id}" class="btn btn-soft-success btn-sm waves-effect waves-light open-remote-modal" data-target="#xlRemoteModal"><i class="mdi mdi-eye font-size-16"></i></a>
                            `;
                        },
                        orderable: false,
                        searchable: false
                    }
                ],
            });
            $('#store_id, #promoter_id, #competition_product_id, #is_stock').on('input change', function() {
                reloadTable('#data-table');
            });

            $('#date_range').on('apply.daterangepicker', function() {
                reloadTable('#data-table');
            });
        });
    </script>
@endsection
