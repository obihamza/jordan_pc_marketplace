@extends('layouts.app')

@section('title', 'Edit seller profile')

@section('content')
@include('partials.page-header', ['title' => 'Edit my seller profile', 'back' => route('sellers.show', $seller)])
@include('partials.forms.seller', ['seller' => $seller])
@endsection
