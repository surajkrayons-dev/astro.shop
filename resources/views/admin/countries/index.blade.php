@extends('layouts.master')

@section('title') Countries @endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">All Countries</h4>

                @if (Can::is_accessible('countries', 'create'))
                    <div class="page-title-right">
                        <a href="javascript:void(0);" data-href="{{ route('admin.countries.create.index') }}" class="btn btn-soft-info waves-effect waves-light open-remote-modal"><i class="fas fa-plus"></i> Create</a>
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
                                <th>Country</th>
                                <th width="180px;">Status</th>
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
            $('#data-table').on('change', '.change-status', function (e) {
                e.preventDefault();

                const { id } = $(this).data();
                $(this).prop('disabled', true);
                $.get(`{{ route("admin.countries.change.status") }}/${id}`, () => reloadTable('data-table'));
            });

            $('#data-table').DataTable({
                ajax: '{{ route("admin.countries.list") }}',
                @if (Can::is_accessible('countries', 'create'))
                    dom: DT_DOM_OPTION,
                    buttons: DT_BUTTONS_OPTION,
                @endif
                columns : [
                    { data: 'name' },
                    {
                        data: null,
                        name: 'status',
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            return `
                                @if (Can::is_accessible('countries', 'update'))
                                    <div class="square-switch">
                                        <input type="checkbox" id="status-switch-${row.id}" class="change-status" switch="status" data-id="${row.id}" ${row.status == 1 ? 'checked' : ''} />
                                        <label for="status-switch-${row.id}" data-on-label="Yes" data-off-label="No"></label>
                                    </div>
                                @else
                                    ${row.status == 1 ? 'Active' : 'Inactive'}
                                @endif

                            `;
                        }
                    },
                    {
                        data: null,
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            return `
                                @if (Can::is_accessible('countries', 'update'))
                                    <a href="javascript:void(0);" data-href="{{ route('admin.countries.update.index') }}/${row.id}" class="btn btn-soft-info btn-sm waves-effect waves-light open-remote-modal"><i class="bx bx-pencil font-size-16"></i></a>
                                @endif
                                @if (Can::is_accessible('countries', 'delete'))
                                    <button type="button" class="btn btn-soft-danger btn-sm waves-effect waves-light delete-entry" data-href="{{ route("admin.countries.delete") }}/${row.id}" data-tbl="data"><i class="bx bx-trash font-size-16"></i></button>
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
