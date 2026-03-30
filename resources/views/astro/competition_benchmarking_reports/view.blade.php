<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel">Competition Benchmarking Report Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
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
                        <th>Competition Product</th>
                        <td>{!! $report->competition_product_info ?? 'N/A' !!}</td>
                    </tr>
                    <tr>
                        <th>Stock Available</th>
                        <td>
                            <span class="{{ $report->is_stock ? 'text-success' : 'text-danger' }}">
                                {{ $report->is_stock ? 'In Stock' : 'Out Of Stock' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>Promo Running</th>
                        <td>{{ $report->promo_running ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>No Permission Reason</th>
                        <td>{{ $report->no_permission_reason ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Regular Price</th>
                        <td>{{ $report->regular_price ? number_format($report->regular_price) : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Selling Price</th>
                        <td>{{ $report->selling_price ? number_format($report->selling_price) : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Count of Facing</th>
                        <td>{{ $report->count_of_facing ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
</div>
