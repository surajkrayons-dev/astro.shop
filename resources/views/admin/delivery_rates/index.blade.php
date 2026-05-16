@extends('layouts.master')

@section('title', 'Delivery Rates')

@section('content')

{{-- PAGE HEADER --}}
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Delivery Rates</h4>

            <div class="page-title-right">
                <a href="{{ route('admin.delivery_rates.create.index') }}" class="btn btn-soft-info">
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

                        <label class="form-label fw-bold">
                            State
                        </label>

                        <select id="state" class="form-control select2-class2" data-placeholder="Choose State">

                            <option value=""></option>

                            @foreach($states as $state)

                            <option value="{{ $state }}">
                                {{ $state }}
                            </option>

                            @endforeach

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
                            <th>State</th>
                            <th>Delivery Charge</th>
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
            url: "{{ route('admin.delivery_rates.list') }}",

            data: function(d) {
                d.state = $('#state').val();
                d.status = $('#status').val();
            }
        },

        columns: [{
                data: 'state',
                name: 'state'
            },
            {
                data: 'delivery_charge',
                name: 'delivery_charge',

                render: function(data) {
                    return `₹ ${data}`;
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
                                       data-off-label="No">
                                </label>

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
                            <a href="{{ route('admin.delivery_rates.update.index') }}/${row.id}"
                               class="btn btn-soft-info">

                                <i class="bx bx-pencil"></i>

                            </a>

                            <button class="btn btn-soft-danger delete-entry"
                                    data-href="{{ route('admin.delivery_rates.delete') }}/${row.id}">

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

        $.get(`{{ route('admin.delivery_rates.change.status') }}/${id}`)

            .done(function() {

                table.ajax.reload(null, false);

            })

            .fail(function() {

                checkbox.prop(
                    'checked',
                    !checkbox.prop('checked')
                );

            })

            .always(function() {

                checkbox.prop('disabled', false);

            });
    });

    $('#state, #status').on('change', function() {

        table.ajax.reload();

    });

    $('#reset-filter').on('click', function() {

        $('#state').val('').trigger('change');

        $('#status').val('').trigger('change');

        table.ajax.reload();

    });

});
</script>

@endsection