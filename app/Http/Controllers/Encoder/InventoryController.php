<?php

namespace App\Http\Controllers\Encoder;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Batch;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with(['transmittal', 'batch', 'outgoingSlip', 'encoder'])
            ->orderByRaw("
                CASE 
                    WHEN UPPER(COALESCE(repair_status, '')) IN ('IN PROCESS', 'PENDING', 'IN_PROCESS', '') OR repair_status IS NULL THEN 1 
                    ELSE 0 
                END ASC
            ")
            ->latest('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('box_no', 'like', "%{$search}%")
                  ->orWhereHas('batch', function ($bq) use ($search) {
                      $bq->where('batch_no', 'like', "%{$search}%");
                  })
                  ->orWhereHas('transmittal', function ($tq) use ($search) {
                      $tq->where('transmittal_no', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        if ($request->filled('stock_status')) {
            $query->where('stock_status', $request->stock_status);
        }

        $items = $query->paginate(20)->withQueryString();
        $batches = Batch::all();

        return view('encoder.inventory.index', compact('items', 'batches'));
    }
}
