@extends('layouts.app')

@section('title', 'Order items')

@section('content')
@include('partials.page-header', ['title' => 'Order items'])
@include('partials.order-items-table', ['items' => $items, 'showSeller' => true, 'canUpdate' => true])
@endsection
