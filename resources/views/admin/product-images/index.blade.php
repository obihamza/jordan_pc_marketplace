@extends('layouts.admin')

@section('title', 'Product images')

@section('content')
@include('partials.page-header', ['title' => 'Product images', 'url' => route('product-images.create'), 'label' => 'Upload image'])
@include('partials.images-table', ['images' => $images])
@endsection
