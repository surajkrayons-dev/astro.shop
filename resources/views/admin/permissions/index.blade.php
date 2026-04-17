@extends('layouts.master')

@section('title') Permissions @endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Permissions Access</h4>

            <div class="page-title-right">
                <a href="javascript:void(0);" class="btn btn-soft-info disabled">
                    <i class="fas fa-info-circle"></i> Given Permissions Access
                </a>
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
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
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

    @if(session('success'))
    toastr.success("{{ session('success') }}");
    @endif

    $('#data-table').DataTable({
        processing: true,
        serverSide: true,

        ajax: {
            url: "{{ route('admin.permissions.list') }}"
        },

        columns: [{
                data: 'name',
                name: 'name'
            },
            {
                data: 'username',
                name: 'username'
            },
            {
                data: 'email',
                name: 'email'
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <a href="/permissions/update/${row.id}" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-user-shield"></i> Manage
                        </a>
                    `;
                }
            }
        ]
    });

});
</script>

@endsection