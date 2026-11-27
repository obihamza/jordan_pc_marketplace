@php($type = $type ?? 'text')
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
           value="{{ $type === 'password' ? '' : old($name, $value ?? '') }}"
           class="form-control @error($name) is-invalid @enderror"
           @if($required ?? false) required @endif
           @isset($step) step="{{ $step }}" @endisset
           @isset($min) min="{{ $min }}" @endisset
           @if($type === 'password') autocomplete="new-password" @endif>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
