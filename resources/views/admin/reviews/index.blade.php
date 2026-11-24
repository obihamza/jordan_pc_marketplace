@extends('layouts.app')

@section('title', 'Reviews')

@section('content')
@include('partials.page-header', ['title' => 'Reviews'])
@include('partials.reviews-table', ['reviews' => $reviews, 'showUser' => true])
@endsection
