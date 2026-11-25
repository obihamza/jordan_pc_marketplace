@extends('layouts.app')

@section('title', 'Products')

@section('content')
@include('partials.page-header', ['title' => 'Products'])
@include('partials.product-filters', ['categories' => $categories])
@include('partials.products-grid', ['products' => $products])
@endsection
