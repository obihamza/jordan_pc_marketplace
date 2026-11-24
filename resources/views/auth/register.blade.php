@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Create an account</h1>
                <p class="text-muted small">Everyone starts as a buyer. You can become a seller later from your profile.</p>

                <form action="{{ route('register') }}" method="POST">
                    @csrf

                    @include('partials.forms.input', ['name' => 'name', 'label' => 'Full name', 'required' => true])
                    @include('partials.forms.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true])
                    @include('partials.forms.input', ['name' => 'phone', 'label' => 'Phone (optional)'])
                    @include('partials.forms.input', ['name' => 'password', 'label' => 'Password (min 8 characters)', 'type' => 'password', 'required' => true])
                    @include('partials.forms.input', ['name' => 'password_confirmation', 'label' => 'Confirm password', 'type' => 'password', 'required' => true])

                    <button class="btn btn-primary w-100">Register</button>
                </form>

                <p class="text-center text-muted mt-3 mb-0">
                    Already registered? <a href="{{ route('login') }}">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
