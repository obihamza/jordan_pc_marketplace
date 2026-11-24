{{-- Navbar brand: full logo on desktop (lg and up), compact mark + name on small screens --}}
<a class="navbar-brand d-flex align-items-center gap-2 py-1" href="{{ route('home') }}">
    <img src="{{ asset('images/jo-logo.svg') }}" alt="JO-Marketplace" class="jo-logo d-none d-lg-block">

    <img src="{{ asset('images/jo-logo-mark.svg') }}" alt="" class="jo-logo d-lg-none" style="height: 32px;">
    <span class="fw-bold d-lg-none">JO-<span class="text-orange">Marketplace</span></span>
</a>
