@extends('layouts.app')

@section('title', 'Edit category')

@section('content')
@include('partials.page-header', ['title' => 'Edit category', 'back' => route('categories.index')])
@include('partials.forms.category', ['category' => $category])
@endsection
