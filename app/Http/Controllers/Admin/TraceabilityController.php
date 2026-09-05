<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Batch;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $outgoingSlips = \App\Models\OutgoingSlip::with('items')->latest()->get();
        $batches = Batch::whereHas('items', function($q) {
            $q->whereNotNull('outgoing_slip_id');
        })->latest()->get();

        $activeSlipId = $request->input('outgoing_slip_id');
        $activeBatchId = $request->input('batch_id');
        $tab = $request->input('tab');
        $openItemId = $request->input('open_item_id');
        $openItem = null;

        if (!empty($openItemId)) {
            $openItem = InventoryItem::with(['outgoingSlip', 'batch', 'encoder'])->find($openItemId);
            if ($openItem && empty($activeSlipId) && !empty($openItem->outgoing_slip_id)) {
                $activeSlipId = $openItem->outgoing_slip_id;
            }
        }

        $query = InventoryItem::with(['outgoingSlip', 'batch', 'encoder'])
            ->whereNotNull('outgoing_slip_id')
            ->orderBy('item_no', 'asc')
            ->orderBy('id', 'asc');

        $hasSelection = $request->filled('outgoing_slip_id') 
            || $request->filled('batch_id') 
            || $request->filled('tab') 
            || $request->filled('status') 
            || $request->filled('brand')
            || !empty($openItem);

        if (!$hasSelection) {
            $query->whereRaw('1 = 0');
        }

        if (!empty($activeSlipId) && $activeSlipId !== 'all') {
            $query->where('outgoing_slip_id', $activeSlipId);
        } elseif (!empty($activeBatchId) && $activeBatchId !== 'all') {
            $query->where('batch_id', $activeBatchId);
        }

        if ($tab) {
            if (str_ends_with($tab, '-BER')) {
                $brandName = substr($tab, 0, -4);
                $query->where('brand', 'like', $brandName)->where('repair_status', 'BER');
            } elseif (str_ends_with($tab, '-INPROCESS')) {
                $brandName = substr($tab, 0, -10);
                $query->where('brand', 'like', $brandName)->where(function($q) {
                    $q->where('repair_status', 'In process')
                      ->orWhere('repair_status', 'IN_PROCESS')
                      ->orWhere('repair_status', 'PENDING')
                      ->orWhereNull('repair_status');
                });
            } elseif (str_ends_with($tab, '-REPAIRED')) {
                $brandName = substr($tab, 0, -9);
                $query->where('brand', 'like', $brandName)->where('repair_status', 'Repaired');
            } else {
                $query->where('brand', 'like', $tab);
            }
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

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        $items = $query->paginate(50)->withQueryString();

        $brandQuery = InventoryItem::whereNotNull('outgoing_slip_id')
            ->whereNotNull('brand')
            ->where('brand', '!=', '');

        if (!empty($activeSlipId) && $activeSlipId !== 'all') {
            $brandQuery->where('outgoing_slip_id', $activeSlipId);
        } elseif (!empty($activeBatchId) && $activeBatchId !== 'all') {
            $brandQuery->where('batch_id', $activeBatchId);
        }

        $brands = $brandQuery->distinct()
            ->pluck('brand')
            ->map(fn($b) => strtoupper(trim($b)))
            ->unique()
            ->values();

        $hasBatchSelection = (!empty($activeSlipId) && $activeSlipId !== 'all') 
            || (!empty($activeBatchId) && $activeBatchId !== 'all') 
            || !empty($tab);

        if (!$hasBatchSelection) {
            $repairedCount = 0;
            $inProcessCount = 0;
            $berCount = 0;
        } else {
            $metricsQuery = InventoryItem::whereNotNull('outgoing_slip_id');
            if (!empty($activeSlipId) && $activeSlipId !== 'all') {
                $metricsQuery->where('outgoing_slip_id', $activeSlipId);
            } elseif (!empty($activeBatchId) && $activeBatchId !== 'all') {
                $metricsQuery->where('batch_id', $activeBatchId);
            }

            $repairedCount = (clone $metricsQuery)->where('repair_status', 'Repaired')->count();
            $inProcessCount = (clone $metricsQuery)->where(function($q) {
                $q->whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])
                  ->orWhereNull('repair_status');
            })->count();
            $berCount = (clone $metricsQuery)->where('repair_status', 'BER')->count();
        }

        $firstItem = $items->first();
        $selectedSlip = (!empty($activeSlipId) && $activeSlipId !== 'all') 
            ? \App\Models\OutgoingSlip::find($activeSlipId) 
            : ($firstItem?->outgoingSlip ?? null);

        $selectedBatch = $selectedSlip ? Batch::where('batch_no', $selectedSlip->batch_no)->first() : ($firstItem?->batch);

        return view('admin.traceability.index', compact(
            'items',
            'batches',
            'outgoingSlips',
            'brands',
            'repairedCount',
            'inProcessCount',
            'berCount',
            'selectedBatch',
            'selectedSlip',
            'firstItem',
            'activeSlipId',
            'activeBatchId',
            'tab',
            'openItem'
        ));
    }

    public function lookup(Request $request)
    {
        $code = trim($request->input('code') ?? $request->input('query') ?? '');
        if (empty($code)) {
            return response()->json([
                'found' => false,
                'message' => 'Please enter or scan a Serial Number or MAC Address.'
            ], 400);
        }

        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', $code);

        // Search exact match or cleaned alphanumeric match
        $item = InventoryItem::with(['outgoingSlip', 'batch', 'encoder'])
            ->where(function($q) use ($code, $cleanCode) {
                $q->where('serial_number', $code)
                  ->orWhere('mac_address', $code);
                if (!empty($cleanCode)) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(serial_number, ':', ''), '-', ''), ' ', '') = ?", [$cleanCode])
                      ->orWhereRaw("REPLACE(REPLACE(REPLACE(mac_address, ':', ''), '-', ''), ' ', '') = ?", [$cleanCode]);
                }
            })
            ->first();

        // Fallback fuzzy search if not found
        if (!$item) {
            $item = InventoryItem::with(['outgoingSlip', 'batch', 'encoder'])
                ->where(function($q) use ($code) {
                    $q->where('serial_number', 'LIKE', "%{$code}%")
                      ->orWhere('mac_address', 'LIKE', "%{$code}%");
                })
                ->first();
        }

        if (!$item) {
            return response()->json([
                'found' => false,
                'message' => "No unit found with Serial Number or MAC: \"{$code}\""
            ], 404);
        }

        $statusParam = match(strtoupper(str_replace(' ', '_', $item->repair_status ?? 'IN_PROCESS'))) {
            'REPAIRED' => 'Repaired',
            'BER' => 'BER',
            default => 'In process'
        };

        return response()->json([
            'found' => true,
            'item' => $item,
            'outgoing_slip_id' => $item->outgoing_slip_id,
            'batch_id' => $item->batch_id,
            'batch_no' => $item->batch?->batch_no ?? $item->outgoingSlip?->batch_no,
            'slip_no' => $item->outgoingSlip?->slip_no,
            'has_outgoing_slip' => !empty($item->outgoing_slip_id),
            'status_param' => $statusParam,
            'message' => "Unit found: {$item->serial_number}"
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

        ActivityLog::log('TRACEABILITY_UPDATED', "Updated repair traceability for item SN: {$item->serial_number}.");

        return back()->with('success', "Traceability record for SN: {$item->serial_number} updated!");
    }

    public function print(Request $request)
    {
        $query = InventoryItem::with(['outgoingSlip', 'batch'])
            ->whereNotNull('outgoing_slip_id')
            ->orderBy('item_no', 'asc')
            ->orderBy('id', 'asc');

        if ($request->filled('outgoing_slip_id')) {
            $query->where('outgoing_slip_id', $request->outgoing_slip_id);
        } elseif ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
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

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        $items = $query->get();
        $filterStatus = $request->input('status', 'ALL');
        $firstItem = $items->first();
        $selectedSlip = $request->filled('outgoing_slip_id') ? \App\Models\OutgoingSlip::find($request->outgoing_slip_id) : ($firstItem?->outgoingSlip);
        $selectedBatch = $selectedSlip ? Batch::where('batch_no', $selectedSlip->batch_no)->first() : ($firstItem?->batch);

        return view('print.traceability_matrix', compact('items', 'filterStatus', 'selectedBatch', 'selectedSlip'));
    }
}
