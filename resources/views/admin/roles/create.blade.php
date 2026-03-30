@extends('layouts.master')

@section('title') Add Role @endsection

@section('style')
<style>
    .card-sticky table thead th{
        position: sticky;
        top: 0; /* jitna distance chahiye utna set kar lo */
        z-index: 10; /* taaki header upar dikhte rahe */
        background: #f8f9fa; /* bootstrap table-light ka color fix karne ke liye */
    }
    .card-sticky .col-lg-12 {
        max-height: 400px; /* apni requirement ke hisaab se */
        overflow-y: auto;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Add Role</h4>

                <div class="page-title-right">
                    <a href="{{ route("admin.roles.index") }}" class="btn btn-primary waves-effect waves-light"><i class="fas fa-reply-all"></i> Back to list</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <form id="createFrm">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Role Info</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="name" class="form-label fw-bold">Role Name <sup class="text-danger fs-5">*</sup> :</label>
                                            <input type="text" id="name" name="role_name" class="form-control" placeholder="Enter Role Name" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Set Role Permissions</h4>
                            </div>
                            <div class="card-body card-sticky">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <table id="" class="table table-bordered nowrap w-100">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Menu</th>
                                                    <th class="col-1">List</th>
                                                    <th class="col-1">Add</th>
                                                    <th class="col-1">Edit</th>
                                                    <th class="col-1">Delete</th>
                                                    <th class="col-1">Audit</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach (config('permissions.permissions_names') as $key => $text)
                                                    @php
                                                        $actions = config("permissions.available_permissions.{$key}");
                                                        $default_action_statuses = config("permissions.default_permissions.{$key}");
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $text }}</td>
                                                        @foreach (config('permissions.available_actions') as $action)
                                                            @if (in_array($action, $actions))
                                                                <td>
                                                                    <div class="square-switch">
                                                                        <input type="checkbox" id="permission-switch-{{ $key }}-{{ $action }}" switch="status" value="1" name="permissions[{{ $key }}][{{ $action }}]" @checked($default_action_statuses[$action]) />
                                                                        <label for="permission-switch-{{ $key }}-{{ $action }}" data-on-label="Yes" data-off-label="No"></label>
                                                                    </div>
                                                                </td>
                                                            @else
                                                                <td class="text-center">--</td>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="card action-btn">
                            <div class="card-body p-2 text-end">
                                <button id="createBtn" type="button" class="btn btn-success waves-effect waves-light">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(function() {
            $(document).on('click','#createBtn', function(e) {
                e.preventDefault();
                const $btn = $(this);

                $.ajax({
                    dataType: 'json',
                    type: 'POST',
                    url: "{{ route('admin.roles.create') }}",
                    data: $('#createFrm').serialize(),
                    beforeSend: () => {
                        $btn.attr('disabled', true);
                        showToastr();
                    },
                    error: (jqXHR, exception) => {
                        $btn.attr('disabled', false);
                        showToastr('error', formatErrorMessage(jqXHR, exception));
                    },
                    success: response => {
                        $btn.attr('disabled', false);
                        showToastr('success', response.message);
                        location.replace('{{ route("admin.roles.index")}}');
                    }
                });
            });
        });
    </script>
@endsection
