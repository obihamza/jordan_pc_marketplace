{{-- Params: address (optional, edit), users (optional, admin create) --}}
@php($address = $address ?? null)
<form action="{{ $address ? route('addresses.update', $address) : route('addresses.store') }}" method="POST" class="card card-body">
    @csrf
    @if($address) @method('PUT') @endif

    @isset($users)
        @include('partials.forms.select', [
            'name' => 'user_id', 'label' => 'User', 'required' => true, 'placeholder' => '-- choose user --',
            'options' => $users->mapWithKeys(fn ($u) => [$u->id => $u->name.' ('.$u->email.')']),
        ])
    @endisset

    @include('partials.forms.input', ['name' => 'address_line', 'label' => 'Address line', 'required' => true, 'value' => $address?->address_line])
    @include('partials.forms.input', ['name' => 'postal_code', 'label' => 'Postal code', 'value' => $address?->postal_code])
    @include('partials.forms.input', ['name' => 'country', 'label' => 'Country', 'value' => $address?->country ?? 'Jordan'])

    @include('partials.forms.actions', ['cancel' => route('addresses.index'), 'submit' => $address ? 'Update address' : 'Add address'])
</form>
