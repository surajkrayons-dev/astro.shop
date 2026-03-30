<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel">Sale Report Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-6">
            <table class="table table-striped table-bordered mb-0">
                <tbody>
                    <tr>
                        <th width="200px;">Promoter</th>
                        <td>{!! $report->promoter_info ?? 'N/A' !!}</td>
                    </tr>
                    <tr>
                        <th>Store</th>
                        <td>{!! $report->store_info ?? 'N/A' !!}</td>
                    </tr>
                    <tr>
                        <th>Product</th>
                        <td>{!! $report->product_info ?? 'N/A' !!}</td>
                    </tr>
                    <tr>
                        <th>Remain Qty</th>
                        <td>{{ $report->remain_qty ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Product Price</th>
                        <td>{{ $report->product_price !== null ? number_format($report->product_price, 2) : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <th>Replenishment Stock</th>
                        <td>{{ $report->replenishment_stock ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-lg-6">
            <table class="table table-striped table-bordered mb-0">
                <tbody>
                    <tr>
                        <th>Closing Stock</th>
                        <td>{{ $report->closing_stock ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Total Sale</th>
                        <td>{{ $report->total_sale !== null ? number_format($report->total_sale, 2) : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Customers Approached</th>
                        <td>{{ $report->customers_approached ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Customers Converted</th>
                        <td>{{ $report->customers_converted ?? 'N/A' }}</td>
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
    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
</div>
