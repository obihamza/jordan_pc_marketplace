{{-- Product filter bar. Param: categories --}}
@php
    $hasFilters = request()->hasAny(['q', 'category_id', 'min_price', 'max_price']) || request('sort') === 'oldest';
@endphp

<form action="{{ route('products.index') }}" method="GET" class="card card-body mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-3">
            <label for="f_q" class="form-label small mb-1">Search by name</label>
            <input type="search" name="q" id="f_q" value="{{ request('q') }}" class="form-control" placeholder="e.g. laptop">
        </div>

        <div class="col-12 col-lg-2">
            <label for="f_category" class="form-label small mb-1">Category</label>
            <select name="category_id" id="f_category" class="form-select">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 col-lg-2">
            <label for="f_min" class="form-label small mb-1">Min price (JOD)</label>
            <input type="number" name="min_price" id="f_min" value="{{ request('min_price') }}" min="0" step="0.01" class="form-control" placeholder="0">
        </div>

        <div class="col-6 col-lg-2">
            <label for="f_max" class="form-label small mb-1">Max price (JOD)</label>
            <input type="number" name="max_price" id="f_max" value="{{ request('max_price') }}" min="0" step="0.01" class="form-control" placeholder="Any">
        </div>

        <div class="col-12 col-lg-2">
            <label for="f_sort" class="form-label small mb-1">Sort by</label>
            <select name="sort" id="f_sort" class="form-select">
                <option value="newest" @selected(request('sort', 'newest') !== 'oldest')>Newest first</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
            </select>
        </div>

        <div class="col-12 col-lg-1 d-grid">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </div>

    @if($hasFilters)
        <div class="mt-2">
            <a href="{{ route('products.index') }}" class="small text-decoration-none">&times; Clear filters</a>
        </div>
    @endif
</form>
