@extends('layouts.seller')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">My shop</h1>
        <div class="text-muted small">{{ auth()->user()->seller->official_name }} &middot; how your business is doing.</div>
    </div>
    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#productModal"><i class="bi bi-plus-lg"></i> Add a product</button>
</div>

@php
    $cards = [
        ['Revenue (JOD)', number_format($stats['revenue'], 2), 'Cancelled items excluded', 'JD'],
        ['Items sold', $stats['items_sold'], 'In '.$stats['orders'].' '.Str::plural('order', $stats['orders']), '#'],
        ['To prepare', $stats['to_process'], 'Pending, confirmed or processing', '!'],
        ['Products', $stats['products'], $stats['available'].' available', 'P'],
        ['Low stock', $stats['low_stock'], '5 or fewer left', '↓'],
        ['Rating', $stats['avg_rating'] ?: '-', $stats['reviews'].' '.Str::plural('review', $stats['reviews']), '★'],
    ];
@endphp
<div class="row g-3 mb-3">
    @foreach($cards as [$label, $value, $hint, $icon])
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card jo-stat">
                <div class="card-body d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="jo-stat-label">{{ $label }}</div>
                        <div class="jo-stat-value">{{ $value }}</div>
                        <div class="small text-muted">{{ $hint }}</div>
                    </div>
                    <div class="jo-stat-icon" aria-hidden="true">{{ $icon }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold">Revenue and items sold, last 6 months</div>
            <div class="card-body"><div class="jo-chart-box"><canvas id="chartSales" role="img" aria-label="Revenue and items sold per month"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Order items by status</div>
            <div class="card-body"><div class="jo-chart-box"><canvas id="chartStatus" role="img" aria-label="Order items by status"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Best selling products (units)</div>
            <div class="card-body"><div class="jo-chart-box"><canvas id="chartTop" role="img" aria-label="Best selling products"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Customer ratings</div>
            <div class="card-body"><div class="jo-chart-box"><canvas id="chartRatings" role="img" aria-label="Number of reviews per star rating"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Items to prepare</span>
                <a href="{{ route('order-items.index') }}" class="btn btn-sm btn-outline-secondary">All sales</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Order #</th><th>Product</th><th>Qty</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($toProcess as $item)
                            <tr>
                                <td><a href="{{ route('orders.show', $item->order) }}" class="text-decoration-none fw-semibold">{{ $item->order->order_number }}</a></td>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>@include('partials.status-badge', ['status' => $item->item_status])</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Nothing waiting. You are all caught up.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Low stock (5 or fewer)</div>
            <ul class="list-group list-group-flush">
                @forelse($lowStock as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">{{ $product->name }}</a>
                        <span class="d-flex align-items-center gap-2">
                            <span class="badge {{ $product->stock_quantity == 0 ? 'text-bg-danger' : 'text-bg-warning' }}">{{ $product->stock_quantity }} left</span>
                            <a class="jo-icon-btn" href="{{ route('products.edit', $product) }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square"></i></a>
                        </span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Everything is well stocked.</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Latest reviews</span>
                <a href="{{ route('reviews.index') }}" class="btn btn-sm btn-outline-secondary">All</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($latestReviews as $review)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold small">{{ $review->product?->name }}</span>
                            <span class="text-warning small">{{ str_repeat('★', (int) $review->rating) }}<span class="text-muted">{{ str_repeat('★', 5 - (int) $review->rating) }}</span></span>
                        </div>
                        <div class="small text-muted">{{ $review->user?->name }}: {{ Str::limit($review->comment, 90) }}</div>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No reviews yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') { return; }

        var data = @json($charts);
        var orange = '#ff6a00', orangeSoft = 'rgba(255,106,0,.35)', grid = 'rgba(0,0,0,.07)';

        Chart.defaults.color = '#5b5b63';
        Chart.defaults.borderColor = grid;
        Chart.defaults.maintainAspectRatio = false;

        new Chart(document.getElementById('chartSales'), {
            data: {
                labels: data.months,
                datasets: [
                    { type: 'bar', label: 'Revenue (JOD)', data: data.revenue, backgroundColor: orangeSoft, borderColor: orange, borderWidth: 1, yAxisID: 'y' },
                    { type: 'line', label: 'Items sold', data: data.items, borderColor: '#111111', backgroundColor: '#111111', tension: .3, yAxisID: 'y1' }
                ]
            },
            options: {
                scales: {
                    y:  { beginAtZero: true, title: { display: true, text: 'JOD' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 }, title: { display: true, text: 'Items' } }
                }
            }
        });

        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: data.statuses.map(function (s) { return s.label; }),
                datasets: [{
                    data: data.statuses.map(function (s) { return s.value; }),
                    backgroundColor: ['#6c757d', '#0dcaf0', '#6f42c1', '#ffc107', '#198754', '#dc3545'],
                    borderColor: '#ffffff'
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        new Chart(document.getElementById('chartTop'), {
            type: 'bar',
            data: { labels: data.top.labels, datasets: [{ label: 'Units sold', data: data.top.values, backgroundColor: orange }] },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        new Chart(document.getElementById('chartRatings'), {
            type: 'bar',
            data: { labels: ['1 star', '2 stars', '3 stars', '4 stars', '5 stars'], datasets: [{ label: 'Reviews', data: data.ratings, backgroundColor: '#ffc107' }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    })();
</script>
@endpush
