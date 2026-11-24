@extends('layouts.app')

@section('title', 'New coupon')

@section('content')
@include('partials.page-header', ['title' => 'New coupon', 'back' => route('coupons.index')])
@include('partials.forms.coupon')
@endsection
