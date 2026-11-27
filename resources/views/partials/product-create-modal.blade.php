@if(isset($modalCategories))
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="productModalLabel"><i class="bi bi-plus-circle text-orange"></i> New product</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('partials.forms.product', ['categories' => $modalCategories, 'sellers' => $modalSellers ?? null, 'modal' => true])
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="productCreateForm" class="btn btn-primary">Create product</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var open = @json(request('add') === 'product' || session('open_product_modal') || ($errors->any() && old('_modal') === 'product'));
        if (open && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById('productModal')).show();
        }
    });
</script>
@endif
