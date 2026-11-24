{{-- Params: sellers (paginator, with user, withCount products) --}}
@php($isAdmin = auth()->user()->isAdmin())
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Seller</th>
                    <th>Owner</th>
                    @if($isAdmin)<th>National ID</th>@endif
                    <th>Products</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $seller)
                    <tr>
                        <td><a href="{{ route('sellers.show', $seller) }}" class="fw-semibold text-decoration-none">{{ $seller->official_name }}</a></td>
                        <td>{{ $seller->user?->name }}</td>
                        @if($isAdmin)<td>{{ $seller->national_id }}</td>@endif
                        <td>{{ $seller->products_count }}</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('sellers.show', $seller) }}">View</a>
                            @if($isAdmin || $seller->user_id == auth()->id())
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('sellers.edit', $seller) }}">Edit</a>
                            @endif
                            @if($isAdmin)
                                @include('partials.delete-button', ['action' => route('sellers.destroy', $seller)])
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">No sellers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $sellers->links() }}</div>
