<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        // Everyone can browse categories.
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->paginate(15);

        if (! $me) {

            return view('guest.categories.index', compact('categories'));

        } elseif ($me->isAdmin()) {

            return view('admin.categories.index', compact('categories'));

        } elseif ($me->isSeller()) {

            return view('seller.categories.index', compact('categories'));

        } else {

            return view('buyer.categories.index', compact('categories'));
        }
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            return view('admin.categories.create');
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
            'name' => 'required|string|max:120|unique:categories,name',
            'description' => 'nullable|string|max:255',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully!');
    }

    public function show(Category $category)
    {
        $me = auth()->user();

        $category = Category::findOrFail($category->id);

        // Products in this category that can be bought.
        $products = $category->products()
            ->available()
            ->with('primaryImage')
            ->paginate(15);

        if (! $me) {

            return view('guest.categories.show', compact('category', 'products'));

        } elseif ($me->isAdmin()) {

            return view('admin.categories.show', compact('category', 'products'));

        } elseif ($me->isSeller()) {

            return view('seller.categories.show', compact('category', 'products'));

        } else {

            return view('buyer.categories.show', compact('category', 'products'));
        }
    }

    public function edit(Category $category)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $category = Category::findOrFail($category->id);

            return view('admin.categories.edit', compact('category'));
        }

        abort(403);
    }

    public function update(Request $request, Category $category)
    {
        $me = auth()->user();

        if (! $me->isAdmin()) {
            abort(403);
        }

        $category = Category::findOrFail($category->id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => 'nullable|string|max:255',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        $me = auth()->user();

        if (! $me->isAdmin()) {
            abort(403);
        }

        $category = Category::findOrFail($category->id);

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully!');
    }
}
