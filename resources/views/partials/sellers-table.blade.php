@php($isAdmin = auth()->user()?->isAdmin() ?? false)
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Seller</th>
                    <th>Owner</th>
                    @if($isAdmin)<th>National ID</th><th>Status</th>@endif
                    <th>Products</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $seller)
                    <tr>
                        <td><a href="{{ route('sellers.show', $seller) }}" class="fw-semibold text-decoration-none">{{ $seller->official_name }}</a></td>
                        <td>{{ $seller->user?->name }}</td>
                        @if($isAdmin)
                            <td>{{ $seller->national_id }}</td>
                            <td>@include('partials.status-badge', ['status' => $seller->verification_status])</td>
                        @endif
                        <td>{{ $seller->products_count }}</td>
                        <td class="text-end text-nowrap">
                            <a class="jo-icon-btn" href="{{ route('sellers.show', $seller) }}" title="View" aria-label="View"><i class="bi bi-eye"></i></a>
                            @if($isAdmin || (auth()->check() && $seller->user_id == auth()->id()))
                                <a class="jo-icon-btn" href="{{ route('sellers.edit', $seller) }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square"></i></a>
                            @endif
                            @if($isAdmin)
                                @include('partials.seller-review-buttons', ['seller' => $seller, 'icons' => true])
                                @include('partials.delete-button', ['icon' => true, 'action' => route('sellers.destroy', $seller)])
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $isAdmin ? 6 : 4 }}" class="text-center text-muted py-4">No sellers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $sellers->links() }}</div>
