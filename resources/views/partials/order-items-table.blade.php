<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Product</th>
                    @if($showSeller ?? false)<th>Seller</th>@endif
                    <th>Qty</th>
                    <th>Unit price</th>
                    <th>Subtotal</th>
                    <th>Status</th>
                    @if($canUpdate ?? false)<th>Change status</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td><a href="{{ route('orders.show', $item->order) }}" class="text-decoration-none">{{ $item->order->order_number }}</a></td>
                        <td>{{ $item->product_name }}</td>
                        @if($showSeller ?? false)<td>{{ $item->seller?->official_name }}</td>@endif
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->unit_price, 2) }} JOD</td>
                        <td>{{ number_format($item->subtotal, 2) }} JOD</td>
                        <td>@include('partials.status-badge', ['status' => $item->item_status])</td>
                        @if($canUpdate ?? false)
                            <td>
                                <form action="{{ route('order-items.update', $item) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="item_status" class="form-select form-select-sm" style="width: 140px;">
                                        @foreach(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'] as $s)
                                            <option value="{{ $s }}" @selected($item->item_status === $s)>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-primary">Save</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No order items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $items->links() }}</div>
