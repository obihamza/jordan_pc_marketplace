@extends('layouts.seller')

@section('title', 'Products')

@section('content')
@include('partials.page-header', ['title' => 'My products', 'url' => route('products.create'), 'label' => 'New product'])
@include('partials.product-filters', ['categories' => $categories])
@include('partials.products-table', ['products' => $products])

<h2 class="h4 mt-5 mb-3">Marketplace</h2>
<p class="text-muted small">Products from other sellers. You can shop here too.</p>
@include('partials.products-grid', ['products' => $marketplace])
@endsection
