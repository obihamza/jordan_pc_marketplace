@extends('layouts.admin')

@section('title', $product->name)

@section('content')
@include('partials.product-detail', ['product' => $product])
@endsection
