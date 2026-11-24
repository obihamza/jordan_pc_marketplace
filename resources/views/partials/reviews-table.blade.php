{{-- Params: reviews (paginator, with product loaded), showUser (optional) --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    @if($showUser ?? false)<th>Reviewer</th>@endif
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    @php
                        $isMine = $review->user_id == auth()->id();
                        $isAdmin = auth()->user()->isAdmin();
                    @endphp
                    <tr>
                        <td>
                            @if($review->product)
                                <a href="{{ route('products.show', $review->product) }}" class="text-decoration-none">{{ $review->product->name }}</a>
                            @else
                                <span class="text-muted">Removed product</span>
                            @endif
                        </td>
                        @if($showUser ?? false)<td>{{ $review->user?->name }}</td>@endif
                        <td class="text-warning text-nowrap">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                        <td style="max-width: 360px;">{{ \Illuminate\Support\Str::limit($review->comment, 120) }}</td>
                        <td>{{ $review->created_at?->format('Y-m-d') }}</td>
                        <td class="text-end text-nowrap">
                            @if($isMine && ! $isAdmin)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('reviews.edit', $review) }}">Edit</a>
                            @endif
                            @if($isMine || $isAdmin)
                                @include('partials.delete-button', ['action' => route('reviews.destroy', $review)])
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No reviews found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $reviews->links() }}</div>
