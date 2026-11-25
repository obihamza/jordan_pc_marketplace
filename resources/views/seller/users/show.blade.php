@extends('layouts.seller')

@section('title', 'My profile')

@section('content')
@include('partials.page-header', ['title' => 'My profile'])
@include('partials.user-detail', ['user' => $user])
@endsection
