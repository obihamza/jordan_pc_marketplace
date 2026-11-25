@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
@include('partials.page-header', ['title' => 'Categories', 'modal' => '#categoryModal', 'label' => 'New category'])
@include('partials.categories-table', ['categories' => $categories])
@endsection
