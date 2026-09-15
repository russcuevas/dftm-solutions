<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transmittal;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\OutgoingSlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $companyName = Auth::user()->company_name;

        // Scoped metrics
        $totalTransmittals = Transmittal::where('company_name', $companyName)->count();
        $totalBatches = Batch::where('company_name', $companyName)->count();
        $totalUnits = InventoryItem::where('company_name', $companyName)->count();

        $inStockUnits = InventoryItem::where('company_name', $companyName)
            ->where('stock_status', 'IN_STOCK')->count();
        $releasedUnits = InventoryItem::where('company_name', $companyName)
            ->whereIn('stock_status', ['OUTGOING', 'RELEASED'])->count();

        $repairedCount = InventoryItem::where('company_name', $companyName)
            ->where('repair_status', 'Repaired')->count();
        $inProcessCount = InventoryItem::where('company_name', $companyName)
            ->whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])->count();
        $berCount = InventoryItem::where('company_name', $companyName)
            ->where('repair_status', 'BER')->count();

        // Recent records
        $recentTransmittals = Transmittal::where('company_name', $companyName)
            ->with('items')->latest()->take(5)->get();
        $recentBatches = Batch::where('company_name', $companyName)
            ->with('items')->latest()->take(5)->get();
        $recentOutgoing = OutgoingSlip::where('company_name', $companyName)
            ->with('items')->latest()->take(5)->get();

        // Quick Serial / MAC Live Unit Search
        $searchResult = null;
        if ($request->filled('track_serial')) {
            $search = trim($request->input('track_serial'));
            $searchResult = InventoryItem::where('company_name', $companyName)
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
