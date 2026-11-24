@extends('layouts.app')

@section('title', 'Payments')

@section('content')
@include('partials.page-header', ['title' => 'Payments'])
@include('partials.payments-table', ['payments' => $payments, 'canUpdate' => true])
@endsection
