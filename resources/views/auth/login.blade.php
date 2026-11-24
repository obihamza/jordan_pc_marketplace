@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Login</h1>

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    @include('partials.forms.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
                    @include('partials.forms.input', ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => true])

                    <button class="btn btn-primary w-100">Login</button>
                </form>

                <p class="text-center text-muted mt-3 mb-0">
                    New here? <a href="{{ route('register') }}">Create an account</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
