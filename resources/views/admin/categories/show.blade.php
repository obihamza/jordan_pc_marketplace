@extends('layouts.admin')

@section('title', $category->name)

@section('content')
@include('partials.category-detail', ['category' => $category, 'products' => $products])
@endsection
