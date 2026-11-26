{{-- Params: products (paginator).
     Lazy loading: the next page is fetched and appended when the visitor scrolls near the bottom
     (script below, once per page). Without JavaScript the normal page links still work. --}}
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3" data-lazy-grid data-next="{{ $products->nextPageUrl() }}">
    @forelse($products as $product)
        <div class="col">
            @include('partials.product-card', ['product' => $product])
        </div>
    @empty
        <div class="col-12"><div class="alert alert-info mb-0">No products found.</div></div>
    @endforelse
</div>

{{-- Shown while the next page loads (only when there is a next page) --}}
@if($products->hasMorePages())
    <div class="text-center text-muted py-4" data-lazy-status hidden>
        <div class="spinner-border spinner-border-sm text-warning me-2" role="status" aria-hidden="true"></div>
        <span data-lazy-text>Loading more products...</span>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-2" data-lazy-retry hidden>Try again</button>
    </div>
@endif

<div class="mt-3" data-lazy-pagination>{{ $products->links() }}</div>

@once
    @push('scripts')
    <script>
        // Lazy loading of product grids (infinite scroll).
        (function () {
            var grids = document.querySelectorAll('[data-lazy-grid]');
            if (! grids.length || ! ('IntersectionObserver' in window)) { return; }   // keep the page links

            grids.forEach(function (grid, index) {
                if (! grid.dataset.next) { return; }

                var status = grid.nextElementSibling;                   // the "loading" row
                var pager = status ? status.nextElementSibling : null;  // the normal page links
                if (! status || ! status.hasAttribute('data-lazy-status')) { return; }

                var text = status.querySelector('[data-lazy-text]');
                var retry = status.querySelector('[data-lazy-retry]');
                var spinner = status.querySelector('.spinner-border');
                var loading = false;

                if (pager) { pager.hidden = true; }     // the scroll replaces the page links
                status.hidden = false;

                function finish() {
                    observer.disconnect();
                    status.hidden = true;
                }

                function loadMore() {
                    if (loading || ! grid.dataset.next) { return; }
                    loading = true;
                    retry.hidden = true;
                    spinner.hidden = false;
                    text.textContent = 'Loading more products...';

                    fetch(grid.dataset.next, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                        .then(function (res) { if (! res.ok) { throw new Error('bad response'); } return res.text(); })
                        .then(function (html) {
                            var doc = new DOMParser().parseFromString(html, 'text/html');
                            var next = doc.querySelectorAll('[data-lazy-grid]')[index];
                            if (! next) { throw new Error('grid not found'); }

                            Array.prototype.slice.call(next.children).forEach(function (item) {
                                grid.appendChild(document.importNode(item, true));
                            });

                            grid.dataset.next = next.dataset.next || '';
                            loading = false;

                            if (! grid.dataset.next) { finish(); }
                        })
                        .catch(function () {
                            loading = false;
                            spinner.hidden = true;
                            text.textContent = 'Could not load more products.';
                            retry.hidden = false;
                        });
                }

                var observer = new IntersectionObserver(function (entries) {
                    if (entries[0].isIntersecting) { loadMore(); }
                }, { rootMargin: '400px 0px' });

                observer.observe(status);
                retry.addEventListener('click', loadMore);
            });
        })();
    </script>
    @endpush
@endonce
