@php
    $label = $label ?? 'Delete';
    $confirmText = $confirm ?? 'Are you sure?';
@endphp
<form action="{{ $action }}" method="POST" class="d-inline"
      data-confirm="{{ $confirmText }}" data-confirm-title="{{ $label }}" data-confirm-button="{{ $label }}">
    @csrf
    @method('DELETE')
    @if($icon ?? false)
        <button type="submit" class="jo-icon-btn jo-icon-danger" title="{{ $label }}" aria-label="{{ $label }}"><i class="bi bi-trash3"></i></button>
    @else
        <button type="submit" class="btn {{ $class ?? 'btn-sm btn-outline-danger' }}">{{ $label }}</button>
    @endif
</form>
