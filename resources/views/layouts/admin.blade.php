<!DOCTYPE html>
{{-- Admin layout: top bar + side bar (lists) + main area. Same dark Bootstrap theme as the site. --}}
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Admin - {{ config('app.name', 'JO-Marketplace') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/jo-logo-mark.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <a class="navbar-brand d-flex align-items-center gap-2 py-0 me-0" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('images/jo-logo-mark.svg') }}" alt="" style="height: 30px;">
                <span class="fw-bold">JO-<span class="text-orange">Marketplace</span></span>
                <span class="badge text-bg-primary ms-1">Admin</span>
            </a>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light d-none d-sm-inline-block">View website</a>
            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ auth()->user()->name }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('users.show', auth()->user()) }}">My profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('home') }}">View website</a></li>
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
        <aside class="offcanvas-lg offcanvas-start jo-admin-side" tabindex="-1" id="adminSide" aria-label="Admin menu">
            <div class="offcanvas-header d-lg-none">
                <span class="fw-bold">Menu</span>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#adminSide" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-block p-0">
                @include('partials.admin-sidebar')
            </div>
        </aside>

        <main class="jo-admin-main">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>

    @include('partials.chatbot')
    @stack('scripts')
</body>
</html>
