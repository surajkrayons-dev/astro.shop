<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel">Store Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <table class="table table-striped table-bordered mb-0">
                <tbody>
                    <tr>
                        <th>Client</th>
                        <td>[ {{ ($store->client)->code }} ]-{{ ($store->client)->name }}</td>
                    </tr>
                    <tr>
                        <th>Client Store Code</th>
                        <td>{{ $store->client_store_code }}</td>
                    </tr>
                    <tr>
                        <th>Store Name</th>
                        <td>{{ $store->name }}</td>
                    </tr>
                    <tr>
                        <th>Distributor Code</th>
                        <td>{{ $store->distributor_code }}</td>
                    </tr>
                    <tr>
                        <th>Distributor Name</th>
                        <td>{{ $store->distributor_name }}</td>
                    </tr>
                    <tr>
                        <th>Store Type</th>
                        <td>{{ $store->store_type ?? 'N/A'}}</td>
                    </tr>
                    <tr>
                        <th>Store Chain</th>
                        <td>{{ $store->chain->name ?? 'N/A' }}</td>
                    </tr>
                    {{-- <tr>
                        <th>KYC Status</th>
                        <td>{{ $store->kyc_status ? 'Verified' : 'Not Verified' }}</td>
                    </tr> --}}
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="{{ $store->status ? 'text-success' : 'text-danger' }}">
                                {{ $store->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-striped table-bordered mb-0">
                <tbody>
                    <tr>
                        <th>Store Code</th>
                        <td>{{ $store->code }}</td>
                    </tr>
                    <tr>
                        <th>City</th>
                        <td>{{ $store->city->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>State</th>
                        <td>{{ $store->state->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Country</th>
                        <td>{{ $store->country->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>{{ $store->address ?? 'N/A'}}</td>
                    </tr>
                    <tr>
                        <th>Region</th>
                        <td>[ {{ $store->region->code ?? 'N/A' }} ] - {{ $store->region->name ?? 'N/A' }}</td>

                    </tr>
                    <tr>
                        <th>Format</th>
                        <td>[ {{ $store->format->code ?? 'N/A' }} ] - {{ $store->format->name ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
</div>
