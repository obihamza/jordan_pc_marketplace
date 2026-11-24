{{-- Params: coupon (optional, edit) --}}
@php($coupon = $coupon ?? null)
<form action="{{ $coupon ? route('coupons.update', $coupon) : route('coupons.store') }}" method="POST" class="card card-body">
    @csrf
    @if($coupon) @method('PUT') @endif

    @include('partials.forms.input', ['name' => 'code', 'label' => 'Code', 'required' => true, 'value' => $coupon?->code])

    <div class="row">
        <div class="col-md-6">
            @include('partials.forms.select', [
                'name' => 'discount_type', 'label' => 'Discount type', 'required' => true,
                'options' => ['fixed' => 'Fixed amount (JOD)', 'percent' => 'Percent (%)'],
                'selected' => $coupon?->discount_type ?? 'fixed',
            ])
        </div>
        <div class="col-md-6">
            @include('partials.forms.input', ['name' => 'discount_value', 'label' => 'Discount value', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'required' => true, 'value' => $coupon?->discount_value])
        </div>
        <div class="col-md-6">
            @include('partials.forms.input', ['name' => 'min_order_amount', 'label' => 'Minimum order amount (JOD)', 'type' => 'number', 'step' => '0.01', 'min' => 0, 'required' => true, 'value' => $coupon?->min_order_amount ?? 0])
        </div>
        <div class="col-md-6">
            @include('partials.forms.input', ['name' => 'usage_limit', 'label' => 'Usage limit (empty = unlimited)', 'type' => 'number', 'min' => 1, 'value' => $coupon?->usage_limit])
        </div>
        <div class="col-md-6">
            @include('partials.forms.input', ['name' => 'starts_at', 'label' => 'Starts at', 'type' => 'datetime-local', 'required' => true, 'value' => ($coupon?->starts_at ?? now())->format('Y-m-d\TH:i')])
        </div>
        <div class="col-md-6">
            @include('partials.forms.input', ['name' => 'ends_at', 'label' => 'Ends at (optional)', 'type' => 'datetime-local', 'value' => $coupon?->ends_at?->format('Y-m-d\TH:i')])
        </div>
    </div>

    @include('partials.forms.checkbox', ['name' => 'is_active', 'label' => 'Active', 'checked' => $coupon?->is_active ?? true])

    @include('partials.forms.actions', ['cancel' => route('coupons.index'), 'submit' => $coupon ? 'Update coupon' : 'Create coupon'])
</form>
