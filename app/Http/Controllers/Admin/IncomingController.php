<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IncomingController extends Controller
{
    public function index(Request $request)
    {
        $query = Batch::with(['items', 'encoder'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('item_description', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('slip_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->paginate(15)->withQueryString();
        $brands = Batch::select('brand')->whereNotNull('brand')->distinct()->pluck('brand');

        return view('admin.incoming.index', compact('batches', 'brands'));
    }

    public function create()
    {
        $nextBatchNumber = 'BATCH ' . (Batch::count() + 1);
        $slipNo = 'IRS-' . date('Ymd') . '-' . str_pad(Batch::count() + 1, 3, '0', STR_PAD_LEFT);
        return view('admin.incoming.create', compact('nextBatchNumber', 'slipNo'));
    }

    public function store(Request $request)
    {
        $batch = DB::transaction(function () use ($request) {
            $slipNo = $request->input('slip_no') ?: ('IRS-' . date('Ymd') . '-' . str_pad(Batch::count() + 1, 3, '0', STR_PAD_LEFT));
            $customStatus = $request->filled('status') ? trim($request->input('status')) : null;

            $batch = Batch::create([
                'slip_no' => $slipNo,
                'batch_no' => $request->input('batch_no'),
                'company_name' => $request->input('company_name'),
                'date_delivered' => $request->input('date_delivered', now()->format('Y-m-d')),
                'item_description' => $request->input('item_description'),
                'brand' => $request->input('brand'),
                'model' => $request->input('model'),
                'status' => $customStatus,
                'notes' => $request->input('notes'),
                'encoded_by' => Auth::id(),
            ]);

            $serials = $request->input('serial_number', []);
            $macs = $request->input('mac_address', []);
            $boxes = $request->input('box_no', []);

            $itemCount = 0;

            if (is_array($serials)) {
                foreach ($serials as $i => $sn) {
                    $mac = $macs[$i] ?? null;
                    $box = $boxes[$i] ?? null;

                    // Save incoming items
                    if (!empty($sn) || !empty($mac) || !empty($box)) {
                        $itemCount++;
                        InventoryItem::create([
                            'batch_id' => $batch->id,
                            'item_no' => $itemCount,
                            'brand' => $batch->brand,
                            'model' => $batch->model,
                            'serial_number' => $sn ? trim($sn) : null,
                            'mac_address' => $mac ? trim($mac) : null,
                            'box_no' => $box ? trim($box) : null,
                            'technical_diagnostic' => null,
                            'replace_parts' => null,
                            'repair_status' => $customStatus ?: 'In process',
                            'stock_status' => 'IN_STOCK',
                            'company_name' => $batch->company_name,
                            'date_delivered' => $batch->date_delivered,
                            'encoded_by' => Auth::id(),
                        ]);
                    }
                }
            }

            $batch->update([
                'total_quantity' => $itemCount,
                'in_stock_quantity' => $itemCount,
                'outgoing_quantity' => 0,
            ]);

            ActivityLog::log('INCOMING_CREATED', "Created Incoming Batch {$batch->batch_no} ({$batch->slip_no}) with {$itemCount} items.");

            return $batch;
        });

        return redirect()->route('admin.incoming.show', $batch->id)->with('success', "Incoming Repair Slip {$batch->slip_no} created successfully with {$batch->total_quantity} items!");
    }

    public function show($id)
    {
        $batch = Batch::with(['items', 'encoder'])->findOrFail($id);
        return view('admin.incoming.show', compact('batch'));
    }

    public function edit($id)
    {
        $batch = Batch::with('items')->findOrFail($id);
        return view('admin.incoming.edit', compact('batch'));
    }

    public function update(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        DB::transaction(function () use ($request, $batch) {
            $batch->update([
                'slip_no' => $request->input('slip_no', $batch->slip_no),
                'batch_no' => $request->input('batch_no', $batch->batch_no),
                'company_name' => $request->input('company_name', $batch->company_name),
                'date_delivered' => $request->input('date_delivered', $batch->date_delivered),
                'brand' => $request->input('brand', $batch->brand),
                'model' => $request->input('model', $batch->model),
                'status' => $request->filled('status') ? trim($request->input('status')) : null,
                'notes' => $request->input('notes', $batch->notes),
            ]);

            // Process existing items or replaced items
            $itemIds = $request->input('item_id', []);
            $serials = $request->input('serial_number', []);
            $macs = $request->input('mac_address', []);
            $boxes = $request->input('box_no', []);

            $keptIds = [];

            if (is_array($serials)) {
                $itemNo = 1;
                foreach ($serials as $i => $sn) {
                    $id = $itemIds[$i] ?? null;
                    $mac = $macs[$i] ?? null;
                    $box = $boxes[$i] ?? null;

                    if (!empty($sn) || !empty($mac) || !empty($box) || $id) {
                        if ($id) {
                            $item = InventoryItem::where('batch_id', $batch->id)->find($id);
                            if ($item) {
                                $item->update([
                                    'item_no' => $itemNo++,
                                    'brand' => $batch->brand,
                                    'model' => $batch->model,
                                    'serial_number' => $sn ? trim($sn) : null,
                                    'mac_address' => $mac ? trim($mac) : null,
                                    'box_no' => $box ? trim($box) : null,
                                    'company_name' => $batch->company_name,
                                ]);
                                $keptIds[] = $item->id;
                            }
                        } else {
                            $newItem = InventoryItem::create([
                                'batch_id' => $batch->id,
                                'item_no' => $itemNo++,
                                'brand' => $batch->brand,
                                'model' => $batch->model,
                                'serial_number' => $sn ? trim($sn) : null,
                                'mac_address' => $mac ? trim($mac) : null,
                                'box_no' => $box ? trim($box) : null,
                                'technical_diagnostic' => null,
                                'replace_parts' => null,
                                'repair_status' => $batch->status ?: 'In process',
                                'stock_status' => 'IN_STOCK',
                                'company_name' => $batch->company_name,
                                'date_delivered' => $batch->date_delivered,
                                'encoded_by' => Auth::id(),
                            ]);
                            $keptIds[] = $newItem->id;
                        }
                    }
                }
            }

            // Remove items that were deleted from form (only if they aren't released)
            InventoryItem::where('batch_id', $batch->id)
                ->where('stock_status', 'IN_STOCK')
                ->whereNotIn('id', $keptIds)
                ->delete();

            $batch->recalculateQuantities();
            ActivityLog::log('INCOMING_UPDATED', "Updated Incoming Batch {$batch->batch_no}.");
        });

        return redirect()->route('admin.incoming.show', $batch->id)->with('success', "Batch {$batch->batch_no} updated successfully!");
    }

    public function destroy($id)
    {
        $batch = Batch::findOrFail($id);
        $batchNo = $batch->batch_no;
        $batch->delete();

        ActivityLog::log('INCOMING_DELETED', "Deleted Incoming Batch {$batchNo}.");

        return redirect()->route('admin.incoming.index')->with('success', "Batch {$batchNo} deleted successfully.");
    }

    public function print($id)
    {
        $batch = Batch::with('items')->findOrFail($id);
        return view('print.incoming_slip', compact('batch'));
    }

    /**
     * Real-time polling API: Get all items for the batch
     */
    public function getItems($id)
    {
        /** @var Batch $batch */
        $batch = Batch::with(['items' => function ($q) {
            $q->orderBy('item_no', 'asc')->orderBy('id', 'asc');
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'batch' => [
                'id' => $batch->id,
                'slip_no' => $batch->slip_no,
                'batch_no' => $batch->batch_no,
                'company_name' => $batch->company_name,
                'brand' => $batch->brand,
                'model' => $batch->model,
                'status' => $batch->status,
                'total_quantity' => $batch->total_quantity,
                'in_stock_quantity' => $batch->in_stock_quantity,
                'outgoing_quantity' => $batch->outgoing_quantity,
            ],
            'items' => $batch->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_no' => $item->item_no,
                    'serial_number' => $item->serial_number ?? '',
                    'mac_address' => $item->mac_address ?? '',
                    'box_no' => $item->box_no ?? '',
                    'stock_status' => $item->stock_status,
                    'repair_status' => $item->repair_status,
                    'updated_at' => $item->updated_at ? $item->updated_at->toIso8601String() : null,
                ];
            }),
        ]);
    }

    /**
     * Auto-save single row item (Create or Update)
     */
    public function saveItem(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        $itemId = $request->input('item_id');
        $sn = $request->filled('serial_number') ? trim($request->input('serial_number')) : null;
        $mac = $request->filled('mac_address') ? trim($request->input('mac_address')) : null;
        $box = $request->filled('box_no') ? trim($request->input('box_no')) : null;

        $item = null;

        if ($itemId) {
            $item = InventoryItem::where('batch_id', $batch->id)->find($itemId);
            if ($item) {
                $item->update([
                    'serial_number' => $sn,
                    'mac_address' => $mac,
                    'box_no' => $box,
                    'brand' => $batch->brand,
                    'model' => $batch->model,
                    'company_name' => $batch->company_name,
                ]);
            }
        } else {
            // Only create if at least one field has data
            if (!empty($sn) || !empty($mac) || !empty($box)) {
                $nextNo = ($batch->items()->max('item_no') ?? 0) + 1;
                $item = InventoryItem::create([
                    'batch_id' => $batch->id,
                    'item_no' => $nextNo,
                    'brand' => $batch->brand,
                    'model' => $batch->model,
                    'serial_number' => $sn,
                    'mac_address' => $mac,
                    'box_no' => $box,
                    'repair_status' => $batch->status ?: 'In process',
                    'stock_status' => 'IN_STOCK',
                    'company_name' => $batch->company_name,
                    'date_delivered' => $batch->date_delivered,
                    'encoded_by' => Auth::id(),
                ]);
            }
        }

        $batch->recalculateQuantities();

        return response()->json([
            'success' => true,
            'item' => $item ? [
                'id' => $item->id,
                'item_no' => $item->item_no,
                'serial_number' => $item->serial_number ?? '',
                'mac_address' => $item->mac_address ?? '',
                'box_no' => $item->box_no ?? '',
                'stock_status' => $item->stock_status,
                'repair_status' => $item->repair_status,
                'updated_at' => $item->updated_at ? $item->updated_at->toIso8601String() : null,
            ] : null,
            'batch' => [
                'total_quantity' => $batch->total_quantity,
                'in_stock_quantity' => $batch->in_stock_quantity,
                'outgoing_quantity' => $batch->outgoing_quantity,
            ],
        ]);
    }

    /**
     * Delete single row item in real-time
     */
    public function deleteItem($id, $itemId)
    {
        $batch = Batch::findOrFail($id);
        $item = InventoryItem::where('batch_id', $batch->id)->find($itemId);

        if ($item) {
            if ($item->stock_status === 'IN_STOCK') {
                $item->delete();
            }
        }

        $batch->recalculateQuantities();

        return response()->json([
            'success' => true,
            'batch' => [
                'total_quantity' => $batch->total_quantity,
                'in_stock_quantity' => $batch->in_stock_quantity,
                'outgoing_quantity' => $batch->outgoing_quantity,
            ],
        ]);
    }

    /**
     * Auto-save batch header details
     */
    public function saveHeader(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        $batch->update([
            'slip_no' => $request->input('slip_no', $batch->slip_no),
            'batch_no' => $request->input('batch_no', $batch->batch_no),
            'company_name' => $request->input('company_name', $batch->company_name),
            'date_delivered' => $request->input('date_delivered', $batch->date_delivered),
            'brand' => $request->input('brand', $batch->brand),
            'model' => $request->input('model', $batch->model),
            'status' => $request->filled('status') ? trim($request->input('status')) : null,
            'notes' => $request->input('notes', $batch->notes),
        ]);

        // Sync brand/model/company to inventory items of this batch
        InventoryItem::where('batch_id', $batch->id)->update([
            'brand' => $batch->brand,
            'model' => $batch->model,
            'company_name' => $batch->company_name,
            'date_delivered' => $batch->date_delivered,
        ]);

        return response()->json([
            'success' => true,
            'batch' => $batch,
        ]);
    }
}
