<!DOCTYPE html>
{{-- Light storefront (white + orange accents, black footer) --}}
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Home') - {{ config('app.name', 'JO-Marketplace') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/jo-logo-mark.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    {{-- JO-Marketplace theme (must come after Bootstrap) --}}
    <link href="{{ asset('css/jo-marketplace.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

    @include('partials.nav')

    <main class="container py-4 flex-grow-1">
        @include('partials.alerts')

        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Floating chat button (bottom right) on every page --}}
    @include('partials.confirm-modal')
    @include('partials.chatbot')

    @stack('scripts')
</body>
</html>
