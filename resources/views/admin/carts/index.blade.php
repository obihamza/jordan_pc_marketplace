@extends('layouts.admin')

@section('title', 'Carts')

@section('content')
@include('partials.page-header', ['title' => 'Carts'])

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>User</th><th>Items</th><th>Total</th><th>Status</th><th>Updated</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($carts as $cart)
                    <tr>
                        <td>{{ $cart->user?->name }}</td>
                        <td>
                            {{ $cart->items->sum('quantity') }}
                            <span class="small text-muted">({{ $cart->items->pluck('product.name')->filter()->implode(', ') }})</span>
                        </td>
                        <td>{{ number_format($cart->total, 2) }} JOD</td>
                        <td>@include('partials.status-badge', ['status' => $cart->status])</td>
                        <td>{{ $cart->updated_at?->diffForHumans() }}</td>
                        <td class="text-end">@include('partials.delete-button', ['action' => route('carts.destroy', $cart)])</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No carts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $carts->links() }}</div>
@endsection
