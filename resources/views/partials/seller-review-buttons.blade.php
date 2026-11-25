{{-- Admin only. Params: seller, icons (true = icon buttons for tables, false = labelled buttons) --}}
@php $icons = $icons ?? false; @endphp
@if(! $seller->isApproved())
    <form action="{{ route('sellers.approve', $seller) }}" method="POST" class="d-inline">
        @csrf @method('PATCH')
        @if($icons)
            <button type="submit" class="jo-icon-btn jo-icon-success" title="Approve" aria-label="Approve"><i class="bi bi-check-lg"></i></button>
        @else
            <button type="submit" class="btn btn-success"><i class="bi bi-check-lg"></i> Approve</button>
        @endif
    </form>
@endif
@if(! $seller->isRejected())
    <form action="{{ route('sellers.reject', $seller) }}" method="POST" class="d-inline"
          data-confirm="Reject this seller application? The user can fix it and apply again." data-confirm-title="Reject application" data-confirm-button="Reject">
        @csrf @method('PATCH')
        @if($icons)
            <button type="submit" class="jo-icon-btn jo-icon-danger" title="Reject" aria-label="Reject"><i class="bi bi-x-lg"></i></button>
        @else
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i> Reject</button>
        @endif
    </form>
@endif
