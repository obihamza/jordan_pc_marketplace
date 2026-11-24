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
            <a class="navbar-brand" href="{{ url('/') }}">Masterpiece</a>

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

                    @if(\Illuminate\Support\Facades\Route::has('logout'))
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-outline-light ms-lg-2">Logout</button>
                            </form>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endauth
