@extends('layouts.app')

@section('title', 'Products')

@section('content')
@include('partials.page-header', ['title' => 'Products', 'url' => route('products.create'), 'label' => 'New product'])
@include('partials.product-filters', ['categories' => $categories])
@include('partials.products-table', ['products' => $products, 'showSeller' => true])
@endsection
