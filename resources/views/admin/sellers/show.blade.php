@extends('layouts.app')

@section('title', $seller->official_name)

@section('content')
@include('partials.page-header', ['title' => $seller->official_name, 'back' => route('sellers.index')])
@include('partials.seller-detail', ['seller' => $seller])
@endsection
