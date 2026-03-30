@extends('layouts.login')

@section('title') Two Factor Authentication @endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="text-center mb-5 text-muted">
                <a href="javascript:void(0);" class="d-block auth-logo">
                    <img src="{{ url('assets/images/favicon.png') }}" alt="" height="50" class="auth-logo-dark mx-auto">
                    <img src="{{ url('assets/images/favicon.png') }}" alt="" height="50" class="auth-logo-light mx-auto">
                </a>
                <p class="mt-3">{{env('APP_NAME')}}</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="p-2">
                <div class="text-center">
                    <div class="avatar-md mx-auto">
                        <div class="avatar-title rounded-circle bg-light">
                            <i class="bx bxs-envelope h1 mb-0 text-primary"></i>
                        </div>
                    </div>
                    <div class="p-2 mt-4">
                        <h4>Two Factor Verification</h4>
                        <p class="mb-5">Please enter the 4 digit code sent to <span class="fw-semibold">{{ $user->email }}</span></p>

                        <form id="otpFrm">
                            <div class="row">
                                <div class="col-3">
                                    <div class="mb-3">
                                        <label for="digit1-input" class="visually-hidden">Dight 1</label>
                                        <input type="text" class="form-control form-control-lg text-center two-step" maxLength="1" data-value="1" id="digit1-input" autofocus>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="mb-3">
                                        <label for="digit2-input" class="visually-hidden">Dight 2</label>
                                        <input type="text" class="form-control form-control-lg text-center two-step" maxLength="1" data-value="2" id="digit2-input">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="mb-3">
                                        <label for="digit3-input" class="visually-hidden">Dight 3</label>
                                        <input type="text" class="form-control form-control-lg text-center two-step" maxLength="1" data-value="3" id="digit3-input">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="mb-3">
                                        <label for="digit4-input" class="visually-hidden">Dight 4</label>
                                        <input type="text" class="form-control form-control-lg text-center two-step" maxLength="1" data-value="4" id="digit4-input">
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="mt-4">
                            <button id="verifyBtn" type="button" class="btn btn-success w-md">Verify</button>
                            <p id="message-box" class="mb-0 mt-3"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function moveToNext(t,u){0<t.value.length&&$("#digit"+u+"-input").focus()}var count=1;$(".two-step").keyup(function(t){0==count&&(count=1),8===t.keyCode?(5==count&&(count=3),$("#digit"+count+"-input").focus(),count--):0<count&&(count++,$("#digit"+count+"-input").focus())});
    </script>

    <script>
        $(document).on('click', '#verifyBtn', function (e) {
            e.preventDefault();
            const $loader = $('#message-box');
            const $btn = $(this);
            let otp = '';

            $('#otpFrm .two-step').each((i, el) => otp = `${otp}${$(el).val()}`);

            $.ajax({
                dataType: 'json',
                type: 'POST',
                url: "{{ route('auth.two.factor.verify') }}",
                data: {
                    otp: otp,
                    token: '{{ $user->hash_token }}',
                    _token: '{{ csrf_token() }}',
                },
                beforeSend: () => {
                    $btn.prop('disabled', true);
                    $loader.html(`<div class="alert alert-info fade show mb-0" role="alert"><i class="mdi mdi-reload"></i> Please wait..</div>`);
                },
                error: (jqXHR, exception) => {
                    $btn.prop('disabled', false);
                    $loader.html(`
                        <div class="alert alert-danger alert-dismissible fade show mb-0" role="alert">
                            ${formatErrorMessage(jqXHR, exception)}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                    $('#otpFrm .two-step:last').focus();
                },
                success: response => {
                    $btn.prop('disabled', false);
                    $loader.html(`
                        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
                            ${response.message}
                        </div>
                    `);

                    setTimeout(() => location.replace('{{ route("admin.dashboard.index") }}'), 1500);
                },
            });
        });
    </script>
@endsection
