@extends('layouts.master')

@section('title') Roles & Permissions @endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">All Roles & Permissions</h4>

                @if (Can::is_accessible('roles', 'create'))
                    <div class="page-title-right">
                        <a href="{{ route('admin.roles.create.index') }}" class="btn btn-primary waves-effect waves-light">
                            <i class="fas fa-plus"></i> Add New Role
                        </a>
                    </div>
                @endif
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
                                <th>Role</th>
                                <th width="180px;">Action</th>
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
            $('#data-table').DataTable({
                ajax: '{{ route("admin.roles.list") }}',
                columns : [
                    { data: 'name' },
                    {
                        data: null,
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            return `
                                @if (Can::is_accessible('roles', 'update'))
                                    <a href="{{ route('admin.roles.update.index') }}/${row.id}" class="btn btn-soft-info btn-sm waves-effect waves-light"><i class="bx bx-pencil font-size-16"></i></a>
                                @endif
                                @if (Can::is_accessible('roles', 'delete'))
                                    <button type="button" class="btn btn-soft-danger btn-sm waves-effect waves-light delete-entry" data-href="{{ route("admin.roles.delete") }}/${row.id}" data-tbl="data" data-message="Delete role will delete all the linked staff members as well. Are you sure?"><i class="bx bx-trash font-size-16"></i></button>
                                @endif
                            `;
                        },
                        orderable: false,
                        searchable: false
                    }
                ],
            });
        });
    </script>
@endsection
