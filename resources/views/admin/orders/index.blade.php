@extends('layouts.app')

@section('title', 'Orders')

@section('content')
@include('partials.page-header', ['title' => 'Orders'])
@include('partials.orders-table', ['orders' => $orders, 'showBuyer' => true])
@endsection
