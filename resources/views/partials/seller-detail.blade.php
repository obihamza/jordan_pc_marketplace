{{-- Param: seller (with user and available products) --}}
@php
    $me = auth()->user();
    $canEdit = $me && ($me->isAdmin() || $seller->user_id == $me->id);
@endphp

@if($canEdit && ! $seller->isApproved())
    <div class="alert alert-{{ $seller->isRejected() ? 'danger' : 'warning' }} d-flex align-items-start gap-2">
        <i class="bi {{ $seller->isRejected() ? 'bi-x-octagon-fill' : 'bi-hourglass-split' }} fs-5"></i>
        <div>
            @if($seller->isRejected())
                <strong>Application rejected.</strong>
                @if(! $me->isAdmin())
                    Edit your details and upload a new ID picture to apply again.
                @endif
            @else
                <strong>Pending review.</strong>
                @if($me->isAdmin())
                    Check the ID picture below, then approve or reject this seller.
                @else
                    An admin is checking your ID. You can list products once you are approved.
                @endif
            @endif
        </div>
    </div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">Owner</dt><dd class="col-sm-9">{{ $seller->user?->name }}</dd>
            @if($canEdit)
                <dt class="col-sm-3">National ID</dt><dd class="col-sm-9">{{ $seller->national_id }}</dd>
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9">@include('partials.status-badge', ['status' => $seller->verification_status])</dd>
                <dt class="col-sm-3">ID picture</dt>
                <dd class="col-sm-9">
                    @if($seller->national_id_image)
                        <a href="{{ route('sellers.national-id', $seller) }}" target="_blank" rel="noopener">
                            <img src="{{ route('sellers.national-id', $seller) }}" alt="National ID of {{ $seller->official_name }}" class="img-thumbnail" style="max-height: 240px;">
                        </a>
                    @else
                        <span class="text-muted">No picture uploaded.</span>
                    @endif
                </dd>
            @endif
            <dt class="col-sm-3">Available products</dt><dd class="col-sm-9">{{ $seller->products->count() }}</dd>
        </dl>
    </div>
    @if($canEdit)
        <div class="card-footer d-flex gap-2">
            <a class="jo-icon-btn" href="{{ route('sellers.edit', $seller) }}" title="Edit profile" aria-label="Edit profile"><i class="bi bi-pencil-square"></i></a>
            @if($me->isAdmin())
                <span class="ms-auto d-flex gap-2">@include('partials.seller-review-buttons', ['seller' => $seller])</span>
                @include('partials.delete-button', ['icon' => true, 'action' => route('sellers.destroy', $seller), 'label' => 'Delete seller'])
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
