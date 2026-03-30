@extends('layouts.master')

@section('title')
    Return Requests
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Return Requests</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Filter</h4>

                    <button id="reset-filter-btn" class="btn btn-light">
                        <i class="fa fa-undo"></i> Reset
                    </button>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col">
                            <label class="form-label fw-bold">User</label>
                            <select id="user_id" class="form-control select2-class2" data-placeholder="Select User">
                                <option value=""></option>
                                @foreach (\App\Models\User::where('role_id', 3)->get() as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->code }} - {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Product</label>
                            <select id="product_id" class="form-control select2-class2" data-placeholder="Select Product">
                                <option value=""></option>
                                @foreach (\App\Models\Product::orderBy('name')->get() as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Status</label>
                            <select id="status" class="form-control select2-class2" data-placeholder="Select Status">
                                <option value=""></option>
                                <option value="requested">Requested</option>
                                <option value="approved">Approved</option>
                                <option value="picked">Picked</option>
                                <option value="refunded">Refunded</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body">
            <table id="returns-table" class="table table-bordered dt-responsive nowrap w-100">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-center">View</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection


@section('script')
    <script>
        $(function() {

            const table = $('#returns-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin.returns.list') }}',
                    data: function(d) {
                        d.user_id = $('#user_id').val();
                        d.product_id = $('#product_id').val();
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: 'order_no'
                    },
                    {
                        data: 'user'
                    },
                    {
                        data: 'product'
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'requested_at'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(row) {
                            return `
                        <a href="javascript:void(0);" 
                           data-href="{{ route('admin.returns.view', '') }}/${row.return_id}" 
                           class="btn btn-sm btn-soft-primary open-remote-modal"
                           data-target="#xxlRemoteModal">
                           <i class="mdi mdi-eye"></i>
                        </a>
                    `;
                        }
                    }
                ]
            });

            $('#user_id, #product_id, #status').on('change', function() {
                table.ajax.reload();
            });

            $('#reset-filter-btn').on('click', function() {
                $('#user_id, #product_id, #status')
                    .val('')
                    .trigger('change');
                table.ajax.reload();
            });

        });
    </script>
@endsection
