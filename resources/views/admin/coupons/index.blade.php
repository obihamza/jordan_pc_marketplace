@extends('layouts.admin')

@section('title', 'Coupons')

@section('content')
@include('partials.page-header', ['title' => 'Coupons', 'url' => route('coupons.create'), 'label' => 'New coupon'])
@include('partials.coupons-table', ['coupons' => $coupons])
@endsection
