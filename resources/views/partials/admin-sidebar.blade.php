@php
    $groups = [
        'Overview' => [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard', 'bi-speedometer2'],
            ['Assistant', 'assistant', 'assistant', 'bi-chat-dots'],
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
    $pendingSellers = $pendingSellers ?? 0;
    $pendingReviews = $pendingReviews ?? 0;
@endphp

<nav class="jo-side-nav" aria-label="Admin menu">
    @if($pendingSellers)
        <a href="{{ route('sellers.index') }}" class="jo-side-alert">
            <i class="bi bi-person-badge-fill fs-4" aria-hidden="true"></i>
            <span>
                <strong>{{ $pendingSellers }} seller {{ Str::plural('application', $pendingSellers) }}</strong>
                <small class="d-block">waiting for your approval</small>
            </span>
            <i class="bi bi-chevron-right ms-auto" aria-hidden="true"></i>
        </a>
    @endif

    @if($pendingReviews)
        <a href="{{ route('reviews.index') }}" class="jo-side-alert">
            <i class="bi bi-star-fill fs-4" aria-hidden="true"></i>
            <span>
                <strong>{{ $pendingReviews }} {{ Str::plural('review', $pendingReviews) }}</strong>
                <small class="d-block">waiting for your approval</small>
            </span>
            <i class="bi bi-chevron-right ms-auto" aria-hidden="true"></i>
        </a>
    @endif

    @foreach($groups as $title => $links)
        <div class="jo-side-title">{{ $title }}</div>
        <ul class="nav flex-column mb-3">
            @foreach($links as [$label, $route, $pattern, $icon])
                <li class="nav-item">
                    <a class="jo-side-link {{ request()->routeIs($pattern) ? 'active' : '' }}" href="{{ route($route) }}">
                        <i class="bi {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                        @php
                            $badge = match ($route) {
                                'reviews.index' => $pendingReviews,
                                'sellers.index' => $pendingSellers,
                                default => 0,
                            };
@endphp
                        @if($badge)
                            <span class="badge rounded-pill text-bg-warning ms-auto" title="Waiting for your approval">{{ $badge }}</span>
                        @endif
                    </a>
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
