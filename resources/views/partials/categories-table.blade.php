{{-- Params: categories (paginator, withCount products) --}}
@php($isAdmin = auth()->user()?->isAdmin() ?? false)
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td><a href="{{ route('categories.show', $category) }}" class="fw-semibold text-decoration-none">{{ $category->name }}</a></td>
                        <td>{{ $category->description }}</td>
                        <td>{{ $category->products_count }}</td>
                        <td class="text-end text-nowrap">
                            <a class="jo-icon-btn" href="{{ route('categories.show', $category) }}" title="Browse" aria-label="Browse"><i class="bi bi-eye"></i></a>
                            @if($isAdmin)
                                <a class="jo-icon-btn" href="{{ route('categories.edit', $category) }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square"></i></a>
                                @include('partials.delete-button', ['icon' => true, 'action' => route('categories.destroy', $category)])
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $categories->links() }}</div>
