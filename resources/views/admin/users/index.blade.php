@extends('layouts.admin')

@section('title', 'Users')

@section('content')
@include('partials.page-header', ['title' => 'Users', 'url' => route('users.create'), 'label' => 'New user'])

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><a href="{{ route('users.show', $user) }}" class="fw-semibold text-decoration-none">{{ $user->name }}</a></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td><span class="badge text-bg-secondary">{{ $user->is_admin ? 'Admin' : ($user->seller ? 'Seller' : 'Buyer') }}</span></td>
                        <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('users.show', $user) }}">View</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $user) }}">Edit</a>
                            @include('partials.delete-button', ['action' => route('users.destroy', $user)])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $users->links() }}</div>
@endsection
