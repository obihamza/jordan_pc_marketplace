@extends('layouts.app')

@section('title', 'My addresses')

@section('content')
@include('partials.page-header', ['title' => 'My addresses', 'url' => route('addresses.create'), 'label' => 'Add address'])
@include('partials.addresses-table', ['addresses' => $addresses])
@endsection
