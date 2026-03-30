<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel">Visibility Report Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="row">
                <div class="col-md-4 text-center">
                    <img class="img-fluid rounded mb-2" src="{{ $report->photo_left_side ? asset('storage/visibility_reports/' . $report->photo_left_side) : 'https://placehold.co/250x250' }}" style="max-height: 250px; object-fit: cover;" onerror="this.onerror=null;this.src='https://placehold.co/250x250';">
                    <p><strong>Photo Left Side</strong></p>
                </div>
                <div class="col-md-4 text-center">
                    <img class="rounded mb-2" src="{{ $report->photo_close_up ? asset('storage/visibility_reports/' . $report->photo_close_up) : 'https://placehold.co/250x250' }}" style="max-height: 250px; object-fit: cover;" onerror="this.onerror=null;this.src='https://placehold.co/250x250';">
                    <p><strong>Photo Close Up</strong></p>
                </div>
                <div class="col-md-4 text-center">
                    <img class="rounded mb-2" src="{{ $report->photo_right_side ? asset('storage/visibility_reports/' . $report->photo_right_side) : 'https://placehold.co/250x250' }}" style="max-height: 250px; object-fit: cover;" onerror="this.onerror=null;this.src='https://placehold.co/250x250';">
                    <p><strong>Photo Right Side</strong></p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <table class="table table-bordered table-striped">
                <tr>
                    <th>Promoter</th>
                    <td>{!! $report->promoter_info ?? 'N/A' !!}</td>
                </tr>
                <tr>
                    <th>Store</th>
                    <td>{!! $report->store_info ?? 'N/A' !!}</td>
                </tr>
                <tr>
                    <th>POSM</th>
                    <td>{!! $report->posm_info ?? 'N/A' !!}</td>
                </tr>
                <tr>
                    <th>Visibility Action</th>
                    <td>{{ $report->visibility_action }}</td>
                </tr>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-bordered table-striped">
                <tr>
                    <th>Is Adhoc Visibility Available</th>
                    <td>
                        <span class="{{ $report->is_adhoc_visibility_available ? 'text-success' : 'text-danger' }}">
                            {{ $report->is_adhoc_visibility_available ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Stock as per Planogram</th>
                    <td>
                        <span class="{{ $report->stock_as_per_planogram ? 'text-success' : 'text-danger' }}">
                            {{ $report->stock_as_per_planogram ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Is Stock Available</th>
                    <td>
                        <span class="{{ $report->is_stock_available ? 'text-success' : 'text-danger' }}">
                            {{ $report->is_stock_available ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Branding Condition</th>
                    <td>{{ $report->branding_condition ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>No Permission Reason</th>
                    <td>{{ $report->no_permission_reason ?? 'N/A' }}</td>
                </tr>

            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
</div>
