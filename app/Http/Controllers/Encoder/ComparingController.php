<?php

namespace App\Http\Controllers\Encoder;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Batch;
use App\Models\Transmittal;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComparingController extends Controller
{
    /**
     * Display the Comparing & Batch Creation interface for Encoders
     */
    public function index(Request $request)
    {
        // 1. Get all companies that have unbatched / in-process items across any incoming transmittal
        $companiesWithCounts = InventoryItem::whereNull('batch_id')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->select('company_name', DB::raw('count(*) as unbatched_count'))
            ->groupBy('company_name')
            ->orderBy('company_name', 'asc')
            ->get();

        $allRegisteredCompanies = User::where('role', 'client')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->pluck('company_name')
            ->merge(Transmittal::whereNotNull('company_name')->where('company_name', '!=', '')->pluck('company_name'))
            ->merge(InventoryItem::whereNotNull('company_name')->where('company_name', '!=', '')->pluck('company_name'))
            ->unique()
            ->sort()
            ->values();

        $selectedCompany = $request->input('company', '');

        $batchCount = Batch::count();
        $nextBatchNumber = 'BATCH ' . ($batchCount + 1);

        $recentBatches = Batch::latest()->take(5)->get();

        return view('admin.comparing.index', compact(
            'companiesWithCounts',
            'allRegisteredCompanies',
            'selectedCompany',
            'nextBatchNumber',
            'recentBatches'
        ));
    }

    /**
     * AJAX: Get all incoming unbatched / in-process units for a selected company
     */
    public function getCompanyUnits(Request $request)
    {
        $company = trim($request->input('company', ''));
        $filterBrand = trim($request->input('brand', ''));
        $filterModel = trim($request->input('model', ''));

        if (empty($company)) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a company name.',
                'units' => [],
                'brands_data' => [],
                'total' => 0
            ], 400);
        }

        $baseQuery = InventoryItem::with(['transmittal', 'encoder'])
            ->whereNull('batch_id')
            ->where(function ($q) use ($company) {
                $q->where('company_name', $company)
                  ->orWhereHas('transmittal', function ($tq) use ($company) {
                      $tq->where('company_name', $company);
                  });
            });

        $allItemsForCompany = (clone $baseQuery)->get();

        $brandsData = [];
        $groupedByBrand = $allItemsForCompany->groupBy(function($it) {
            return trim($it->brand ?: 'UNKNOWN');
        });

        foreach ($groupedByBrand as $bName => $bItems) {
            $modelsData = [];
            $groupedByModel = $bItems->groupBy(function($it) {
                return trim($it->model ?: 'UNKNOWN');
            });

            foreach ($groupedByModel as $mName => $mItems) {
                $modelsData[] = [
                    'model' => $mName,
                    'count' => $mItems->count(),
                ];
            }

            usort($modelsData, fn($a, $b) => strcmp($a['model'], $b['model']));

            $brandsData[] = [
                'brand' => $bName,
                'count' => $bItems->count(),
                'models' => $modelsData,
            ];
        }

        usort($brandsData, fn($a, $b) => strcmp($a['brand'], $b['brand']));

        $filteredQuery = clone $baseQuery;
        if (!empty($filterBrand) && $filterBrand !== 'all') {
            $filteredQuery->where('brand', $filterBrand);
        }
        if (!empty($filterModel) && $filterModel !== 'all') {
            $filteredQuery->where('model', $filterModel);
        }

        $items = $filteredQuery
            ->orderBy('transmittal_id', 'desc')
            ->orderBy('item_no', 'asc')
            ->get();

        $transmittalList = $items->pluck('transmittal.transmittal_no')->filter()->unique()->values();

        return response()->json([
            'success' => true,
            'company' => $company,
            'total' => $items->count(),
            'company_total' => $allItemsForCompany->count(),
            'brands_data' => $brandsData,
            'transmittals' => $transmittalList,
            'filter_brand' => $filterBrand,
            'filter_model' => $filterModel,
            'units' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_no' => $item->item_no,
                    'serial_number' => $item->serial_number ?? '',
                    'mac_address' => $item->mac_address ?? '',
                    'box_no' => $item->box_no ?? '',
                    'brand' => $item->brand ?? '',
                    'model' => $item->model ?? '',
                    'company_name' => $item->company_name ?: ($item->transmittal?->company_name ?? ''),
                    'transmittal_id' => $item->transmittal_id,
                    'transmittal_no' => $item->transmittal?->transmittal_no ?? 'TR-UNASSIGNED',
                    'date_received' => $item->date_delivered ? $item->date_delivered->format('Y-m-d') : ($item->transmittal?->date_received ? $item->transmittal->date_received->format('Y-m-d') : ''),
                    'technical_diagnostic' => $item->technical_diagnostic ?: 'Test and Clean',
                    'replace_parts' => $item->replace_parts ?: 'GOOD',
                    'repair_status' => $item->repair_status ?: 'In process',
                    'stock_status' => $item->stock_status ?: 'IN_STOCK',
                ];
            })
        ]);
    }

    /**
     * AJAX: Fast barcode scanner verification ("babarilin")
     */
    public function verifyScan(Request $request)
    {
        $code = trim($request->input('code', ''));
        $company = trim($request->input('company', ''));

        if (empty($code)) {
            return response()->json([
                'found' => false,
                'message' => 'Please scan or enter a Serial Number or MAC Address.'
            ], 400);
        }

        $cleanCode = preg_replace('/[^A-Za-z0-9]/', '', $code);

        $query = InventoryItem::with(['transmittal', 'batch', 'encoder']);

        if (!empty($company)) {
            $query->where(function ($q) use ($company) {
                $q->where('company_name', $company)
                  ->orWhereHas('transmittal', function ($tq) use ($company) {
                      $tq->where('company_name', $company);
                  });
            });
        }

        $item = (clone $query)->where(function ($q) use ($code, $cleanCode) {
            $q->where('serial_number', $code)
              ->orWhere('mac_address', $code);
            if (!empty($cleanCode)) {
                $q->orWhereRaw("REPLACE(REPLACE(REPLACE(serial_number, ':', ''), '-', ''), ' ', '') = ?", [$cleanCode])
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(mac_address, ':', ''), '-', ''), ' ', '') = ?", [$cleanCode]);
            }
        })->first();

        if (!$item) {
            $item = (clone $query)->where(function ($q) use ($code) {
                $q->where('serial_number', 'LIKE', "%{$code}%")
                  ->orWhere('mac_address', 'LIKE', "%{$code}%");
            })->first();
        }

        if (!$item) {
            $anywhereItem = InventoryItem::with(['transmittal', 'batch'])
                ->where(function ($q) use ($code, $cleanCode) {
                    $q->where('serial_number', $code)
                      ->orWhere('mac_address', $code);
                    if (!empty($cleanCode)) {
                        $q->orWhereRaw("REPLACE(REPLACE(REPLACE(serial_number, ':', ''), '-', ''), ' ', '') = ?", [$cleanCode])
                          ->orWhereRaw("REPLACE(REPLACE(REPLACE(mac_address, ':', ''), '-', ''), ' ', '') = ?", [$cleanCode]);
                    }
                })->first();

            if ($anywhereItem) {
                if ($anywhereItem->batch_id) {
                    return response()->json([
                        'found' => false,
                        'is_already_batched' => true,
                        'item' => $anywhereItem,
                        'message' => "Unit {$code} is already assigned to {$anywhereItem->batch?->batch_no}!"
                    ]);
                }

                $itemComp = $anywhereItem->company_name ?: ($anywhereItem->transmittal?->company_name ?? 'Unknown Company');
                return response()->json([
                    'found' => false,
                    'is_other_company' => true,
                    'other_company' => $itemComp,
                    'item' => $anywhereItem,
                    'message' => "Unit {$code} belongs to company '{$itemComp}', not '{$company}'."
                ]);
            }

            return response()->json([
                'found' => false,
                'message' => "Barcode / Serial Number \"{$code}\" not found in Incoming records."
            ], 404);
        }

        if ($item->batch_id) {
            return response()->json([
                'found' => true,
                'is_already_batched' => true,
                'item' => $item,
                'batch_no' => $item->batch?->batch_no,
                'message' => "Unit {$item->serial_number} is already part of Batch {$item->batch?->batch_no}."
            ]);
        }

        return response()->json([
            'found' => true,
            'is_already_batched' => false,
            'item' => [
                'id' => $item->id,
                'serial_number' => $item->serial_number,
                'mac_address' => $item->mac_address,
                'box_no' => $item->box_no,
                'brand' => $item->brand,
                'model' => $item->model,
                'company_name' => $item->company_name ?: ($item->transmittal?->company_name ?? ''),
                'transmittal_no' => $item->transmittal?->transmittal_no ?? 'TR-UNASSIGNED',
                'technical_diagnostic' => $item->technical_diagnostic ?: 'Test and Clean',
                'replace_parts' => $item->replace_parts ?: 'GOOD',
                'repair_status' => $item->repair_status ?: 'In process',
                'stock_status' => $item->stock_status ?: 'IN_STOCK',
            ],
            'message' => "Unit {$item->serial_number} matched successfully!"
        ]);
    }

    /**
     * Store and create new Traceability Batch from the Compared units for Encoder
     */
    public function storeBatch(Request $request)
    {
        $request->validate([
            'batch_no' => 'required|string|max:150',
            'company_name' => 'required|string|max:255',
            'selected_items' => 'required|array|min:1',
        ], [
            'selected_items.required' => 'Please scan or select at least one unit to form this Batch.',
            'selected_items.min' => 'Please scan or select at least one unit to form this Batch.',
        ]);

        $selectedItemIds = $request->input('selected_items', []);

        $batch = DB::transaction(function () use ($request, $selectedItemIds) {
            $companyName = trim($request->input('company_name'));
            $batchNo = trim($request->input('batch_no'));
            $dateDelivered = $request->input('date_delivered', now()->format('Y-m-d'));
            $defaultBrand = $request->input('brand');
            $defaultModel = $request->input('model');
            $defaultStatus = $request->input('status', 'GOOD');
            $notes = $request->input('notes');

            $items = InventoryItem::whereIn('id', $selectedItemIds)->with('transmittal')->get();
            $firstItem = $items->first();

            $brand = $defaultBrand ?: ($firstItem?->brand ?? 'N/A');
            $model = $defaultModel ?: ($firstItem?->model ?? 'N/A');

            $rowDiagnostics = $request->input('row_diagnostic', []);
            $rowParts = $request->input('row_parts', []);
            $rowBoxes = $request->input('row_box', []);
            $rowStatuses = $request->input('row_status', []);

            $batch = Batch::create([
                'batch_no' => $batchNo,
                'company_name' => $companyName,
                'date_delivered' => $dateDelivered,
                'brand' => $brand,
                'model' => $model,
                'total_quantity' => count($items),
                'in_stock_quantity' => count($items),
                'outgoing_quantity' => 0,
                'status' => $defaultStatus,
                'notes' => $notes,
                'encoded_by' => Auth::id(),
            ]);

            foreach ($items as $index => $item) {
                $diag = !empty($rowDiagnostics[$item->id]) ? trim($rowDiagnostics[$item->id]) : ($item->technical_diagnostic ?: 'Test and Clean');
                $part = !empty($rowParts[$item->id]) ? trim($rowParts[$item->id]) : ($item->replace_parts ?: 'GOOD');
                $box = !empty($rowBoxes[$item->id]) ? trim($rowBoxes[$item->id]) : $item->box_no;
                $stat = !empty($rowStatuses[$item->id]) ? trim($rowStatuses[$item->id]) : ($defaultStatus ?: 'GOOD');

                $item->update([
                    'batch_id' => $batch->id,
                    'item_no' => $index + 1,
                    'company_name' => $companyName,
                    'box_no' => $box,
                    'technical_diagnostic' => $diag,
                    'replace_parts' => $part,
                    'repair_status' => $stat,
                    'stock_status' => 'IN_STOCK',
                ]);
            }

            $batch->recalculateQuantities();

            ActivityLog::log('COMPARING_BATCH_CREATED', "Encoder created Traceability Batch {$batch->batch_no} with {$batch->total_quantity} units for company '{$companyName}'.");

            return $batch;
        });

        return redirect()->route('encoder.traceability.index', ['batch_id' => $batch->id])
            ->with('success', "Comparing Complete! Batch '{$batch->batch_no}' successfully created with {$batch->total_quantity} units. Now open in Traceability Matrix.");
    }
}
