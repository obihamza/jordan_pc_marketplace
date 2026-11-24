{{-- Params: addresses (paginator), showUser (optional) --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @if($showUser ?? false)<th>User</th>@endif
                    <th>Address</th>
                    <th>Postal code</th>
                    <th>Country</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($addresses as $address)
                    <tr>
                        @if($showUser ?? false)<td>{{ $address->user?->name }}</td>@endif
                        <td>{{ $address->address_line }}</td>
                        <td>{{ $address->postal_code }}</td>
                        <td>{{ $address->country }}</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('addresses.edit', $address) }}">Edit</a>
                            @include('partials.delete-button', ['action' => route('addresses.destroy', $address)])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No addresses found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $addresses->links() }}</div>
