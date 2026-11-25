{{-- Flash messages appear as toasts (top right): green = success, red = danger action (delete, remove, cancel) --}}
@php
    $toasts = array_filter([
        ['type' => 'success', 'icon' => 'bi-check-circle-fill', 'title' => 'Done', 'text' => session('success')],
        ['type' => 'danger', 'icon' => 'bi-exclamation-octagon-fill', 'title' => 'Removed', 'text' => session('danger')],
    ], fn ($t) => filled($t['text']));
@endphp

@if($toasts)
    <div class="toast-container position-fixed top-0 end-0 p-3 jo-toasts">
        @foreach($toasts as $t)
            <div class="toast align-items-center text-bg-{{ $t['type'] }} border-0 jo-toast" role="{{ $t['type'] === 'danger' ? 'alert' : 'status' }}"
                 aria-live="{{ $t['type'] === 'danger' ? 'assertive' : 'polite' }}" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi {{ $t['icon'] }}" aria-hidden="true"></i>
                        <span>{{ $t['text'] }}</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // Bootstrap's JS is loaded with `defer`, so wait for it before showing the toasts.
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.jo-toast').forEach(function (el) {
                if (window.bootstrap) { bootstrap.Toast.getOrCreateInstance(el).show(); }
                else { el.classList.add('show'); }
            });
        });
    </script>
@endif

{{-- Validation errors stay inline above the page content --}}
@if($errors->any() && ! old('_modal'))
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
