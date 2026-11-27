<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Min order</th>
                    <th>Used</th>
                    <th>Valid</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td><a href="{{ route('coupons.show', $coupon) }}" class="fw-semibold text-decoration-none">{{ $coupon->code }}</a></td>
                        <td>
                            @if($coupon->discount_type === 'percent')
                                {{ rtrim(rtrim(number_format($coupon->discount_value, 2), '0'), '.') }}%
                            @else
                                {{ number_format($coupon->discount_value, 2) }} JOD
                            @endif
                        </td>
                        <td>{{ number_format($coupon->min_order_amount, 2) }} JOD</td>
                        <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td class="small">
                            {{ $coupon->starts_at?->format('Y-m-d') }} &rarr; {{ $coupon->ends_at?->format('Y-m-d') ?? 'no end' }}
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $coupon->is_active ? 'success' : 'secondary' }}">{{ $coupon->is_active ? 'Yes' : 'No' }}</span>
                        </td>
                        <td class="text-end text-nowrap">
                            <a class="jo-icon-btn" href="{{ route('coupons.show', $coupon) }}" title="View" aria-label="View"><i class="bi bi-eye"></i></a>
                            <a class="jo-icon-btn" href="{{ route('coupons.edit', $coupon) }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square"></i></a>
                            @include('partials.delete-button', ['icon' => true, 'action' => route('coupons.destroy', $coupon)])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No coupons found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $coupons->links() }}</div>
