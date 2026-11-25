{{-- "New category" pop-up (admin and approved sellers). Opened by any [data-bs-target="#categoryModal"] button. --}}
@if(isset($modalCategories))
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="categoryModalLabel"><i class="bi bi-tags text-orange"></i> New category</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('partials.forms.category', ['modal' => true])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="categoryCreateForm" class="btn btn-primary">Create category</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Open the pop-up when asked, or again after a validation error inside it.
    document.addEventListener('DOMContentLoaded', function () {
        var open = @json((bool) session('open_category_modal') || ($errors->any() && old('_modal') === 'category'));
        if (open && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('categoryModal')).show();
        }
    });
</script>
@endif
