{{-- Params: product (optional, edit), categories, sellers (optional, admin create), modal (optional: inside the pop-up, no card/buttons) --}}
@php
    $product = $product ?? null;
    $modal = $modal ?? false;
@endphp
<form action="{{ $product ? route('products.update', $product) : route('products.store') }}" method="POST" @if($modal) id="productCreateForm" @else class="card card-body" @endif>
    @csrf
    @if($modal)<input type="hidden" name="_modal" value="product">@endif
    @if($product) @method('PUT') @endif

    @isset($sellers)
        @include('partials.forms.select', [
            'name' => 'seller_id', 'label' => 'Seller', 'required' => true, 'placeholder' => '-- choose seller --',
            'options' => $sellers->mapWithKeys(fn ($s) => [$s->user_id => $s->official_name]),
        ])
    @endisset

    @include('partials.forms.select', [
        'name' => 'category_id', 'label' => 'Category', 'required' => ! $modal, 'placeholder' => '-- choose category --',
        'options' => $categories->pluck('name', 'id'), 'selected' => $product?->category_id,
    ])

    {{-- Not in the list? Type a new category and it is created together with the product. --}}
    @if($modal)
        @include('partials.forms.input', ['name' => 'new_category', 'label' => 'Or create a new category'])
    @endif

    @include('partials.forms.input', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $product?->name])

    <div class="row">
        <div class="col-md-6">
            @include('partials.forms.input', ['name' => 'brand', 'label' => 'Brand', 'value' => $product?->brand])
        </div>
        <div class="col-md-6">
            @include('partials.forms.input', ['name' => 'product_variant', 'label' => 'Variant (color, size...)', 'value' => $product?->product_variant])
        </div>
    </div>

    @include('partials.forms.textarea', ['name' => 'description', 'label' => 'Description', 'required' => true, 'value' => $product?->description])

    <div class="row">
        <div class="col-md-4">
            @include('partials.forms.input', ['name' => 'price', 'label' => 'Price (JOD)', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'required' => true, 'value' => $product?->price])
        </div>
        <div class="col-md-4">
            @include('partials.forms.input', ['name' => 'stock_quantity', 'label' => 'Stock quantity', 'type' => 'number', 'min' => 0, 'required' => true, 'value' => $product?->stock_quantity ?? 1])
        </div>
        <div class="col-md-4">
            @include('partials.forms.select', [
                'name' => 'condition_type', 'label' => 'Condition', 'required' => true,
                'options' => ['new' => 'New', 'used' => 'Used'], 'selected' => $product?->condition_type ?? 'new',
            ])
        </div>
    </div>

    {{-- The controller sets new products to "available"; the status can be changed when editing. --}}
    @if($product)
        @include('partials.forms.select', [
            'name' => 'listing_status', 'label' => 'Listing status', 'required' => true,
            'options' => ['available' => 'Available', 'sold' => 'Sold', 'hidden' => 'Hidden', 'reserved' => 'Reserved'],
            'selected' => $product->listing_status,
        ])
    @endif

    @unless($modal)
        @include('partials.forms.actions', ['cancel' => route('products.index'), 'submit' => $product ? 'Update product' : 'Create product'])
    @endunless
</form>
