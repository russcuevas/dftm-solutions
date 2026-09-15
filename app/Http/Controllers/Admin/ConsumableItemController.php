<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsumableCategory;
use App\Models\ConsumableItem;
use App\Models\ConsumableMonthlyStock;
use Illuminate\Http\Request;

class ConsumableItemController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = $request->get('category_id');
        $search = $request->get('search');

        $query = ConsumableItem::with('category');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('category_id')->orderBy('sort_order')->orderBy('name')->paginate(25)->withQueryString();
        $categories = ConsumableCategory::orderBy('sort_order')->orderBy('name')->get();

        // Common units suggestions
        $commonUnits = ['BUNDLE', 'PCS', 'PCK', 'REAM', 'BOX', 'ROLL', 'PAD', 'BX', 'CAN', 'BOTTLE', 'SET', 'METER'];

        return view('admin.consumables.items.index', compact('items', 'categories', 'commonUnits', 'categoryId', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:consumable_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'initial_beginning' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $item = ConsumableItem::create([
            'category_id' => $validated['category_id'],
            'name' => trim($validated['name']),
            'unit' => strtoupper(trim($validated['unit'])),
            'cost' => $validated['cost'] ?? 0.00,
            'min_stock' => $validated['min_stock'] ?? 0,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        // If initial beginning stock is provided, record it for current year/month
        if (isset($validated['initial_beginning'])) {
            $now = now();
            ConsumableMonthlyStock::updateOrCreate(
                [
                    'consumable_item_id' => $item->id,
                    'year' => $now->year,
                    'month' => $now->month,
                ],
                [
                    'beginning_stock' => $validated['initial_beginning'],
                ]
            );
        }

        return redirect()->route('admin.consumables.items.index')
            ->with('success', "Consumable item '{$item->name}' created successfully.");
    }

    public function update(Request $request, $id)
    {
        $item = ConsumableItem::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:consumable_categories,id',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'cost' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $item->update([
            'category_id' => $validated['category_id'],
            'name' => trim($validated['name']),
            'unit' => strtoupper(trim($validated['unit'])),
            'cost' => $validated['cost'] ?? 0.00,
            'min_stock' => $validated['min_stock'] ?? 0,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        return redirect()->back()->with('success', "Item '{$item->name}' updated successfully.");
    }

    public function destroy($id)
    {
        $item = ConsumableItem::findOrFail($id);
        $name = $item->name;
        $item->delete();

        return redirect()->route('admin.consumables.items.index')
            ->with('success', "Item '{$name}' deleted successfully.");
    }
}
