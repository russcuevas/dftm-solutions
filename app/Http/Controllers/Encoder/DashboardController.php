<?php

namespace App\Http\Controllers\Encoder;

use App\Http\Controllers\Controller;
use App\Models\Transmittal;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\OutgoingSlip;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $myEncodedTransmittals = Transmittal::where('encoded_by', auth()->id())->count();
        $totalInStock = InventoryItem::where('stock_status', 'IN_STOCK')->count();
        $totalRepaired = InventoryItem::where('repair_status', 'Repaired')->count();
        $totalInProcess = InventoryItem::whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])->count();
        $totalBer = InventoryItem::where('repair_status', 'BER')->count();
        $totalBatches = Batch::count();

        $totalUnits = InventoryItem::count();
        $releasedUnits = InventoryItem::whereIn('stock_status', ['OUTGOING', 'RELEASED'])->count();

        $recentTransmittals = Transmittal::with('items')->latest()->take(5)->get();
        $recentBatches = Batch::with('items')->latest()->take(5)->get();
        $recentOutgoing = OutgoingSlip::with('items')->latest()->take(5)->get();

        return view('encoder.dashboard.index', compact(
            'myEncodedTransmittals',
            'totalInStock',
            'totalRepaired',
            'totalInProcess',
            'totalBer',
            'totalBatches',
            'totalUnits',
            'releasedUnits',
            'recentTransmittals',
            'recentBatches',
            'recentOutgoing'
        ));
    }
}
