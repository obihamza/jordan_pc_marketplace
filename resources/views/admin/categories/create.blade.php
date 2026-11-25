@extends('layouts.admin')

@section('title', 'New category')

@section('content')
@include('partials.page-header', ['title' => 'New category', 'back' => route('categories.index')])
@include('partials.forms.category')
@endsection
