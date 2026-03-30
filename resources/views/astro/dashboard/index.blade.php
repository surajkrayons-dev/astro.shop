@extends('layouts.master')

@section('title') Dashboard @endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Dashboard</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">Home</li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">

    {{-- Today Earnings --}}
    <div class="col-md-3">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <p class="text-muted fw-medium">Today’s Earnings</p>
                <h4 class="mb-0 today_earnings">₹ 0</h4>
            </div>
        </div>
    </div>

    {{-- Active Status --}}
    <div class="col-md-3">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <p class="text-muted fw-medium">Active Status</p>
                <h4 class="mb-0 active_status">--</h4>
            </div>
        </div>
    </div>

    {{-- Total Sessions --}}
    <div class="col-md-3">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <p class="text-muted fw-medium">Today’s Sessions</p>
                <h4 class="mb-0 today_sessions">0</h4>
            </div>
        </div>
    </div>

    {{-- Wallet Balance --}}
    <div class="col-md-3">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <p class="text-muted fw-medium">Wallet Balance</p>
                <h4 class="mb-0">₹ <span class="wallet_balance">0</span></h4>
            </div>
        </div>
    </div>

</div>

{{-- Extra info row (optional but recommended) --}}
<div class="row mt-3">

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted mb-1">Today Chats</p>
                <h5 class="today_chats">0</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted mb-1">Today Calls</p>
                <h5 class="today_calls">0</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted mb-1">Chat Duration</p>
                <h5 class="chat_duration">0 min</h5>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted mb-1">Call Duration</p>
                <h5 class="call_duration">0 min</h5>
            </div>
        </div>
    </div>

</div>
@endsection

@section('script')
<script>
$(document).ready(function () {

    $.get("{{ route('astro.dashboard.stats') }}", function (res) {

        $('.today_earnings').text('₹ ' + res.today_earnings);
        $('.active_status').text(res.active_status);
        $('.today_sessions').text(res.total_sessions);

        $('.wallet_balance').text(res.wallet_balance);

        // Extra stats
        $('.today_chats').text(res.today_chats);
        $('.today_calls').text(res.today_calls);
        $('.chat_duration').text(res.chat_duration + ' min');
        $('.call_duration').text(res.call_duration + ' min');
    });

});
</script>
@endsection
