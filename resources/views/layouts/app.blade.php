<!DOCTYPE html>
{{-- The whole project uses Bootstrap's dark theme --}}
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') - {{ config('app.name', 'JO-Marketplace') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/jo-logo-mark.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    {{-- JO-Marketplace orange + black theme (must come after Bootstrap) --}}
    <link href="{{ asset('css/jo-marketplace.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    @include('partials.nav')

    {{-- Full-width section shown before the page content (the landing page hero uses it) --}}
    @yield('hero')

    <main class="container py-4 flex-grow-1">
        @include('partials.alerts')

        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Floating chat button (bottom right) on every page --}}
    @include('partials.chatbot')

    <script>
        // Let the hero fill exactly the first screen: viewport height minus the navbar height.
        (function () {
            function setNavHeight() {
                var nav = document.querySelector('.navbar');
                if (nav) {
                    document.documentElement.style.setProperty('--jo-nav-h', nav.offsetHeight + 'px');
                }
            }
            setNavHeight();
            window.addEventListener('load', setNavHeight);
            window.addEventListener('resize', setNavHeight);
        })();
    </script>
    @stack('scripts')
</body>
</html>
