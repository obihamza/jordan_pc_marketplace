@extends('layouts.admin')

@section('title', 'Addresses')

@section('content')
@include('partials.page-header', ['title' => 'Addresses', 'url' => route('addresses.create'), 'label' => 'New address'])
@include('partials.addresses-table', ['addresses' => $addresses, 'showUser' => true])
@endsection
