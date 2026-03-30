@php
    $user = $order->user;
@endphp

<div class="modal-header border-0 pb-0">
    <div>
        <h5 class="mb-0">
            Order Details
            <span class="text-muted">#{{ $order->order_number }}</span>
        </h5>
        <small class="text-muted">
            Placed on {{ optional($order->created_at)->format('d M Y h:i A') }}
        </small>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body pt-3">

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-primary fw-semibold mb-3">Order Summary</h6>

                    @php
                        $statusBadge = match ($order->status) {
                            'pending' => 'bg-warning',
                            'paid' => 'bg-info',
                            'packed' => 'bg-primary',
                            'shipped' => 'bg-dark',
                            'delivered' => 'bg-success',
                            'cancelled' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp

                    <p class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge {{ $statusBadge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>

                    @if ($order->delivered_at)
                        <p class="mb-2">
                            <strong>Delivered At:</strong><br>
                            <small class="text-muted">
                                {{ $order->delivered_at->format('d M Y h:i A') }}
                            </small>
                        </p>
                    @endif

                    <hr>

                    <p class="mb-1">
                        Subtotal:
                        <span class="float-end">
                            ₹ {{ number_format($order->subtotal ?? 0, 2) }}
                        </span>
                    </p>

                    <p class="mb-1 text-success">
                        Discount:
                        <span class="float-end">
                            - ₹ {{ number_format($order->discount ?? 0, 2) }}
                        </span>
                    </p>

                    <hr>

                    <p class="fw-bold mb-0">
                        Total:
                        <span class="float-end text-success">
                            ₹ {{ number_format($order->total_amount ?? 0, 2) }}
                        </span>
                    </p>

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-primary fw-semibold mb-3">Customer Details</h6>

                    <p class="mb-1"><strong>Code:</strong> {{ $user->code ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Name:</strong> {{ $user->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $user->email ?? 'N/A' }}</p>
                    <p class="mb-0"><strong>Mobile:</strong> {{ $user->mobile ?? 'N/A' }}</p>

                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-primary fw-semibold mb-3">Order Stats</h6>

                    <p class="mb-1">
                        <strong>Total Items:</strong>
                        {{ $order->items->sum('quantity') }}
                    </p>

                    <p class="mb-1">
                        <strong>Total Products:</strong>
                        {{ $order->items->count() }}
                    </p>

                    <p class="mb-3">
                        <strong>Unique Categories:</strong>
                        {{ $order->items->filter(fn($item) => $item->product && $item->product->category)->pluck('product.category.name')->unique()->count() }}
                    </p>

                    @if ($order->payment)
                        <hr>
                        <h6 class="text-primary fw-semibold mb-2">Payment</h6>

                        <p class="mb-1">
                            <strong>Transaction: </strong>{{ $order->payment->transaction_id }}
                        </p>

                        <p class="mb-1">
                            <strong>Method: </strong>
                            {{ strtoupper($order->payment->method) }}
                        </p>

                        <p class="mb-1">
                            <strong>Status: </strong>
                            <span class="badge bg-{{ $order->payment->status == 'success' ? 'success' : 'danger' }}">
                                {{ ucfirst($order->payment->status) }}
                            </span>
                        </p>
                    @endif

                    @if ($order->coupon)
                        <hr>
                        <h6 class="text-primary fw-semibold mb-2">Coupon Applied</h6>

                        <p class="mb-1">
                            <strong>Code:</strong>
                            <span class="badge bg-dark">
                                {{ $order->coupon->code }}
                            </span>
                        </p>

                        <p class="mb-1">
                            <strong>Type:</strong>
                            {{ ucfirst($order->coupon->discount_type) }}
                        </p>

                        <p class="mb-0">
                            <strong>Value:</strong>
                            @if ($order->coupon->discount_type == 'percentage')
                                {{ $order->coupon->discount_value }}%
                            @else
                                ₹ {{ number_format($order->coupon->discount_value, 2) }}
                            @endif
                        </p>
                    @endif

                </div>
            </div>
        </div>

    </div>

    <div class="mt-4">
        <h6 class="text-primary fw-semibold mb-3">Order Items</h6>

        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                        <th>User Rating</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($order->items as $item)
                        @php
                            $product = $item->product;
                            $category = optional($product?->category)->name ?? 'N/A';
                            $review = $product
                                ? $product->storeReviews->where('user_id', $order->user_id)->first()
                                : null;
                        @endphp

                        <tr>

                            <td>
                                <span class="badge bg-info">
                                    {{ $category }}
                                </span>
                            </td>

                            <td>
                                <small class="text-muted">
                                    SKU: {{ $product->code ?? 'N/A' }}
                                </small><br>
                                <strong>{{ $product->name ?? 'Product Deleted' }}</strong>
                            </td>

                            <td class="text-center">
                                {{ $item->quantity }}
                            </td>

                            <td>
                                ₹ {{ number_format($item->price ?? 0, 2) }}
                            </td>

                            <td class="fw-semibold">
                                ₹ {{ number_format($item->total ?? 0, 2) }}
                            </td>

                            <td>
                                @if ($review)
                                    <span class="badge bg-warning text-dark">
                                        ⭐ {{ $review->rating }}/5
                                    </span>
                                    @if ($review->review)
                                        <div class="small text-muted mt-1">
                                            "{{ $review->review }}"
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted small">
                                        No review
                                    </span>
                                @endif
                            </td>

                        </tr>
                    @endforeach

                </tbody>

            </table>
        </div>
    </div>

</div>

<div class="modal-footer border-0">
    <button class="btn btn-secondary" data-bs-dismiss="modal">
        Close
    </button>
</div>
