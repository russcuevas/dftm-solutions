<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\OutgoingSlip;
use App\Models\InventoryItem;
use App\Traits\ClientScopedQueries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutgoingController extends Controller
{
    use ClientScopedQueries;

    public function index(Request $request)
    {
        $companyName = Auth::user()->company_name;

        // Auto-heal any batches/slips missing company_name if transmittal belongs to client
        $this->autoHealClientOwnership($companyName);

        // Fetch batches that belong to this client
        $batches = $this->getClientBatchesQuery($companyName)->with('items')->latest()->get();

        $selectedBatchId = $request->input('batch_id');
        if (!$selectedBatchId && $batches->isNotEmpty()) {
            $selectedBatchId = $batches->first()->id;
        }

        $selectedBatch = null;
        $items = collect();
        if ($selectedBatchId) {
            $selectedBatch = $this->getClientBatchesQuery($companyName)->with('items')->find($selectedBatchId);
            if ($selectedBatch) {
                $items = $selectedBatch->items;
            }
        }

        // Recent release records
        $recentOutgoing = $this->getClientOutgoingSlipsQuery($companyName, $batches)
            ->with('items')->latest()->paginate(15);

        return view('client.outgoing.index', compact(
            'companyName',
            'batches',
            'selectedBatch',
            'selectedBatchId',
            'items',
            'recentOutgoing'
        ));
    }

    public function print(Request $request)
    {
        $companyName = Auth::user()->company_name;
        $batchId = $request->input('batch_id');

        $batch = $this->getClientBatchesQuery($companyName)->with('items')->findOrFail($batchId);
        $items = $batch->items;

        $customerName = $batch->company_name ?: $companyName;
        $siNumber = $request->input('si_number');
        $drNumber = $request->input('dr_number');
        $dateReleased = $request->input('date_released', now()->format('Y-m-d'));

        return view('print.outgoing_slip', compact(
            'batch',
            'items',
            'customerName',
            'siNumber',
            'drNumber',
            'dateReleased'
        ));
    }
}
