<div class="modal-header">
    <h5 class="modal-title">Payout Request Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    {{-- USER INFO --}}
    <h6 class="text-primary mb-3">User Information</h6>
    <table class="table table-bordered">
        <tr>
            <th width="35%">Name</th>
            <td>{{ $payout->user->name }}</td>
        </tr>
        <tr>
            <th>User Code</th>
            <td>{{ $payout->user->code }}</td>
        </tr>
        <tr>
            <th>User Type</th>
            <td>
                {{ $payout->user->role_id == 2 ? 'Astrologer' : 'User' }}
            </td>
        </tr>
    </table>

    {{-- PAYOUT INFO --}}
    <h6 class="text-primary mb-3 mt-4">Payout Information</h6>
    <table class="table table-bordered">
        <tr>
            <th width="35%">Amount</th>
            <td>₹ {{ number_format($payout->amount, 2) }}</td>
        </tr>
        <tr>
            <th>Payment Method</th>
            <td>{{ strtoupper($payout->paymentAccount->type) }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if($payout->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                @elseif($payout->status === 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                @else
                    <span class="badge bg-warning">Pending</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Requested On</th>
            <td>{{ $payout->created_at->format('d M, Y h:i A') }}</td>
        </tr>
        <tr>
            <th>Last Updated</th>
            <td>{{ $payout->updated_at->format('d M, Y h:i A') }}</td>
        </tr>
    </table>

    {{-- PAYMENT ACCOUNT DETAILS --}}
    <h6 class="text-primary mb-3 mt-4">Payment / Bank Details</h6>

    <table class="table table-bordered">
        <tr>
            <th width="35%">Account Holder</th>
            <td>{{ $payout->paymentAccount->account_holder_name }}</td>
        </tr>

        @if($payout->paymentAccount->type === 'upi')
            <tr>
                <th>UPI ID</th>
                <td>{{ $payout->paymentAccount->upi_id }}</td>
            </tr>
        @endif

        @if($payout->paymentAccount->type === 'bank')
            <tr>
                <th>Bank Name</th>
                <td>{{ $payout->paymentAccount->bank_name }}</td>
            </tr>
            <tr>
                <th>Account Number</th>
                <td>{{ $payout->paymentAccount->account_number }}</td>
            </tr>
            <tr>
                <th>IFSC Code</th>
                <td>{{ $payout->paymentAccount->ifsc_code }}</td>
            </tr>
        @endif
    </table>

</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
        Close
    </button>
</div>
