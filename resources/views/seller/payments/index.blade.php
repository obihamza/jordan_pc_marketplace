@extends('layouts.seller')

@section('title', 'Payments')

@section('content')
@include('partials.page-header', ['title' => 'Payments'])

<h2 class="h5">Payments for my sales</h2>
@include('partials.payments-table', ['payments' => $payments])

<h2 class="h5 mt-5">My payments</h2>
@include('partials.payments-table', ['payments' => $myPayments])
@endsection
