@extends('layouts.app')

@section('title', 'Become a seller')

@section('content')
@include('partials.page-header', ['title' => 'Become a seller', 'back' => route('sellers.index')])
<p class="text-muted">Send your shop name, national ID number and a picture of the ID. An admin reviews it, and once you are approved you can start listing products. You keep all your buyer features.</p>
@include('partials.forms.seller')
@endsection
