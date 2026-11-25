@extends('layouts.admin')

@section('title', 'Upload image')

@section('content')
@include('partials.page-header', ['title' => 'Upload image', 'back' => route('product-images.index')])
@include('partials.forms.product-image', ['products' => $products])
@endsection
