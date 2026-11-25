{{-- Seller side bar: my shop, my shopping (a seller is also a buyer), and a link back to the website. --}}
@php
    $pendingSales = $pendingSales ?? 0;
    $groups = [
        'Overview' => [
            ['Dashboard', 'seller.dashboard', 'seller.dashboard', 'bi-speedometer2'],
        ],
        'My shop' => [
            ['My products', 'products.index', ['products.index', 'products.show', 'products.edit'], 'bi-box-seam'],
            ['Product images', 'product-images.index', 'product-images.*', 'bi-images'],
            ['Sales (order items)', 'order-items.index', 'order-items.*', 'bi-list-check'],
        ],
        'My shopping' => [
            ['Marketplace categories', 'categories.index', 'categories.*', 'bi-tags'],
            ['Cart', 'carts.index', 'carts.*', 'bi-cart3'],
            ['My orders', 'orders.index', 'orders.*', 'bi-receipt'],
            ['My payments', 'payments.index', 'payments.*', 'bi-credit-card'],
            ['My reviews', 'reviews.index', 'reviews.*', 'bi-star'],
            ['My addresses', 'addresses.index', 'addresses.*', 'bi-geo-alt'],
        ],
        'Account' => [
            ['My profile', 'users.show', ['users.*', 'sellers.edit'], 'bi-person-circle'],
        ],
    ];
    $routeFor = function (string $name) {
        $me = auth()->user();
        return match ($name) {
            'users.show' => route('users.show', $me),
            default => route($name),
        };
    };
@endphp

<nav class="jo-side-nav" aria-label="Seller menu">
    {{-- Notice: order lines waiting for the seller --}}
    @if($pendingSales)
        <a href="{{ route('order-items.index') }}" class="jo-side-alert">
            <i class="bi bi-bag-check-fill fs-4" aria-hidden="true"></i>
            <span>
                <strong>{{ $pendingSales }} {{ Str::plural('item', $pendingSales) }} to prepare</strong>
                <small class="d-block">new orders are waiting for you</small>
            </span>
            <i class="bi bi-chevron-right ms-auto" aria-hidden="true"></i>
        </a>
    @endif

    @foreach($groups as $title => $links)
        <div class="jo-side-title">{{ $title }}</div>
        <ul class="nav flex-column mb-3">
            @foreach($links as $link)
                @php [$label, $route, $pattern, $icon] = $link; $modal = $link[4] ?? null; @endphp
                <li class="nav-item">
                    <a class="jo-side-link {{ request()->routeIs(...(array) $pattern) ? 'active' : '' }}" href="{{ $modal ? '#' : $routeFor($route) }}"
                       @if($modal) data-bs-toggle="modal" data-bs-target="{{ $modal }}" role="button" @endif>
                        <i class="bi {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                        @if($route === 'order-items.index' && $pendingSales)<span class="badge rounded-pill text-bg-warning ms-auto" title="Items waiting to be prepared">{{ $pendingSales }}</span>@endif
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
