@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-0">Dashboard</h1>
        <div class="text-muted small">A quick look at the whole website. <span id="dashUpdated"></span></div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="dashRefresh"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
</div>

<div class="row g-3 mb-3" id="dashCards" aria-live="polite">
    @for($i = 0; $i < 8; $i++)
        <div class="col-6 col-md-4 col-xl-3">
            <div class="card jo-stat"><div class="card-body placeholder-glow">
                <span class="placeholder col-6 mb-2"></span><span class="placeholder col-8 placeholder-lg d-block mb-2"></span><span class="placeholder col-10"></span>
            </div></div>
        </div>
    @endfor
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold">Revenue and orders, last 6 months</div>
            <div class="card-body"><div class="jo-chart-box" data-chart-box><canvas id="chartSales" role="img" aria-label="Revenue and orders per month"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Orders by status</div>
            <div class="card-body"><div class="jo-chart-box" data-chart-box><canvas id="chartStatus" role="img" aria-label="Orders by status"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">New users per month</div>
            <div class="card-body"><div class="jo-chart-box" data-chart-box><canvas id="chartUsers" role="img" aria-label="New users per month"></canvas></div></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Products per category (top 6)</div>
            <div class="card-body"><div class="jo-chart-box" data-chart-box><canvas id="chartCategories" role="img" aria-label="Products per category"></canvas></div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Recent orders</span>
                <a href="{{ route('orders.index') }}" class="btn btn-sm btn-outline-secondary">All orders</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Order #</th><th>Buyer</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody id="dashOrders"><tr><td colspan="4" class="text-center text-muted py-4">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Best selling products</div>
            <ul class="list-group list-group-flush" id="dashTop"><li class="list-group-item text-muted">Loading...</li></ul>
        </div>

        <div class="card">
            <div class="card-header fw-semibold">Low stock (5 or fewer)</div>
            <ul class="list-group list-group-flush" id="dashLow"><li class="list-group-item text-muted">Loading...</li></ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        var api = {
            stats:  @json(route('api.admin.dashboard.stats')),
            charts: @json(route('api.admin.dashboard.charts')),
            orders: @json(route('api.admin.dashboard.recent-orders')),
            top:    @json(route('api.admin.dashboard.top-products')),
            low:    @json(route('api.admin.dashboard.low-stock'))
        };

        var orange = '#ff6a00', orangeSoft = 'rgba(255,106,0,.35)';
        var badgeColors = { pending: 'warning', confirmed: 'info', processing: 'primary', shipped: 'primary', delivered: 'success', cancelled: 'danger' };

        function get(url) {
            return fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (res) {
                    if (! res.ok) { throw new Error('HTTP ' + res.status); }
                    return res.json();
                })
                .then(function (json) { return json.data; });
        }

        function el(tag, cls, text) {
            var e = document.createElement(tag);
            if (cls) { e.className = cls; }
            if (text !== undefined && text !== null) { e.textContent = text; }
            return e;
        }

        function money(n) { return Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
        function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : '-'; }

        function failed(container, retry, asRow) {
            container.textContent = '';
            var msg = el('span', 'text-danger', 'Could not load this. ');
            var btn = el('button', 'btn btn-link btn-sm p-0 align-baseline', 'Try again');
            btn.type = 'button';
            btn.addEventListener('click', retry);
            msg.appendChild(btn);

            if (asRow === 'row') {
                var tr = el('tr'), td = el('td', 'text-center py-4'); td.colSpan = 4; td.appendChild(msg); tr.appendChild(td); container.appendChild(tr);
            } else if (asRow === 'li') {
                var li = el('li', 'list-group-item'); li.appendChild(msg); container.appendChild(li);
            } else {
                var col = el('div', 'col-12'); col.appendChild(msg); container.appendChild(col);
            }
        }

        function loadStats() {
            var box = document.getElementById('dashCards');

            return get(api.stats).then(function (s) {
                var cards = [
                    ['Revenue (JOD)', money(s.revenue), 'Excludes cancelled orders', 'JD'],
                    ['Orders', s.orders, s.pending_orders + ' pending', '#'],
                    ['Products', s.products, s.low_stock + ' low on stock', 'P'],
                    ['Buyers & sellers', s.users, s.sellers + ' approved sellers', 'U'],
                    ['Seller applications', s.pending_sellers, 'Waiting for approval', 'ID'],
                    ['Categories', s.categories, 'Product groups', 'C'],
                    ['Pending payments', s.pending_payments, 'Cash on delivery', '$'],
                    ['Reviews', s.reviews, s.pending_reviews + ' waiting for approval · avg ' + (s.avg_rating || '-') + ' / 5', '★']
                ];

                box.textContent = '';
                cards.forEach(function (c) {
                    var col = el('div', 'col-6 col-md-4 col-xl-3');
                    var card = el('div', 'card jo-stat');
                    var body = el('div', 'card-body d-flex justify-content-between align-items-start gap-2');
                    var text = el('div');
                    text.appendChild(el('div', 'jo-stat-label', c[0]));
                    text.appendChild(el('div', 'jo-stat-value', c[1]));
                    text.appendChild(el('div', 'small text-muted', c[2]));
                    var icon = el('div', 'jo-stat-icon', c[3]);
                    icon.setAttribute('aria-hidden', 'true');
                    body.appendChild(text); body.appendChild(icon); card.appendChild(body); col.appendChild(card); box.appendChild(col);
                });
            }).catch(function () { failed(box, loadStats); });
        }

        function makeChart(id, config) {
            var canvas = document.getElementById(id);
            var old = Chart.getChart(canvas);
            if (old) { old.destroy(); }
            new Chart(canvas, config);
        }

        function loadCharts() {
            var boxes = document.querySelectorAll('[data-chart-box]');

            return get(api.charts).then(function (d) {
                if (typeof Chart === 'undefined') { return; }

                Chart.defaults.color = '#5b5b63';
                Chart.defaults.borderColor = 'rgba(0,0,0,.07)';
                Chart.defaults.maintainAspectRatio = false;

                makeChart('chartSales', {
                    data: {
                        labels: d.months,
                        datasets: [
                            { type: 'bar', label: 'Revenue (JOD)', data: d.revenue, backgroundColor: orangeSoft, borderColor: orange, borderWidth: 1, yAxisID: 'y' },
                            { type: 'line', label: 'Orders', data: d.orders, borderColor: '#111111', backgroundColor: '#111111', tension: .3, yAxisID: 'y1' }
                        ]
                    },
                    options: {
                        scales: {
                            y:  { beginAtZero: true, title: { display: true, text: 'JOD' } },
                            y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { precision: 0 }, title: { display: true, text: 'Orders' } }
                        }
                    }
                });

                makeChart('chartStatus', {
                    type: 'doughnut',
                    data: {
                        labels: d.statuses.map(function (s) { return s.label; }),
                        datasets: [{
                            data: d.statuses.map(function (s) { return s.value; }),
                            backgroundColor: ['#6c757d', '#0dcaf0', '#6f42c1', '#ffc107', '#198754', '#dc3545'],
                            borderColor: '#ffffff'
                        }]
                    },
                    options: { plugins: { legend: { position: 'bottom' } } }
                });

                makeChart('chartUsers', {
                    type: 'line',
                    data: { labels: d.months, datasets: [{ label: 'New users', data: d.users, borderColor: orange, backgroundColor: orangeSoft, fill: true, tension: .3 }] },
                    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                });

                makeChart('chartCategories', {
                    type: 'bar',
                    data: { labels: d.categories.labels, datasets: [{ label: 'Products', data: d.categories.values, backgroundColor: orange }] },
                    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
                });
            }).catch(function () {
                boxes.forEach(function (b) { b.textContent = 'Could not load the chart.'; b.classList.add('text-danger', 'small'); });
            });
        }

        function loadOrders() {
            var body = document.getElementById('dashOrders');

            return get(api.orders).then(function (rows) {
                body.textContent = '';
                if (! rows.length) {
                    var tr = el('tr'), td = el('td', 'text-center text-muted py-4', 'No orders yet.'); td.colSpan = 4; tr.appendChild(td); body.appendChild(tr);
                    return;
                }
                rows.forEach(function (o) {
                    var tr = el('tr');
                    var a = el('a', 'text-decoration-none fw-semibold', o.order_number); a.href = o.url;
                    var c1 = el('td'); c1.appendChild(a);
                    var c4 = el('td'); c4.appendChild(el('span', 'badge text-bg-' + (badgeColors[o.status] || 'secondary'), cap(o.status)));
                    tr.appendChild(c1); tr.appendChild(el('td', '', o.buyer || '-')); tr.appendChild(el('td', '', money(o.total) + ' JOD')); tr.appendChild(c4);
                    body.appendChild(tr);
                });
            }).catch(function () { failed(body, loadOrders, 'row'); });
        }

        function loadList(id, url, empty, badge, retry) {
            var ul = document.getElementById(id);

            return get(url).then(function (rows) {
                ul.textContent = '';
                if (! rows.length) { ul.appendChild(el('li', 'list-group-item text-muted', empty)); return; }
                rows.forEach(function (p) {
                    var li = el('li', 'list-group-item d-flex justify-content-between align-items-center');
                    var a = el('a', 'text-decoration-none', p.name); a.href = p.url;
                    li.appendChild(a);
                    var b = badge(p);
                    li.appendChild(el('span', 'badge ' + b[0], b[1]));
                    ul.appendChild(li);
                });
            }).catch(function () { failed(ul, retry, 'li'); });
        }

        function loadTop() {
            return loadList('dashTop', api.top, 'No products yet.', function (p) { return ['text-bg-secondary', p.sold + ' sold']; }, loadTop);
        }

        function loadLow() {
            return loadList('dashLow', api.low, 'Everything is well stocked.', function (p) {
                return [p.stock === 0 ? 'text-bg-danger' : 'text-bg-warning', p.stock + ' left'];
            }, loadLow);
        }

        var refreshBtn = document.getElementById('dashRefresh');

        function loadAll() {
            refreshBtn.disabled = true;

            Promise.all([loadStats(), loadCharts(), loadOrders(), loadTop(), loadLow()]).then(function () {
                refreshBtn.disabled = false;
                document.getElementById('dashUpdated').textContent = 'Updated ' + new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + '.';
            });
        }

        refreshBtn.addEventListener('click', loadAll);
        loadAll();
    })();
</script>
@endpush
