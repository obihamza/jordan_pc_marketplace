{{-- Params: category (optional, edit), modal (optional: inside the pop-up, no card/buttons) --}}
@php
    $category = $category ?? null;
    $modal = $modal ?? false;
@endphp
<form action="{{ $category ? route('categories.update', $category) : route('categories.store') }}" method="POST" @if($modal) id="categoryCreateForm" @else class="card card-body" @endif>
    @csrf
    @if($category) @method('PUT') @endif
    @if($modal)<input type="hidden" name="_modal" value="category">@endif

    @include('partials.forms.input', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $category?->name])
    @include('partials.forms.input', ['name' => 'description', 'label' => 'Description', 'value' => $category?->description])

    @unless($modal)
        @include('partials.forms.actions', ['cancel' => route('categories.index'), 'submit' => $category ? 'Update category' : 'Create category'])
    @endunless
</form>
