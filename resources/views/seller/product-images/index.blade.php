@extends('layouts.app')

@section('title', 'My product images')

@section('content')
@include('partials.page-header', ['title' => 'My product images', 'url' => route('product-images.create'), 'label' => 'Upload image'])
@include('partials.images-table', ['images' => $images])
@endsection
