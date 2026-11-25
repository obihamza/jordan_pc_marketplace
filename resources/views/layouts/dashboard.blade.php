<!DOCTYPE html>
{{-- Dashboard shell shared by the admin and seller areas: top bar (with bell) + side bar + main area.
     Child layouts (layouts.admin / layouts.seller) fill the `sidebar` section. --}}
@php
    $panelUser = auth()->user();
    $panelIsAdmin = $panelUser->isAdmin();
    $panelHome = $panelIsAdmin ? route('admin.dashboard') : route('seller.dashboard');
    $panelRole = $panelIsAdmin ? 'Admin' : 'Seller';
@endphp
<html lang="en" data-bs-theme="dark">
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
<body>

    {{-- Top bar --}}
    <header class="navbar navbar-dark bg-dark sticky-top jo-admin-top px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#adminSide" aria-controls="adminSide" aria-label="Open menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand d-flex align-items-center gap-2 py-0 me-0" href="{{ $panelHome }}">
                <img src="{{ asset('images/jo-logo-mark.svg') }}" alt="" style="height: 30px;">
                <span class="fw-bold">JO-<span class="text-orange">Marketplace</span></span>
                <span class="badge text-bg-primary ms-1">{{ $panelRole }}</span>
            </a>
        </div>

        <div class="d-flex align-items-center gap-2">
            {{-- Notification bell: new events for the admin --}}
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary position-relative jo-bell" type="button" id="joBell"
                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Notifications">
                    <i class="bi bi-bell-fill" aria-hidden="true"></i>
                    <span class="badge rounded-pill text-bg-danger position-absolute top-0 start-100 translate-middle jo-bell-count" id="joBellCount" style="display:none"></span>
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

            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ auth()->user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('users.show', auth()->user()) }}">My profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="jo-admin-shell">
        {{-- Side bar: fixed on large screens, slides in on small ones --}}
        <aside class="offcanvas-lg offcanvas-start jo-admin-side" tabindex="-1" id="adminSide" aria-label="{{ $panelRole }} menu">
            <div class="offcanvas-header d-lg-none">
                <span class="fw-bold">Menu</span>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#adminSide" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-block p-0">
                @yield('sidebar')
            </div>
        </aside>

        <main class="jo-admin-main">
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
                    count.textContent = feed.unread > 9 ? '9+' : feed.unread;
                    count.style.display = '';
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
