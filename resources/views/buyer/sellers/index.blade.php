@extends('layouts.app')

@section('title', 'Sellers')

@section('content')
@include('partials.page-header', ['title' => 'Sellers', 'url' => route('sellers.create'), 'label' => 'Become a seller'])
@include('partials.sellers-table', ['sellers' => $sellers])
@endsection
