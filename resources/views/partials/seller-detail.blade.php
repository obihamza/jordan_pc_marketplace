{{-- Param: seller (with user and available products) --}}
@php
    $me = auth()->user();
    $canEdit = $me->isAdmin() || $seller->user_id == $me->id;
@endphp

<div class="card mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Owner</dt><dd class="col-sm-9">{{ $seller->user?->name }}</dd>
            @if($canEdit)
                <dt class="col-sm-3">National ID</dt><dd class="col-sm-9">{{ $seller->national_id }}</dd>
            @endif
            <dt class="col-sm-3">Available products</dt><dd class="col-sm-9">{{ $seller->products->count() }}</dd>
        </dl>
    </div>
    @if($canEdit)
        <div class="card-footer d-flex gap-2">
            <a href="{{ route('sellers.edit', $seller) }}" class="btn btn-sm btn-primary">Edit profile</a>
            @if($me->isAdmin())
                @include('partials.delete-button', ['action' => route('sellers.destroy', $seller), 'label' => 'Delete seller'])
            @endif
        </div>
    @endif
</div>

<h2 class="h5">Products</h2>
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
    @forelse($seller->products as $product)
        <div class="col">
            @include('partials.product-card', ['product' => $product])
        </div>
    @empty
        <div class="col-12"><div class="alert alert-info mb-0">This seller has no available products.</div></div>
    @endforelse
</div>
