@extends('layouts.master')

@section('title')
Dashboard
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    @if (Can::is_accessible('customers', 'view'))
    <div class="col-md-4">
        <div class="card mini-stats-wid h-100">
            <div class="card-body d-flex">
                <div class="flex-grow-1">
                    <p class="text-muted fw-medium mb-2">Total Clients</p>
                    <h4 class="mb-0 total_clients"><i class="fa fa-spinner fa-pulse"></i></h4>
                </div>
                <div class="flex-shrink-0 align-self-center">
                    <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                        <span class="avatar-title rounded-circle">
                            <i class="bx bx-user font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (Can::is_accessible('promoters', 'view'))
    <div class="col-md-4">
        <div class="card mini-stats-wid h-100">
            <div class="card-body d-flex">
                <div class="flex-grow-1">
                    <p class="text-muted fw-medium mb-2">Total Promoters</p>
                    <h4 class="mb-0 total_promoters"><i class="fa fa-spinner fa-pulse"></i></h4>
                </div>
                <div class="flex-shrink-0 align-self-center">
                    <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                        <span class="avatar-title rounded-circle">
                            <i class="bx bx-user-plus font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if (Can::is_accessible('staff', 'view'))
    <div class="col-md-4">
        <div class="card mini-stats-wid h-100">
            <div class="card-body d-flex">
                <div class="flex-grow-1">
                    <p class="text-muted fw-medium mb-2">Total Staff</p>
                    <h4 class="mb-0 total_staff"><i class="fa fa-spinner fa-pulse"></i></h4>
                </div>
                <div class="flex-shrink-0 align-self-center">
                    <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                        <span class="avatar-title rounded-circle">
                            <i class="bx bx-group font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('script')
<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch('{{ route('
            admin.dashboard.stats ') }}')
        .then(res => res.json())
        .then(data => {
            document.querySelector('.total_clients').innerText = data.total_clients ?? '0';
            document.querySelector('.total_promoters').innerText = data.total_promoters ?? '0';
            document.querySelector('.total_staff').innerText = data.total_staff ?? '0';
        })
        .catch(err => {
            console.error("Stats fetch failed:", err);
            document.querySelectorAll('.total_clients, .total_promoters, .total_staff')
                .forEach(el => el.innerText = '0');
        });
});
</script>
<script>
const orderDates = {
        start: moment().add(1, 'days').subtract(7, 'days'),
        end: moment()
    },
    datePickerRange = {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'Last 15 Days': [moment().subtract(15, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
            'month')],
        'Last 3 Month': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
        'This Year': [moment().startOf('year'), moment().endOf('year')],
        'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
    },
    datePickerLocale = {
        format: 'YYYY-MM-DD',
        applyLabel: 'Apply',
        cancelLabel: 'Cancel',
        customRangeLabel: 'Custom',
    };

$(document).on('click', '.collapse-card', function(e) {
    e.preventDefault();
    const cardBody = $(this).closest('.card').find('.card-body');
    cardBody.slideToggle();
});

@if(Can::is_accessible('orders'))
setTimeout(getOrdersData, 400);

// Chart
const ordersChart = new Chart($('#ordersChart').get(0).getContext('2d'), {
    type: 'line',
    data: {
        labels: [],
        datasets: []
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index',
        },
    }
});

$('.orders_date_range').daterangepicker({
    opens: 'left',
    startDate: orderDates.start,
    endDate: orderDates.end,
    locale: datePickerLocale,
    ranges: datePickerRange,
}, getOrdersData);

function getOrdersData(start = orderDates.start, end = orderDates.end) {
    orderDates.start = start;
    orderDates.end = end;

    $.ajax({
        dataType: 'json',
        type: 'GET',
        url: '{{ route('
        admin.dashboard.orders.graph ') }}',
        data: {
            start_date: start.lang('en').format('YYYY-MM-DD'),
            end_date: end.lang('en').format('YYYY-MM-DD'),
        },
        success: response => {
            ordersChart.data = {
                labels: response.labels,
                datasets: [{
                        label: 'Orders',
                        data: response.orders,
                        borderColor: "rgb(52, 144, 220)",
                        backgroundColor: 'rgb(52, 144, 220, .1)',
                        borderWidth: 1.5,
                        tension: 0.2,
                        fill: true,
                    },
                    {
                        label: 'Order Amount',
                        data: response.earnings,
                        borderColor: "rgb(77, 220, 52)",
                        backgroundColor: 'rgb(77, 220, 52, .1)',
                        borderWidth: 1.5,
                        tension: 0.2,
                        fill: true,
                    },
                ]
            };
            ordersChart.update();
        }
    });

    reloadTable('orders-tbl');
}

// Table
$('#orders-tbl').DataTable({
    ajax: {
        type: 'POST',
        url: '{{ route('
        admin.orders.report.list ') }}',
        data: params => {
            @if($admin - > isStaff())
            params.user_id = '{{ $admin->id }}';
            @endif
            params.status = 'completed';
            params.start_date = orderDates.start.format('YYYY-MM-DD');
            params.end_date = orderDates.end.format('YYYY-MM-DD');
            params._token = '{{ csrf_token() }}';
            return params;
        }
    },
    deferLoading: false,
    aaSorting: [
        [6, 'desc']
    ],
    @if(Can::is_accessible('orders', 'create'))
    dom: DT_DOM_OPTION,
    buttons: DT_BUTTONS_OPTION,
    @endif
    columns: [{
            data: 'order_code'
        },
        {
            data: 'name',
            name: 'order_customer_details.name',
            mRender: (data, type, row) => {
                return `<p class="m-0"><b>${data}</b></p><p class="m-0">[<a href="tel:+91${row.mobile}">${row.mobile}</a>]</p>`;
            }
        },
        {
            data: 'total_amount',
            mRender: data => `<b><i class="bx bx-rupee"></i>${formatMoney(data)}</b>`
        },
        {
            data: 'user',
            name: 'users.name'
        },
        {
            data: 'payment_method_text',
            name: 'orders.payment_method'
        },
        {
            data: 'status_text',
            name: 'orders.status'
        },
        {
            data: 'created_at',
            mRender: data => formatDate(data)
        },
    ],
});
@endif

@if(Can::is_accessible('customers', 'view') ||
    Can::is_accessible('staff', 'view') ||
    Can::is_accessible('orders', 'view') ||
    Can::is_accessible('sale_report', 'view'))
getStats();

function getStats() {
    $.ajax({
        dataType: 'json',
        type: 'GET',
        url: '{{ route('
        admin.dashboard.stats ') }}',
        error: () => $('.total_customers, .total_users, .total_sales').text('0'),
        success: response => $.each(response, (key, val) => $(`.${key}`).text(val)),
    });
}
@endif
</script>
@endsection