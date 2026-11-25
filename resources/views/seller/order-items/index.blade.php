@extends('layouts.seller')

@section('title', 'Order items')

@section('content')
@include('partials.page-header', ['title' => 'Order items'])

<h2 class="h5">Items I have to fulfil</h2>
<p class="text-muted small">Lines of customers' orders that contain your products. Update the status as you prepare and ship them.</p>
@include('partials.order-items-table', ['items' => $items, 'canUpdate' => true])

<h2 class="h5 mt-5">Items I bought</h2>
@include('partials.order-items-table', ['items' => $purchasedItems])
@endsection
