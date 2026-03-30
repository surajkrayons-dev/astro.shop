<div class="modal-header">
    <h5 class="modal-title">
        Product Details – {{ $product->name }}
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <div class="row">

        {{-- 🔹 BASIC --}}
        <div class="col-md-4">
            <h6 class="text-primary mb-2">Basic Info</h6>
            <table class="table table-bordered">
                <tr>
                    <th>Code</th>
                    <td>{{ $product->code }}</td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ $product->name }}</td>
                </tr>
                <tr>
                    <th>Slug</th>
                    <td>{{ $product->slug }}</td>
                </tr>
                <tr>
                    <th>Stone</th>
                    <td>{{ $product->stone_name }}</td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ $product->category->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="badge bg-{{ $product->status ? 'success' : 'danger' }}">
                            {{ $product->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>

        {{-- 🔹 PRICING --}}
        <div class="col-md-4">
            <h6 class="text-primary mb-2">Pricing</h6>
            <table class="table table-bordered">
                <tr>
                    <th>Before Price</th>
                    <td>₹ {{ number_format($product->before_price, 2) }}</td>
                </tr>
                <tr>
                    <th>After Price</th>
                    <td class="text-success fw-bold">₹ {{ number_format($product->after_price, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- 🔹 STOCK --}}
        <div class="col-md-4">
            <h6 class="text-primary mb-2">Stock</h6>

            <table class="table table-bordered">
                <tr>
                    <th>Qty</th>
                    <td><span id="current-stock">{{ $product->stock_qty }}</span></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @php
                            $colors = [
                                'in_stock' => 'success',
                                'few_left' => 'warning',
                                'out_of_stock' => 'danger',
                            ];
                        @endphp

                        <span id="stock-status-badge" class="badge bg-{{ $colors[$product->stock_status] }}">
                            {{ ucfirst(str_replace('_', ' ', $product->stock_status)) }}
                        </span>
                    </td>
                </tr>
            </table>

            <div class="mt-2">
                <input type="number" id="stock-input" class="form-control mb-2" placeholder="Add Qty">
                <button class="btn btn-success w-100" id="updateStockBtn" data-id="{{ $product->id }}">
                    Update Stock
                </button>
            </div>
        </div>

    </div>

    {{-- 🔹 IMAGE --}}
    @if ($product->image)
        <div class="mt-4 text-center">
            <h6 class="text-primary">Primary Image</h6>
            <img src="{{ asset('storage/product/' . $product->image) }}" class="img-fluid rounded"
                style="max-height:250px;">
        </div>
    @endif

    {{-- 🔹 GALLERY --}}
    @if ($product->images->count())
        <div class="mt-4">
            <h6 class="text-primary">Gallery</h6>
            <div class="row">
                @foreach ($product->images as $img)
                    <div class="col-md-3 mb-2 text-center">
                        @if (Str::endsWith($img->images, '.mp4'))
                            <video src="{{ asset('storage/product/' . $img->images) }}" controls
                                style="max-height:150px;"></video>
                        @else
                            <img src="{{ asset('storage/product/' . $img->images) }}" class="img-fluid rounded"
                                style="max-height:150px;">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 🔹 DESCRIPTION --}}
    <div class="mt-4">
        <h6 class="text-primary">Description</h6>
        <div class="card">
            <div class="card-body">{!! $product->description !!}</div>
        </div>
    </div>

    {{-- 🔹 BENEFITS --}}
    <div class="mt-3">
        <h6 class="text-primary">Benefits</h6>
        <div class="card">
            <div class="card-body">{!! $product->benefits !!}</div>
        </div>
    </div>

    {{-- 🔹 HOW TO USE --}}
    <div class="mt-3">
        <h6 class="text-primary">How To Use</h6>
        <div class="card">
            <div class="card-body">{!! $product->how_to_use !!}</div>
        </div>
    </div>

    {{-- 🔹 PURITY --}}
    <div class="mt-3">
        <h6 class="text-primary">Purity</h6>
        <div class="card">
            <div class="card-body">{!! $product->purity !!}</div>
        </div>
    </div>

    {{-- 🔹 RATTI --}}
    @if (!empty($product->ratti_options))
        <div class="mt-4">
            <h6 class="text-primary">Ratti Options</h6>
            <table class="table table-bordered">
                <tr>
                    <th>Ratti</th>
                    <th>Before</th>
                    <th>After</th>
                </tr>
                @foreach ($product->ratti_options as $r)
                    <tr>
                        <td>{{ $r['ratti'] }}</td>
                        <td>₹ {{ $r['ratti_beforePrice'] }}</td>
                        <td class="text-success">₹ {{ $r['ratti_afterPrice'] }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- 🔹 SPEC --}}
    @if (!empty($product->specifications))
        <div class="mt-4">
            <h6 class="text-primary">Specifications</h6>
            <table class="table table-bordered">
                @foreach ($product->specifications as $s)
                    <tr>
                        <th>{{ $s['title'] }}</th>
                        <td>{{ $s['value'] }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- 🔹 FAQ --}}
    @if (!empty($product->faq))
        <div class="mt-4">
            <h6 class="text-primary">FAQ</h6>
            @foreach ($product->faq as $f)
                <div class="mb-2">
                    <strong>Q:</strong> {{ $f['question'] }} <br>
                    <strong>A:</strong> {{ $f['answer'] }}
                </div>
            @endforeach
        </div>
    @endif

    {{-- 🔹 LAB --}}
    @if (!empty($product->lab_certificates))
        <div class="mt-4">
            <h6 class="text-primary">Lab Certificates</h6>
            <table class="table table-bordered">
                @foreach ($product->lab_certificates as $lab)
                    <tr>
                        <td><img src="{{ asset('storage/lab_certificates/' . $lab['image']) }}" width="80"></td>
                        <td>{{ $lab['number'] }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    {{-- 🔹 EXTRA --}}
    <div class="mt-4">
        <h6 class="text-primary">Extra Info</h6>
        <table class="table table-bordered">
            <tr>
                <th>Origin</th>
                <td>{{ $product->origin }}</td>
            </tr>
            <tr>
                <th>Planet</th>
                <td>{{ $product->planet }}</td>
            </tr>
            <tr>
                <th>Shipping</th>
                <td>{{ $product->shipping_info }}</td>
            </tr>
        </table>
    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>

<script>
    $(document).on('click', '#updateStockBtn', function() {

        let id = $(this).data('id');
        let qty = $('#stock-input').val();

        if (!qty) {
            showToastr('error', 'Enter quantity');
            return;
        }

        $.post("{{ route('admin.product_stocks.update', '') }}/" + id, {
            _token: "{{ csrf_token() }}",
            stock_qty: qty
        }, function(res) {

            $('#current-stock').text(res.stock_qty);

            let colors = {
                in_stock: 'success',
                few_left: 'warning',
                out_of_stock: 'danger'
            };

            $('#stock-status-badge')
                .attr('class', 'badge bg-' + colors[res.stock_status])
                .text(res.stock_status.replace('_', ' ').toUpperCase());

            $('#stock-input').val('');
            showToastr('success', 'Updated');

        });

    });
</script>
