@extends('layouts.app')

@section('title', 'My cart')

@section('content')
@include('partials.page-header', ['title' => 'My cart'])

@if($cart->items->isEmpty())
    <div class="alert alert-info">
        Your cart is empty. <a href="{{ route('products.index') }}" class="alert-link">Browse products</a>.
    </div>
@else
    <div class="card mb-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Product</th><th>Price</th><th style="width: 220px;">Quantity</th><th>Line total</th><th class="text-end">Remove</th></tr>
                </thead>
                <tbody>
                    @foreach($cart->items as $item)
                        <tr>
                            <td>
                                @if($item->product)
                                    <a href="{{ route('products.show', $item->product) }}" class="text-decoration-none">{{ $item->product->name }}</a>
                                @else
                                    <span class="text-muted">Product no longer available</span>
                                @endif
                            </td>
                            <td>{{ number_format($item->product?->price ?? 0, 2) }} JOD</td>
                            <td>
                                <form action="{{ route('cart-items.update', $item) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                                           max="{{ $item->product?->stock_quantity }}" class="form-control form-control-sm" style="width: 90px;">
                                    <button class="btn btn-sm btn-outline-primary">Update</button>
                                </form>
                            </td>
                            <td>{{ number_format($item->quantity * ($item->product?->price ?? 0), 2) }} JOD</td>
                            <td class="text-end">@include('partials.delete-button', ['icon' => true, 'action' => route('cart-items.destroy', $item), 'label' => 'Remove', 'confirm' => 'Remove this item?'])</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="3" class="text-end">Total</th>
                        <th colspan="2">{{ number_format($cart->total, 2) }} JOD</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        @include('partials.delete-button', ['action' => route('carts.destroy', $cart), 'label' => 'Clear cart', 'confirm' => 'Remove everything from your cart?', 'class' => 'btn-outline-danger'])
        <a href="{{ route('orders.create') }}" class="btn btn-primary">Proceed to checkout</a>
    </div>
@endif
@endsection
