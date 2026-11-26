<!DOCTYPE html>
{{-- Dashboard shell shared by the admin and seller areas (Car Rent style):
     full-height dark side bar with the logo and Logout, light header with the bell, content on the right.
     Child layouts (layouts.admin / layouts.seller) fill the `sidebar` section. --}}
@php
    $panelUser = auth()->user();
    $panelIsAdmin = $panelUser->isAdmin();
    $panelHome = $panelIsAdmin ? route('admin.dashboard') : route('seller.dashboard');
    $panelRole = $panelIsAdmin ? 'Admin' : 'Seller';
    $panelInitial = mb_strtoupper(mb_substr($panelUser->name, 0, 1));
@endphp
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ $panelRole }} - {{ config('app.name', 'JO-Marketplace') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/jo-logo-mark.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <link href="{{ asset('css/jo-marketplace.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="jo-dash-body">

    {{-- ============ Side bar (large screens: fixed; small screens: slides in from the left) ============ --}}
    <aside class="jo-sidebar d-none d-lg-block" data-bs-theme="dark" aria-label="{{ $panelRole }} menu">
        <div class="jo-sidebar-inner">
            @include('partials.dashboard-brand')
            <div class="jo-sidebar-scroll">@yield('sidebar')</div>
            @include('partials.dashboard-logout')
        </div>
    </aside>

    <div class="offcanvas offcanvas-start jo-offcanvas d-lg-none" tabindex="-1" id="adminSide" data-bs-theme="dark" aria-label="{{ $panelRole }} menu">
        <div class="jo-sidebar-inner">
            @include('partials.dashboard-brand', ['closeable' => true])
            <div class="jo-sidebar-scroll">@yield('sidebar')</div>
            @include('partials.dashboard-logout')
        </div>
    </div>

    {{-- ============ Main ============ --}}
    <div class="jo-dash-main">
        <header class="jo-dash-top">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary d-lg-none" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#adminSide" aria-controls="adminSide" aria-label="Open menu">
                    <i class="bi bi-list fs-5" aria-hidden="true"></i>
                </button>
                <div class="lh-sm">
                    <div class="fw-semibold">Hello, {{ \Illuminate\Support\Str::before($panelUser->name, ' ') }}</div>
                    <div class="small text-muted">{{ now()->format('D, j M Y') }}</div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 gap-sm-3">
                {{-- Notification bell: new events (red dot = something unread) --}}
                <div class="dropdown">
                    <button class="jo-bell" type="button" id="joBell"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell" aria-hidden="true"></i>
                        <span class="jo-bell-dot" id="joBellCount" style="display:none"></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0 jo-bell-menu" aria-labelledby="joBell">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                            <strong>Notifications</strong>
                            <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none">Mark all as read</button>
                            </form>
                        </div>
                        <div id="joBellList" class="jo-bell-list"></div>
                    </div>
                </div>

                {{-- Account --}}
                <div class="dropdown">
                    <a class="jo-user dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="jo-avatar">{{ $panelInitial }}</span>
                        <span class="d-none d-sm-block lh-sm text-start">
                            <span class="d-block fw-semibold">{{ $panelUser->name }}</span>
                            <span class="d-block small text-muted">{{ $panelRole }}</span>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('users.show', $panelUser) }}"><i class="bi bi-person-circle me-2"></i>My profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="jo-dash-content">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>

    @include('partials.product-create-modal')
    @include('partials.category-create-modal')
    @include('partials.confirm-modal')
    @include('partials.chatbot')

    <script>
        // Bell: draws the list and re-checks for new events every 30 seconds.
        (function () {
            var icons = { seller_application: 'bi-person-badge', order: 'bi-bag-check', user: 'bi-person-plus', seller_approved: 'bi-patch-check', review: 'bi-star' };
            var feedUrl = @json(route('notifications.feed'));
            var count = document.getElementById('joBellCount');
            var list = document.getElementById('joBellList');

            function render(feed) {
                if (feed.unread > 0) {
                    count.style.display = '';
                    count.title = feed.unread + ' unread';
                } else {
                    count.style.display = 'none';
                }

                list.innerHTML = '';
                if (! feed.items.length) {
                    var empty = document.createElement('div');
                    empty.className = 'text-muted text-center py-4 small';
                    empty.textContent = 'No notifications yet.';
                    list.appendChild(empty);
                    return;
                }

                feed.items.forEach(function (n) {
                    var a = document.createElement('a');
                    a.href = n.url;
                    a.className = 'jo-bell-item' + (n.read ? '' : ' unread');

                    var i = document.createElement('i');
                    i.className = 'bi ' + (icons[n.kind] || 'bi-info-circle');

                    var box = document.createElement('div');
                    var t = document.createElement('div'); t.className = 'fw-semibold small'; t.textContent = n.title;
                    var m = document.createElement('div'); m.className = 'small'; m.textContent = n.message;
                    var w = document.createElement('div'); w.className = 'text-muted jo-bell-time'; w.textContent = n.time;
                    box.appendChild(t); box.appendChild(m); box.appendChild(w);

                    a.appendChild(i); a.appendChild(box);
                    list.appendChild(a);   // textContent only: never inject HTML
                });
            }

            render(@json($bellFeed));

            setInterval(function () {
                if (document.hidden) { return; }
                fetch(feedUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (feed) { if (feed) { render(feed); } })
                    .catch(function () {});
            }, 30000);
        })();
    </script>
    @stack('scripts')
</body>
</html>
