@extends('layouts.app')

@section('title', 'New user')

@section('content')
@include('partials.page-header', ['title' => 'New user', 'back' => route('users.index')])
@include('partials.forms.user')
@endsection
