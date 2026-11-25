{{-- Params: title, url (optional), label (optional), back (optional), modal (optional: id of a pop-up to open instead of linking to url) --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">{{ $title }}</h1>

    <div class="d-flex gap-2">
        @isset($back)
            <a href="{{ $back }}" class="btn btn-outline-secondary">Back</a>
        @endisset

        @isset($modal)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="{{ $modal }}">{{ $label ?? 'New' }}</button>
        @elseif(isset($url))
            <a href="{{ $url }}" class="btn btn-primary">{{ $label ?? 'New' }}</a>
        @endisset
    </div>
</div>
