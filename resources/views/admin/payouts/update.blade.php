<div class="modal-header">
    <h5 class="modal-title">Update Payout Request</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    
    <p>
        <strong>User Type:</strong>
        {{ $payout->user->role_id == 2 ? 'Astrologer' : 'User' }}
    </p>
    <p><strong>Name:</strong> {{ $payout->user->name }}</p>
    <p><strong>Amount:</strong> ₹{{ number_format($payout->amount, 2) }}</p>

    <p>
        <strong>Payment Method:</strong>
        {{ strtoupper($payout->paymentAccount->type) }}
    </p>

    <p><strong>Status:</strong>
        <span class="badge
            @if($payout->status === 'approved') bg-success
            @elseif($payout->status === 'rejected') bg-danger
            @else bg-warning
            @endif">
            {{ ucfirst($payout->status) }}
        </span>
    </p>

</div>

<div class="modal-footer">

    @if($payout->status === 'pending')
        <button class="btn btn-success" id="approve-btn" data-id="{{ $payout->id }}">
            Approve
        </button>

        <button class="btn btn-danger" id="reject-btn" data-id="{{ $payout->id }}">
            Reject
        </button>
    @else
        <button class="btn btn-secondary" disabled>
            Already {{ ucfirst($payout->status) }}
        </button>
    @endif

</div>

<script>
$(document).ready(function () {

    const reloadTable = () => {
        $('#data-table').DataTable().ajax.reload(null, false);
    };

    $('#approve-btn').on('click', function () {

        const payoutId = $(this).data('id');

        Swal.fire({
            title: 'Approve Payout?',
            text: 'Wallet balance will be deducted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.post(
                '{{ route("admin.payouts.approve", ":id") }}'.replace(':id', payoutId),
                { _token: '{{ csrf_token() }}' },
                function () {
                    $('.modal').modal('hide');
                    reloadTable();
                    Swal.fire('Approved', 'Payout approved successfully', 'success');
                }
            ).fail(function (xhr) {
                Swal.fire('Error', xhr.responseJSON?.message ?? 'Something went wrong', 'error');
            });
        });
    });

    $('#reject-btn').on('click', function () {

        const payoutId = $(this).data('id');

        Swal.fire({
            title: 'Reject Payout?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Reject',
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.post(
                '{{ route("admin.payouts.reject", ":id") }}'.replace(':id', payoutId),
                { _token: '{{ csrf_token() }}' },
                function () {
                    $('.modal').modal('hide');
                    reloadTable();
                    Swal.fire('Rejected', 'Payout rejected successfully', 'success');
                }
            );
        });
    });

});
</script>


