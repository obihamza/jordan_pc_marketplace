@extends('layouts.seller')

@section('title', $category->name)

@section('content')
@include('partials.category-detail', ['category' => $category, 'products' => $products])
@endsection
