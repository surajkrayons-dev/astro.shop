<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title> @yield('title') | {{ env('APP_NAME') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="author" content="Suraj Verma" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

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

    <link rel="stylesheet" type="text/css" href="{{ url('assets/libs/toastr/build/toastr.min.css') }}">

    <!-- Responsive datatable examples -->
    <link href="{{ url('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" type="text/css" />

    <!-- Sweet Alert-->
    <link href="{{ url('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Daterange Picker-->
    <link href="{{ url('assets/libs/daterangepicker/daterangepicker.css') }}" rel="stylesheet" type="text/css" />

    <!-- App Css-->
    <link href="{{ url('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/css/style.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    @yield('style')
</head>

<body data-sidebar="dark" data-layout-mode="light">

    <div class="layout-wrapper">

        @include('layouts.partials.header')

        @include('layouts.partials.leftmenu')

        <div class="main-content">

            <!--[ Page Content ] start -->
            <div class="page-content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            @include('layouts.partials.footer')

        </div>

    </div>

    @include('layouts.partials.drawer')

    <!-- Remote Modals -->
    <div id="smRemoteModal" class="modal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-sm">
            <div class="modal-content"></div>
        </div>
    </div>
    <div id="remoteModal" class="modal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content"></div>
        </div>
    </div>
    <div id="lgRemoteModal" class="modal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content"></div>
        </div>
    </div>
    <div id="xlRemoteModal" class="modal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl">
            <div class="modal-content"></div>
        </div>
    </div>
    <style>
        .modal-xxl {
            max-width: 95% !important;
            margin: 20px auto;
        }

        #xxlRemoteModal .modal-content {
            border-radius: 8px;
        }
    </style>
    <div id="xxlRemoteModal" class="modal" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xxl">
            <div class="modal-content"></div>
        </div>
    </div>


    <!-- JAVASCRIPT -->
    <script src="{{ url('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ url('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ url('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ url('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ url('assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ url('assets/libs/moment/min/moment.min.js') }}"></script>

    <!-- Select2 Js -->
    <script src="{{ url('assets/libs/select2/js/select2.min.js') }}"></script>
    <script src="{{ url('assets/libs/spectrum-colorpicker2/spectrum.min.js') }}"></script>

    <!-- dropify file-upload Js -->
    <script src="{{ url('assets/libs/dropify/dropify.min.js') }}"></script>

    <!-- Sweet Alerts js -->
    <script src="{{ url('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- Daterange Picker js -->
    <script src="{{ url('assets/libs/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ url('assets/libs/daterangepicker/daterangepicker.js') }}"></script>

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

    <script src="{{ url('assets/libs/toastr/build/toastr.min.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- App js -->
    <script src="{{ url('assets/js/app.js') }}"></script>
    <script src="{{ url('assets/js/custom.js') }}"></script>
    <script src="{{ url('assets/js/main.js') }}"></script>

    <script>
        const DT_DOM_OPTION = '<"container-fluid"<"row"<"col-1"l><"col"B><"col"f>>>rtip';
        // const DT_BUTTONS_OPTION = ['csv', 'excel', 'pdf', 'colvis'];
        const DT_BUTTONS_OPTION = [];

        // Default Datatables Options
        $.extend(true, $.fn.dataTable.defaults, {
            // scrollX: true,
            // scrollCollapse: true,
            // fixedColumns: true,
            stateSave: true,
            processing: true,
            serverSide: true,
            // dom: '<"container-fluid"<"row"<"col-1"l><"col"B><"col"f>>>rtip',
            // buttons: ['csv', 'excel', 'pdf', 'colvis'],
            lengthMenu: [
                [10, 25, 50, 100, 500, 1000],
                ['10 Rows', '25 Rows', '50 Rows', '100 Rows', '500 Rows', '1000 Rows']
            ],
            language: {
                url: '{{ url('assets/libs/datatables.net/language/english.json') }}',
            },
        });

        // Http Errors
        const ajax_errors = {
            http_not_connected: "Not connected. Please verify your network connection.",
            request_forbidden: "Forbidden resource can not be accessed.",
            not_found_request: "Requested page not found. [404]",
            session_expire: "Session expired, please reload the page and try again.",
            service_unavailable: "Service unavailable.",
            parser_error: "Error.Parsing JSON Request failed.",
            request_timeout: "Request Time out",
            request_abort: "Request was aborted by the server."
        };

        $(document).on('click', 'a.open-remote-modal', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const target = $($(this).data('target') ?? '#remoteModal');

            target.modal('show');
            target.find('.modal-content')
                .html(
                    `<div class="modal-body"><h4 style="margin: 0;"><i class="fa fa-spinner fa-pulse"></i> Please wait...</h4></div>`
                )
                .load($(this).data('href'));
        });

        $('#smRemoteModal, #remoteModal, #lgRemoteModal, #xlRemoteModal, #xxlRemoteModal').on('hidden.bs.modal', () =>
            toastr.remove());

        // $('#data-table').on('click', '.delete-entry', async function(e) {
        //     e.preventDefault();
        //     const message = $(this).data('message') ?? 'Are you sure?';

        //     if (await confirmAlert(message)) {
        //         const href = $(this).data('href');
        //         const tbl = $(this).data('tbl');
        //         $.get( href, () => reloadTable(`${tbl}-table`));
        //     }
        // });

        $('#data-table').on('click', '.delete-entry', function(e) {
            e.preventDefault();

            const $this = $(this);
            const message = $this.data('message') ?? 'Are you sure?';
            const href = $this.data('href');
            const tbl = $this.data('tbl') ?? 'data'; // id of table without '-table'

            Swal.fire({
                title: 'Confirm Deletion',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.get(href, function(res) {
                        toastr.success(res.message ?? 'Deleted successfully');

                        // ✅ Reload only the table, not full page
                        $(`#${tbl}-table`).DataTable().ajax.reload(null, false);
                    }).fail(function() {
                        toastr.error('Something went wrong. Try again.');
                    });
                }
            });
        });

        // await confirmAlert()
        async function confirmAlert(text = 'Are you sure?') {
            const {
                value: isAccepted
            } = await Swal.fire({
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fa fa-check"></i> Yes',
                cancelButtonText: '<i class="fa fa-times"></i> Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            });
            return isAccepted === true;
        }

        async function infoAlert(msg, icon = 'warning') {
            await Swal.fire({
                text: msg,
                icon, // warning, error, success, info, and question
                showCancelButton: false,
                confirmButtonText: '<i class="fa fa-check"></i> Okay',
                confirmButtonColor: '#3085d6',
            });
        }


        function showToastr(type = 'info', content = '<i class="fa fa-spinner fa-pulse"></i> Please wait...', title = '') {
            // type: warning, success, error, success

            const options = {
                closeButton: (type == 'info' ? false : true),
                progressBar: (type == 'info' ? false : true),
                newestOnTop: false,
                preventDuplicates: true,
                tapToDismiss: false,
                showDuration: 0,
                hideDuration: 300,
                timeOut: (type == 'info' ? 0 : 5000),
                extendedTimeOut: 5000,
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut",
                positionClass: "toast-top-right",
                onclick: null,
                debug: false,
            };

            // toastr.clear();
            toastr.remove();

            eval(`toastr.${type}(content, title, options)`);
        }
    </script>

    @yield('script')
</body>

</html>
