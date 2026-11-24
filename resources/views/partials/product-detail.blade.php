{{-- Param: product (with seller, category, images, reviews.user) --}}
@php
    $me = auth()->user();
    $canManage = $me->isAdmin() || $product->seller_id == $me->id;
    $avgRating = $product->reviews->avg('rating');
    $images = $product->images->sortByDesc('is_primary');
@endphp

<div class="row g-4">
    <div class="col-md-5">
        @if($images->isNotEmpty())
            <img src="{{ asset('storage/'.$images->first()->image_path) }}" alt="{{ $product->name }}"
                 class="img-fluid rounded border mb-2 w-100" style="max-height: 380px; object-fit: cover;">

            @if($images->count() > 1)
                <div class="d-flex flex-wrap gap-2">
                    @foreach($images->skip(1) as $image)
                        <img src="{{ asset('storage/'.$image->image_path) }}" alt="" class="rounded border"
                             style="width: 80px; height: 80px; object-fit: cover;">
                    @endforeach
                </div>
            @endif
        @else
            <div class="bg-secondary-subtle text-muted d-flex align-items-center justify-content-center rounded" style="height: 300px;">No image</div>
        @endif
    </div>

    <div class="col-md-7">
        <h1 class="h3">{{ $product->name }}</h1>

        <div class="mb-2">
            <span class="badge text-bg-light border">{{ $product->category?->name }}</span>
            <span class="badge text-bg-light border">{{ ucfirst($product->condition_type) }}</span>
            @include('partials.status-badge', ['status' => $product->listing_status])
        </div>

        <div class="fs-3 fw-bold mb-2">{{ number_format($product->price, 2) }} JOD</div>

        <dl class="row mb-3">
            @if($product->brand)
                <dt class="col-sm-3">Brand</dt><dd class="col-sm-9">{{ $product->brand }}</dd>
            @endif
            @if($product->product_variant)
                <dt class="col-sm-3">Variant</dt><dd class="col-sm-9">{{ $product->product_variant }}</dd>
            @endif
            <dt class="col-sm-3">In stock</dt><dd class="col-sm-9">{{ $product->stock_quantity }}</dd>
            <dt class="col-sm-3">Seller</dt>
            <dd class="col-sm-9">
                @if($product->seller)
                    <a href="{{ route('sellers.show', $product->seller) }}" class="text-decoration-none">{{ $product->seller->official_name }}</a>
                @else
                    <span class="text-muted">Unknown</span>
                @endif
            </dd>
            <dt class="col-sm-3">Rating</dt>
            <dd class="col-sm-9">
                @if($avgRating)
                    <span class="text-warning">{{ str_repeat('★', (int) round($avgRating)) }}{{ str_repeat('☆', 5 - (int) round($avgRating)) }}</span>
                    {{ number_format($avgRating, 1) }} ({{ $product->reviews->count() }})
                @else
                    <span class="text-muted">No reviews yet</span>
                @endif
            </dd>
        </dl>

        <p style="white-space: pre-line;">{{ $product->description }}</p>

        {{-- Buying: any non-admin, except on their own product --}}
        @if(! $me->isAdmin() && ! $canManage && $product->listing_status === 'available' && $product->stock_quantity > 0)
            <form action="{{ route('cart-items.store') }}" method="POST" class="d-flex gap-2 mb-3" style="max-width: 320px;">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}" class="form-control">
                <button class="btn btn-primary text-nowrap">Add to cart</button>
            </form>
        @endif

        <div class="d-flex flex-wrap gap-2">
            @if(! $me->isAdmin() && ! $canManage)
                <a href="{{ route('reviews.create', ['product_id' => $product->id]) }}" class="btn btn-outline-secondary">Write a review</a>
            @endif

            @if($canManage)
                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary">Edit</a>
                <a href="{{ route('product-images.create', ['product_id' => $product->id]) }}" class="btn btn-outline-secondary">Add image</a>
                @include('partials.delete-button', ['action' => route('products.destroy', $product), 'class' => 'btn-outline-danger'])
            @endif

            <a href="{{ route('products.index') }}" class="btn btn-link">Back to products</a>
        </div>
    </div>
</div>

<h2 class="h5 mt-5">Reviews ({{ $product->reviews->count() }})</h2>
@forelse($product->reviews as $review)
    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between">
                <strong>{{ $review->user?->name }}</strong>
                <span class="text-warning">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
            </div>
            <div style="white-space: pre-line;">{{ $review->comment }}</div>
            <div class="small text-muted">{{ $review->created_at?->diffForHumans() }}</div>
        </div>
    </div>
@empty
    <div class="text-muted">Nobody has reviewed this product yet.</div>
@endforelse
