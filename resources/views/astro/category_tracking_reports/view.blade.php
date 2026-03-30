<div class="modal-header">
    <h5 class="modal-title">Category Tracking Report Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-3">
            <div class="p-1">
                <h6>Photo</h6>
                <img src="{{ $report->photo ? asset('storage/category_tracking_reports/' . $report->photo) : 'https://placehold.co/250x250' }}"
                    alt="Category Photo" class="img-fluid rounded">
            </div>
        </div>

        <div class="col-md-5">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>Promoter</th>
                        <td>{!! $report->promoter_info ?? 'N/A' !!}</td>
                    </tr>
                    <tr>
                        <th>Store</th>
                        <td>{!! $report->store_info ?? 'N/A' !!}</td>
                    </tr>
                    <tr>
                        <th>Category Tracking</th>
                        <td>{!! $report->category_tracking_info ?? 'N/A' !!}</td>
                    </tr>
                    <tr>
                        <th>Count of Facing</th>
                        <td>{{ $report->count_of_facing ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Total Category Facing</th>
                        <td>{{ $report->total_category_facing ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-4">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>Achievement SOS</th>
                        <td>{{ $report->achievement_sos ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Target SOS</th>
                        <td>{{ $report->target_sos ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Remaining Target Value</th>
                        <td>{{ $report->remaining_target_value ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Is Category Available</th>
                        <td>
                            <span class="{{ $report->is_category_available ? 'text-success' : 'text-danger' }}">
                                {{ $report->is_category_available ? 'Yes' : 'No' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>No Permission Reason</th>
                        <td>{{ $report->no_permission_reason ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
