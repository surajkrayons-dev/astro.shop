@php
    $countryMap = cache('country_dial_map', []);
@endphp

<div class="modal-header">
    <h5 class="modal-title">Interaction Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <div class="row">

        {{-- ================= ASTRO INFO ================= --}}
        <div class="col-md-3">
            <h6 class="text-primary mb-2">Astrologer Information</h6>
            <table class="table table-bordered">
                <tr>
                    <th>Code</th>
                    <td>
                        {{ $astro->code ?? 'N/A' }}

                        @if (optional($astro)->deleted_at)
                            <span class="badge bg-danger ms-1">Deleted</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>
                        {{ $astro->name ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <th>Mobile</th>
                    <td>{{ $astro->mobile ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Country</th>
                    <td>
                        {{ $countryMap[$astro->country_code] ?? 'Unknown' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- ================= USER INFO ================= --}}
        <div class="col-md-3">
            <h6 class="text-primary mb-2">User Information</h6>
            <table class="table table-bordered">
                <tr>
                    <th>Code</th>
                    <td>{{ $user->code }}</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>Mobile</th>
                    <td>{{ $user->mobile ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Country</th>
                    <td>
                        {{ $countryMap[$user->country_code] ?? 'Unknown' }}
                    </td>
                </tr>
            </table>
        </div>

        {{-- ================= WALLET SUMMARY ================= --}}
        <div class="col-md-3">
            <h6 class="text-primary mb-2">Wallet Summary (This Interaction)</h6>
            <table class="table table-bordered">
                <tr>
                    <th>Total Spent</th>
                    <td class="fw-bold text-danger">₹ {{ number_format($total_spent, 2) }}</td>
                </tr>
                <tr>
                    <th>Chat Amount</th>
                    <td>₹ {{ number_format($total_chat_amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Call Amount</th>
                    <td>₹ {{ number_format($total_call_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- ================= INTERACTION SUMMARY ================= --}}
        <div class="col-md-3">
            <h6 class="text-primary mb-2">Interaction Summary</h6>
            <table class="table table-bordered">
                <tr>
                    <th>Chat Duration</th>
                    <td>{{ $total_chat_duration }} sec</td>
                </tr>
                <tr>
                    <th>Call Duration</th>
                    <td>{{ $total_call_duration }} sec</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span
                            class="badge bg-{{ ($session->status ?? '') === 'completed' ? 'success' : 'secondary' }}">
                            {{ ucfirst($session->status ?? 'N/A') }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
