<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_admin' => 'boolean',
        ]);

        // The column is `password_hash` (hashed by the model cast).
        $validated['password_hash'] = $validated['password'];
        unset($validated['password']);

        $validated = $this->withAvatar($request, $validated);

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
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar' => 'nullable|boolean',
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

        $validated = $this->withAvatar($request, $validated, $user);

        $user->update($validated);

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /** Streams a profile photo to logged-in users (the files live on the public disk, no storage:link needed). */
    public function avatar(User $user)
    {
        $disk = Storage::disk('public');

        abort_unless($user->user_img && ! str_starts_with($user->user_img, 'http') && $disk->exists($user->user_img), 404);

        return $disk->response($user->user_img, null, ['Cache-Control' => 'private, max-age=3600']);
    }

    /**
     * Turns the uploaded "avatar" file (or the "remove photo" tick) into the user_img column,
     * and deletes the old stored photo when it is replaced or removed.
     */
    private function withAvatar(Request $request, array $validated, ?User $user = null): array
    {
        unset($validated['avatar'], $validated['remove_avatar']);

        $old = $user?->user_img;
        $oldStored = $old && str_starts_with($old, 'avatars/');

        if ($request->hasFile('avatar')) {
            $validated['user_img'] = $request->file('avatar')->store('avatars', 'public');

            if ($oldStored) {
                Storage::disk('public')->delete($old);
            }
        } elseif ($request->boolean('remove_avatar') && $old) {
            $validated['user_img'] = null;

            if ($oldStored) {
                Storage::disk('public')->delete($old);
            }
        }

        return $validated;
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
