@extends('layouts.master')

@section('title')
    Payout
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">All Payout</h4>
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
                                <th>Invoice no</th>
                                <th>Client</th>
                                {{-- <th>Amount</th> --}}
                                {{-- <th>Service Code & Name</th> --}}
                                <th>Services</th>
                                {{-- <th>Payment Method</th> --}}
                                <th>Status</th>
                                <th>Action</th>
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
                $(this).prop('disabled', true);
                $.get(`{{ route('admin.pin_codes.change.status') }}/${id}`, () => reloadTable(
                'data-table'));
            });

            $('#data-table').DataTable({
                ajax: '{{ route("client.payout_clients.list") }}',
                @if (Can::is_accessible('payout_clients', 'create'))
                    dom: DT_DOM_OPTION,
                    buttons: DT_BUTTONS_OPTION,
                @endif
                columns: [{
                        data: 'invoice_no'
                    },
                    {
                        data: 'client_details'
                    },
                    // { data: 'service_cost', render: function(data) { return `₹ ${data.toLocaleString()}`; } },
                    // { data: 'service_details' },
                    // { data: 'service_name' },
                    {
                        data: 'service_name',
                        mRender: function(data, type, row) {
                            if (!data) return '';

                            return data
                                .split(',')
                                .map(item =>
                                    item
                                    .trim()
                                    .replace(/_/g, ' ')
                                    .replace(/\b\w/g, c => c.toUpperCase())
                                )
                                .join(', ');
                        }
                    },
                    // { data: 'payment_mode' },
                    {
                        data: 'status',
                        render: function(data, type, row) {
                            if (row.status === 'unpaid') {
                                return '<span class="badge badge-pill badge-soft-danger font-size-12 p-2 border w-md">UnPaid</span>';
                            } else {
                                return '<span class="badge badge-pill badge-soft-success font-size-12 p-2 border w-md">Paid</span>';
                            }
                        }
                    },
                    {
                        data: null,
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            if (row.status == 'unpaid') {
                                return `
                                        <a href="javascript:void(0);" data-href="{{ route('client.payout_clients.view') }}/${row.id}" class="btn btn-soft-success btn-sm waves-effect waves-light open-remote-modal" data-target="#remoteModal" title="View Details"><i class="mdi mdi-eye font-size-16"></i></a>
                                        <a href="{{ route('client.payout_clients.invoice') }}/${row.id}" class="btn btn-soft-info btn-sm waves-effect waves-light" title="Download Invoice"><i class="bx bx-download font-size-16"></i></a>
                                `;
                            } else {
                                return `
                                        <a href="javascript:void(0);" data-href="{{ route('client.payout_clients.view') }}/${row.id}" class="btn btn-soft-success btn-sm waves-effect waves-light open-remote-modal" data-target="#remoteModal" title="View Details"><i class="mdi mdi-eye font-size-16"></i></a>
                                        <a href="{{ route('client.payout_clients.invoice') }}/${row.id}" class="btn btn-soft-info btn-sm waves-effect waves-light" title="Download Invoice"><i class="bx bx-download font-size-16"></i></a>
                                `;
                            }
                        },
                        orderable: false,
                        searchable: false
                    }
                ],
            });
        });
    </script>
@endsection
