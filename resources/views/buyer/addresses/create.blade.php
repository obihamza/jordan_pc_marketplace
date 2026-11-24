@extends('layouts.app')

@section('title', 'Add address')

@section('content')
@include('partials.page-header', ['title' => 'Add address', 'back' => route('addresses.index')])
@include('partials.forms.address')
@endsection
