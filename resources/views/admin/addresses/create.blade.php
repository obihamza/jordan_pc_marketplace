@extends('layouts.admin')

@section('title', 'New address')

@section('content')
@include('partials.page-header', ['title' => 'New address', 'back' => route('addresses.index')])
@include('partials.forms.address', ['users' => $users])
@endsection
