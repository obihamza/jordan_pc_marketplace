<form action="{{ route('product-images.store') }}" method="POST" enctype="multipart/form-data" class="card card-body">
    @csrf

    @include('partials.forms.select', [
        'name' => 'product_id', 'label' => 'Product', 'required' => true, 'placeholder' => '-- choose product --',
        'options' => $products->pluck('name', 'id'), 'selected' => request('product_id'),
    ])

    <div class="mb-3">
        <label for="image" class="form-label">Image (max 2 MB)</label>
        <input type="file" name="image" id="image" accept="image/*" required
               class="form-control @error('image') is-invalid @enderror">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @include('partials.forms.checkbox', ['name' => 'is_primary', 'label' => 'Use as the primary image'])

    @include('partials.forms.actions', ['cancel' => route('product-images.index'), 'submit' => 'Upload image'])
</form>
