@extends('layouts.app')

@section('title', 'Products')

@section('content')
@include('partials.page-header', ['title' => 'Products'])
@include('partials.products-grid', ['products' => $products])
@endsection
