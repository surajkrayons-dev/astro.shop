@extends('layouts.master')

@section('title')
    Coupons
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Coupons</h4>

                <div class="page-title-right">
                    <a href="javascript:void(0);" data-href="{{ route('admin.coupons.create.index') }}"
                        class="btn btn-soft-info open-remote-modal" data-target="#lgRemoteModal">
                        <i class="fas fa-plus"></i> Create
                    </a>
                </div>
            </div>
        </div>
    </div>

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
                    <div class="row">

                        <div class="col">
                            <label class="form-label fw-bold">Coupon Code</label>
                            <select id="id" class="form-control select2-class2" data-placeholder="Select Coupon">
                                <option value=""></option>
                                @foreach (\App\Models\Coupon::orderBy('code')->get() as $coupon)
                                    <option value="{{ $coupon->id }}">
                                        {{ $coupon->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Type</label>
                            <select class="form-control select2-class2" id="discount_type" data-placeholder="Choose Type">
                                <option value=""></option>
                                <option value="flat">Flat</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-control select2-class2" id="status" data-placeholder="Choose Status">
                                <option value=""></option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <div class="col">
                            <label class="form-label fw-bold">Visible</label>
                            <select class="form-control select2-class2" id="is_visible" data-placeholder="Choose Visibility">
                                <option value=""></option>
                                <option value="1">Visible</option>
                                <option value="0">Hidden</option>
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
                    <table id="data-table" class="table table-bordered dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Expiry</th>
                                <th>Status</th>
                                <th>Visible</th>
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
        $(function() {

            let table = $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.coupons.list') }}",
                    data: function(d) {
                        d.id = $('#id').val();
                        d.discount_type = $('#discount_type').val();
                        d.status = $('#status').val();
                        d.is_visible = $('#is_visible').val();
                    }
                },
                columns: [{
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'discount_type',
                        name: 'discount_type'
                    },
                    {
                        data: 'discount_value',
                        name: 'discount_value'
                    },
                    {
                        data: 'expiry_date',
                        name: 'expiry_date'
                    }, {
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
                        name: 'is_visible',
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            return `
                                <div class="square-switch">
                                    <input type="checkbox"
                                        id="visible-switch-${row.id}"
                                        class="change-visible"
                                        switch="info"
                                        data-id="${row.id}"
                                        ${row.is_visible == 1 ? 'checked' : ''} />

                                    <label for="visible-switch-${row.id}"
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
                        <a href="javascript:void(0);"
                           data-href="{{ route('admin.coupons.update.index') }}/${row.id}"
                           class="btn btn-soft-info open-remote-modal" data-target="#lgRemoteModal">
                            <i class="bx bx-pencil"></i>
                        </a>

                        <button class="btn btn-soft-danger delete-entry"
                                data-href="{{ route('admin.coupons.delete') }}/${row.id}">
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

                $.get(`{{ route('admin.coupons.change.status') }}/${id}`)
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

            $(document).on('change', '.change-visible', function() {

                let checkbox = $(this);
                let id = checkbox.data('id');

                checkbox.prop('disabled', true);

                $.get(`{{ route('admin.coupons.change.visible') }}/${id}`)
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

            $('#id, #discount_type, #status, #is_visible').on('change', function() {
                table.ajax.reload();
            });

            $('#reset-filter').on('click', function() {
                $('#id').val('').trigger('change');
                $('#discount_type').val('').trigger('change');
                $('#status').val('').trigger('change');
                $('#is_visible').val('').trigger('change');
                table.ajax.reload();
            });

        });
    </script>
@endsection
