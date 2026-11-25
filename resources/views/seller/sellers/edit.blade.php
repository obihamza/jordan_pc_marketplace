@extends('layouts.seller')

@section('title', 'Edit seller profile')

@section('content')
@include('partials.page-header', ['title' => 'Edit my seller profile', 'back' => route('users.show', auth()->user())])
@include('partials.forms.seller', ['seller' => $seller])
@endsection
