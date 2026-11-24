{{-- Params: review (optional, edit), products (create only) --}}
@php($review = $review ?? null)
<form action="{{ $review ? route('reviews.update', $review) : route('reviews.store') }}" method="POST" class="card card-body">
    @csrf
    @if($review) @method('PUT') @endif

    @if($review)
        <div class="mb-3">
            <label class="form-label">Product</label>
            <input type="text" class="form-control" value="{{ $review->product?->name }}" disabled>
        </div>
    @else
        @include('partials.forms.select', [
            'name' => 'product_id', 'label' => 'Product', 'required' => true, 'placeholder' => '-- choose product --',
            'options' => $products->pluck('name', 'id'), 'selected' => request('product_id'),
        ])
    @endif

    @include('partials.forms.select', [
        'name' => 'rating', 'label' => 'Rating', 'required' => true,
        'options' => [5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Okay', 2 => '2 - Poor', 1 => '1 - Terrible'],
        'selected' => $review?->rating ?? 5,
    ])

    @include('partials.forms.textarea', ['name' => 'comment', 'label' => 'Comment', 'required' => true, 'value' => $review?->comment])

    @include('partials.forms.actions', ['cancel' => route('reviews.index'), 'submit' => $review ? 'Update review' : 'Post review'])
</form>
