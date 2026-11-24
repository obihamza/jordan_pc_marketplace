@extends('layouts.app')

@section('title', 'My payments')

@section('content')
@include('partials.page-header', ['title' => 'My payments'])
@include('partials.payments-table', ['payments' => $payments])
@endsection
