@extends('layouts.app')

@section('title', $user->name)

@section('content')
@include('partials.page-header', ['title' => $user->name, 'back' => route('users.index')])
@include('partials.user-detail', ['user' => $user])
@endsection
