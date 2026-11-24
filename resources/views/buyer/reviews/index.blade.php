@extends('layouts.app')

@section('title', 'My reviews')

@section('content')
@include('partials.page-header', ['title' => 'My reviews', 'url' => route('reviews.create'), 'label' => 'Write a review'])
@include('partials.reviews-table', ['reviews' => $reviews])
@endsection
