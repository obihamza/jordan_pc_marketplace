@extends('layouts.seller')

@section('title', $product->name)

@section('content')
@include('partials.product-detail', ['product' => $product])
@endsection
