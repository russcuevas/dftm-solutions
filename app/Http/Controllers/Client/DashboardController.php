<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transmittal;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\OutgoingSlip;
use App\Traits\ClientScopedQueries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    use ClientScopedQueries;

    public function index(Request $request)
    {
        $companyName = Auth::user()->company_name;

        // Auto-heal any batches/slips/items missing company_name if transmittal belongs to client
        $this->autoHealClientOwnership($companyName);

        // Scoped metrics
        $totalTransmittals = $this->getClientTransmittalsQuery($companyName)->count();
        $totalBatches = $this->getClientBatchesQuery($companyName)->count();
        $totalUnits = $this->getClientItemsQuery($companyName)->count();

        $inStockUnits = $this->getClientItemsQuery($companyName)
            ->where('stock_status', 'IN_STOCK')->count();
        $releasedUnits = $this->getClientItemsQuery($companyName)
            ->whereIn('stock_status', ['OUTGOING', 'RELEASED'])->count();

        $repairedCount = $this->getClientItemsQuery($companyName)
            ->where('repair_status', 'Repaired')->count();
        $inProcessCount = $this->getClientItemsQuery($companyName)
            ->whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])->count();
        $berCount = $this->getClientItemsQuery($companyName)
            ->where('repair_status', 'BER')->count();

        // Recent records
        $recentTransmittals = $this->getClientTransmittalsQuery($companyName)
            ->with('items')->latest()->take(5)->get();
        $recentBatches = $this->getClientBatchesQuery($companyName)
            ->with('items')->latest()->take(5)->get();
        $recentOutgoing = $this->getClientOutgoingSlipsQuery($companyName, $recentBatches)
            ->with('items')->latest()->take(5)->get();

        // Quick Serial / MAC Live Unit Search
        $searchResult = null;
        if ($request->filled('track_serial')) {
            $search = trim($request->input('track_serial'));
            $searchResult = $this->getClientItemsQuery($companyName)
                ->where(function($q) use ($search) {
                    $q->where('serial_number', 'like', "%{$search}%")
                      ->orWhere('mac_address', 'like', "%{$search}%");
                })
                ->with(['transmittal', 'batch', 'outgoingSlip'])
                ->get();
        }

        return view('client.dashboard.index', compact(
            'companyName',
            'totalTransmittals',
            'totalBatches',
            'totalUnits',
            'inStockUnits',
            'releasedUnits',
            'repairedCount',
            'inProcessCount',
            'berCount',
            'recentTransmittals',
            'recentBatches',
            'recentOutgoing',
            'searchResult'
        ));
    }
}
