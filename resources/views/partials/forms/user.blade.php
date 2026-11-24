{{-- Params: user (optional, edit) --}}
@php($user = $user ?? null)
<form action="{{ $user ? route('users.update', $user) : route('users.store') }}" method="POST" class="card card-body">
    @csrf
    @if($user) @method('PUT') @endif

    @include('partials.forms.input', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $user?->name])
    @include('partials.forms.input', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'value' => $user?->email])
    @include('partials.forms.input', [
        'name' => 'password', 'type' => 'password', 'required' => ! $user,
        'label' => $user ? 'New password (leave empty to keep the current one)' : 'Password (min 8 characters)',
    ])
    @include('partials.forms.input', ['name' => 'phone', 'label' => 'Phone', 'value' => $user?->phone])
    @include('partials.forms.input', ['name' => 'user_img', 'label' => 'Profile image (path or URL)', 'value' => $user?->user_img])

    {{-- Only admins can change the admin flag (the controller ignores it for everyone else). --}}
    @if(auth()->user()->isAdmin())
        @include('partials.forms.checkbox', ['name' => 'is_admin', 'label' => 'Administrator', 'checked' => $user?->is_admin ?? false])
    @endif

    @include('partials.forms.actions', [
        'cancel' => auth()->user()->isAdmin() ? route('users.index') : route('users.show', auth()->user()),
        'submit' => $user ? 'Update profile' : 'Create user',
    ])
</form>
