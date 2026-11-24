{{-- Params: products (paginator) --}}
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
    @forelse($products as $product)
        <div class="col">
            @include('partials.product-card', ['product' => $product])
        </div>
    @empty
        <div class="col-12"><div class="alert alert-info mb-0">No products found.</div></div>
    @endforelse
</div>
<div class="mt-3">{{ $products->links() }}</div>
