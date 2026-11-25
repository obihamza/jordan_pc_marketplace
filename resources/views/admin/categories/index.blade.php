@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
@include('partials.page-header', ['title' => 'Categories', 'url' => route('categories.create'), 'label' => 'New category'])
@include('partials.categories-table', ['categories' => $categories])
@endsection
