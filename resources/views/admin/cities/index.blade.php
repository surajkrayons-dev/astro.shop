@extends('layouts.master')

@section('title') Cities @endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">All Cities</h4>

                @if (Can::is_accessible('cities', 'create'))
                    <div class="page-title-right">
                        <a href="javascript:void(0);" data-href="{{ route('admin.cities.create.index') }}" class="btn btn-soft-info waves-effect waves-light open-remote-modal"><i class="fas fa-plus"></i> Create</a>
                        {{-- <a href="javascript:void(0);" data-href="{{ route('admin.cities.import.csv.index') }}" class="btn btn-success waves-effect waves-light open-remote-modal"><i class="fas fa-file"></i> Import</a> --}}
                        <a href="javascript:void(0);" data-href="{{ route('admin.cities.import.xlsx.index') }}" class="btn btn-soft-success waves-effect waves-light open-remote-modal" title="Import"><i class="fas fa-file"></i> Import </a>
                        <a href="{{ route('admin.cities.export.xlsx.data') }}" class="btn btn-soft-warning waves-effect waves-light" title="Export"><i class="fas fa-file"></i> Export</a>
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
                                <th>City</th>
                                <th>Country</th>
                                <th>State</th>
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
                $.get(`{{ route("admin.cities.change.status") }}/${id}`, () => reloadTable('data-table'));
            });

            $('#data-table').DataTable({
                ajax: '{{ route("admin.cities.list") }}',
                @if (Can::is_accessible('cities', 'create'))
                    dom: DT_DOM_OPTION,
                    buttons: DT_BUTTONS_OPTION,
                @endif
                columns : [
                    { data: 'name' },
                    { data: 'country', name: 'countries.name' },
                    { data: 'state', name: 'states.name' },
                    {
                        data: null,
                        name: 'status',
                        className: 'text-center',
                        mRender: (data, type, row) => {
                            return `
                                @if (Can::is_accessible('cities', 'update'))
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
                                @if (Can::is_accessible('cities', 'update'))
                                    <a href="javascript:void(0);" data-href="{{ route('admin.cities.update.index') }}/${row.id}" class="btn btn-soft-info btn-sm waves-effect waves-light open-remote-modal"><i class="bx bx-pencil font-size-16"></i></a>
                                @endif
                                @if (Can::is_accessible('cities', 'delete'))
                                    <button type="button" class="btn btn-soft-danger btn-sm waves-effect waves-light delete-entry" data-href="{{ route("admin.cities.delete") }}/${row.id}" data-tbl="data"><i class="bx bx-trash font-size-16"></i></button>
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
