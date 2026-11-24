{{-- Params: payments (paginator, with order loaded), canUpdate (optional; admin only) --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    @if($canUpdate ?? false)<th>Buyer</th>@endif
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    @if($canUpdate ?? false)<th>Change status</th>@endif
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><a href="{{ route('orders.show', $payment->order) }}" class="text-decoration-none">{{ $payment->order->order_number }}</a></td>
                        @if($canUpdate ?? false)<td>{{ $payment->order->user?->name }}</td>@endif
                        <td>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</td>
                        <td>{{ number_format($payment->amount_jod, 2) }} JOD</td>
                        <td>@include('partials.status-badge', ['status' => $payment->payment_status])</td>
                        @if($canUpdate ?? false)
                            <td>
                                <form action="{{ route('payments.update', $payment) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <select name="payment_status" class="form-select form-select-sm" style="width: 140px;">
                                        @foreach(['pending', 'authorized', 'paid', 'failed', 'refunded'] as $s)
                                            <option value="{{ $s }}" @selected($payment->payment_status === $s)>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-primary">Save</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No payments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $payments->links() }}</div>
