{{-- Param: user (with seller, addresses) --}}
@php
    $me = auth()->user();
    $isOwn = $user->id == $me->id;
    $roleLabel = $user->is_admin ? 'Admin' : ($user->seller ? 'Seller' : 'Buyer');
@endphp

<div class="row g-3">
    <div class="{{ $user->seller ? 'col-lg-6' : 'col-12' }}">
        <div class="card h-100">
            <div class="card-header fw-semibold">Account</div>
            <div class="card-body">
                <span class="jo-avatar jo-avatar-lg mb-3">
                    @if($user->avatarUrl())<img src="{{ $user->avatarUrl() }}" alt="">@else{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}@endif
                </span>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $user->name }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $user->email }}</dd>
                    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $user->phone ?: '-' }}</dd>
                    <dt class="col-sm-4">Role</dt><dd class="col-sm-8"><span class="badge text-bg-secondary">{{ $roleLabel }}</span></dd>
                    <dt class="col-sm-4">Joined</dt><dd class="col-sm-8">{{ $user->created_at?->format('Y-m-d') }}</dd>
                </dl>
            </div>
            <div class="card-footer d-flex flex-wrap gap-2">
                <a class="jo-icon-btn" href="{{ route('users.edit', $user) }}" title="Edit {{ $isOwn ? 'my profile' : 'user' }}" aria-label="Edit {{ $isOwn ? 'my profile' : 'user' }}"><i class="bi bi-pencil-square"></i></a>
                @include('partials.delete-button', ['icon' => true, 
                    'action' => route('users.destroy', $user),
                    'label' => $isOwn ? 'Delete my account' : 'Delete user',
                    'confirm' => $isOwn ? 'Delete your account?' : 'Delete this user?',
                ])
                @if($isOwn && ! $user->is_admin && ! $user->seller)
                    <a href="{{ route('sellers.create') }}" class="btn btn-sm btn-warning ms-auto">Become a seller</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Seller profile: only shown when this user really is a seller (or applied to be one) --}}
    @if($user->seller)
        @php $seller = $user->seller; @endphp
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Seller profile</span>
                    @include('partials.status-badge', ['status' => $seller->verification_status])
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Shop name</dt>
                        <dd class="col-sm-8">
                            @if($seller->isApproved())
                                <a href="{{ route('sellers.show', $seller) }}" class="text-decoration-none">{{ $seller->official_name }}</a>
                            @else
                                {{ $seller->official_name }}
                            @endif
                        </dd>
                        @if($isOwn || $me->isAdmin())
                            <dt class="col-sm-4">National ID</dt><dd class="col-sm-8">{{ $seller->national_id }}</dd>
                            <dt class="col-sm-4">ID picture</dt>
                            <dd class="col-sm-8">
                                @if($seller->national_id_image)
                                    <a href="{{ route('sellers.national-id', $seller) }}" target="_blank" rel="noopener">
                                        <img src="{{ route('sellers.national-id', $seller) }}" alt="National ID" class="img-thumbnail" loading="lazy" decoding="async" style="max-height: 120px;">
                                    </a>
                                @else
                                    <span class="text-muted">No picture uploaded.</span>
                                @endif
                            </dd>
                        @endif
                    </dl>

                    @if($isOwn && ! $seller->isApproved())
                        <div class="alert alert-{{ $seller->isRejected() ? 'danger' : 'warning' }} py-2 small mt-3 mb-0">
                            @if($seller->isRejected())
                                Your application was rejected. Edit your details and upload a new ID picture to apply again.
                            @else
                                Pending review: an admin is checking your ID. You can list products once you are approved.
                            @endif
                        </div>
                    @endif
                </div>
                @if($isOwn || $me->isAdmin())
                    <div class="card-footer d-flex flex-wrap align-items-center gap-2">
                        <a class="jo-icon-btn" href="{{ route('sellers.edit', $seller) }}" title="Edit seller profile" aria-label="Edit seller profile"><i class="bi bi-pencil-square"></i></a>
                        @if($seller->isApproved())
                            <a href="{{ route('sellers.show', $seller) }}" class="btn btn-sm btn-outline-secondary ms-auto"><i class="bi bi-shop"></i> Public shop page</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Addresses</span>
        @if($isOwn || $me->isAdmin())
            <a href="{{ route('addresses.create') }}" class="btn btn-sm btn-outline-primary">Add address</a>
        @endif
    </div>
    <ul class="list-group list-group-flush">
        @forelse($user->addresses as $address)
            <li class="list-group-item">
                {{ $address->address_line }}
                <span class="text-muted">{{ $address->postal_code }} {{ $address->country }}</span>
            </li>
        @empty
            <li class="list-group-item text-muted">No addresses yet.</li>
        @endforelse
    </ul>
</div>
