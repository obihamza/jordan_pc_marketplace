@php
    $me = auth()->user();
    $isAdmin = $me?->isAdmin() ?? false;
    $isSeller = $me?->isSeller() ?? false;
    $roleLabel = $isAdmin ? 'Admin' : ($isSeller ? 'Seller' : 'Buyer');
@endphp

<nav class="navbar navbar-expand-xl navbar-dark bg-dark sticky-top">
    <div class="container-fluid px-3 px-xl-4">

        @include('partials.brand')

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            {{-- ============ Centre: main links ============ --}}
            <ul class="navbar-nav mx-xl-auto align-items-xl-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('sellers.*') ? 'active' : '' }}" href="{{ route('sellers.index') }}">Sellers</a>
                </li>

                {{-- Role menus --}}
                @if($isAdmin)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Manage</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('users.index') }}">Users</a></li>
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}">Orders</a></li>
                            <li><a class="dropdown-item" href="{{ route('order-items.index') }}">Order items</a></li>
                            <li><a class="dropdown-item" href="{{ route('payments.index') }}">Payments</a></li>
                            <li><a class="dropdown-item" href="{{ route('coupons.index') }}">Coupons</a></li>
                            <li><a class="dropdown-item" href="{{ route('reviews.index') }}">Reviews</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('product-images.index') }}">Product images</a></li>
                            <li><a class="dropdown-item" href="{{ route('carts.index') }}">Carts</a></li>
                            <li><a class="dropdown-item" href="{{ route('addresses.index') }}">Addresses</a></li>
                        </ul>
                    </li>
                @elseif($isSeller)
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Selling</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('products.create') }}">List a product</a></li>
                            <li><a class="dropdown-item" href="{{ route('product-images.index') }}">My product images</a></li>
                            <li><a class="dropdown-item" href="{{ route('order-items.index') }}">Order items</a></li>
                        </ul>
                    </li>
                @endif
            </ul>

            {{-- ============ Search bar ============ --}}
            <form action="{{ route('products.index') }}" method="GET" role="search"
                  class="d-flex jo-nav-search my-3 my-xl-0 me-xl-3">
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                       placeholder="Search products..." aria-label="Search products">
                <button type="submit" class="btn btn-sm btn-primary ms-1" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                    </svg>
                </button>
            </form>

            {{-- ============ Right: account ============ --}}
            <ul class="navbar-nav align-items-xl-center">
                @guest
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light me-2 my-1">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="btn btn-sm btn-primary my-1">Register</a>
                    </li>
                @else
                    @unless($isAdmin)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('carts.*') ? 'active' : '' }}" href="{{ route('carts.index') }}">Cart</a>
                        </li>
                    @endunless

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $me->name }} <span class="badge text-bg-primary">{{ $roleLabel }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('users.show', $me) }}">My profile</a></li>

                            @unless($isAdmin)
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">My orders</a></li>
                                <li><a class="dropdown-item" href="{{ route('payments.index') }}">My payments</a></li>
                                <li><a class="dropdown-item" href="{{ route('reviews.index') }}">My reviews</a></li>
                                <li><a class="dropdown-item" href="{{ route('addresses.index') }}">My addresses</a></li>
                            @endunless

                            @unless($isAdmin || $isSeller)
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-orange" href="{{ route('sellers.create') }}">Become a seller</a></li>
                            @endunless

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
