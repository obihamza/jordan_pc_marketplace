<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $coupons = Coupon::latest()->paginate(15);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        Coupon::create($validated);

        return redirect()->route('coupons.index')
            ->with('success', 'Coupon created successfully!');
    }

    public function show(Coupon $coupon)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $coupon = Coupon::withCount('orders')->findOrFail($coupon->id);

        return view('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $coupon = Coupon::findOrFail($coupon->id);

        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $coupon = Coupon::findOrFail($coupon->id);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after:starts_at',
        ]);

        $coupon->update($validated);

        return redirect()->route('coupons.index')
            ->with('success', 'Coupon updated successfully!');
    }

    public function destroy(Coupon $coupon)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $coupon = Coupon::findOrFail($coupon->id);

        $coupon->delete();

        return redirect()->route('coupons.index')
            ->with('danger', 'Coupon deleted successfully!');
    }
}
