@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
@php($me = auth()->user())

<section class="jo-banner mb-5">
    <div class="row align-items-center g-4 g-lg-5">

        <div class="col-lg-7">
            <span class="badge rounded-pill jo-pill px-3 py-2 mb-3">Jordan's marketplace for new &amp; used</span>

            <h1 class="display-5 fw-bold mb-3">
                Hello to <span class="text-orange">JO-Marketplace</span>
            </h1>

            <p class="lead text-white-50 mb-4" style="max-width: 540px;">
                Find great deals from sellers across Jordan, order in a few clicks
                and pay cash on delivery.
            </p>

            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="{{ route('products.index') }}" class="btn btn-primary btn-lg px-4">Shop now</a>

                @guest
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg px-4">Create an account</a>
                @else
                    @if($me->isAdmin())
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-light btn-lg px-4">Manage orders</a>
                    @elseif($me->isSeller())
                        <a href="{{ route('seller.dashboard', ['add' => 'product']) }}" class="btn btn-outline-light btn-lg px-4">List a product</a>
                    @elseif($me->sellerStatus())
                        <a href="{{ route('sellers.show', $me->seller) }}" class="btn btn-outline-light btn-lg px-4">Seller application ({{ $me->sellerStatus() }})</a>
                    @else
                        <a href="{{ route('sellers.create') }}" class="btn btn-outline-light btn-lg px-4">Become a seller</a>
                    @endif
                @endguest
            </div>

            @auth
                <p class="small text-white-50 mb-4">Signed in as {{ $me->name }}.</p>
            @endauth

            <div class="d-flex gap-4">
                <div class="jo-stat"><strong>{{ number_format($stats['products']) }}</strong><span>Products</span></div>
                <div class="jo-stat"><strong>{{ number_format($stats['sellers']) }}</strong><span>Sellers</span></div>
                <div class="jo-stat"><strong>{{ number_format($stats['categories']) }}</strong><span>Categories</span></div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card jo-bestsellers border-0 shadow-lg overflow-hidden text-body">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <span>Best sellers</span>
                    <a href="{{ route('products.index') }}" class="small text-decoration-none">View all &rarr;</a>
                </div>

                <ul class="list-group list-group-flush">
                    @forelse($bestSellers as $product)
                        <li class="list-group-item d-flex align-items-center gap-3 py-3">
                            <span class="jo-rank">{{ $loop->iteration }}</span>

                            @if($product->primaryImage)
                                <img src="{{ $product->primaryImage->url() }}" alt="" class="jo-thumb" loading="lazy" decoding="async">
                            @else
                                <div class="jo-thumb d-flex align-items-center justify-content-center text-muted small">No image</div>
                            @endif

                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('products.show', $product) }}"
                                   class="fw-semibold text-body text-decoration-none text-truncate d-block">{{ $product->name }}</a>
                                <div class="small text-muted text-truncate">
                                    {{ $product->category?->name }}
                                    @if($product->sold_count)
                                        &middot; {{ $product->sold_count }} sold
                                    @endif
                                </div>
                            </div>

                            <div class="fw-bold text-nowrap">{{ number_format($product->price, 2) }} <small class="text-muted">JOD</small></div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted py-4 text-center">No products have been listed yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</section>

<section class="mb-5" id="categories">
    @include('partials.section-head', ['titleHtml' => 'Shop from <span class="text-orange">top categories</span>', 'url' => route('categories.index')])

    <div class="jo-cat-row">
        @forelse($categories as $category)
            <a href="{{ route('categories.show', $category) }}" class="jo-cat-item text-decoration-none">
                <span class="jo-cat-circle {{ $loop->first ? 'active' : '' }}">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                <span class="jo-cat-name">{{ $category->name }}</span>
                <span class="small text-muted">{{ $category->products_count }} {{ \Illuminate\Support\Str::plural('item', $category->products_count) }}</span>
            </a>
        @empty
            <div class="alert alert-info mb-0 w-100">No categories yet.</div>
        @endforelse
    </div>
</section>

@foreach($categoryRows as $category)
    <section class="mb-5">
        @include('partials.section-head', [
            'titleHtml' => 'Grab the best deal on <span class="text-orange">'.e($category->name).'</span>',
            'url' => route('products.index', ['category_id' => $category->id]),
        ])
        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3">
            @foreach($category->latestProducts as $product)
                <div class="col">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    </section>
@endforeach

<section class="mb-5">
    @include('partials.section-head', ['titleHtml' => 'Selected <span class="text-orange">items</span>', 'url' => route('products.index'), 'label' => 'See all products'])

    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3">
        @forelse($selected as $product)
            <div class="col">
                @include('partials.product-card', ['product' => $product])
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info mb-0">More items are coming soon.</div></div>
        @endforelse
    </div>
</section>

<section class="mb-5">
    <div class="text-center mb-4">
        <h2 class="h3 fw-bold">Why <span class="text-orange">JO-Marketplace</span>?</h2>
        <p class="text-muted mb-0">Shopping and selling made simple, for Jordan.</p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
        <div class="col">
            <div class="card h-100 border-0 shadow-sm text-center">
                <div class="card-body p-4">
                    <div class="jo-why-icon mb-3">&#128181;</div>
                    <h3 class="h5">Cash on delivery</h3>
                    <p class="text-muted mb-0">Pay when your order arrives. Nothing to pay online.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 border-0 shadow-sm text-center">
                <div class="card-body p-4">
                    <div class="jo-why-icon mb-3">&#128737;</div>
                    <h3 class="h5">Verified sellers</h3>
                    <p class="text-muted mb-0">Every seller registers with their national ID and official name.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 border-0 shadow-sm text-center">
                <div class="card-body p-4">
                    <div class="jo-why-icon mb-3">&#9851;</div>
                    <h3 class="h5">New &amp; used</h3>
                    <p class="text-muted mb-0">Brand-new items or great pre-owned finds, clearly labelled.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 border-0 shadow-sm text-center">
                <div class="card-body p-4">
                    <div class="jo-why-icon mb-3">&#9733;</div>
                    <h3 class="h5">Honest reviews</h3>
                    <p class="text-muted mb-0">Read what other buyers say before you decide.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@if(! $me || (! $me->isAdmin() && ! $me->isSeller()))
    <section class="jo-cta rounded-4 p-4 p-lg-5 text-center">
        <h2 class="h3 fw-bold">Have something to sell?</h2>
        <p class="mb-4">Open a seller profile and reach buyers all over Jordan.</p>
        @guest
            <a href="{{ route('register') }}" class="btn btn-dark btn-lg px-4">Create a free account</a>
        @elseif($me->sellerStatus())
            <a href="{{ route('sellers.show', $me->seller) }}" class="btn btn-dark btn-lg px-4">See my seller application ({{ $me->sellerStatus() }})</a>
        @else
            <a href="{{ route('sellers.create') }}" class="btn btn-dark btn-lg px-4">Become a seller</a>
        @endguest
    </section>
@endif
@endsection
