<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 d-flex align-items-center gap-2" id="confirmModalTitle">
                    <i class="bi bi-exclamation-triangle-fill text-danger" aria-hidden="true"></i>
                    <span id="confirmModalHeading">Please confirm</span>
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmModalText">Are you sure?</div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmModalYes">Yes, continue</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var pending = null;

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (! form.matches || ! form.matches('form[data-confirm]') || form.dataset.confirmed === '1') { return; }

            e.preventDefault();

            var modalEl = document.getElementById('confirmModal');
            if (! window.bootstrap) {
                if (window.confirm(form.dataset.confirm)) { form.dataset.confirmed = '1'; form.submit(); }
                return;
            }

            pending = form;
            document.getElementById('confirmModalHeading').textContent = form.dataset.confirmTitle || 'Please confirm';
            document.getElementById('confirmModalText').textContent = form.dataset.confirm;
            document.getElementById('confirmModalYes').textContent = form.dataset.confirmButton || 'Yes, continue';
            document.getElementById('confirmModalYes').disabled = false;
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        document.getElementById('confirmModalYes').addEventListener('click', function () {
            if (! pending) { return; }
            this.disabled = true;
            pending.dataset.confirmed = '1';
            pending.submit();
        });

        document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function () { pending = null; });
    })();
</script>
