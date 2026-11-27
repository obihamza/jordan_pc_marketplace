<div class="modal fade" id="imageSettingsModal" tabindex="-1" aria-labelledby="imageSettingsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="imageSettingsForm" method="POST" action="">
                @csrf
                @method('PATCH')

                <div class="modal-header">
                    <h2 class="modal-title h5" id="imageSettingsTitle"><i class="bi bi-image text-orange"></i> Picture settings</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="text-center bg-body-tertiary rounded-3 p-2 mb-3">
                        <img id="imageSettingsPreview" src="" alt="" class="img-fluid rounded" style="max-height: 320px; object-fit: contain;">
                    </div>

                    <div class="small text-muted mb-3">Product: <strong id="imageSettingsProduct"></strong></div>

                    <input type="hidden" name="is_primary" value="0">
                    <div class="form-check form-switch fs-5">
                        <input class="form-check-input" type="checkbox" role="switch" name="is_primary" value="1" id="imageSettingsPrimary">
                        <label class="form-check-label" for="imageSettingsPrimary">Primary picture</label>
                    </div>
                    <div class="form-text">
                        The primary picture is the one shown on the product card, the home page and first on the product page.
                        Only one picture per product can be primary: choosing this one replaces the current primary.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-image-settings]');
            if (! trigger || ! window.bootstrap) { return; }

            e.preventDefault();

            document.getElementById('imageSettingsForm').action = trigger.dataset.action;
            document.getElementById('imageSettingsPreview').src = trigger.dataset.src;
            document.getElementById('imageSettingsProduct').textContent = trigger.dataset.product || '';
            document.getElementById('imageSettingsPrimary').checked = trigger.dataset.primary === '1';

            bootstrap.Modal.getOrCreateInstance(document.getElementById('imageSettingsModal')).show();
        });
    })();
</script>
