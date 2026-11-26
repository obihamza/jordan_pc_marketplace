{{-- Admin only. Params: review. Approve / reject icon buttons. --}}
@if(! $review->isApproved())
    <form action="{{ route('reviews.approve', $review) }}" method="POST" class="d-inline">
        @csrf @method('PATCH')
        <button type="submit" class="jo-icon-btn jo-icon-success" title="Approve" aria-label="Approve"><i class="bi bi-check-lg"></i></button>
    </form>
@endif
@if($review->status !== 'rejected')
    <form action="{{ route('reviews.reject', $review) }}" method="POST" class="d-inline"
          data-confirm="Reject this review? It will not be shown on the product page." data-confirm-title="Reject review" data-confirm-button="Reject">
        @csrf @method('PATCH')
        <button type="submit" class="jo-icon-btn jo-icon-danger" title="Reject" aria-label="Reject"><i class="bi bi-x-lg"></i></button>
    </form>
@endif
