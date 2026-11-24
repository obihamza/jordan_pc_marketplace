{{-- Params: action, label (optional), confirm (optional), class (optional) --}}
<form action="{{ $action }}" method="POST" class="d-inline"
      onsubmit="return confirm('{{ $confirm ?? 'Are you sure?' }}')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn {{ $class ?? 'btn-sm btn-outline-danger' }}">{{ $label ?? 'Delete' }}</button>
</form>
