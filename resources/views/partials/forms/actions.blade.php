{{-- Params: cancel (url), submit (label) --}}
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">{{ $submit ?? 'Save' }}</button>
    <a href="{{ $cancel }}" class="btn btn-outline-secondary">Cancel</a>
</div>
