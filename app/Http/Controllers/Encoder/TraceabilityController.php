<?php

namespace App\Http\Controllers\Encoder;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Batch;
use App\Models\Transmittal;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        // Auto-heal any batches missing company_name if their items or transmittals have it
        $batchesToHeal = Batch::where(function($q) {
            $q->whereNull('company_name')
              ->orWhere('company_name', '')
              ->orWhere('company_name', 'DFTM DIGITAL SOLUTIONS');
        })->with(['items.transmittal'])->get();

        foreach ($batchesToHeal as $b) {
            $firstItem = $b->items->first();
            $detectedCompany = $firstItem?->company_name ?: ($firstItem?->transmittal?->company_name ?? null);
            if ($detectedCompany && $detectedCompany !== 'DFTM DIGITAL SOLUTIONS') {
                $b->update(['company_name' => $detectedCompany]);
            }
        }

        // Collect all distinct companies across Batches, Transmittals, Items, and Client accounts
        $companies = Batch::whereNotNull('company_name')->where('company_name', '!=', '')->pluck('company_name')
            ->merge(Transmittal::whereNotNull('company_name')->where('company_name', '!=', '')->pluck('company_name'))
            ->merge(InventoryItem::whereNotNull('company_name')->where('company_name', '!=', '')->pluck('company_name'))
            ->merge(\App\Models\User::where('role', 'client')->whereNotNull('company_name')->where('company_name', '!=', '')->pluck('company_name'))
            ->unique()
            ->sort()
            ->values();

        $companyFilter = $request->input('company');
        $activeBatchId = $request->input('batch_id');
        $statusFilter = $request->input('status');
        $brandFilter = $request->input('brand');

        $batchesQuery = Batch::with(['items.transmittal'])->latest();
        if (!empty($companyFilter) && $companyFilter !== 'all') {
            $batchesQuery->where(function($q) use ($companyFilter) {
                $q->where('company_name', $companyFilter)
                  ->orWhereHas('items', function($iq) use ($companyFilter) {
                      $iq->where('company_name', $companyFilter)
                         ->orWhereHas('transmittal', function($tq) use ($companyFilter) {
                             $tq->where('company_name', $companyFilter);
                         });
                  });
            });
        }
        $batches = $batchesQuery->get();

        if (!$activeBatchId && $batches->isNotEmpty()) {
            $activeBatchId = $batches->first()->id;
        }

        $query = InventoryItem::with(['batch', 'transmittal', 'encoder'])
            ->orderBy('item_no', 'asc')
            ->orderBy('id', 'asc');

        if (!empty($activeBatchId) && $activeBatchId !== 'all') {
            $query->where('batch_id', $activeBatchId);
        } else {
            $query->whereNotNull('batch_id');
        }

        if ($statusFilter) {
            if ($statusFilter === 'In process' || $statusFilter === 'IN_PROCESS') {
                $query->where(function($q) {
                    $q->whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])
                      ->orWhereNull('repair_status');
                });
            } else {
                $query->where('repair_status', $statusFilter);
            }
        }

        if ($brandFilter) {
            $query->where('brand', $brandFilter);
        }

        $items = $query->paginate(50)->withQueryString();

        $selectedBatch = !empty($activeBatchId) && $activeBatchId !== 'all' 
            ? Batch::with(['items.transmittal'])->find($activeBatchId) 
            : null;

        $metricsQuery = InventoryItem::query();
        if ($selectedBatch) {
            $metricsQuery->where('batch_id', $selectedBatch->id);
        } else {
            $metricsQuery->whereNotNull('batch_id');
        }

        $repairedCount = (clone $metricsQuery)->where('repair_status', 'Repaired')->count();
        $inProcessCount = (clone $metricsQuery)->where(function($q) {
            $q->whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])
              ->orWhereNull('repair_status');
        })->count();
        $berCount = (clone $metricsQuery)->where('repair_status', 'BER')->count();

        $unbatchedUnitsCount = InventoryItem::whereNull('batch_id')->count();

        return view('encoder.traceability.index', compact(
            'items',
            'batches',
            'selectedBatch',
            'activeBatchId',
            'companies',
            'companyFilter',
            'repairedCount',
            'inProcessCount',
            'berCount',
            'unbatchedUnitsCount'
        ));
    }

    public function createBatch(Request $request)
    {
        $nextBatchNo = 'BATCH ' . (Batch::count() + 1);

        $availableItems = InventoryItem::with('transmittal')
            ->whereNull('batch_id')
            ->orderBy('transmittal_id', 'desc')
            ->orderBy('item_no', 'asc')
            ->get();

        $transmittals = Transmittal::whereHas('items', function($q) {
            $q->whereNull('batch_id');
        })->latest()->get();

        $companies = \App\Models\User::where('role', 'client')->whereNotNull('company_name')->pluck('company_name')
            ->merge(Transmittal::whereNotNull('company_name')->pluck('company_name'))
            ->merge(InventoryItem::whereNotNull('company_name')->pluck('company_name'))
            ->merge(Batch::whereNotNull('company_name')->pluck('company_name'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $registeredClients = $companies;

        return view('encoder.traceability.create_batch', compact('nextBatchNo', 'availableItems', 'transmittals', 'registeredClients', 'companies'));
    }

    public function storeBatch(Request $request)
    {
        $selectedItemIds = $request->input('selected_items', []);

        if (empty($selectedItemIds) || !is_array($selectedItemIds)) {
            return back()->withInput()->with('error', 'Please select at least one unit to include in this Traceability Batch.');
        }

        $batch = DB::transaction(function () use ($request, $selectedItemIds) {
            $items = InventoryItem::whereIn('id', $selectedItemIds)->with('transmittal')->get();
            $firstItem = $items->first();

            $batchNo = $request->input('batch_no') ?: ('BATCH ' . (Batch::count() + 1));
            $resolvedCompany = $firstItem?->company_name ?: ($firstItem?->transmittal?->company_name ?? null);
            $companyName = $request->filled('company_name') ? trim($request->input('company_name')) : ($resolvedCompany ?: 'DFTM DIGITAL SOLUTIONS');
            $brand = $request->input('brand') ?: ($firstItem?->brand ?? null);
            $model = $request->input('model') ?: ($firstItem?->model ?? null);
            $dateDelivered = $request->input('date_delivered', now()->format('Y-m-d'));
            $defaultStatus = $request->input('status') ?: 'In process';

            $batch = Batch::create([
                'batch_no' => $batchNo,
                'company_name' => $companyName,
                'date_delivered' => $dateDelivered,
                'brand' => $brand,
                'model' => $model,
                'total_quantity' => count($selectedItemIds),
                'in_stock_quantity' => count($selectedItemIds),
                'status' => $defaultStatus,
                'notes' => $request->input('notes'),
                'encoded_by' => Auth::id(),
            ]);

            $oldBatchIds = $items->pluck('batch_id')->filter()->unique();

            // Assign batch to items and ensure company_name is populated
            foreach ($items as $idx => $item) {
                $targetCompany = $item->company_name ?: ($companyName !== 'DFTM DIGITAL SOLUTIONS' ? $companyName : ($item->transmittal?->company_name ?? $companyName));
                $item->update([
                    'batch_id' => $batch->id,
                    'company_name' => $targetCompany,
                    'repair_status' => $item->repair_status ?: $defaultStatus,
                    'stock_status' => 'IN_STOCK',
                ]);
            }

            $batch->recalculateQuantities();

            foreach ($oldBatchIds as $oldId) {
                if ($oldId != $batch->id) {
                    Batch::find($oldId)?->recalculateQuantities();
                }
            }

            ActivityLog::log('TRACEABILITY_BATCH_CREATED', "Encoder created Traceability Batch {$batch->batch_no} with {$batch->total_quantity} units.");

            return $batch;
        });

        return redirect()->route('encoder.traceability.index', ['batch_id' => $batch->id])
            ->with('success', "Traceability Batch {$batch->batch_no} created successfully with {$batch->total_quantity} units!");
    }

    public function bulkUpdate(Request $request)
    {
        $itemIds = $request->input('item_ids', []);
        if (empty($itemIds) || !is_array($itemIds)) {
            return response()->json(['success' => false, 'message' => 'No items selected.'], 400);
        }

        $updates = [];
        if ($request->filled('status')) {
            $updates['repair_status'] = trim($request->input('status'));
        }
        if ($request->filled('technical_diagnostic')) {
            $updates['technical_diagnostic'] = trim($request->input('technical_diagnostic'));
        }
        if ($request->filled('replace_parts')) {
            $updates['replace_parts'] = trim($request->input('replace_parts'));
        }

        if (empty($updates)) {
            return response()->json(['success' => false, 'message' => 'No update fields provided.'], 400);
        }

        InventoryItem::whereIn('id', $itemIds)->update($updates);

        ActivityLog::log('TRACEABILITY_BULK_UPDATED', "Encoder bulk updated " . count($itemIds) . " items in Traceability.");

        return response()->json([
            'success' => true,
            'message' => "Successfully updated " . count($itemIds) . " items.",
            'updated_fields' => $updates,
        ]);
    }

    public function saveItem(Request $request)
    {
        $itemId = $request->input('item_id');
        $item = InventoryItem::findOrFail($itemId);

        $item->update([
            'technical_diagnostic' => $request->input('technical_diagnostic', $item->technical_diagnostic),
            'replace_parts' => $request->input('replace_parts', $item->replace_parts),
            'repair_status' => $request->input('repair_status', $item->repair_status),
            'box_no' => $request->input('box_no', $item->box_no),
            'notes' => $request->input('notes', $item->notes),
        ]);

        return response()->json([
            'success' => true,
            'item' => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $item->update([
            'technical_diagnostic' => $request->input('technical_diagnostic', $item->technical_diagnostic),
            'replace_parts' => $request->input('replace_parts', $item->replace_parts),
            'repair_status' => $request->input('repair_status', $item->repair_status),
            'box_no' => $request->input('box_no', $item->box_no),
            'notes' => $request->input('notes', $item->notes),
        ]);

        ActivityLog::log('TRACEABILITY_UPDATED', "Encoder updated repair traceability for SN: {$item->serial_number}.");

        return back()->with('success', "Traceability record for SN: {$item->serial_number} updated!");
    }

    public function destroyBatch($id)
    {
        $batch = Batch::findOrFail($id);
        $batchNo = $batch->batch_no;

        InventoryItem::where('batch_id', $batch->id)->update([
            'batch_id' => null,
        ]);

        $batch->delete();

        ActivityLog::log('TRACEABILITY_BATCH_DELETED', "Encoder deleted Batch {$batchNo}. Units returned to unbatched pool.");

        return redirect()->route('encoder.traceability.index')->with('success', "Batch {$batchNo} deleted. All units returned to incoming pool.");
    }

    public function lookup(Request $request)
    {
        $code = trim($request->input('code') ?? $request->input('query') ?? '');
        if (empty($code)) {
            return response()->json(['found' => false, 'message' => 'Please enter or scan a Serial Number or MAC Address.'], 400);
        }

        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', $code);

        $item = InventoryItem::with(['batch', 'transmittal', 'encoder'])
            ->where(function($q) use ($code, $cleanCode) {
                $q->where('serial_number', $code)
                  ->orWhere('mac_address', $code);
                if (!empty($cleanCode)) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(serial_number, ':', ''), '-', ''), ' ', '') = ?", [$cleanCode])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(mac_address, ':', ''), '-', ''), ' ', '') = ?", [$cleanCode]);
                }
            })
            ->first();

        if (!$item) {
            $item = InventoryItem::with(['batch', 'transmittal', 'encoder'])
                ->where(function($q) use ($code) {
                    $q->where('serial_number', 'LIKE', "%{$code}%")
                      ->orWhere('mac_address', 'LIKE', "%{$code}%");
                })
                ->first();
        }

        if (!$item) {
            return response()->json(['found' => false, 'message' => "No unit found with Serial Number or MAC: \"{$code}\""], 404);
        }

        return response()->json([
            'found' => true,
            'item' => $item,
            'batch_id' => $item->batch_id,
            'batch_no' => $item->batch?->batch_no ?? 'Unbatched',
            'transmittal_no' => $item->transmittal?->transmittal_no ?? 'N/A',
            'message' => "Unit found: {$item->serial_number}"
        ]);
    }

    public function print(Request $request)
    {
        $batchId = $request->input('batch_id');
        $batch = null;

        $query = InventoryItem::with(['batch', 'transmittal'])
            ->orderBy('item_no', 'asc')
            ->orderBy('id', 'asc');

        if ($batchId && $batchId !== 'all') {
            $batch = Batch::find($batchId);
            $query->where('batch_id', $batchId);
        } else {
            $query->whereNotNull('batch_id');
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'In process' || $status === 'IN_PROCESS') {
                $query->where(function($q) {
                    $q->whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])
                      ->orWhereNull('repair_status');
                });
            } else {
                $query->where('repair_status', $status);
            }
        }

        $items = $query->get();
        $selectedBatch = $batch ?: ($items->first()?->batch);

        return view('print.traceability_matrix', compact('items', 'selectedBatch'));
    }
}
