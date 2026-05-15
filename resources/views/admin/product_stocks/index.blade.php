@extends('layouts.master')

@section('title')
    Product Stock
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">
                    <i class="bx bx-package"></i> Product Stock
                </h4>
            </div>
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
                            <label class="form-label fw-bold">Category</label>
                            <select id="category_id" class="form-control select2-class2" data-placeholder="Select Category">
                                <option value=""></option>
                                @foreach (\App\Models\Category::orderBy('name')->get() as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->code }} - {{ $cat->name }}
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
                                        {{ $product->code }} - {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Stock Status</label>
                            <select id="stock_status" class="form-control select2-class2" data-placeholder="Select Status">
                                <option value=""></option>
                                <option value="in_stock">In Stock</option>
                                <option value="few_left">Few Left</option>
                                <option value="out_of_stock">Out Of Stock</option>
                            </select>
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

                    <table id="stock-table" class="table table-bordered dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Product</th>
                                <th>Stock Qty</th>
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
    <script>
        $(document).ready(function() {

            // const table = $('#stock-table').DataTable({
            table = $('#stock-table').DataTable({
                processing: true,
                serverSide: true,

                ajax: {
                    url: '{{ route('admin.product_stocks.list') }}',
                    data: function(d) {
                        d.category_id = $('#category_id').val();
                        d.product_id = $('#product_id').val();
                        d.stock_status = $('#stock_status').val();
                    }
                },

                columns: [{
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'stock_qty',
                        name: 'stock_qty'
                    },
                    {
                        data: 'status',
                        name: 'stock_status'
                    },
                    {
                        data: null,
                        className: "text-center",
                        render: function(row) {
                            return `
                        <a href="javascript:void(0);" 
                           data-href="{{ route('admin.product_stocks.view', '') }}/${row.id}" 
                           class="btn btn-sm btn-soft-primary open-remote-modal"
                           data-target="#xxlRemoteModal">
                           <i class="mdi mdi-eye"></i>
                        </a>
                    `;
                        }
                    }
                ]
            });

            $('#category_id, #product_id, #stock_status').on('change', function() {
                table.ajax.reload();
            });

            $('#reset-filter-btn').on('click', function() {

                $('#category_id').val('').trigger('change');
                $('#product_id').val('').trigger('change');
                $('#stock_status').val('').trigger('change');

                table.ajax.reload();

            });

            $(window).on('focus', function() {

                table.ajax.reload(null, false);

            });

        });
    </script>
    <script>
        function loadFilterData() {

            $.ajax({

                url: "{{ route('admin.product_stocks.filter.data') }}",

                type: "GET",

                data: {
                    category_id: $('#category_id').val(),
                    product_id: $('#product_id').val(),
                    stock_status: $('#stock_status').val(),
                },

                success: function(res) {

                    let selectedProduct = $('#product_id').val();
                    let selectedStock = $('#stock_status').val();

                    // PRODUCTS
                    $('#product_id').html('<option value=""></option>');

                    $.each(res.products, function(key, product) {

                        $('#product_id').append(`
                    <option value="${product.id}">
                        ${product.code} - ${product.name}
                    </option>
                `);

                    });

                    $('#product_id').val(selectedProduct);

                    // STOCK STATUS
                    $('#stock_status').html('<option value=""></option>');

                    $.each(res.stock_statuses, function(key, stock) {

                        let text = stock
                            .replaceAll('_', ' ')
                            .replace(/\b\w/g, l => l.toUpperCase());

                        $('#stock_status').append(`
                    <option value="${stock}">
                        ${text}
                    </option>
                `);

                    });

                    $('#stock_status').val(selectedStock);

                }

            });

        }

        $('#category_id, #product_id, #stock_status').on('change', function() {

            loadFilterData();

            table.ajax.reload();

        });
    </script>
@endsection
