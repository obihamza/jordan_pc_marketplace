@extends('layouts.app')

@section('title', 'Write a review')

@section('content')
@include('partials.page-header', ['title' => 'Write a review', 'back' => route('reviews.index')])
@include('partials.forms.review', ['products' => $products])
@endsection
