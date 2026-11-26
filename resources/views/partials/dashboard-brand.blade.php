{{-- Logo block at the top of the dashboard side bar. Param: closeable (small-screen pop-out gets a close button) --}}
<div class="jo-sidebar-brand">
    <a href="{{ $panelHome }}" class="d-flex align-items-center gap-2 text-decoration-none">
        <img src="{{ asset('images/jo-logo-mark.svg') }}" alt="" style="height: 36px;">
        <span class="fw-bold fs-5 text-white">JO-<span class="text-orange">Marketplace</span></span>
    </a>
    @if($closeable ?? false)
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#adminSide" aria-label="Close"></button>
    @endif
</div>
