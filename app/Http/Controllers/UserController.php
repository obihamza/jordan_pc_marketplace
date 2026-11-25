<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $users = User::with('seller')
                ->latest()
                ->paginate(15);

            return view('admin.users.index', compact('users'));
        }

        // Sellers and buyers only manage their own profile (show / edit).
        abort(403);
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            return view('admin.users.create');
        }

        abort(403);
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        if (! $me->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:180|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:30',
            'user_img' => 'nullable|string',
            'is_admin' => 'boolean',
        ]);

        // The column is `password_hash` (hashed by the model cast).
        $validated['password_hash'] = $validated['password'];
        unset($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }

    public function show(User $user)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $user = User::with(['seller', 'addresses'])
                ->findOrFail($user->id);

            return view('admin.users.show', compact('user'));

        } elseif ($me->isSeller()) {

            // Own profile only.
            $user = User::with(['seller', 'addresses'])
                ->findOrFail($me->id);

            return view('seller.users.show', compact('user'));

        } else {

            $user = User::with('addresses')
                ->findOrFail($me->id);

            return view('buyer.users.show', compact('user'));
        }
    }

    public function edit(User $user)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $user = User::findOrFail($user->id);

            return view('admin.users.edit', compact('user'));

        } elseif ($me->isSeller()) {

            $user = User::findOrFail($me->id);

            return view('seller.users.edit', compact('user'));

        } else {

            $user = User::findOrFail($me->id);

            return view('buyer.users.edit', compact('user'));
        }
    }

    public function update(Request $request, User $user)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $user = User::findOrFail($user->id);
        } else {
            // Own profile only.
            $user = User::findOrFail($me->id);
        }

        $rules = [
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:180', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:30',
            'user_img' => 'nullable|string',
        ];

        // Only an admin can change the admin flag.
        if ($me->isAdmin()) {
            $rules['is_admin'] = 'boolean';
        }

        $validated = $request->validate($rules);

        if (! empty($validated['password'])) {
            $validated['password_hash'] = $validated['password'];
        }
        unset($validated['password']);

        $user->update($validated);

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $user = User::findOrFail($user->id);
            $user->delete();

            return redirect()->route('users.index')
                ->with('danger', 'User deleted successfully!');
        }

        // Anyone else can only delete their own account.
        $user = User::findOrFail($me->id);
        $user->delete();

        return redirect('/')
            ->with('danger', 'Your account was deleted.');
    }
}
