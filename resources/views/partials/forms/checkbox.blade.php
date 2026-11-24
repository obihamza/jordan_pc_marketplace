{{-- Params: name, label, checked --}}
<div class="form-check mb-3">
    {{-- The hidden 0 makes an unchecked box submit "0" instead of nothing. --}}
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" class="form-check-input"
           @checked(old($name, $checked ?? false))>
    <label for="{{ $name }}" class="form-check-label">{{ $label }}</label>
</div>
