@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)

@section('content')
@include('partials.page-header', ['title' => 'Order '.$order->order_number])
@include('partials.order-detail', ['order' => $order])
@endsection
