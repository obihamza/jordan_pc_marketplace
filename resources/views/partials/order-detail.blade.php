{{-- Param: order (with user, address, coupon, items.product, payment) --}}
@php
    $me = auth()->user();
    $isMyOrder = $order->user_id == $me->id;
@endphp

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header fw-semibold">Items</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th><th>Qty</th><th>Unit price</th><th>Subtotal</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }} JOD</td>
                                <td>{{ number_format($item->subtotal, 2) }} JOD</td>
                                <td>
                                    @include('partials.status-badge', ['status' => $item->item_status])

                                    {{-- A seller updates the lines that contain their own products --}}
                                    @if($me->isSeller() && $item->seller_id == $me->id)
                                        <form action="{{ route('order-items.update', $item) }}" method="POST" class="d-flex gap-1 mt-1">
                                            @csrf
                                            @method('PUT')
                                            <select name="item_status" class="form-select form-select-sm">
                                                @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $s)
                                                    <option value="{{ $s }}" @selected($item->item_status === $s)>{{ ucfirst($s) }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-primary">Save</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Summary</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between"><span>Subtotal</span><span>{{ number_format($order->subtotal, 2) }} JOD</span></li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Discount @if($order->coupon)<span class="badge text-bg-secondary">{{ $order->coupon->code }}</span>@endif</span>
                    <span>- {{ number_format($order->discount_amount, 2) }} JOD</span>
                </li>
                <li class="list-group-item d-flex justify-content-between"><span>Shipping</span><span>{{ number_format($order->shipping_cost, 2) }} JOD</span></li>
                <li class="list-group-item d-flex justify-content-between fw-bold"><span>Total</span><span>{{ number_format($order->total_amount, 2) }} JOD</span></li>
            </ul>
        </div>

        <div class="card mb-3">
            <div class="card-header fw-semibold">Details</div>
            <div class="card-body">
                <div class="mb-2">Order status: @include('partials.status-badge', ['status' => $order->order_status])</div>
                <div class="mb-2">
                    Payment: Cash on delivery
                    @include('partials.status-badge', ['status' => $order->payment?->payment_status])
                </div>
                @if(! $isMyOrder)
                    <div class="mb-2"><span class="text-muted">Buyer:</span> {{ $order->user?->name }}</div>
                @endif
                <div class="mb-2"><span class="text-muted">Placed:</span> {{ $order->created_at?->format('Y-m-d H:i') }}</div>
                <div><span class="text-muted">Ship to:</span> {{ $order->shipping_address }}</div>
            </div>
        </div>

        {{-- Admin: change status / delete --}}
        @if($me->isAdmin())
            <div class="card mb-3">
                <div class="card-header fw-semibold">Admin</div>
                <div class="card-body">
                    <form action="{{ route('orders.update', $order) }}" method="POST" class="d-flex gap-2 mb-3">
                        @csrf
                        @method('PUT')
                        <select name="order_status" class="form-select">
                            @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $s)
                                <option value="{{ $s }}" @selected($order->order_status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary">Save</button>
                    </form>
                    @include('partials.delete-button', ['icon' => true, 'action' => route('orders.destroy', $order), 'label' => 'Delete order'])
                </div>
            </div>

        {{-- Buyer (or seller as buyer): cancel their own pending order --}}
        @elseif($isMyOrder && $order->order_status === 'pending')
            <form action="{{ route('orders.update', $order) }}" method="POST"
                  onsubmit="return confirm('Cancel this order?')">
                @csrf
                @method('PUT')
                <button class="btn btn-outline-danger w-100">Cancel order</button>
            </form>
        @endif

        <a href="{{ route('orders.index') }}" class="btn btn-link mt-2">Back to orders</a>
    </div>
</div>
