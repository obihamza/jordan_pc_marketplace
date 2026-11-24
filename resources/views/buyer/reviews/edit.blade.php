@extends('layouts.app')

@section('title', 'Edit review')

@section('content')
@include('partials.page-header', ['title' => 'Edit review', 'back' => route('reviews.index')])
@include('partials.forms.review', ['review' => $review])
@endsection
