{{-- Param: images (paginator, with product loaded) --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Image</th><th>Product</th><th>Primary</th><th>Uploaded</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($images as $image)
                    <tr>
                        <td><img src="{{ asset('storage/'.$image->image_path) }}" alt="" class="rounded border" style="width: 64px; height: 64px; object-fit: cover;"></td>
                        <td>
                            @if($image->product)
                                <a href="{{ route('products.show', $image->product) }}" class="text-decoration-none">{{ $image->product->name }}</a>
                            @else
                                <span class="text-muted">Removed product</span>
                            @endif
                        </td>
                        <td>@if($image->is_primary)<span class="badge text-bg-success">Primary</span>@endif</td>
                        <td>{{ $image->created_at?->format('Y-m-d') }}</td>
                        <td class="text-end">@include('partials.delete-button', ['action' => route('product-images.destroy', $image)])</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No images found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $images->links() }}</div>
