<div class="modal-header">
    <h5 class="modal-title" id="myModalLabel">Product Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-striped table-bordered mb-0">
                <tbody>
                    <tr>
                        <table class="table table-striped table-bordered">
                            <tbody>
                                <tr>
                                    <th width="200px;">Product Code</th>
                                    <td>{{ $product->code }}</td>
                                </tr>
                                <tr>
                                    <th>Product Name</th>
                                    <td><b>{{ $product->name }}</b></td>
                                </tr>
                                <tr>
                                    <th>Client</th>
                                    <td>[ {{ ($product->client)->code }} ]-{{ ($product->client)->name }}</td>
                                </tr>
                                <tr>
                                    <th>Brand</th>
                                    <td>{{ $product->brand ? $product->brand->name : '' }}</td>
                                </tr>
                                <tr>
                                    <th>Product Category</th>
                                    <td>{{ $product->category }}</td>
                                </tr>
                                <tr>
                                    <th>Product Price</th>
                                    <td>
                                        <b><i class="bx bx-rupee"></i>{{ formatNumber($product->price) }}</b>
                                    </td>
                                </tr>
                                <th>Published</th>
                                    <td>
                                        <span class="{{ $product->status == 1 ? 'text-success' : 'text-danger' }}">
                                            {{ $product->status == 1 ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                    <tr>
                                        <th>Product Order</th>
                                        <td>{{ $product->order }}</td>
                                    </tr>
                                    <th>Product Description</th>
                                    <td>{{ $product->description }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Close</button>
</div>
