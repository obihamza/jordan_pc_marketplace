@extends('layouts.admin')

@section('title', 'New seller')

@section('content')
@include('partials.page-header', ['title' => 'New seller profile', 'back' => route('sellers.index')])
@include('partials.forms.seller', ['users' => $users])
@endsection
