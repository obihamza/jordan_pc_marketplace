@php
    $seller = $seller ?? null;
    $isAdminForm = isset($users) || (auth()->user()?->isAdmin() ?? false);

    $imageRequired = ! $isAdminForm && (! $seller || $seller->isRejected());
@endphp
<form action="{{ $seller ? route('sellers.update', $seller) : route('sellers.store') }}" method="POST" enctype="multipart/form-data" class="card card-body">
    @csrf
    @if($seller) @method('PUT') @endif

    @isset($users)
        @include('partials.forms.select', [
            'name' => 'user_id', 'label' => 'User', 'required' => true, 'placeholder' => '-- choose user --',
            'options' => $users->mapWithKeys(fn ($u) => [$u->id => $u->name.' ('.$u->email.')']),
        ])
    @endisset

    @include('partials.forms.input', ['name' => 'official_name', 'label' => 'Official (shop) name', 'required' => true, 'value' => $seller?->official_name])
    @include('partials.forms.input', ['name' => 'national_id', 'label' => 'National ID number', 'required' => true, 'value' => $seller?->national_id])

    <div class="mb-3">
        <label for="national_id_image" class="form-label">National ID picture @if($imageRequired)<span class="text-danger">*</span>@endif</label>
        <input type="file" name="national_id_image" id="national_id_image" accept="image/png,image/jpeg"
               class="form-control @error('national_id_image') is-invalid @enderror" @if($imageRequired) required @endif>
        @error('national_id_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">
            A clear photo of your national ID (JPG or PNG, up to 4 MB). Only you and the admin can see it.
            @if($seller && $seller->national_id_image) Leave empty to keep the current picture. @endif
        </div>
    </div>

    @unless($isAdminForm)
        <div class="alert alert-warning py-2 small">
            Your application stays <strong>pending</strong> until an admin checks your ID and approves it.
            You can't list products before that.
        </div>
    @endunless

    @include('partials.forms.actions', ['cancel' => route('sellers.index'), 'submit' => $seller ? 'Update seller profile' : ($isAdminForm ? 'Create seller profile' : 'Send application')])
</form>
