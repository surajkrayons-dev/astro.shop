@extends('layouts.login')

@section('title') Login @endsection

@section('content')
    <div class="card overflow-hidden">
        <div class="bg-primary bg-soft">
            <div class="row">
                <div class="col-7">
                    <div class="text-primary p-4">
                        <h5 class="text-primary">Welcome Back !</h5>
                        <p>Sign in with {{env('APP_NAME')}}.</p>
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

                <form id="loginFrm" class="form-horizontal">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" autofocus />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group auth-pass-inputgroup">
                            <input type="password" class="form-control" name="password" placeholder="Enter password" />
                            <button class="btn btn-light" type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                        </div>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember-check" name="remember_me" value="1" />
                        <label class="form-check-label" for="remember-check">Remember me</label>
                    </div>

                    <div class="mt-3 d-grid">
                        <button id="loginBtn" type="button" class="btn btn-primary waves-effect waves-light" type="submit">Log In</button>
                    </div>

                    <div class="mt-4 text-center">
                        <a href="{{ route('auth.forgot.password.index') }}" class="text-muted"><i class="mdi mdi-lock me-1"></i> Forgot your password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).on('keypress', '[name="username"], [name="password"]', function(e) {
            if(e.which == 10 || e.which == 13) {
                e.preventDefault();
                $('#loginBtn').click();
            }
        });

        $(document).on('click', '#loginBtn', function (e) {
            e.preventDefault();
            const $loader = $('#message-box');
            const $btn = $(this);

            $.ajax({
                dataType: 'json',
                type: 'POST',
                data: $('#loginFrm').serialize(),
                url: "{{ route('auth.login') }}",
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

                    location.replace(response.redirect_url);
                },
            });
        });
    </script>
@endsection
