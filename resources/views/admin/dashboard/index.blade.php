@extends('layouts.master')

@section('title') Dashboard @endsection

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4>Dashboard</h4>
    </div>
</div>

{{-- STATS --}}
<div class="row g-3 mb-3">
    @foreach([
        'total_users' => 'Total Users',
        'online_users' => 'Users Online',
        'total_orders' => 'Total Orders',
        'pending_orders' => 'Pending Orders',
        'delivered_orders' => 'Delivered Orders',
        'cancelled_orders' => 'Cancelled Orders',
        'total_products' => 'Total Products',
        'total_revenue' => 'Total Revenue'
    ] as $key => $label)
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <p class="text-muted">{{ $label }}</p>
                <h4 class="{{ $key }}"><i class="fa fa-spinner fa-pulse"></i></h4>
            </div>
        </div>
    </div>
    @endforeach
</div>


<div class="row g-3 mb-4">
    <div class="col-md-12 d-flex justify-content-end align-items-center">
        <label class="me-2 fw-bold mb-0">Date picker :</label>
        <input type="text" id="dashboard_date_range" class="form-control w-25" placeholder="Select Date Range">
    </div>
</div>

{{-- GRAPHS --}}
<div class="row g-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Sales & Revenue Analytics</div>
            <div class="card-body">
                <canvas id="growthChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-12 mt-3">
        <div class="card">
            <div class="card-header">User Growth Analytics</div>
            <div class="card-body">
                <canvas id="engagementChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let datePickerRanges = {
    'Today': [moment(), moment()],
    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    'Last 15 Days': [moment().subtract(15, 'days'), moment()],
    'This Month': [moment().startOf('month'), moment().endOf('month')],
    'Last Month': [
        moment().subtract(1, 'month').startOf('month'),
        moment().subtract(1, 'month').endOf('month')
    ],
    'Last 3 Month': [
        moment().subtract(2, 'month').startOf('month'),
        moment().endOf('month')
    ],
    'This Year': [moment().startOf('year'), moment().endOf('year')],
    'Last Year': [
        moment().subtract(1, 'year').startOf('year'),
        moment().subtract(1, 'year').endOf('year')
    ],
};

let datePickerLocale = {
    format: 'YYYY-MM-DD',
    applyLabel: 'Apply',
    cancelLabel: 'Cancel',
    customRangeLabel: 'Custom',
};

let start = moment().subtract(6, 'days');
let end = moment();

$('#dashboard_date_range').daterangepicker({
    startDate: start,
    endDate: end,
    ranges: datePickerRanges,
    locale: datePickerLocale
}, loadGraphs);


const growthChart = new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: {
        labels: [],
        datasets: []
    }
});

const engagementChart = new Chart(document.getElementById('engagementChart'), {
    type: 'line',
    data: {
        labels: [],
        datasets: []
    }
});


// MAIN GRAPH LOADER
function loadGraphs(start, end) {

    loadStats(start, end); // refresh stats

    const params = {
        start_date: start.format('YYYY-MM-DD'),
        end_date: end.format('YYYY-MM-DD')
    };

    $.get('{{ route("admin.dashboard.graph.growth") }}', params, res => {
        growthChart.data.labels = res.labels;
        growthChart.data.datasets = [{
                label: 'Orders',
                data: res.orders,
                borderWidth: 2
            },
            {
                label: 'Revenue',
                data: res.revenue,
                borderWidth: 2
            }
        ];
        growthChart.update();
    });

    $.get('{{ route("admin.dashboard.graph.engagement") }}', params, res => {
        engagementChart.data.labels = res.labels;
        engagementChart.data.datasets = [{
                label: 'Registrations',
                data: res.registrations,
                borderWidth: 2
            },
            {
                label: 'Online Users',
                data: res.online_users,
                borderWidth: 2
            }
        ];
        engagementChart.update();
    });
}


// SUMMARY BOXES LOADER
function loadStats(start, end) {

    const params = {
        start_date: start.format('YYYY-MM-DD'),
        end_date: end.format('YYYY-MM-DD')
    };

    $.get('{{ route("admin.dashboard.stats") }}', params, res => {
        Object.keys(res).forEach(k => $('.' + k).text(res[k]));
    });
}


// INITIAL LOAD
loadGraphs(start, end);
</script>
@endsection