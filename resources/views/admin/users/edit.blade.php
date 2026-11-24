@extends('layouts.app')

@section('title', 'Edit user')

@section('content')
@include('partials.page-header', ['title' => 'Edit user', 'back' => route('users.index')])
@include('partials.forms.user', ['user' => $user])
@endsection
