<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}"
            class="form-select @error($name) is-invalid @enderror"
            @if($required ?? false) required @endif>
        @isset($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endisset
        @foreach($options as $optionValue => $optionText)
            <option value="{{ $optionValue }}" @selected((string) old($name, $selected ?? '') === (string) $optionValue)>{{ $optionText }}</option>
        @endforeach
    </select>
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
