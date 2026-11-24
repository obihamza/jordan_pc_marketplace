@extends('layouts.app')

@section('title', $product->name)

@section('content')
@include('partials.product-detail', ['product' => $product])
@endsection
