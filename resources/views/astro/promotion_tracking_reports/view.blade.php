<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel">Promotion Tracking Report Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="row">
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
                    <th>Promotion Product</th>
                    <td>{!! $report->promotion_product_info ?? 'N/A' !!}</td>
                </tr>
                <tr>
                    <th>Same Promotion Running</th>
                    <td>
                        <span class="{{ $report->is_same_promotion_running ? 'text-success' : 'text-danger' }}">
                            {{ $report->is_same_promotion_running ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Promotion Running Reason</th>
                    <td>{{ $report->promotion_running_reason ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Same on POS</th>
                    <td>
                        <span class="{{ $report->is_same_on_pos ? 'text-success' : 'text-danger' }}">
                            {{ $report->is_same_on_pos ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>POS Reason</th>
                    <td>{{ $report->pos_reason ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Same Self Talker</th>
                    <td>
                        <span class="{{ $report->is_same_self_talker ? 'text-success' : 'text-danger' }}">
                            {{ $report->is_same_self_talker ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Self Talker Reason</th>
                    <td>{{ $report->self_talker_reason ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
</div>
