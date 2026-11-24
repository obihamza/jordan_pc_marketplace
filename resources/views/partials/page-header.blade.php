{{-- Params: title, url (optional), label (optional), back (optional) --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">{{ $title }}</h1>

    <div class="d-flex gap-2">
        @isset($back)
            <a href="{{ $back }}" class="btn btn-outline-secondary">Back</a>
        @endisset

        @isset($url)
            <a href="{{ $url }}" class="btn btn-primary">{{ $label ?? 'New' }}</a>
        @endisset
    </div>
</div>
