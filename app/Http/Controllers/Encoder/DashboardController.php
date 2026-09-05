<?php

namespace App\Http\Controllers\Encoder;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\OutgoingSlip;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $myEncodedBatches = Batch::where('encoded_by', auth()->id())->count();
        $totalInStock = InventoryItem::where('stock_status', 'IN_STOCK')->count();
        $totalRepaired = InventoryItem::where('repair_status', 'Repaired')->count();
        $totalInProcess = InventoryItem::whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])->count();
        $totalBer = InventoryItem::where('repair_status', 'BER')->count();
        $totalOutgoing = OutgoingSlip::count();

        $recentBatches = Batch::with('items')->latest()->take(5)->get();
        $recentOutgoing = OutgoingSlip::with('items')->latest()->take(5)->get();

        return view('encoder.dashboard.index', compact(
            'myEncodedBatches',
            'totalInStock',
            'totalRepaired',
            'totalInProcess',
            'totalBer',
            'totalOutgoing',
            'recentBatches',
            'recentOutgoing'
        ));
    }
}
