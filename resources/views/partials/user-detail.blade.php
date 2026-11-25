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
                <dl class="row mb-0">
                    <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $user->name }}</dd>
                    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $user->email }}</dd>
                    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $user->phone ?: '-' }}</dd>
                    <dt class="col-sm-4">Role</dt><dd class="col-sm-8"><span class="badge text-bg-secondary">{{ $roleLabel }}</span></dd>
                    <dt class="col-sm-4">Joined</dt><dd class="col-sm-8">{{ $user->created_at?->format('Y-m-d') }}</dd>
                </dl>
            </div>
            <div class="card-footer d-flex flex-wrap gap-2">
                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">Edit {{ $isOwn ? 'my profile' : 'user' }}</a>
                @include('partials.delete-button', [
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

    {{-- Seller profile: only shown when this user really is a seller --}}
    @if($user->seller)
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">Seller profile</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Shop name</dt>
                        <dd class="col-sm-8"><a href="{{ route('sellers.show', $user->seller) }}" class="text-decoration-none">{{ $user->seller->official_name }}</a></dd>
                        <dt class="col-sm-4">National ID</dt><dd class="col-sm-8">{{ $user->seller->national_id }}</dd>
                    </dl>
                </div>
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
