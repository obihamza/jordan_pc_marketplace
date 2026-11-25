@extends(auth()->user()?->isSeller() ? 'layouts.seller' : 'layouts.app')

@section('title', 'Edit address')

@section('content')
@include('partials.page-header', ['title' => 'Edit address', 'back' => route('addresses.index')])
@include('partials.forms.address', ['address' => $address])
@endsection
