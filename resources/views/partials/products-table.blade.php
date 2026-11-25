{{-- Params: products (paginator), showSeller (optional) --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    @if($showSeller ?? false)<th>Seller</th>@endif
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Condition</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="fw-semibold text-decoration-none">{{ $product->name }}</a>
                            <div class="small text-muted">{{ $product->brand }}</div>
                        </td>
                        @if($showSeller ?? false)<td>{{ $product->seller?->official_name }}</td>@endif
                        <td>{{ $product->category?->name }}</td>
                        <td>{{ number_format($product->price, 2) }} JOD</td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>{{ ucfirst($product->condition_type) }}</td>
                        <td>@include('partials.status-badge', ['status' => $product->listing_status])</td>
                        <td class="text-end text-nowrap">
                            <a class="jo-icon-btn" href="{{ route('products.show', $product) }}" title="View" aria-label="View"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->isAdmin() || $product->seller_id == auth()->id())
                                <a class="jo-icon-btn" href="{{ route('products.edit', $product) }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square"></i></a>
                                @include('partials.delete-button', ['icon' => true, 'action' => route('products.destroy', $product)])
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $products->links() }}</div>
