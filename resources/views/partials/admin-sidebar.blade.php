{{-- Admin side bar: lists of everything the admin manages + a link back to the website. --}}
@php
    $groups = [
        'Overview' => [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard', 'M0 0h6v6H0zm8 0h8v6H8zM0 8h8v8H0zm10 0h6v8h-6z'],
        ],
        'Marketplace' => [
            ['Users', 'users.index', 'users.*', ''],
            ['Sellers', 'sellers.index', 'sellers.*', ''],
            ['Categories', 'categories.index', 'categories.*', ''],
            ['Products', 'products.index', 'products.*', ''],
            ['Product images', 'product-images.index', 'product-images.*', ''],
        ],
        'Sales' => [
            ['Orders', 'orders.index', 'orders.*', ''],
            ['Order items', 'order-items.index', 'order-items.*', ''],
            ['Payments', 'payments.index', 'payments.*', ''],
            ['Coupons', 'coupons.index', 'coupons.*', ''],
            ['Reviews', 'reviews.index', 'reviews.*', ''],
        ],
        'Other' => [
            ['Carts', 'carts.index', 'carts.*', ''],
            ['Addresses', 'addresses.index', 'addresses.*', ''],
        ],
    ];
@endphp

<nav class="jo-side-nav" aria-label="Admin menu">
    @foreach($groups as $title => $links)
        <div class="jo-side-title">{{ $title }}</div>
        <ul class="nav flex-column mb-3">
            @foreach($links as [$label, $route, $pattern])
                <li class="nav-item">
                    <a class="jo-side-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">{{ $label }}</a>
                </li>
            @endforeach
        </ul>
    @endforeach

    <div class="jo-side-title">Website</div>
    <ul class="nav flex-column mb-3">
        <li class="nav-item">
            <a class="jo-side-link jo-side-view" href="{{ route('home') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5z"/></svg>
                View website
            </a>
        </li>
    </ul>
</nav>
