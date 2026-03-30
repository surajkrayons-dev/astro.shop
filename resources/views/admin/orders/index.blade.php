@extends('layouts.master')

@section('title')
    Orders
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Orders</h4>
            </div>
        </div>
    </div>

    {{-- FILTER SECTION --}}
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

                        {{-- CATEGORY --}}
                        <div class="col">
                            <label class="form-label fw-bold">Category</label>
                            <select id="category_id" class="form-control select2-class2" data-placeholder="Select Category">
                                <option value=""></option>
                                @foreach (\App\Models\Category::orderBy('name')->get() as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- PRODUCT --}}
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

                        {{-- ORDER STATUS --}}
                        <div class="col">
                            <label class="form-label fw-bold">Status</label>
                            <select id="status" class="form-control select2-class2" data-placeholder="Select Status">
                                <option value=""></option>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="packed">Packed</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card border">
                <div class="card-body">

                    <table id="orders-table" class="table table-bordered dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Order No</th>
                                <th>User</th>
                                <th>Category</th>
                                <th>
                                    Products
                                    <div class="text-muted small">(Items)</div>
                                </th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Date</th>
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
    <script>
        $(document).ready(function() {

            const table = $('#orders-table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ route('admin.orders.list') }}',
                    data: function(d) {
                        d.user_id = $('#user_id').val();
                        d.category_id = $('#category_id').val();
                        d.product_id = $('#product_id').val();
                        d.status = $('#status').val();
                    }
                },

                columns: [{
                        data: 'order_no',
                        name: 'order_number'
                    },
                    {
                        data: 'user',
                        name: 'user'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'products',
                        name: 'products',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'amount',
                        name: 'total_amount'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: function(row) {
                            return `
                                <a href="javascript:void(0);" data-href="{{ route('admin.orders.view') }}/${row.id}" class="btn btn-soft-success btn-sm waves-effect waves-light open-remote-modal" data-target="#xxlRemoteModal"><i class="mdi mdi-eye font-size-16"></i></a>
                            `;
                        }
                    }
                ]
            });

            $('#user_id, #category_id, #product_id, #status').on('change', function() {
                table.ajax.reload();
            });

            $('#reset-filter-btn').on('click', function() {
                $('#user_id, #category_id, #product_id, #status')
                    .val('')
                    .trigger('change');

                table.ajax.reload();
            });

        });
    </script>
@endsection
