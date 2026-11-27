<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $addresses = Address::with('user')
                ->latest()
                ->paginate(15);

            return view('admin.addresses.index', compact('addresses'));
        } else {
            $addresses = Address::where('user_id', $me->id)
                ->latest()
                ->paginate(15);

            return view('buyer.addresses.index', compact('addresses'));
        }
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $users = User::all();

            return view('admin.addresses.create', compact('users'));
        } else {
            return view('buyer.addresses.create');
        }
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        $rules = [
            'address_line' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:80',
        ];

        if ($me->isAdmin()) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        Address::create([
            'user_id' => $me->isAdmin() ? $validated['user_id'] : $me->id,
            'address_line' => $validated['address_line'],
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? 'Jordan',
        ]);

        return redirect()->route('addresses.index')
            ->with('success', 'Address created successfully!');
    }

    public function edit(Address $address)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $address = Address::findOrFail($address->id);

            return view('admin.addresses.edit', compact('address'));
        } else {
            $address = Address::where('user_id', $me->id)
                ->findOrFail($address->id);

            return view('buyer.addresses.edit', compact('address'));
        }
    }

    public function update(Request $request, Address $address)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $address = Address::findOrFail($address->id);
        } else {
            $address = Address::where('user_id', $me->id)
                ->findOrFail($address->id);
        }

        $validated = $request->validate([
            'address_line' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:80',
        ]);

        $address->update($validated);

        return redirect()->route('addresses.index')
            ->with('success', 'Address updated successfully!');
    }

    public function destroy(Address $address)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $address = Address::findOrFail($address->id);
        } else {
            $address = Address::where('user_id', $me->id)
                ->findOrFail($address->id);
        }

        $address->delete();

        return redirect()->route('addresses.index')
            ->with('danger', 'Address deleted successfully!');
    }
}
