@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Dashboard</h1>
        <div class="text-muted small">A quick look at the whole website.</div>
    </div>
</div>

{{-- ============ Summary cards ============ --}}
@php
    $cards = [
        ['Revenue (JOD)', number_format($stats['revenue'], 2), 'Excludes cancelled orders', 'JD'],
        ['Orders', $stats['orders'], $stats['pending_orders'].' pending', '#'],
        ['Products', $stats['products'], $stats['low_stock'].' low on stock', 'P'],
        ['Buyers & sellers', $stats['users'], $stats['sellers'].' approved sellers', 'U'],
        ['Seller applications', $stats['pending_sellers'], 'Waiting for approval', 'ID'],
        ['Categories', $stats['categories'], 'Product groups', 'C'],
        ['Pending payments', $stats['pending_payments'], 'Cash on delivery', '$'],
        ['Reviews', $stats['reviews'], 'Average '.($stats['avg_rating'] ?: '-').' / 5', '★'],
    ];
@endphp
<div class="row g-3 mb-3">
    @foreach($cards as [$label, $value, $hint, $icon])
        <div class="col-6 col-md-4 col-xl-3">
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

{{-- ============ Charts ============ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold">Revenue and orders, last 6 months</div>
            <div class="card-body"><div class="jo-chart-box"><canvas id="chartSales" role="img" aria-label="Revenue and orders per month"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Orders by status</div>
            <div class="card-body"><div class="jo-chart-box"><canvas id="chartStatus" role="img" aria-label="Orders by status"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">New users per month</div>
            <div class="card-body"><div class="jo-chart-box"><canvas id="chartUsers" role="img" aria-label="New users per month"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Products per category (top 6)</div>
            <div class="card-body"><div class="jo-chart-box"><canvas id="chartCategories" role="img" aria-label="Products per category"></canvas></div></div>
        </div>
    </div>
</div>

{{-- ============ Lists ============ --}}
<div class="row g-3">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Recent orders</span>
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">All orders</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Order #</th><th>Buyer</th><th>Total</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td><a href="{{ route('orders.show', $order) }}" class="text-decoration-none fw-semibold">{{ $order->order_number }}</a></td>
                                <td>{{ $order->user?->name }}</td>
                                <td>{{ number_format($order->total_amount, 2) }} JOD</td>
                                <td>@include('partials.status-badge', ['status' => $order->order_status])</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Best selling products</div>
            <ul class="list-group list-group-flush">
                @forelse($topProducts as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">{{ $product->name }}</a>
                        <span class="badge text-bg-secondary">{{ (int) $product->sold_count }} sold</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No products yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Low stock (5 or fewer)</div>
            <ul class="list-group list-group-flush">
                @forelse($lowStock as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('products.show', $product) }}" class="text-decoration-none">{{ $product->name }}</a>
                        <span class="badge {{ $product->stock_quantity == 0 ? 'text-bg-danger' : 'text-bg-warning' }}">{{ $product->stock_quantity }} left</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Everything is well stocked.</li>
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
        if (typeof Chart === 'undefined') { return; }   // CDN unreachable: the cards and lists still work

        var data = @json($charts);
        var orange = '#ff6a00', orangeSoft = 'rgba(255,106,0,.35)', grey = '#8a8a93', grid = 'rgba(255,255,255,.08)';

        Chart.defaults.color = '#b5b5bd';
        Chart.defaults.borderColor = grid;
        Chart.defaults.maintainAspectRatio = false;

        // Revenue (bars, JOD) + orders (line)
        new Chart(document.getElementById('chartSales'), {
            data: {
                labels: data.months,
                datasets: [
                    { type: 'bar', label: 'Revenue (JOD)', data: data.revenue, backgroundColor: orangeSoft, borderColor: orange, borderWidth: 1, yAxisID: 'y' },
                    { type: 'line', label: 'Orders', data: data.orders, borderColor: '#ffffff', backgroundColor: '#ffffff', tension: .3, yAxisID: 'y1' }
                ]
            },
            options: {
                scales: {
                    y:  { beginAtZero: true, title: { display: true, text: 'JOD' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 }, title: { display: true, text: 'Orders' } }
                }
            }
        });

        // Orders by status
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: data.statuses.map(function (s) { return s.label; }),
                datasets: [{
                    data: data.statuses.map(function (s) { return s.value; }),
                    backgroundColor: ['#6c757d', '#0dcaf0', '#6f42c1', '#ffc107', '#198754', '#dc3545'],
                    borderColor: '#17171a'
                }]
            },
            options: { plugins: { legend: { position: 'bottom' } } }
        });

        // New users
        new Chart(document.getElementById('chartUsers'), {
            type: 'line',
            data: { labels: data.months, datasets: [{ label: 'New users', data: data.users, borderColor: orange, backgroundColor: orangeSoft, fill: true, tension: .3 }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        // Products per category
        new Chart(document.getElementById('chartCategories'), {
            type: 'bar',
            data: { labels: data.categories.labels, datasets: [{ label: 'Products', data: data.categories.values, backgroundColor: orange }] },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    })();
</script>
@endpush
