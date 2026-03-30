@extends('layouts.master')

@section('title', 'Astro Banners')

@section('content')

    {{-- PAGE HEADER --}}
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Astro Banners</h4>

                <div class="page-title-right">
                    <a href="{{ route('admin.astro_banners.create.index') }}" class="btn btn-soft-info">
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
                                <th>Preview</th>
                                <th>Sort Order</th>
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
                    url: "{{ route('admin.astro_banners.list') }}",
                    data: function(d) {
                        d.status = $('#status').val();
                    }
                },
                columns: [{
                        data: 'preview',
                        name: 'preview',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'sort_order',
                        name: 'sort_order'
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
                                <a href="{{ route('admin.astro_banners.update.index') }}/${row.id}"
                                class="btn btn-soft-info">
                                    <i class="bx bx-pencil"></i>
                                </a>
                                <button class="btn btn-soft-danger delete-entry"
                                        data-href="{{ route('admin.astro_banners.delete') }}/${row.id}">
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

                $.get(`{{ route('admin.astro_banners.change.status') }}/${id}`)
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

            $('#status').on('change', function() {
                table.ajax.reload();
            });

            $('#reset-filter').on('click', function() {
                $('#status').val('').trigger('change');
                table.ajax.reload();
            });

        });
    </script>
@endsection
