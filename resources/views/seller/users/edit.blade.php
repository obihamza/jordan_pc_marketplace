@extends('layouts.seller')

@section('title', 'Edit my profile')

@section('content')
@include('partials.page-header', ['title' => 'Edit my profile', 'back' => route('users.show', $user)])
@include('partials.forms.user', ['user' => $user])
@endsection
