<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutgoingSlip;
use App\Models\InventoryItem;
use App\Models\Batch;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OutgoingController extends Controller
{
    public function index(Request $request)
    {
        $query = OutgoingSlip::with(['items', 'encoder'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('slip_no', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('si_number', 'like', "%{$search}%")
                    ->orWhere('dr_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $outgoingSlips = $query->paginate(15)->withQueryString();

        return view('admin.outgoing.index', compact('outgoingSlips'));
    }

    public function create(Request $request)
    {
        $slipNo = 'ORS-' . date('Ymd') . '-' . str_pad(OutgoingSlip::count() + 1, 3, '0', STR_PAD_LEFT);

        // Fetch all in-stock items available for release
        $availableItems = InventoryItem::with('batch')
            ->where('stock_status', 'IN_STOCK')
            ->orderBy('batch_id')
            ->orderBy('item_no')
            ->get();

        $batches = Batch::where('in_stock_quantity', '>', 0)->get();
        $selectedBatchId = $request->input('batch_id');

        return view('admin.outgoing.create', compact('slipNo', 'availableItems', 'batches', 'selectedBatchId'));
    }

    public function store(Request $request)
    {
        $selectedItemIds = $request->input('selected_items', []);

        if (empty($selectedItemIds) || !is_array($selectedItemIds)) {
            return back()->withInput()->with('error', 'Please select at least one serialized item to release/outgoing.');
        }

        $slip = DB::transaction(function () use ($request, $selectedItemIds) {
            $slipNo = $request->input('slip_no') ?: ('ORS-' . date('Ymd') . '-' . str_pad(OutgoingSlip::count() + 1, 3, '0', STR_PAD_LEFT));
            $items = InventoryItem::whereIn('id', $selectedItemIds)->get();

            $firstItem = $items->first();
            $brand = $request->input('brand') ?: ($firstItem?->brand ?? null);
            $model = $request->input('model') ?: ($firstItem?->model ?? null);
            $companyName = $request->input('company_name') ?: ($firstItem?->company_name ?? null);
            $batchNo = $request->input('batch_no') ?: ($firstItem?->batch?->batch_no ?? null);
            $dateDelivered = $request->input('date_delivered', now()->format('Y-m-d'));

            $slip = OutgoingSlip::create([
                'slip_no' => $slipNo,
                'company_name' => $companyName,
                'customer_name' => $request->input('customer_name'),
                'contact_number' => $request->input('contact_number'),
                'date_delivered' => $dateDelivered,
                'date_released' => $dateDelivered,
                'si_number' => $request->input('si_number'),
                'dr_number' => $request->input('dr_number'),
                'batch_no' => $batchNo,
                'brand' => $brand,
                'model' => $model,
                'box_no' => $request->input('box_no'),
                'total_quantity' => count($selectedItemIds),
                'status' => $request->input('status') ?: null,
                'notes' => $request->input('notes'),
                'encoded_by' => Auth::id(),
            ]);

            // Update each item: attach to slip, update customer & SI/DR details, deduct from in_stock
            $affectedBatchIds = [];
            foreach ($items as $index => $item) {
                $item->update([
                    'outgoing_slip_id' => $slip->id,
                    'stock_status' => 'RELEASED',
                    'customer_name' => $slip->customer_name,
                    'customer_contact' => $slip->contact_number,
                    'si_number' => $slip->si_number,
                    'dr_number' => $slip->dr_number,
                    'date_outgoing' => $slip->date_delivered,
                    'box_no' => $request->input('box_no') ?: $item->box_no,
                ]);

                if ($item->batch_id) {
                    $affectedBatchIds[$item->batch_id] = true;
                }
            }

            // Recalculate batch quantities
            foreach (array_keys($affectedBatchIds) as $bId) {
                $batch = Batch::find($bId);
                if ($batch) {
                    $batch->recalculateQuantities();
                }
            }

            ActivityLog::log('OUTGOING_CREATED', "Created Outgoing Repair Slip {$slip->slip_no} (SI: {$slip->si_number}, DR: {$slip->dr_number}) with {$slip->total_quantity} items released to {$slip->customer_name}.");

            return $slip;
        });

        return redirect()->route('admin.outgoing.show', $slip->id)->with('success', "Outgoing Repair Slip {$slip->slip_no} generated and stock successfully deducted!");
    }

    public function show($id)
    {
        $slip = OutgoingSlip::with(['items', 'encoder'])->findOrFail($id);
        return view('admin.outgoing.show', compact('slip'));
    }

    public function edit($id)
    {
        $slip = OutgoingSlip::with('items')->findOrFail($id);

        $availableItems = InventoryItem::with('batch')
            ->where('stock_status', 'IN_STOCK')
            ->orderBy('batch_id')
            ->orderBy('item_no')
            ->get();

        $batches = Batch::where('in_stock_quantity', '>', 0)->get();

        return view('admin.outgoing.edit', compact('slip', 'availableItems', 'batches'));
    }

    public function update(Request $request, $id)
    {
        $slip = OutgoingSlip::with('items')->findOrFail($id);

        DB::transaction(function () use ($request, $slip) {
            $dateDelivered = $request->input('date_delivered', $slip->date_delivered);

            $slip->update([
                'slip_no' => $request->input('slip_no', $slip->slip_no),
                'company_name' => $request->input('company_name'),
                'si_number' => $request->input('si_number'),
                'dr_number' => $request->input('dr_number'),
                'date_delivered' => $dateDelivered,
                'date_released' => $dateDelivered,
                'batch_no' => $request->input('batch_no', $slip->batch_no),
                'brand' => $request->input('brand', $slip->brand),
                'model' => $request->input('model', $slip->model),
                'box_no' => $request->input('box_no'),
                'status' => $request->input('status') ?: null,
                'notes' => $request->input('notes'),
            ]);

            $itemIds = $request->input('item_id', []);
            $serials = $request->input('serial_number', []);
            $macs = $request->input('mac_address', []);
            $boxes = $request->input('box_no_item', $request->input('box_no', []));
            $newSelectedUnitIds = $request->input('new_selected_items', []);

            $activeSlipItemIds = [];
            $affectedBatchIds = [];

            // 1. Process current / modified items in this slip
            if (is_array($serials)) {
                foreach ($serials as $i => $sn) {
                    $itemId = $itemIds[$i] ?? null;
                    $mac = $macs[$i] ?? null;
                    $box = $boxes[$i] ?? null;

                    if (!empty($sn) || !empty($mac) || !empty($box)) {
                        if ($itemId) {
                            $item = InventoryItem::find($itemId);
                            if ($item && $item->outgoing_slip_id == $slip->id) {
                                $item->update([
                                    'serial_number' => $sn ? trim($sn) : null,
                                    'mac_address' => $mac ? trim($mac) : null,
                                    'box_no' => $box ? trim($box) : null,
                                    'brand' => $slip->brand ?: $item->brand,
                                    'model' => $slip->model ?: $item->model,
                                    'company_name' => $slip->company_name,
                                    'si_number' => $slip->si_number,
                                    'dr_number' => $slip->dr_number,
                                    'date_outgoing' => $slip->date_delivered,
                                ]);
                                $activeSlipItemIds[] = $item->id;
                                if ($item->batch_id) $affectedBatchIds[$item->batch_id] = true;
                            }
                        } else {
                            // New row item added to outgoing slip
                            $newItem = InventoryItem::create([
                                'outgoing_slip_id' => $slip->id,
                                'serial_number' => $sn ? trim($sn) : null,
                                'mac_address' => $mac ? trim($mac) : null,
                                'box_no' => $box ? trim($box) : $slip->box_no,
                                'brand' => $slip->brand,
                                'model' => $slip->model,
                                'company_name' => $slip->company_name,
                                'stock_status' => 'RELEASED',
                                'repair_status' => 'In process',
                                'si_number' => $slip->si_number,
                                'dr_number' => $slip->dr_number,
                                'date_outgoing' => $slip->date_delivered,
                                'encoded_by' => Auth::id(),
                            ]);
                            $activeSlipItemIds[] = $newItem->id;
                        }
                    }
                }
            }

            // 2. Return removed items to in-stock inventory
            $removedItems = $slip->items()->whereNotIn('id', $activeSlipItemIds)->get();
            foreach ($removedItems as $removed) {
                if ($removed->batch_id) $affectedBatchIds[$removed->batch_id] = true;
                $removed->update([
                    'outgoing_slip_id' => null,
                    'stock_status' => 'IN_STOCK',
                    'si_number' => null,
                    'dr_number' => null,
                    'date_outgoing' => null,
                ]);
            }

            // 3. Attach newly selected available units from stock to this slip
            if (!empty($newSelectedUnitIds) && is_array($newSelectedUnitIds)) {
                $newUnits = InventoryItem::whereIn('id', $newSelectedUnitIds)
                    ->where('stock_status', 'IN_STOCK')
                    ->get();

                foreach ($newUnits as $unit) {
                    $unit->update([
                        'outgoing_slip_id' => $slip->id,
                        'stock_status' => 'RELEASED',
                        'si_number' => $slip->si_number,
                        'dr_number' => $slip->dr_number,
                        'date_outgoing' => $slip->date_delivered,
                        'company_name' => $slip->company_name ?: $unit->company_name,
                    ]);
                    $activeSlipItemIds[] = $unit->id;
                    if ($unit->batch_id) $affectedBatchIds[$unit->batch_id] = true;
                }
            }

            $totalCount = count($activeSlipItemIds);
            $slip->update(['total_quantity' => $totalCount]);

            foreach (array_keys($affectedBatchIds) as $bId) {
                if ($bId) {
                    $b = Batch::find($bId);
                    $b?->recalculateQuantities();
                }
            }

            ActivityLog::log('OUTGOING_UPDATED', "Updated Outgoing Slip {$slip->slip_no} with {$totalCount} items.");
        });

        return redirect()->route('admin.outgoing.show', $slip->id)->with('success', "Outgoing Repair Slip {$slip->slip_no} updated successfully! (Stocks updated)");
    }

    public function destroy($id)
    {
        $slip = OutgoingSlip::with('items')->findOrFail($id);
        $slipNo = $slip->slip_no;

        DB::transaction(function () use ($slip) {
            $batchIds = [];
            foreach ($slip->items as $item) {
                $batchIds[$item->batch_id] = true;
                $item->update([
                    'outgoing_slip_id' => null,
                    'stock_status' => 'IN_STOCK',
                    'customer_name' => null,
                    'customer_contact' => null,
                    'si_number' => null,
                    'dr_number' => null,
                    'date_outgoing' => null,
                ]);
            }

            $slip->delete();

            foreach (array_keys($batchIds) as $bId) {
                if ($bId) {
                    $batch = Batch::find($bId);
                    $batch?->recalculateQuantities();
                }
            }
        });

        ActivityLog::log('OUTGOING_CANCELLED', "Cancelled Outgoing Slip {$slipNo} and returned units to inventory.");

        return redirect()->route('admin.outgoing.index')->with('success', "Outgoing Slip {$slipNo} deleted and items returned to stock.");
    }

    public function print($id)
    {
        $slip = OutgoingSlip::with('items')->findOrFail($id);
        return view('print.outgoing_slip', compact('slip'));
    }
}
