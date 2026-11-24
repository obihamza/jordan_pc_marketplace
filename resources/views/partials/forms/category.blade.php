{{-- Params: category (optional, edit) --}}
@php($category = $category ?? null)
<form action="{{ $category ? route('categories.update', $category) : route('categories.store') }}" method="POST" class="card card-body">
    @csrf
    @if($category) @method('PUT') @endif

    @include('partials.forms.input', ['name' => 'name', 'label' => 'Name', 'required' => true, 'value' => $category?->name])
    @include('partials.forms.input', ['name' => 'description', 'label' => 'Description', 'value' => $category?->description])

    @include('partials.forms.actions', ['cancel' => route('categories.index'), 'submit' => $category ? 'Update category' : 'Create category'])
</form>
