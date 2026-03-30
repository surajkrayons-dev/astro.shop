@extends('layouts.login')

@section('title') Forgot Password @endsection

@section('content')
    <div class="card overflow-hidden">
        <div class="bg-primary bg-soft">
            <div class="row">
                <div class="col-7">
                    <div class="text-primary p-4">
                        <h5 class="text-primary">Forgot Password</h5>
                        <p>Reset your password with {{env('APP_NAME')}}.</p>
                    </div>
                </div>
                <div class="col-5 align-self-end">
                    <img src="{{ url('assets/images/profile-img.png') }}" alt="" class="img-fluid" />
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="auth-logo">

                <a href="javascript:void(0);" class="auth-logo-light">
                    <div class="avatar-md profile-user-wid mb-4">
                        <span class="avatar-title rounded-circle bg-light">
                            <img src="{{ url('assets/images/favicon.png') }}" alt="" class="rounded-circle" height="32" />
                        </span>
                    </div>
                </a>

                <a href="javascript:void(0);" class="auth-logo-dark">
                    <div class="avatar-md profile-user-wid mb-4">
                        <span class="avatar-title rounded-circle bg-light">
                            <img src="{{ url('assets/images/favicon.png') }}" alt="" class="rounded-circle" height="32" />
                        </span>
                    </div>
                </a>
            </div>
            <div class="p-2">
                <p id="message-box" class="mb-0"></p>

                <form id="forgotPswdFrm" class="form-horizontal" onsubmit="return false;">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" class="form-control" id="email" name="email" placeholder="Enter your email" autofocus />
                    </div>

                    <div class="mt-3 d-grid">
                        <button id="loginBtn" type="button" class="btn btn-primary waves-effect waves-light" type="submit">Submit</button>
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ route('auth.login.index') }}" class="text-muted"><i class="mdi mdi-lock me-1"></i> Sign In Here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).on('click', '#loginBtn', function (e) {
            e.preventDefault();
            const $loader = $('#message-box');
            const $btn = $(this);

            $.ajax({
                dataType: 'json',
                type: 'POST',
                data: $('#forgotPswdFrm').serialize(),
                url: "{{ route('auth.forgot.password') }}",
                beforeSend: () => {
                    $btn.prop('disabled', true);
                    $loader.html(`<div class="alert alert-info fade show" role="alert"><i class="mdi mdi-reload"></i> Please wait..</div>`);
                },
                error: (jqXHR, exception) => {
                    $btn.prop('disabled', false);
                    $loader.html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${formatErrorMessage(jqXHR, exception)}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                },
                success: response => {
                    $btn.prop('disabled', false);
                    $loader.html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.message}
                        </div>
                    `);

                    setTimeout(() => location.replace('{{ route("admin.dashboard.index") }}'), 2500);
                },
            });
        });
    </script>
@endsection
