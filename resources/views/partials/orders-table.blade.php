<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    @if($showBuyer ?? false)<th>Buyer</th>@endif
                    <th>Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><a href="{{ route('orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->order_number }}</a></td>
                        @if($showBuyer ?? false)<td>{{ $order->user?->name }}</td>@endif
                        <td>{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ number_format($order->total_amount, 2) }} JOD</td>
                        <td>@include('partials.status-badge', ['status' => $order->order_status])</td>
                        <td>@include('partials.status-badge', ['status' => $order->payment?->payment_status])</td>
                        <td class="text-end">
                            <a class="jo-icon-btn" href="{{ route('orders.show', $order) }}" title="View" aria-label="View"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
