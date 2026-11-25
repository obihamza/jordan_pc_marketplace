@extends('layouts.seller')

@section('title', 'Categories')

@section('content')
@include('partials.page-header', ['title' => 'Categories'])
@include('partials.categories-table', ['categories' => $categories])
@endsection
