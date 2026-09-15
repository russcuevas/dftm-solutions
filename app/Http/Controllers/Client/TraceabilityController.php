<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TraceabilityController extends Controller
{
    public function index(Request $request)
    {
        $companyName = Auth::user()->company_name;

        $batches = Batch::where('company_name', $companyName)->with('items')->latest()->get();

        $activeBatchId = $request->input('batch_id');
        if (!$activeBatchId && $batches->isNotEmpty()) {
            $activeBatchId = $batches->first()->id;
        }

        $query = InventoryItem::where('company_name', $companyName)
            ->with(['batch', 'transmittal'])
            ->orderBy('item_no', 'asc')
            ->orderBy('id', 'asc');

        if (!empty($activeBatchId) && $activeBatchId !== 'all') {
            $query->where('batch_id', $activeBatchId);
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

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('technical_diagnostic', 'like', "%{$search}%")
                  ->orWhere('replace_parts', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(50)->withQueryString();

        $selectedBatch = (!empty($activeBatchId) && $activeBatchId !== 'all')
            ? Batch::where('company_name', $companyName)->with('items')->find($activeBatchId)
            : null;

        // Metrics for selected batch
        $metricsQuery = InventoryItem::where('company_name', $companyName);
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

        return view('client.traceability.index', compact(
            'companyName',
            'items',
            'batches',
            'selectedBatch',
            'activeBatchId',
            'repairedCount',
            'inProcessCount',
            'berCount'
        ));
    }

    public function print(Request $request)
    {
        $companyName = Auth::user()->company_name;
        $batchId = $request->input('batch_id');

        $batchObj = Batch::where('company_name', $companyName)->findOrFail($batchId);
        $items = $batchObj->items()->orderBy('item_no', 'asc')->get();

        $slipObj = null;
        $firstItem = $items->first();
        $currentStatus = $batchObj->status ?: ($firstItem?->repair_status ?: 'In process');

        return view('print.traceability_matrix', compact(
            'batchObj',
            'slipObj',
            'items',
            'firstItem',
            'currentStatus'
        ));
    }
}
