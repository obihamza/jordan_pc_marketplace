@extends('layouts.app')

@section('title', 'Sellers')

@section('content')
@include('partials.page-header', ['title' => 'Sellers'])
@include('partials.sellers-table', ['sellers' => $sellers])
@endsection
