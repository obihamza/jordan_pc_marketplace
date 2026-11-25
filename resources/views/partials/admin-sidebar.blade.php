{{-- Admin side bar: lists of everything the admin manages + a link back to the website. --}}
@php
    $groups = [
        'Overview' => [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard', 'bi-speedometer2'],
        ],
        'Marketplace' => [
            ['Users', 'users.index', 'users.*', 'bi-people'],
            ['Sellers', 'sellers.index', 'sellers.*', 'bi-shop'],
            ['Categories', 'categories.index', 'categories.*', 'bi-tags'],
            ['Products', 'products.index', 'products.*', 'bi-box-seam'],
            ['Product images', 'product-images.index', 'product-images.*', 'bi-images'],
        ],
        'Sales' => [
            ['Orders', 'orders.index', 'orders.*', 'bi-receipt'],
            ['Order items', 'order-items.index', 'order-items.*', 'bi-list-check'],
            ['Payments', 'payments.index', 'payments.*', 'bi-credit-card'],
            ['Coupons', 'coupons.index', 'coupons.*', 'bi-ticket-perforated'],
            ['Reviews', 'reviews.index', 'reviews.*', 'bi-star'],
        ],
        'Other' => [
            ['Carts', 'carts.index', 'carts.*', 'bi-cart3'],
            ['Addresses', 'addresses.index', 'addresses.*', 'bi-geo-alt'],
        ],
    ];
    $pendingSellers = \App\Models\Seller::where('verification_status', 'pending')->count();
@endphp

<nav class="jo-side-nav" aria-label="Admin menu">
    @foreach($groups as $title => $links)
        <div class="jo-side-title">{{ $title }}</div>
        <ul class="nav flex-column mb-3">
            @foreach($links as [$label, $route, $pattern, $icon])
                <li class="nav-item">
                    <a class="jo-side-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}"><i class="bi {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>@if($route === 'sellers.index' && $pendingSellers)<span class="badge rounded-pill text-bg-warning ms-auto" title="Applications waiting for approval">{{ $pendingSellers }}</span>@endif</a>
                </li>
            @endforeach
        </ul>
    @endforeach

    <div class="jo-side-title">Website</div>
    <ul class="nav flex-column mb-3">
        <li class="nav-item">
            <a class="jo-side-link jo-side-view" href="{{ route('home') }}">
                <i class="bi bi-globe2" aria-hidden="true"></i>
                View website
            </a>
        </li>
    </ul>
</nav>
