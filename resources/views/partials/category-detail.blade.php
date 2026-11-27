@include('partials.page-header', ['title' => $category->name, 'back' => route('categories.index')])

@if($category->description)
    <p class="text-muted">{{ $category->description }}</p>
@endif

@include('partials.products-grid', ['products' => $products])
