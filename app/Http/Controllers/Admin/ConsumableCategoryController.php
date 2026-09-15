<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsumableCategory;
use Illuminate\Http\Request;

class ConsumableCategoryController extends Controller
{
    public function index()
    {
        $categories = ConsumableCategory::withCount('items')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.consumables.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:consumable_categories,name',
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        ConsumableCategory::create([
            'name' => strtoupper(trim($validated['name'])),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.consumables.categories.index')
            ->with('success', 'Consumable category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = ConsumableCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:consumable_categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer',
        ]);

        $category->update([
            'name' => strtoupper(trim($validated['name'])),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.consumables.categories.index')
            ->with('success', 'Consumable category updated successfully.');
    }

    public function destroy($id)
    {
        $category = ConsumableCategory::findOrFail($id);
        $name = $category->name;
        $category->delete();

        return redirect()->route('admin.consumables.categories.index')
            ->with('success', "Category '{$name}' deleted successfully.");
    }
}
