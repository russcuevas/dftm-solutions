<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transmittal;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\OutgoingSlip;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTransmittals = Transmittal::count();
        $totalBatches = Batch::count();
        $totalUnits = InventoryItem::count();
        $inStockUnits = InventoryItem::where('stock_status', 'IN_STOCK')->count();
        $releasedUnits = InventoryItem::whereIn('stock_status', ['OUTGOING', 'RELEASED'])->count();
        
        $repairedCount = InventoryItem::where('repair_status', 'Repaired')->count();
        $inProcessCount = InventoryItem::whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])->count();
        $berCount = InventoryItem::where('repair_status', 'BER')->count();

        // Recent items
        $recentTransmittals = Transmittal::with('items')->latest()->take(5)->get();
        $recentBatches = Batch::with('items')->latest()->take(5)->get();
        $recentOutgoing = OutgoingSlip::with('items')->latest()->take(5)->get();
        $recentLogs = ActivityLog::latest()->take(8)->get();

        // Brand distribution
        $brandDistribution = InventoryItem::selectRaw('COALESCE(brand, "Unassigned") as brand_name, count(*) as count')
            ->groupBy('brand_name')
            ->get();

        return view('admin.dashboard.index', compact(
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
            'recentLogs',
            'brandDistribution'
        ));
    }
}
