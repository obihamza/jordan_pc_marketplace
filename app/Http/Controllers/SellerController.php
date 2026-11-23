<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SellerController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        // Everyone can browse the list of sellers.
        $sellers = Seller::with('user')
            ->withCount('products')
            ->paginate(15);

        if ($me->isAdmin()) {

            return view('admin.sellers.index', compact('sellers'));

        } elseif ($me->isSeller()) {

            return view('seller.sellers.index', compact('sellers'));

        } else {

            return view('buyer.sellers.index', compact('sellers'));
        }
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            // Users who don't have a seller profile yet.
            $users = User::where('is_admin', false)->doesntHave('seller')->get();

            return view('admin.sellers.create', compact('users'));

        } elseif ($me->isSeller()) {

            // Already a seller.
            abort(403);

        } else {

            // A buyer applies to become a seller.
            return view('buyer.sellers.create');
        }
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        if ($me->isSeller()) {
            abort(403);
        }

        $rules = [
            'national_id' => 'required|string|max:30|unique:sellers,national_id',
            'official_name' => 'required|string|max:180|unique:sellers,official_name',
        ];

        if ($me->isAdmin()) {
            $rules['user_id'] = 'required|exists:users,id|unique:sellers,user_id';
        }

        $validated = $request->validate($rules);

        Seller::create([
            'user_id' => $me->isAdmin() ? $validated['user_id'] : $me->id,
            'national_id' => $validated['national_id'],
            'official_name' => $validated['official_name'],
        ]);

        return redirect()->route('sellers.index')
            ->with('success', 'Seller profile created successfully!');
    }

    public function show(Seller $seller)
    {
        $me = auth()->user();

        $seller = Seller::with(['user', 'products' => fn ($q) => $q->available()])
            ->findOrFail($seller->user_id);

        if ($me->isAdmin()) {

            return view('admin.sellers.show', compact('seller'));

        } elseif ($me->isSeller()) {

            return view('seller.sellers.show', compact('seller'));

        } else {

            return view('buyer.sellers.show', compact('seller'));
        }
    }

    public function edit(Seller $seller)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $seller = Seller::with('user')->findOrFail($seller->user_id);

            return view('admin.sellers.edit', compact('seller'));

        } elseif ($me->isSeller()) {

            // Own profile only.
            $seller = Seller::with('user')->findOrFail($me->id);

            return view('seller.sellers.edit', compact('seller'));
        }

        abort(403);
    }

    public function update(Request $request, Seller $seller)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $seller = Seller::findOrFail($seller->user_id);

        } elseif ($me->isSeller()) {

            $seller = Seller::findOrFail($me->id);

        } else {
            abort(403);
        }

        $validated = $request->validate([
            'national_id' => ['required', 'string', 'max:30', Rule::unique('sellers', 'national_id')->ignore($seller->user_id, 'user_id')],
            'official_name' => ['required', 'string', 'max:180', Rule::unique('sellers', 'official_name')->ignore($seller->user_id, 'user_id')],
        ]);

        $seller->update($validated);

        return redirect()->route('sellers.show', $seller)
            ->with('success', 'Seller profile updated successfully!');
    }

    public function destroy(Seller $seller)
    {
        $me = auth()->user();

        if (! $me->isAdmin()) {
            abort(403);
        }

        $seller = Seller::findOrFail($seller->user_id);

        $seller->delete();

        return redirect()->route('sellers.index')
            ->with('success', 'Seller profile deleted successfully!');
    }
}
