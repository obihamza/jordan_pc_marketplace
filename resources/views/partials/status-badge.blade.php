{{-- Param: status --}}
@php
    $badgeColors = [
        'pending' => 'warning', 'confirmed' => 'info', 'processing' => 'primary',
        'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger',
        'available' => 'success', 'sold' => 'secondary', 'hidden' => 'dark', 'reserved' => 'info',
        'active' => 'success', 'abandoned' => 'secondary',
        'authorized' => 'info', 'paid' => 'success', 'failed' => 'danger', 'refunded' => 'secondary',
    ];
@endphp
<span class="badge text-bg-{{ $badgeColors[$status ?? ''] ?? 'secondary' }}">{{ $status ? ucfirst($status) : '-' }}</span>
