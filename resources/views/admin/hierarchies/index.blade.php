@extends('layouts.master')

@section('title') All User Hierarchy @endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">All Users</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">User Hierarchy</li>
                    </ol>
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
                                <th>Client</th>
                                @foreach($roles as $role)
                                    <th>{{ $role->name }}</th>
                                @endforeach
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

        $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route("admin.hierarchies.list") }}',
            columns: [
                { data: 'client', name: 'client' },
                @foreach($roles as $role)
                    { data: 'role_{{ $role->id }}', name: 'role_{{ $role->id }}' },
                @endforeach
            ]
        });

    </script>
@endsection
