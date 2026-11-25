<footer class="jo-footer mt-5 pt-5 pb-3">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <img src="{{ asset('images/jo-logo.svg') }}" alt="JO-Marketplace" class="jo-logo mb-3">
                <p class="mb-0" style="max-width: 420px;">
                    JO-Marketplace is where Jordan buys and sells new and used items.
                    Browse local sellers, order in a few clicks and pay cash on delivery.
                </p>
            </div>

            <div class="col-6 col-lg-2 offset-lg-1">
                <h2 class="mb-3">Shop</h2>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="{{ route('products.index') }}">All products</a></li>
                    <li class="mb-2"><a href="{{ route('categories.index') }}">Categories</a></li>
                    <li class="mb-2"><a href="{{ route('sellers.index') }}">Sellers</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h2 class="mb-3">Account</h2>
                <ul class="list-unstyled mb-0">
                    @guest
                        <li class="mb-2"><a href="{{ route('login') }}">Login</a></li>
                        <li class="mb-2"><a href="{{ route('register') }}">Register</a></li>
                    @else
                        <li class="mb-2"><a href="{{ route('users.show', auth()->user()) }}">My profile</a></li>
                        <li class="mb-2"><a href="{{ route('orders.index') }}">Orders</a></li>
                    @endguest
                </ul>
            </div>

            <div class="col-lg-2">
                <h2 class="mb-3">Sell</h2>
                <ul class="list-unstyled mb-0">
                    @guest
                        <li class="mb-2"><a href="{{ route('register') }}">Start selling</a></li>
                    @else
                        @if(auth()->user()->isAdmin())
                            <li class="mb-2"><a href="{{ route('sellers.index') }}">Sellers</a></li>
                        @elseif(auth()->user()->isSeller())
                            <li class="mb-2"><a href="{{ route('seller.dashboard', ['add' => 'product']) }}">List a product</a></li>
                        @elseif(auth()->user()->sellerStatus())
                            <li class="mb-2"><a href="{{ route('sellers.show', auth()->user()->seller) }}">Seller application ({{ auth()->user()->sellerStatus() }})</a></li>
                        @else
                            <li class="mb-2"><a href="{{ route('sellers.create') }}">Become a seller</a></li>
                        @endif
                    @endguest
                </ul>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex flex-column flex-md-row justify-content-between small">
            <span>&copy; {{ now()->year }} JO-Marketplace. All rights reserved.</span>
            <span>Made in Jordan &middot; Cash on delivery</span>
        </div>
    </div>
</footer>
