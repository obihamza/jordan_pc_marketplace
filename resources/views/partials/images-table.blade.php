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
                        @php
                            $settingsData = 'data-image-settings data-action="'.e(route('product-images.update', $image)).'" data-src="'.e($image->url()).'" data-product="'.e($image->product?->name).'" data-primary="'.($image->is_primary ? '1' : '0').'"';
                        @endphp
                        <td>
                            {{-- Click the picture to open its settings --}}
                            <button type="button" class="btn p-0 border-0 bg-transparent" {!! $settingsData !!} title="Picture settings" aria-label="Open picture settings">
                                <img src="{{ $image->url() }}" alt="" class="rounded border" loading="lazy" style="width: 64px; height: 64px; object-fit: cover;">
                            </button>
                        </td>
                        <td>
                            @if($image->product)
                                <a href="{{ route('products.show', $image->product) }}" class="text-decoration-none">{{ $image->product->name }}</a>
                            @else
                                <span class="text-muted">Removed product</span>
                            @endif
                        </td>
                        <td>@if($image->is_primary)<span class="badge text-bg-success">Primary</span>@endif</td>
                        <td>{{ $image->created_at?->format('Y-m-d') }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="jo-icon-btn" {!! $settingsData !!} title="Picture settings" aria-label="Picture settings"><i class="bi bi-gear"></i></button>
                            @include('partials.delete-button', ['icon' => true, 'action' => route('product-images.destroy', $image)])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No images found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $images->links() }}</div>
@include('partials.image-settings-modal')
