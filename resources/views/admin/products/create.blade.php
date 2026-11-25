@extends('layouts.admin')

@section('title', 'New product')

@section('content')
@include('partials.page-header', ['title' => 'New product', 'back' => route('products.index')])
@include('partials.forms.product', ['categories' => $categories, 'sellers' => $sellers])
@endsection
