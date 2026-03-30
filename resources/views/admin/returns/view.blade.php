@php
    $statusColor = match ($return->status) {
        'requested' => 'warning',
        'approved' => 'info',
        'picked' => 'primary',
        'refunded' => 'success',
        'rejected' => 'danger',
        default => 'secondary',
    };
@endphp

<div class="modal-header border-0">
    <h5 class="mb-0">
        Return #{{ $return->id }}
        <span class="badge bg-{{ $statusColor }} ms-2">
            {{ ucfirst($return->status) }}
        </span>
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    {{-- ================= TOP SECTION ================= --}}
    <div class="row g-4">

        {{-- RETURN DETAILS --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-primary fw-semibold mb-3">Return Details</h6>

                    <p><strong>Reason:</strong><br>
                        <span class="text-muted">{{ $return->reason }}</span>
                    </p>

                    <p><strong>Requested At:</strong><br>
                        {{ $return->created_at->format('d M Y h:i A') }}
                    </p>

                    <p><strong>Refund Amount:</strong><br>
                        <span class="text-success fw-semibold">
                            ₹ {{ number_format($item->total, 2) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        {{-- CUSTOMER --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-primary fw-semibold mb-3">Customer</h6>

                    <p><strong>Code:</strong> {{ $user->code }}</p>
                    <p><strong>Name:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Mobile:</strong> {{ $user->mobile }}</p>
                </div>
            </div>
        </div>

        {{-- ORDER INFO --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h6 class="text-primary fw-semibold mb-3">Order Info</h6>

                    <p><strong>Order No:</strong> {{ $order->order_number }}</p>
                    <p><strong>Total Paid:</strong> ₹ {{ number_format($order->total_amount, 2) }}</p>

                    @if ($order->payment)
                        <hr>
                        <p><strong>Payment Method:</strong> {{ strtoupper($order->payment->method) }}</p>
                        <p><strong>Transaction:</strong> {{ $order->payment->transaction_id }}</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ================= PRODUCT DETAILS ================= --}}
    <div class="mt-4">
        <h6 class="text-primary fw-semibold mb-3">Returned Product</h6>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ optional(optional($product)->category)->name ?? '-' }}</td>
                        <td>{{ $product->name ?? 'Deleted Product' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td>₹ {{ number_format($item->price, 2) }}</td>
                        <td class="fw-semibold">₹ {{ number_format($item->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= REFUND ACCOUNT ================= --}}
    <div class="mt-4">
        <div class="row g-4">

            {{-- ================= SELECTED REFUND ACCOUNT ================= --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">

                        <h6 class="text-primary fw-semibold mb-3">
                            Selected Refund Account
                        </h6>

                        @php
                            $selected = $return->paymentAccount;
                        @endphp

                        @if ($selected)
                            <p>
                                <strong>Type:</strong>
                                <span class="badge bg-success">
                                    {{ strtoupper($selected->type) }}
                                </span>
                            </p>

                            <p><strong>Account Holder:</strong>
                                {{ $selected->account_holder_name }}
                            </p>

                            @if ($selected->type === 'upi')
                                <p><strong>UPI ID:</strong>
                                    {{ $selected->upi_id }}
                                </p>
                            @else
                                <p><strong>Bank:</strong>
                                    {{ $selected->bank_name }}
                                </p>
                                <p><strong>Account No:</strong>
                                    {{ $selected->account_number }}
                                </p>
                                <p><strong>IFSC:</strong>
                                    {{ $selected->ifsc_code }}
                                </p>
                            @endif
                        @else
                            <p class="text-muted">No refund account selected.</p>
                        @endif

                    </div>
                </div>
            </div>

            {{-- ================= ALL USER ACCOUNTS ================= --}}
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">

                        <h6 class="text-primary fw-semibold mb-3">
                            All User Payment Accounts
                        </h6>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Holder</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($userAccounts as $account)
                                        <tr
                                            class="{{ $selected && $selected->id == $account->id ? 'table-success' : '' }}">

                                            <td>
                                                <span class="badge bg-dark">
                                                    {{ strtoupper($account->type) }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ $account->account_holder_name }}
                                                @if ($account->is_default)
                                                    <span class="badge bg-primary ms-1">
                                                        Default
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                @if ($account->type === 'upi')
                                                    {{ $account->upi_id }}
                                                @else
                                                    {{ $account->bank_name }} <br>
                                                    A/C: {{ $account->account_number }} <br>
                                                    IFSC: {{ $account->ifsc_code }}
                                                @endif
                                            </td>

                                        </tr>
                                    @endforeach

                                    @if ($userAccounts->isEmpty())
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">
                                                No payment accounts added
                                            </td>
                                        </tr>
                                    @endif

                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>


    {{-- ================= REFUND HISTORY ================= --}}
    @if ($refundHistory->count())
        <div class="mt-4">
            <h6 class="text-primary fw-semibold mb-3">Refund History</h6>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Picked At</th>
                            <th>Refunded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($refundHistory as $refund)
                            <tr>
                                <td class="text-success fw-semibold">
                                    ₹ {{ number_format($refund->amount, 2) }}
                                </td>
                                <td>
                                    <span class="badge bg-dark">
                                        {{ strtoupper($refund->refund_method) }}
                                    </span>
                                </td>
                                <td>{{ $refund->refund_reference ?? 'Manual' }}</td>
                                <td>{{ optional($refund->picked_at)->format('d M Y h:i A') ?? '-' }}</td>
                                <td>{{ optional($refund->refunded_at)->format('d M Y h:i A') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<div class="modal-footer border-0">
    <button class="btn btn-secondary" data-bs-dismiss="modal">
        Close
    </button>
</div>
