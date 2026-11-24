@extends('layouts.app')

@section('title', 'Coupon '.$coupon->code)

@section('content')
@include('partials.page-header', ['title' => 'Coupon '.$coupon->code, 'back' => route('coupons.index')])

<div class="card">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Discount</dt>
            <dd class="col-sm-9">
                @if($coupon->discount_type === 'percent')
                    {{ rtrim(rtrim(number_format($coupon->discount_value, 2), '0'), '.') }}%
                @else
                    {{ number_format($coupon->discount_value, 2) }} JOD
                @endif
            </dd>
            <dt class="col-sm-3">Minimum order</dt><dd class="col-sm-9">{{ number_format($coupon->min_order_amount, 2) }} JOD</dd>
            <dt class="col-sm-3">Usage</dt><dd class="col-sm-9">{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' of '.$coupon->usage_limit : ' (no limit)' }}</dd>
            <dt class="col-sm-3">Valid</dt><dd class="col-sm-9">{{ $coupon->starts_at?->format('Y-m-d H:i') }} &rarr; {{ $coupon->ends_at?->format('Y-m-d H:i') ?? 'no end date' }}</dd>
            <dt class="col-sm-3">Active</dt><dd class="col-sm-9">{{ $coupon->is_active ? 'Yes' : 'No' }}</dd>
            <dt class="col-sm-3">Orders using it</dt><dd class="col-sm-9">{{ $coupon->orders_count }}</dd>
        </dl>
    </div>
    <div class="card-footer d-flex gap-2">
        <a href="{{ route('coupons.edit', $coupon) }}" class="btn btn-sm btn-primary">Edit</a>
        @include('partials.delete-button', ['action' => route('coupons.destroy', $coupon)])
    </div>
</div>
@endsection
