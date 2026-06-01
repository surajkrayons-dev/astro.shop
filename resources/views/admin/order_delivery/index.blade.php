@extends('layouts.master')

@section('title')
    Order Delivery
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="mb-sm-0 font-size-18">
                    Order Delivery
                </h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-lg-10">

            <div class="card border">

                <div class="card-header">
                    <h4 class="card-title mb-0">
                        Search Order
                    </h4>
                </div>

                <div class="card-body">

                    <div class="row g-2">

                        <div class="col-md-10">

                            <input type="text" id="keyword" class="form-control"
                                placeholder="Enter Order No, Order ID, Mobile or Email">

                        </div>

                        <div class="col-md-2">

                            <button type="button" id="search-order" class="btn btn-primary w-100">

                                Search

                            </button>

                        </div>

                    </div>

                </div>

            </div>

            <div class="card border mt-3 d-none" id="order-card">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h4 class="card-title mb-0">
                        Order Details
                    </h4>

                    <span id="status_badge"></span>

                </div>

                <div class="card-body">

                    <table class="table table-bordered align-middle">

                        <tr>
                            <th width="220">Order No.</th>
                            <td id="order_no"></td>
                        </tr>

                        <tr>
                            <th>Customer</th>
                            <td id="customer"></td>
                        </tr>

                        <tr>
                            <th>Mobile</th>
                            <td id="mobile"></td>
                        </tr>

                        <tr>
                            <th>Email</th>
                            <td id="email"></td>
                        </tr>

                        <tr>
                            <th>Employee</th>
                            <td id="employee"></td>
                        </tr>

                        <tr>
                            <th>Coupon</th>
                            <td id="coupon"></td>
                        </tr>

                        <tr>
                            <th>Order Amount</th>
                            <td id="amount"></td>
                        </tr>

                        <tr>
                            <th>Created At</th>
                            <td id="created_at"></td>
                        </tr>

                    </table>

                    <div class="text-end">

                        <button type="button" id="mark-delivered" class="btn btn-success">

                            <i class="bx bx-check-circle"></i>
                            Mark As Delivered

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </div>
@endsection

@section('script')
    <script>
        let ORDER_ID = null;

        function getStatusBadge(status) {
            const badges = {

                pending: '<span class="badge bg-warning">Pending</span>',

                paid: '<span class="badge bg-info">Paid</span>',

                packed: '<span class="badge bg-primary">Packed</span>',

                shipped: '<span class="badge bg-dark">Shipped</span>',

                delivered: '<span class="badge bg-success">Delivered</span>',

                cancelled: '<span class="badge bg-danger">Cancelled</span>'
            };

            return badges[status] ?? status;
        }

        $('#search-order').on('click', function() {

            const keyword = $('#keyword').val().trim();

            if (!keyword) {

                toastr.warning(
                    'Please enter Order Number, Order ID, Mobile or Email'
                );

                return;
            }

            const button = $(this);

            button
                .prop('disabled', true)
                .html(
                    '<i class="bx bx-loader-alt bx-spin"></i> Searching...'
                );

            $.ajax({

                url: '{{ route('admin.order_delivery.search') }}',

                method: 'POST',

                data: {

                    _token: '{{ csrf_token() }}',

                    keyword: keyword
                },

                success: function(response) {

                    const order = response.order;

                    ORDER_ID = order.id;

                    $('#order-card')
                        .removeClass('d-none');

                    $('#order_no').html(
                        order.order_number ?? '-'
                    );

                    $('#customer').html(
                        order.user?.name ?? '-'
                    );

                    $('#mobile').html(
                        order.mobile ?? '-'
                    );

                    $('#email').html(
                        order.email ?? '-'
                    );

                    $('#employee').html(
                        order.coupon?.employee?.name ?? 'Admin'
                    );

                    $('#coupon').html(
                        order.coupon?.code ?? '-'
                    );

                    $('#amount').html(
                        '₹ ' + order.total_amount
                    );

                    $('#created_at').html(
                        order.created_at
                    );

                    $('#status_badge').html(
                        getStatusBadge(order.status)
                    );

                    if (
                        order.status === 'delivered' ||
                        order.status === 'cancelled'
                    ) {

                        $('#mark-delivered').hide();

                    } else {

                        $('#mark-delivered').show();
                    }
                },

                error: function(xhr) {

                    $('#order-card')
                        .addClass('d-none');

                    toastr.error(
                        xhr.responseJSON?.message ??
                        'Order not found'
                    );
                },

                complete: function() {

                    button
                        .prop('disabled', false)
                        .html('Search');
                }

            });

        });

        $('#mark-delivered').on('click', function() {

            if (!ORDER_ID) {
                return;
            }

            Swal.fire({

                title: 'Mark As Delivered?',

                text: 'Commission will be generated for employee.',

                icon: 'warning',

                showCancelButton: true,

                confirmButtonText: 'Yes, Deliver'

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({

                    url: "{{ route('admin.order_delivery.delivered', ['id' => '__ID__']) }}"
                        .replace('__ID__', ORDER_ID),

                    method: 'POST',

                    data: {

                        _token: '{{ csrf_token() }}'
                    },

                    success: function(response) {

                        toastr.success(
                            response.message
                        );

                        $('#status_badge').html(
                            getStatusBadge('delivered')
                        );

                        $('#mark-delivered')
                            .hide();
                    },

                    error: function(xhr) {

                        toastr.error(
                            xhr.responseJSON?.message ??
                            'Something went wrong'
                        );
                    }
                });

            });

        });

        $('#keyword').on('keypress', function(e) {

            if (e.which === 13) {

                $('#search-order').click();
            }

        });
    </script>
@endsection
