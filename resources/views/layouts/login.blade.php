<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title') | {{ env('APP_NAME') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="author" content="Suraj Verma" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ url('assets/images/favicon.png') }}">

    <!-- Bootstrap Css -->
    <link href="{{ url('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ url('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Select2 css -->
    <link href="{{ url('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/libs/spectrum-colorpicker2/spectrum.min.css') }}" rel="stylesheet" type="text/css">

    <!-- Datepicker Css -->
    <link href="{{ url('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet"
        type="text/css">
    <link href="{{ url('assets/libs/@chenfengyuan/datepicker/datepicker.min.css') }}" rel="stylesheet" type="text/css">

    <!-- dropify file-upload css -->
    <link href="{{ url('assets/libs/dropify/dropify.min.css') }}" rel="stylesheet" type="text/css">

    <!-- DataTables -->
    <link href="{{ url('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ url('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="{{ url('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" type="text/css" />

    <!-- Sweet Alert-->
    <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- App Css-->
    <link href="{{ url('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/css/style.css') }}" id="app-style" rel="stylesheet" type="text/css" />
</head>

<body data-sidebar="dark" data-layout-mode="light">
    <div class="account-pages">
        <div class="container">
            <div class="row justify-content-center align-items-center h-100v">
                <div class="col-md-8 col-lg-6 col-xl-5">

                    @yield('content')

                    <div class="text-center">
                        <p class="m-0">
                            &nbsp;
                            <script>
                                document.write(new Date().getFullYear());
                            </script>
                            {{ env('APP_NAME') }}.
                        </p>
                        <p>Design & Developed by <a href="#" target="_blank">Suraj Verma</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="{{ url('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ url('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ url('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ url('assets/libs/node-waves/waves.min.js') }}"></script>

    <!-- Select2 Js -->
    <script src="{{ url('assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ url('assets/libs/spectrum-colorpicker2/spectrum.min.js') }}"></script>

    <!-- dropify file-upload Js -->
    <script src="{{ url('assets/libs/dropify/dropify.min.js') }}"></script>

    <!-- Sweet Alerts js -->
    <script src="{{ url('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- form repeater js -->
    <script src="{{ url('assets/libs/jquery.repeater/jquery.repeater.min.js') }}"></script>

    <!-- Required datatable js -->
    <script src="{{ url('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Buttons examples -->
    <script src="{{ url('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ url('assets/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ url('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ url('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>

    <script src="{{ url('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ url('assets/libs/@chenfengyuan/datepicker/datepicker.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ url('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ url('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ url('assets/js/app.js') }}"></script>
    <script src="{{ url('assets/js/custom.js') }}"></script>
    <script src="{{ url('assets/js/main.js') }}"></script>

    <script>
        // <!-- Http Errors -->
        const ajax_errors = {
            http_not_connected: "Not connected. Please verify your network connection.",
            request_forbidden: "Forbidden resource can not be accessed.",
            not_found_request: "Requested page not found. [404]",
            session_expire: "Failed to process your request, Please try again later",
            service_unavailable: "Service unavailable.",
            parser_error: "Error.Parsing JSON Request failed.",
            request_timeout: "Request Time out",
            request_abort: "Request was aborted by the server."
        };
    </script>

    @yield('script')
</body>

</html>
