@extends(auth()->user()?->isSeller() ? 'layouts.seller' : 'layouts.app')

@section('title', 'Write a review')

@section('content')
@include('partials.page-header', ['title' => 'Write a review', 'back' => route('reviews.index')])
<div class="alert alert-info py-2 small">
    You can review a product after it was <strong>delivered and paid</strong>. Your review is shown once an admin approves it.
</div>

@if($products->isEmpty())
    <div class="card card-body text-center text-muted py-5">
        <i class="bi bi-bag-check fs-1"></i>
        <p class="mb-0 mt-2">Nothing to review yet. Products you bought will appear here once they are delivered and paid.</p>
    </div>
@else
    @include('partials.forms.review', ['products' => $products])
@endif
@endsection
