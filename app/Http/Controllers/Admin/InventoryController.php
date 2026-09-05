<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Batch;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with(['batch', 'outgoingSlip', 'encoder'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('box_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('si_number', 'like', "%{$search}%")
                  ->orWhere('dr_number', 'like', "%{$search}%")
                  ->orWhereHas('batch', function ($bq) use ($search) {
                      $bq->where('batch_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        if ($request->filled('repair_status')) {
            $query->where('repair_status', $request->repair_status);
        }

        $items = $query->paginate(20)->withQueryString();

        $batches = Batch::all();
        $brands = InventoryItem::select('brand')->whereNotNull('brand')->distinct()->pluck('brand');

        $totalUnits = InventoryItem::count();
        $inStockCount = InventoryItem::where('stock_status', 'IN_STOCK')->count();
        $releasedCount = InventoryItem::whereIn('stock_status', ['OUTGOING', 'RELEASED'])->count();

        return view('admin.inventory.index', compact(
            'items',
            'batches',
            'brands',
            'totalUnits',
            'inStockCount',
            'releasedCount'
        ));
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $item->update([
            'serial_number' => $request->input('serial_number', $item->serial_number),
            'mac_address' => $request->input('mac_address', $item->mac_address),
            'brand' => $request->input('brand', $item->brand),
            'model' => $request->input('model', $item->model),
            'box_no' => $request->input('box_no', $item->box_no),
            'technical_diagnostic' => $request->input('technical_diagnostic', $item->technical_diagnostic),
            'replace_parts' => $request->input('replace_parts', $item->replace_parts),
            'repair_status' => $request->input('repair_status', $item->repair_status),
            'stock_status' => $request->input('stock_status', $item->stock_status),
            'notes' => $request->input('notes', $item->notes),
        ]);

        if ($item->batch) {
            $item->batch->recalculateQuantities();
        }

        ActivityLog::log('ITEM_UPDATED', "Updated inventory item SN: {$item->serial_number} / MAC: {$item->mac_address}.");

        return back()->with('success', "Item SN: {$item->serial_number} updated successfully!");
    }

    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);
        $batch = $item->batch;
        $sn = $item->serial_number ?? "Item #{$item->id}";
        $item->delete();

        if ($batch) {
            $batch->recalculateQuantities();
        }

        ActivityLog::log('ITEM_DELETED', "Deleted item {$sn}.");

        return back()->with('success', "Item {$sn} deleted successfully.");
    }
}
