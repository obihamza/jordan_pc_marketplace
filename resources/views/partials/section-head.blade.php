{{-- Section heading with the orange underline and a "View all" link. Params: title (HTML allowed via titleHtml), url (optional), label (optional) --}}
<div class="jo-section-head d-flex justify-content-between align-items-end mb-3">
    <h2 class="h4 jo-section-title mb-0">{!! $titleHtml ?? e($title) !!}</h2>
    @isset($url)
        <a href="{{ $url }}" class="small text-decoration-none fw-semibold">{{ $label ?? 'View all' }} <i class="bi bi-chevron-right"></i></a>
    @endisset
</div>
