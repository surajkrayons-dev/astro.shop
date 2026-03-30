@extends('layouts.master')

@section('title') Client Payout Invoice | Admin @endsection

@section('style')
    <style>
        @media print {
            .d-print-none {
                display: none !important;
            }

            @page {
                margin: 0;
            }
        }
    </style>
@endsection

@section('content')

<div class="container" style="max-width: 1000px; margin: auto;">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Client Payout Invoice</h4>
            <a href="{{ route('client.payout_clients.index') }}" class="btn btn-primary waves-effect waves-light d-print-none">
                <i class="fas fa-reply-all"></i> Back to list
            </a>
        </div>
    </div>

    <div class="card" id="printArea" style="padding: 30px; border: 1px solid #ddd;">
        <div class="card-body">

            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #ccc; padding-bottom: 20px; margin-bottom: 30px;">
                <!-- Left: Company Info -->
                <div style="width: 48%;">
                    <img src="{{ asset('assets/images/logo-dark.png') }}" alt="Company Logo" height="50">
                    @php $admin = \App\Models\User::find(1); @endphp
                    <div style="margin-top: 15px;">
                        <p style="margin: 0;"><strong>Email:</strong> {{ $admin->email ?? 'N/A' }}</p>
                        <p style="margin: 0;"><strong>Mobile:</strong> {{ $admin->mobile ?? 'N/A' }}</p>
                    </div>
                </div>

                <div style="width: 48%; text-align: right;">
                    <h3 style="margin-bottom: 25px;"><b>Invoice</b></h3>
                    <p style="margin: 0;"><strong>Invoice No:</strong> {{ $payout->invoice_no }}</p>
                    <p style="margin: 0;"><strong>Date:</strong> {{ $payout->created_at->format('d M Y') }}</p>
                    <p style="margin: 0;"><strong>Payment Mode:</strong> {{ ucfirst($payout->payment_mode ?? 'N/A') }}</p>
                    <p style="margin: 6px 0 0;"><strong>Status:</strong>
                        @if($payout->status === 'paid')
                            <span style="padding: 5px 10px; background-color: #28a745; color: #fff; border-radius: 4px; font-size: 13px;">Paid</span>
                        @else
                            <span style="padding: 5px 10px; background-color: #dc3545; color: #fff; border-radius: 4px; font-size: 13px;">Unpaid</span>
                        @endif
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <h5 style="margin-bottom: 10px;"><b>Client Details:</b></h5>
                <p style="margin: 0;"><strong>Name:</strong> {{ $payout->client->name ?? 'N/A' }}</p>
                <p style="margin: 0;"><strong>Email:</strong> {{ $payout->client->email ?? 'N/A' }}</p>
                <p style="margin: 0;"><strong>Mobile:</strong> {{ $payout->client->mobile ?? 'N/A' }}</p>
                <p style="margin: 0;"><strong>Address:</strong> {{ $payout->client->address ?? 'N/A' }}</p>
            </div>

            <div style="margin-bottom: 30px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th style="border: 1px solid #000; padding: 8px; width: 60%; text-align: left;">Service Type</th>
                            <th style="border: 1px solid #000; padding: 8px; width: 20%; text-align: right;">Cost (₹)</th>
                            <th style="border: 1px solid #000; padding: 8px; width: 20%; text-align: right;">Total (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border: 1px solid #000; padding: 8px;">
                                {{ ucwords(str_replace('_', ' ', $payout->userService->services ?? 'N/A')) }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: right;">
                                {{ number_format($payout->service_cost, 2) }}
                            </td>
                            <td style="border: 1px solid #000; padding: 8px; text-align: right;">
                                {{ number_format($payout->service_cost, 2) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" style="border: 1px solid #000; padding: 8px; text-align: right;">Payable Amount:</th>
                            <th style="border: 1px solid #000; padding: 8px; text-align: right;">
                                ₹ {{ number_format($payout->service_cost, 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="margin-bottom: 30px;">
                <h5>Terms & Conditions:</h5>
                <p style="color: #6c757d; font-size: 14px;">
                    lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                    incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                    nostrud exercitation ullamco
                    laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor
                </p>
            </div>

            <div class="row text-center">
                <div class="col-sm-12 invoice-btn-group text-center">
                    <button type="button"
                        class="btn waves-effect waves-light btn-primary btn-print-invoice m-b-10 d-print-none">Print</button>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
@section('script')
    <script>
        document.querySelector('.btn-print-invoice').addEventListener('click', function() {
            var link2 = document.createElement('link');
            link2.innerHTML =
                '<style>@media print{*,::after,::before{text-shadow:none!important;box-shadow:none!important}.pcoded-main-container{margin-left:0px;}a:not(.btn){text-decoration:none}abbr[title]::after{content:" ("attr(title) ")"}pre{white-space:pre-wrap!important}blockquote,pre{border:1px solid #adb5bd;page-break-inside:avoid}thead{display:table-header-group}img,tr{page-break-inside:avoid}h2,h3,p{orphans:3;widows:3}h2,h3{page-break-after:avoid}@page{size:a3}body{min-width:992px!important}.container{min-width:992px!important}.page-header,.pc-sidebar,.pc-mob-header,.pc-header,.pct-customizer,.modal,.pcoded-navbar,.print-btn{display:none}.pc-container{top:0;}.invoice-contact{padding-top:0;}@page,.card-body,.card-header,body,.pcoded-content{padding:0;margin:0}.badge{border:1px solid #000}.table{border-collapse:collapse!important}.table td,.table th{background-color:#fff!important}.table-bordered td,.table-bordered th{border:1px solid #dee2e6!important}.table-dark{color:inherit}.table-dark tbody+tbody,.table-dark td,.table-dark th,.table-dark thead th{border-color:#dee2e6}.table .thead-dark th{color:inherit;border-color:#dee2e6}..table tfoot{borbackgroundder-color:#B9B9B9}</style>';

            document.getElementsByTagName('head')[0].appendChild(link2);
            window.print();
        })
    </script>
@endsection
