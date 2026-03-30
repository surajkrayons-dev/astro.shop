@extends('layouts.master')

@section('title', 'Products')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Products</h4>

                <div class="page-title-right">
                    <a href="{{ route('admin.products.create.index') }}" class="btn btn-soft-info">
                        <i class="fas fa-plus"></i> Create
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Filter</h4>
                    <button id="reset-filter" class="btn btn-light">
                        <i class="fa fa-undo"></i> Reset
                    </button>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col">
                            <label class="form-label fw-bold">Category</label>
                            <select id="category_id" class="form-control select2-class2" data-placeholder="Select Category">
                                <option value=""></option>
                                @foreach (\App\Models\Category::orderBy('name')->get() as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Product</label>
                            <select id="product_id" class="form-control select2-class2" data-placeholder="Select Product">
                                <option value=""></option>
                                @foreach (\App\Models\Product::orderBy('name')->get() as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Stock Status</label>
                            <select id="stock_status" class="form-control select2-class2"
                                data-placeholder="Choose Stock Status">
                                <option value=""></option>
                                <option value="in_stock">In Stock</option>
                                <option value="few_left">Few Left</option>
                                <option value="out_of_stock">Out of Stock</option>
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Status</label>
                            <select id="status" class="form-control select2-class2" data-placeholder="Choose Status">
                                <option value=""></option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="row">
        <div class="col-12">
            <div class="card border">
                <div class="card-body">
                    <table id="data-table" class="table table-bordered dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Product Name</th>
                                <th>Stock Status</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
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

            let table = $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.products.list') }}",
                    data: function(d) {
                        d.category_id = $('#category_id').val();
                        d.product_id = $('#product_id').val();
                        d.stock_status = $('#stock_status').val();
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'code_name',
                        name: 'product_name'
                    },
                    {
                        data: 'stock_status',
                        name: 'products.stock_status',
                        render: function(data) {
                            if (data === 'in_stock') {
                                return '<span class="badge bg-success">In Stock</span>';
                            } else if (data === 'few_left') {
                                return '<span class="badge bg-warning">Few Left</span>';
                            }
                            return '<span class="badge bg-danger">Out of Stock</span>';
                        }
                    },
                    {
                        data: null,
                        name: 'status',
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            return `
                                <div class="square-switch">
                                    <input type="checkbox"
                                           id="status-switch-${row.id}"
                                           class="change-status"
                                           switch="status"
                                           data-id="${row.id}"
                                           ${row.status == 1 ? 'checked' : ''} />

                                    <label for="status-switch-${row.id}"
                                           data-on-label="Yes"
                                           data-off-label="No"></label>
                                </div>
                        `;
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                        <a href="{{ route('admin.products.update.index') }}/${row.id}"
                           class="btn btn-soft-info">
                            <i class="bx bx-pencil"></i>
                        </a>
                        <button class="btn btn-soft-danger delete-entry"
                                data-href="{{ route('admin.products.delete') }}/${row.id}">
                            <i class="bx bx-trash"></i>
                        </button>
                    `;
                        }
                    }
                ]
            });

            $(document).on('change', '.change-status', function() {

                let checkbox = $(this);
                let id = checkbox.data('id');

                checkbox.prop('disabled', true);

                $.get(`{{ route('admin.products.change.status') }}/${id}`)
                    .done(function() {
                        table.ajax.reload(null, false);
                    })
                    .fail(function() {
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    })
                    .always(function() {
                        checkbox.prop('disabled', false);
                    });
            });

            $('#category_id, #product_id, #stock_status, #status').on('change', function() {
                table.ajax.reload();
            });

            $('#reset-filter').on('click', function() {
                $('#category_id, #product_id, #stock_status, #status')
                    .val('')
                    .trigger('change');
                table.ajax.reload();
            });
        });
    </script>
@endsection
