<?php

namespace App\Http\Controllers\Encoder;

use App\Http\Controllers\Controller;
use App\Models\Transmittal;
use App\Models\InventoryItem;
use App\Models\Batch;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IncomingController extends Controller
{
    public function index(Request $request)
    {
        $query = Transmittal::with(['items', 'encoder'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transmittal_no', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $transmittals = $query->paginate(15)->withQueryString();
        return view('encoder.incoming.index', compact('transmittals'));
    }

    public function create()
    {
        $nextTransmittalNo = '';
        $clients = User::where('role', 'client')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->where('status', 'active')
            ->select('company_name')
            ->distinct()
            ->orderBy('company_name', 'asc')
            ->get();

        return view('encoder.incoming.create', compact('nextTransmittalNo', 'clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'transmittal_no' => 'required|string|max:100',
            'company_name' => 'required|string|max:255',
        ]);

        $transmittal = DB::transaction(function () use ($request) {
            $transmittalNo = trim($request->input('transmittal_no'));
            $customStatus = $request->filled('status') ? trim($request->input('status')) : null;

            $transmittal = Transmittal::create([
                'transmittal_no' => $transmittalNo,
                'company_name' => $request->input('company_name'),
                'date_received' => $request->input('date_received', now()->format('Y-m-d')),
                'brand' => $request->input('brand'),
                'model' => $request->input('model'),
                'status' => $customStatus,
                'notes' => $request->input('notes'),
                'encoded_by' => Auth::id(),
            ]);

            $serials = $request->input('serial_number', []);
            $macs = $request->input('mac_address', []);
            $boxes = $request->input('box_no', []);
            $models = $request->input('row_model', []);
            $brands = $request->input('row_brand', []);

            $itemCount = 0;

            if (is_array($serials)) {
                foreach ($serials as $i => $sn) {
                    $mac = $macs[$i] ?? null;
                    $box = $boxes[$i] ?? null;
                    $rowModel = !empty($models[$i]) ? trim($models[$i]) : $transmittal->model;
                    $rowBrand = !empty($brands[$i]) ? trim($brands[$i]) : $transmittal->brand;

                    if (!empty($sn) || !empty($mac) || !empty($box)) {
                        $itemCount++;
                        InventoryItem::create([
                            'transmittal_id' => $transmittal->id,
                            'item_no' => $itemCount,
                            'brand' => $rowBrand,
                            'model' => $rowModel,
                            'serial_number' => $sn ? trim($sn) : null,
                            'mac_address' => $mac ? trim($mac) : null,
                            'box_no' => $box ? trim($box) : null,
                            'technical_diagnostic' => null,
                            'replace_parts' => null,
                            'repair_status' => $customStatus ?: 'In process',
                            'stock_status' => 'IN_STOCK',
                            'company_name' => $transmittal->company_name,
                            'date_delivered' => $transmittal->date_received,
                            'encoded_by' => Auth::id(),
                        ]);
                    }
                }
            }

            $transmittal->update([
                'total_quantity' => $itemCount,
            ]);

            ActivityLog::log('INCOMING_ENCODED', "Encoder created Incoming Transmittal {$transmittal->transmittal_no} with {$itemCount} items.");

            return $transmittal;
        });

        return redirect()->route('encoder.incoming.edit', $transmittal->id)->with('success', "Incoming Transmittal {$transmittal->transmittal_no} encoded successfully! You can now scan barcodes.");
    }

    public function show($id)
    {
        $transmittal = Transmittal::with(['items', 'encoder'])->findOrFail($id);
        return view('encoder.incoming.show', compact('transmittal'));
    }

    public function edit($id)
    {
        $transmittal = Transmittal::with('items')->findOrFail($id);
        $clients = User::where('role', 'client')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->where('status', 'active')
            ->select('company_name')
            ->distinct()
            ->orderBy('company_name', 'asc')
            ->get();

        return view('encoder.incoming.edit', compact('transmittal', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $transmittal = Transmittal::findOrFail($id);

        DB::transaction(function () use ($request, $transmittal) {
            $transmittal->update([
                'transmittal_no' => $request->input('transmittal_no', $transmittal->transmittal_no),
                'company_name' => $request->input('company_name', $transmittal->company_name),
                'date_received' => $request->input('date_received', $transmittal->date_received),
                'brand' => $request->input('brand', $transmittal->brand),
                'model' => $request->input('model', $transmittal->model),
                'status' => $request->filled('status') ? trim($request->input('status')) : null,
                'notes' => $request->input('notes', $transmittal->notes),
            ]);

            $itemIds = $request->input('item_id', []);
            $serials = $request->input('serial_number', []);
            $macs = $request->input('mac_address', []);
            $boxes = $request->input('box_no', []);
            $models = $request->input('row_model', []);
            $brands = $request->input('row_brand', []);

            $keptIds = [];

            if (is_array($serials)) {
                $itemNo = 1;
                foreach ($serials as $i => $sn) {
                    $itemId = $itemIds[$i] ?? null;
                    $mac = $macs[$i] ?? null;
                    $box = $boxes[$i] ?? null;
                    $rowModel = !empty($models[$i]) ? trim($models[$i]) : $transmittal->model;
                    $rowBrand = !empty($brands[$i]) ? trim($brands[$i]) : $transmittal->brand;

                    if (!empty($sn) || !empty($mac) || !empty($box) || $itemId) {
                        if ($itemId) {
                            $item = InventoryItem::where('transmittal_id', $transmittal->id)->find($itemId);
                            if ($item) {
                                $item->update([
                                    'item_no' => $itemNo++,
                                    'brand' => $rowBrand ?: $item->brand,
                                    'model' => $rowModel ?: $item->model,
                                    'serial_number' => $sn ? trim($sn) : null,
                                    'mac_address' => $mac ? trim($mac) : null,
                                    'box_no' => $box ? trim($box) : null,
                                    'company_name' => $transmittal->company_name,
                                ]);
                                $keptIds[] = $item->id;
                            }
                        } else {
                            $newItem = InventoryItem::create([
                                'transmittal_id' => $transmittal->id,
                                'item_no' => $itemNo++,
                                'brand' => $rowBrand ?: $transmittal->brand,
                                'model' => $rowModel ?: $transmittal->model,
                                'serial_number' => $sn ? trim($sn) : null,
                                'mac_address' => $mac ? trim($mac) : null,
                                'box_no' => $box ? trim($box) : null,
                                'technical_diagnostic' => null,
                                'replace_parts' => null,
                                'repair_status' => $transmittal->status ?: 'In process',
                                'stock_status' => 'IN_STOCK',
                                'company_name' => $transmittal->company_name,
                                'date_delivered' => $transmittal->date_received,
                                'encoded_by' => Auth::id(),
                            ]);
                            $keptIds[] = $newItem->id;
                        }
                    }
                }
            }

            InventoryItem::where('transmittal_id', $transmittal->id)
                ->where('stock_status', 'IN_STOCK')
                ->whereNotIn('id', $keptIds)
                ->delete();

            $transmittal->recalculateQuantities();
            ActivityLog::log('INCOMING_UPDATED', "Encoder updated Incoming Transmittal {$transmittal->transmittal_no}.");
        });

        return redirect()->route('encoder.incoming.show', $transmittal->id)->with('success', "Transmittal {$transmittal->transmittal_no} updated successfully!");
    }

    public function print(Request $request, $id)
    {
        $transmittal = Transmittal::with(['items', 'encoder'])->findOrFail($id);

        $filterModel = $request->query('model');

        $query = $transmittal->items();
        if ($filterModel) {
            $query->where('model', $filterModel);
        }

        $allItems = $query->orderBy('model')->orderBy('item_no')->get();

        $itemsByModel = $allItems->groupBy(function($item) {
            return trim($item->model ?: 'Unassigned Model');
        });

        return view('print.incoming_slip', compact('transmittal', 'itemsByModel', 'allItems', 'filterModel'));
    }

    public function getItems($id)
    {
        /** @var Transmittal $transmittal */
        $transmittal = Transmittal::with(['items' => function ($q) {
            $q->orderBy('item_no', 'asc')->orderBy('id', 'asc');
        }])->findOrFail($id);

        return response()->json([
            'success' => true,
            'transmittal' => [
                'id' => $transmittal->id,
                'transmittal_no' => $transmittal->transmittal_no,
                'company_name' => $transmittal->company_name,
                'brand' => $transmittal->brand,
                'model' => $transmittal->model,
                'status' => $transmittal->status,
                'total_quantity' => $transmittal->total_quantity,
            ],
            'items' => $transmittal->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_no' => $item->item_no,
                    'brand' => $item->brand ?? '',
                    'model' => $item->model ?? '',
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

    public function saveItem(Request $request, $id)
    {
        $transmittal = Transmittal::findOrFail($id);

        $itemId = $request->input('item_id');
        $sn = $request->filled('serial_number') ? trim($request->input('serial_number')) : null;
        $mac = $request->filled('mac_address') ? trim($request->input('mac_address')) : null;
        $box = $request->filled('box_no') ? trim($request->input('box_no')) : null;
        $rowModel = $request->filled('model') ? trim($request->input('model')) : $transmittal->model;
        $rowBrand = $request->filled('brand') ? trim($request->input('brand')) : $transmittal->brand;

        $duplicateInfo = null;
        $isDuplicate = false;

        if ($sn) {
            $duplicateQuery = InventoryItem::where('serial_number', $sn);
            if ($itemId) {
                $duplicateQuery->where('id', '!=', $itemId);
            }
            $existing = $duplicateQuery->with(['transmittal', 'batch'])->first();
            if ($existing) {
                $isDuplicate = true;
                $duplicateInfo = [
                    'id' => $existing->id,
                    'serial_number' => $existing->serial_number,
                    'model' => $existing->model,
                    'brand' => $existing->brand,
                    'transmittal_no' => $existing->transmittal?->transmittal_no ?? 'N/A',
                    'batch_no' => $existing->batch?->batch_no ?? 'Not yet batched',
                    'repair_status' => $existing->repair_status,
                    'stock_status' => $existing->stock_status,
                    'date_delivered' => $existing->date_delivered ? $existing->date_delivered->format('Y-m-d') : null,
                    'company_name' => $existing->company_name,
                ];
            }
        }

        $item = null;

        if ($itemId) {
            $item = InventoryItem::where('transmittal_id', $transmittal->id)->find($itemId);
            if ($item) {
                $item->update([
                    'serial_number' => $sn,
                    'mac_address' => $mac,
                    'box_no' => $box,
                    'brand' => $rowBrand ?: $item->brand,
                    'model' => $rowModel ?: $item->model,
                    'company_name' => $transmittal->company_name,
                ]);
            }
        } else {
            if (!empty($sn) || !empty($mac) || !empty($box)) {
                $nextNo = ($transmittal->items()->max('item_no') ?? 0) + 1;
                $item = InventoryItem::create([
                    'transmittal_id' => $transmittal->id,
                    'item_no' => $nextNo,
                    'brand' => $rowBrand ?: $transmittal->brand,
                    'model' => $rowModel ?: $transmittal->model,
                    'serial_number' => $sn,
                    'mac_address' => $mac,
                    'box_no' => $box,
                    'repair_status' => $transmittal->status ?: 'In process',
                    'stock_status' => 'IN_STOCK',
                    'company_name' => $transmittal->company_name,
                    'date_delivered' => $transmittal->date_received,
                    'encoded_by' => Auth::id(),
                ]);
            }
        }

        $transmittal->recalculateQuantities();

        return response()->json([
            'success' => true,
            'is_duplicate' => $isDuplicate,
            'duplicate_info' => $duplicateInfo,
            'item' => $item ? [
                'id' => $item->id,
                'item_no' => $item->item_no,
                'brand' => $item->brand ?? '',
                'model' => $item->model ?? '',
                'serial_number' => $item->serial_number ?? '',
                'mac_address' => $item->mac_address ?? '',
                'box_no' => $item->box_no ?? '',
                'stock_status' => $item->stock_status,
                'repair_status' => $item->repair_status,
                'updated_at' => $item->updated_at ? $item->updated_at->toIso8601String() : null,
            ] : null,
            'transmittal' => [
                'total_quantity' => $transmittal->total_quantity,
            ],
        ]);
    }

    public function deleteItem($id, $itemId)
    {
        $transmittal = Transmittal::findOrFail($id);
        $item = InventoryItem::where('transmittal_id', $transmittal->id)->find($itemId);

        if ($item) {
            $batchId = $item->batch_id;
            $item->delete();
            if ($batchId) {
                $batch = Batch::find($batchId);
                if ($batch && $batch->items()->count() === 0) {
                    $batch->delete();
                }
            }
        }

        $transmittal->recalculateQuantities();

        return response()->json([
            'success' => true,
            'transmittal' => [
                'total_quantity' => $transmittal->total_quantity,
            ],
        ]);
    }

    public function saveHeader(Request $request, $id)
    {
        $transmittal = Transmittal::findOrFail($id);

        $transmittal->update([
            'transmittal_no' => $request->input('transmittal_no', $transmittal->transmittal_no),
            'company_name' => $request->input('company_name', $transmittal->company_name),
            'date_received' => $request->input('date_received', $transmittal->date_received),
            'brand' => $request->input('brand', $transmittal->brand),
            'model' => $request->input('model', $transmittal->model),
            'status' => $request->filled('status') ? trim($request->input('status')) : null,
            'notes' => $request->input('notes', $transmittal->notes),
        ]);

        InventoryItem::where('transmittal_id', $transmittal->id)->update([
            'company_name' => $transmittal->company_name,
            'date_delivered' => $transmittal->date_received,
        ]);

        return response()->json([
            'success' => true,
            'transmittal' => $transmittal,
        ]);
    }

    public function checkDuplicate(Request $request)
    {
        $sn = trim($request->input('serial_number') ?? '');
        $excludeId = $request->input('exclude_id');

        if (empty($sn)) {
            return response()->json(['exists' => false]);
        }

        $query = InventoryItem::with(['transmittal', 'batch', 'encoder'])
            ->where('serial_number', $sn);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            return response()->json(['exists' => false]);
        }

        $first = $items->first();

        return response()->json([
            'exists' => true,
            'count' => $items->count(),
            'item' => [
                'id' => $first->id,
                'serial_number' => $first->serial_number,
                'mac_address' => $first->mac_address,
                'brand' => $first->brand,
                'model' => $first->model,
                'box_no' => $first->box_no,
                'repair_status' => $first->repair_status,
                'stock_status' => $first->stock_status,
                'technical_diagnostic' => $first->technical_diagnostic,
                'replace_parts' => $first->replace_parts,
                'company_name' => $first->company_name,
                'transmittal_no' => $first->transmittal?->transmittal_no ?? 'N/A',
                'batch_no' => $first->batch?->batch_no ?? 'Unbatched',
                'date_delivered' => $first->date_delivered ? $first->date_delivered->format('Y-m-d') : null,
                'encoded_by' => $first->encoder?->name ?? 'System',
                'created_at' => $first->created_at ? $first->created_at->format('Y-m-d H:i') : null,
            ],
            'all_occurrences' => $items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'transmittal_no' => $it->transmittal?->transmittal_no ?? 'N/A',
                    'batch_no' => $it->batch?->batch_no ?? 'Unbatched',
                    'repair_status' => $it->repair_status,
                    'stock_status' => $it->stock_status,
                    'date' => $it->date_delivered ? $it->date_delivered->format('Y-m-d') : null,
                ];
            }),
        ]);
    }

    public function searchSerial(Request $request)
    {
        $query = trim($request->input('query') ?? $request->input('serial') ?? '');
        if (empty($query)) {
            return response()->json(['found' => false, 'message' => 'Please enter a serial number.'], 400);
        }

        $items = InventoryItem::with(['transmittal', 'batch', 'encoder'])
            ->where('serial_number', 'like', "%{$query}%")
            ->orWhere('mac_address', 'like', "%{$query}%")
            ->latest()
            ->take(10)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['found' => false, 'message' => "No records found matching '{$query}'."]);
        }

        return response()->json([
            'found' => true,
            'count' => $items->count(),
            'items' => $items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'serial_number' => $it->serial_number,
                    'mac_address' => $it->mac_address,
                    'brand' => $it->brand,
                    'model' => $it->model,
                    'box_no' => $it->box_no,
                    'repair_status' => $it->repair_status,
                    'stock_status' => $it->stock_status,
                    'technical_diagnostic' => $it->technical_diagnostic,
                    'replace_parts' => $it->replace_parts,
                    'transmittal_no' => $it->transmittal?->transmittal_no ?? 'N/A',
                    'batch_no' => $it->batch?->batch_no ?? 'Unbatched',
                    'date' => $it->date_delivered ? $it->date_delivered->format('Y-m-d') : null,
                    'company' => $it->company_name,
                    'notes' => $it->notes,
                ];
            }),
        ]);
    }
}
