@extends('layouts.app')

@section('title', 'Orders')

@section('content')
@include('partials.page-header', ['title' => 'Orders'])

<h2 class="h5">Sales <span class="text-muted fs-6">(orders containing my products)</span></h2>
@include('partials.orders-table', ['orders' => $sales, 'showBuyer' => true])

<h2 class="h5 mt-5">My purchases</h2>
@include('partials.orders-table', ['orders' => $purchases])
@endsection
