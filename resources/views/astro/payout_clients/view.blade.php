<div id="view-payouts-wrapper">
    <div class="modal-header">
        <h5 class="modal-title">Client Payout Details</h5>
        <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-lg-12">
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <th>Invoice Id</th>
                            <td>{{ $payout->invoice_no }}</td>
                        </tr>
                        <tr>
                            <th>Client </th>
                            <td>[{{ $client->code }}] - {{ $client->name }}</td>
                        </tr>
                        <tr>
                            <th>Service Type </th>
                            <td>{{ ucwords(str_replace('_', ' ', $payout->userService->services ?? '-')) }}</td>
                        </tr>
                        <tr>
                            <th>Amount</th>
                            <td>{{ $payout->service_cost }}</td>
                        </tr>
                        <tr>
                            <th>Payment Method</th>
                            <td>{{ $payout->payment_mode ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>{{ $payout->status }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-lg-12">
                <div class="form-group">
                    <label class="col-from-label" for="amount">Description:</label>
                    <textarea name="description" id="description" class="form-control" rows="2" disabled>{{ old('description', ucwords(str_replace('_', ' ', $payout->description ?? ''))) }}</textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
            </div>

        </div>
    </div>
</div>
