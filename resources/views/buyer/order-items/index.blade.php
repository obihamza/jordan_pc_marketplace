@extends('layouts.app')

@section('title', 'My order items')

@section('content')
@include('partials.page-header', ['title' => 'My order items'])
@include('partials.order-items-table', ['items' => $items])
@endsection
