{{-- Params: name, label, value, required, rows --}}
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <textarea name="{{ $name }}" id="{{ $name }}" rows="{{ $rows ?? 4 }}"
              class="form-control @error($name) is-invalid @enderror"
              @if($required ?? false) required @endif>{{ old($name, $value ?? '') }}</textarea>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
