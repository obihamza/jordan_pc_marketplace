@extends('layouts.seller')

@section('title', 'Reviews')

@section('content')
@include('partials.page-header', ['title' => 'Reviews', 'url' => route('reviews.create'), 'label' => 'Write a review'])

<h2 class="h5">Reviews on my products</h2>
@include('partials.reviews-table', ['reviews' => $received, 'showUser' => true])

<h2 class="h5 mt-5">Reviews I wrote</h2>
@include('partials.reviews-table', ['reviews' => $written])
@endsection
