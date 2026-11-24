@extends('layouts.app')

@section('title', 'Edit seller')

@section('content')
@include('partials.page-header', ['title' => 'Edit seller profile', 'back' => route('sellers.index')])
@include('partials.forms.seller', ['seller' => $seller])
@endsection
