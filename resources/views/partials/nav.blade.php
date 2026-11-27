@php
    $me = auth()->user();
    $isAdmin = $me?->isAdmin() ?? false;
    $isSeller = $me?->isSeller() ?? false;
    $roleLabel = $isAdmin ? 'Admin' : ($isSeller ? 'Seller' : 'Buyer');
    $navCategories = $navCategories ?? collect();
@endphp

{{-- Header: logo + search + account on top, a row of pills (pages and categories) underneath --}}
<header class="jo-site-header sticky-top">
    <div class="container py-2">
        <div class="d-flex align-items-center gap-3">

            @include('partials.brand')

            {{-- Search bar --}}
            <form action="{{ route('products.index') }}" method="GET" role="search" class="jo-search flex-grow-1 d-none d-md-flex">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search for products..." aria-label="Search products">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <div class="d-flex align-items-center gap-2 ms-auto ms-md-0">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                @else
                    @unless($isAdmin)
                        <a class="jo-head-icon {{ request()->routeIs('carts.*') ? 'active' : '' }}" href="{{ route('carts.index') }}" title="Cart" aria-label="Cart">
                            <i class="bi bi-cart3" aria-hidden="true"></i>
                        </a>
                    @endunless

                    <div class="dropdown">
                        <a class="jo-user dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="jo-avatar">@if($me->avatarUrl())<img src="{{ $me->avatarUrl() }}" alt="">@else{{ mb_strtoupper(mb_substr($me->name, 0, 1)) }}@endif</span>
                            <span class="d-none d-lg-block lh-sm text-start">
                                <span class="d-block fw-semibold">{{ $me->name }}</span>
                                <span class="d-block small text-muted">{{ $roleLabel }}</span>
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($isAdmin)
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin dashboard</a></li>
                            @elseif($isSeller)
                                <li><a class="dropdown-item" href="{{ route('seller.dashboard') }}">Seller dashboard</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('users.show', $me) }}">My profile</a></li>

                            @unless($isAdmin)
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">My orders</a></li>
                                <li><a class="dropdown-item" href="{{ route('payments.index') }}">My payments</a></li>
                                <li><a class="dropdown-item" href="{{ route('reviews.index') }}">My reviews</a></li>
                                <li><a class="dropdown-item" href="{{ route('addresses.index') }}">My addresses</a></li>
                            @endunless

                            @unless($isAdmin || $isSeller)
                                <li><hr class="dropdown-divider"></li>
                                @if($me->sellerStatus())
                                    <li><a class="dropdown-item text-orange" href="{{ route('sellers.show', $me->seller) }}">Seller application ({{ $me->sellerStatus() }})</a></li>
                                @else
                                    <li><a class="dropdown-item text-orange" href="{{ route('sellers.create') }}">Become a seller</a></li>
                                @endif
                            @endunless

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endguest
            </div>
        </div>

        {{-- Search on small screens --}}
        <form action="{{ route('products.index') }}" method="GET" role="search" class="jo-search d-flex d-md-none mt-2">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search for products..." aria-label="Search products">
            <button type="submit" class="btn btn-primary">Go</button>
        </form>
    </div>

    {{-- Pill bar: pages, then categories (scrolls sideways on small screens) --}}
    <div class="jo-pillbar">
        <div class="container">
            <nav class="d-flex align-items-center gap-2" aria-label="Main">
                <a class="jo-pill-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                <a class="jo-pill-link {{ request()->routeIs('products.*') && ! request('category_id') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a>
                <a class="jo-pill-link {{ request()->routeIs('sellers.*') ? 'active' : '' }}" href="{{ route('sellers.index') }}">Sellers</a>

                @if($navCategories->isNotEmpty())
                    <span class="jo-pill-sep" aria-hidden="true"></span>
                    @foreach($navCategories as $navCategory)
                        <a class="jo-pill-link {{ request('category_id') == $navCategory->id ? 'active' : '' }}"
                           href="{{ route('products.index', ['category_id' => $navCategory->id]) }}">{{ $navCategory->name }}</a>
                    @endforeach
                    <a class="jo-pill-link jo-pill-more {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">All categories <i class="bi bi-arrow-right"></i></a>
                @endif
            </nav>
        </div>
    </div>
</header>
