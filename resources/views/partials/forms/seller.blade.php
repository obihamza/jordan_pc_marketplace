{{-- Params: seller (optional, edit), users (optional, admin create) --}}
@php($seller = $seller ?? null)
<form action="{{ $seller ? route('sellers.update', $seller) : route('sellers.store') }}" method="POST" class="card card-body">
    @csrf
    @if($seller) @method('PUT') @endif

    @isset($users)
        @include('partials.forms.select', [
            'name' => 'user_id', 'label' => 'User', 'required' => true, 'placeholder' => '-- choose user --',
            'options' => $users->mapWithKeys(fn ($u) => [$u->id => $u->name.' ('.$u->email.')']),
        ])
    @endisset

    @include('partials.forms.input', ['name' => 'official_name', 'label' => 'Official (shop) name', 'required' => true, 'value' => $seller?->official_name])
    @include('partials.forms.input', ['name' => 'national_id', 'label' => 'National ID', 'required' => true, 'value' => $seller?->national_id])

    @include('partials.forms.actions', ['cancel' => route('sellers.index'), 'submit' => $seller ? 'Update seller profile' : 'Create seller profile'])
</form>
