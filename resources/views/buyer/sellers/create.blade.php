@extends('layouts.app')

@section('title', 'Become a seller')

@section('content')
@include('partials.page-header', ['title' => 'Become a seller', 'back' => route('sellers.index')])
<p class="text-muted">Create your seller profile to start listing products. You keep all your buyer features.</p>
@include('partials.forms.seller')
@endsection
