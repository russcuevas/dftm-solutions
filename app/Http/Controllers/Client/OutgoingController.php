<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\OutgoingSlip;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutgoingController extends Controller
{
    public function index(Request $request)
    {
        $companyName = Auth::user()->company_name;

        // Fetch batches that belong to this client
        $batches = Batch::where('company_name', $companyName)->with('items')->latest()->get();

        $selectedBatchId = $request->input('batch_id');
        if (!$selectedBatchId && $batches->isNotEmpty()) {
            $selectedBatchId = $batches->first()->id;
        }

        $selectedBatch = null;
        $items = collect();
        if ($selectedBatchId) {
            $selectedBatch = Batch::where('company_name', $companyName)->with('items')->find($selectedBatchId);
            if ($selectedBatch) {
                $items = $selectedBatch->items;
            }
        }

        // Recent release records
        $recentOutgoing = OutgoingSlip::where('company_name', $companyName)
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

        $batch = Batch::where('company_name', $companyName)->with('items')->findOrFail($batchId);
        $items = $batch->items;

        $customerName = $batch->company_name;
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
