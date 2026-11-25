@extends('layouts.admin')

@section('title', 'Edit coupon')

@section('content')
@include('partials.page-header', ['title' => 'Edit coupon', 'back' => route('coupons.index')])
@include('partials.forms.coupon', ['coupon' => $coupon])
@endsection
