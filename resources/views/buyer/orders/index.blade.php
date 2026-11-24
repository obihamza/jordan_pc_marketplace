@extends('layouts.app')

@section('title', 'My orders')

@section('content')
@include('partials.page-header', ['title' => 'My orders'])
@include('partials.orders-table', ['orders' => $orders])
@endsection
