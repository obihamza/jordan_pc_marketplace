@extends('layouts.seller')

@section('title', 'Edit product')

@section('content')
@include('partials.page-header', ['title' => 'Edit product', 'back' => route('products.index')])
@include('partials.forms.product', ['product' => $product, 'categories' => $categories])
@endsection
