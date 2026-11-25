@extends(auth()->user()?->isSeller() ? 'layouts.seller' : 'layouts.app')

@section('title', 'Checkout')

@section('content')
@include('partials.page-header', ['title' => 'Checkout', 'back' => route('carts.index')])

@if(! $cart || $cart->items->isEmpty())
    <div class="alert alert-info">
        Your cart is empty. <a href="{{ route('products.index') }}" class="alert-link">Browse products</a>.
    </div>
@else
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header fw-semibold">Order summary</div>
                <ul class="list-group list-group-flush">
                    @foreach($cart->items as $item)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $item->product?->name ?? 'Unavailable product' }} &times; {{ $item->quantity }}</span>
                            <span>{{ number_format($item->quantity * ($item->product?->price ?? 0), 2) }} JOD</span>
                        </li>
                    @endforeach
                    <li class="list-group-item d-flex justify-content-between fw-bold">
                        <span>Subtotal</span><span>{{ number_format($cart->total, 2) }} JOD</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-5">
            <form action="{{ route('orders.store') }}" method="POST" class="card card-body">
                @csrf

                @if($addresses->isEmpty())
                    <div class="alert alert-warning">
                        You need a delivery address first.
                        <a href="{{ route('addresses.create') }}" class="alert-link">Add one</a>.
                    </div>
                @else
                    @include('partials.forms.select', [
                        'name' => 'address_id', 'label' => 'Deliver to', 'required' => true,
                        'options' => $addresses->pluck('address_line', 'id'),
                    ])
                @endif

                @include('partials.forms.input', ['name' => 'coupon_code', 'label' => 'Coupon code (optional)'])

                <p class="small text-muted">Payment method: <strong>Cash on delivery</strong>.</p>

                <button class="btn btn-primary" @disabled($addresses->isEmpty())>Place order</button>
            </form>
        </div>
    </div>
@endif
@endsection
