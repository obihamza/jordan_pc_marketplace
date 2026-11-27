{{-- Params: user (optional, edit) --}}
@php($user = $user ?? null)
<form action="{{ $user ? route('users.update', $user) : route('users.store') }}" method="POST" enctype="multipart/form-data" class="card card-body">
    @csrf
    @if($user) @method('PUT') @endif

    @include('partials.forms.input', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $user?->name])
    @include('partials.forms.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $user?->email])
    @include('partials.forms.input', [
        'name' => 'password', 'type' => 'password', 'required' => ! $user,
        'label' => $user ? 'New password (leave empty to keep the current one)' : 'Password (min 8 characters)',
    ])
    @include('partials.forms.input', ['name' => 'phone', 'label' => 'Phone', 'value' => $user?->phone])
    <div class="mb-3">
        <label for="avatar" class="form-label">Profile photo (JPG, PNG or WebP, max 2 MB)</label>
        <div class="d-flex align-items-center gap-3">
            <span class="jo-avatar jo-avatar-lg" id="avatarPreview">
                @if($user?->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="">
                @else
                    {{ $user ? mb_strtoupper(mb_substr($user->name, 0, 1)) : '?' }}
                @endif
            </span>
            <div class="flex-grow-1">
                <input type="file" name="avatar" id="avatar" accept="image/png,image/jpeg,image/webp"
                       class="form-control @error('avatar') is-invalid @enderror">
                @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if($user?->user_img)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_avatar" id="remove_avatar" value="1">
                        <label class="form-check-label" for="remove_avatar">Remove the current photo</label>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @once
        @push('scripts')
        <script>
            // Shows the chosen photo before saving.
            document.getElementById('avatar').addEventListener('change', function () {
                var file = this.files[0], box = document.getElementById('avatarPreview');
                if (! file || ! file.type.startsWith('image/')) { return; }
                var img = document.createElement('img');
                img.alt = '';
                img.src = URL.createObjectURL(file);
                box.textContent = '';
                box.appendChild(img);
            });
        </script>
        @endpush
    @endonce

    {{-- Only admins can change the admin flag (the controller ignores it for everyone else). --}}
    @if(auth()->user()->isAdmin())
        @include('partials.forms.checkbox', ['name' => 'is_admin', 'label' => 'Administrator', 'checked' => $user?->is_admin ?? false])
    @endif

    @include('partials.forms.actions', [
        'cancel' => auth()->user()->isAdmin() ? route('users.index') : route('users.show', auth()->user()),
        'submit' => $user ? 'Update profile' : 'Create user',
    ])
</form>
