{{-- Navbar brand: full logo on desktop (xl and up), compact mark + name on smaller screens --}}
<a class="navbar-brand d-flex align-items-center gap-2 py-1 me-xl-4" href="{{ route('home') }}">
    <img src="{{ asset('images/jo-logo.svg') }}" alt="JO-Marketplace" class="jo-logo d-none d-xl-block">

    <img src="{{ asset('images/jo-logo-mark.svg') }}" alt="" class="jo-logo d-xl-none" style="height: 32px;">
    <span class="fw-bold d-xl-none">JO-<span class="text-orange">Marketplace</span></span>
</a>
