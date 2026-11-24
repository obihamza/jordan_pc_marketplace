{{-- Param: product (ideally with primaryImage, category, seller loaded) --}}
@php
    $me = auth()->user();
    $isOwn = $me && $product->seller_id == $me->id;
@endphp
<div class="card h-100 shadow-sm">
    @if($product->primaryImage)
        <img src="{{ asset('storage/'.$product->primaryImage->image_path) }}" class="card-img-top"
             alt="{{ $product->name }}" style="height: 180px; object-fit: cover;">
    @else
        <div class="bg-secondary-subtle text-muted d-flex align-items-center justify-content-center" style="height: 180px;">No image</div>
    @endif

    <div class="card-body d-flex flex-column">
        <h2 class="h6 card-title mb-1">
            <a href="{{ route('products.show', $product) }}" class="text-decoration-none">{{ $product->name }}</a>
        </h2>
        <div class="small text-muted mb-2">
            {{ $product->category?->name }}
            <span class="badge text-bg-light border">{{ ucfirst($product->condition_type) }}</span>
        </div>
        <div class="fw-bold mb-2">{{ number_format($product->price, 2) }} JOD</div>

        <div class="mt-auto">
            @if($isOwn)
                <span class="badge text-bg-info">Your product</span>
            @elseif(! $me)
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">Login to buy</a>
            @elseif(! $me->isAdmin() && $product->listing_status === 'available' && $product->stock_quantity > 0)
                <form action="{{ route('cart-items.store') }}" method="POST" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock_quantity }}"
                           class="form-control form-control-sm" style="width: 80px;">
                    <button class="btn btn-sm btn-primary">Add to cart</button>
                </form>
            @endif
        </div>
    </div>
</div>
