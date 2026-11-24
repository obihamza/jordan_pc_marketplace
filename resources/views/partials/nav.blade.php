@auth
    @php
        $me = auth()->user();

        if ($me->isAdmin()) {
            $roleLabel = 'Admin';
            $links = [
                'Users' => 'users.index',
                'Sellers' => 'sellers.index',
                'Categories' => 'categories.index',
                'Products' => 'products.index',
                'Images' => 'product-images.index',
                'Coupons' => 'coupons.index',
                'Carts' => 'carts.index',
                'Orders' => 'orders.index',
                'Order items' => 'order-items.index',
                'Payments' => 'payments.index',
                'Reviews' => 'reviews.index',
                'Addresses' => 'addresses.index',
            ];
        } elseif ($me->isSeller()) {
            // A seller is also a buyer, so they get the shopping links too.
            $roleLabel = 'Seller';
            $links = [
                'Products' => 'products.index',
                'Images' => 'product-images.index',
                'Categories' => 'categories.index',
                'Sellers' => 'sellers.index',
                'Cart' => 'carts.index',
                'Orders' => 'orders.index',
                'Order items' => 'order-items.index',
                'Payments' => 'payments.index',
                'Reviews' => 'reviews.index',
                'Addresses' => 'addresses.index',
            ];
        } else {
            $roleLabel = 'Buyer';
            $links = [
                'Products' => 'products.index',
                'Categories' => 'categories.index',
                'Sellers' => 'sellers.index',
                'Cart' => 'carts.index',
                'Orders' => 'orders.index',
                'Payments' => 'payments.index',
                'Reviews' => 'reviews.index',
                'Addresses' => 'addresses.index',
            ];
        }
    @endphp

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            @include('partials.brand')

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    @foreach($links as $label => $routeName)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs(\Illuminate\Support\Str::before($routeName, '.').'.*') ? 'active' : '' }}"
                               href="{{ route($routeName) }}">{{ $label }}</a>
                        </li>
                    @endforeach
                </ul>

                <ul class="navbar-nav align-items-lg-center">
                    @if($roleLabel === 'Buyer')
                        <li class="nav-item">
                            <a class="btn btn-sm btn-warning me-lg-2 my-1" href="{{ route('sellers.create') }}">Become a seller</a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('users.show', $me) }}">
                            {{ $me->name }} <span class="badge text-bg-secondary">{{ $roleLabel }}</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-sm btn-outline-light ms-lg-2">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@endauth

@guest
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            @include('partials.brand')

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#guestNav"
                    aria-controls="guestNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="guestNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Products</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Categories</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('sellers.*') ? 'active' : '' }}" href="{{ route('sellers.index') }}">Sellers</a></li>
                </ul>

                <div class="d-flex gap-2 my-2 my-lg-0">
                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Login</a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-warning">Register</a>
                </div>
            </div>
        </div>
    </nav>
@endguest
