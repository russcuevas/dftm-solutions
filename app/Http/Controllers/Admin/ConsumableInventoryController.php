<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsumableCategory;
use App\Models\ConsumableDailyLog;
use App\Models\ConsumableItem;
use App\Models\ConsumableMonthlyStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsumableInventoryController extends Controller
{
    /**
     * Monthly Grid Matrix View
     */
    public function index(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $categoryId = $request->get('category_id');
        $search = $request->get('search');

        // Number of days in selected month
        $dateObj = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $dateObj->daysInMonth;
        $monthName = $dateObj->format('F Y');

        // Query categories & items
        $categoriesQuery = ConsumableCategory::with(['items' => function ($q) use ($search) {
            $q->where('is_active', true);
            if ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('unit', 'like', "%{$search}%");
                });
            }
            $q->orderBy('sort_order')->orderBy('name');
        }])->orderBy('sort_order')->orderBy('name');

        if ($categoryId) {
            $categoriesQuery->where('id', $categoryId);
        }

        $categories = $categoriesQuery->get();

        // Get all items in the query
        $allItemIds = $categories->pluck('items')->flatten()->pluck('id')->toArray();

        // Beginning stocks for this month
        $monthlyStocks = ConsumableMonthlyStock::whereIn('consumable_item_id', $allItemIds)
            ->where('year', $year)
            ->where('month', $month)
            ->pluck('beginning_stock', 'consumable_item_id')
            ->toArray();

        // Daily logs for this month
        $startDate = $dateObj->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $dateObj->copy()->endOfMonth()->format('Y-m-d');

        $dailyLogs = ConsumableDailyLog::whereIn('consumable_item_id', $allItemIds)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->get();

        // Group daily logs by item_id and day
        $itemDailyLogs = [];
        foreach ($dailyLogs as $log) {
            $day = (int) Carbon::parse($log->log_date)->format('j');
            if (!isset($itemDailyLogs[$log->consumable_item_id])) {
                $itemDailyLogs[$log->consumable_item_id] = [];
            }
            if (!isset($itemDailyLogs[$log->consumable_item_id][$day])) {
                $itemDailyLogs[$log->consumable_item_id][$day] = ['in' => 0, 'out' => 0];
            }
            $itemDailyLogs[$log->consumable_item_id][$day]['in'] += $log->in_qty;
            $itemDailyLogs[$log->consumable_item_id][$day]['out'] += $log->out_qty;
        }

        // Summary Calculations
        $totalItemsCount = 0;
        $totalBeginningSum = 0;
        $totalInSum = 0;
        $totalOutSum = 0;
        $totalRemainingSum = 0;
        $totalValuation = 0.00;
        $lowStockCount = 0;

        $matrixData = [];

        foreach ($categories as $cat) {
            $matrixData[$cat->id] = [
                'category' => $cat,
                'items' => [],
            ];

            foreach ($cat->items as $item) {
                $totalItemsCount++;
                $beginning = $monthlyStocks[$item->id] ?? 0;
                $totalBeginningSum += $beginning;

                $dayLogs = $itemDailyLogs[$item->id] ?? [];
                $monthDailyIn = 0;
                $monthDailyOut = 0;

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    if (isset($dayLogs[$d])) {
                        $monthDailyIn += $dayLogs[$d]['in'];
                        $monthDailyOut += $dayLogs[$d]['out'];
                    }
                }

                // In Excel formula: Total In = Beginning + Sum of Daily In
                $totalIn = $beginning + $monthDailyIn;
                $totalOut = $monthDailyOut;
                $remaining = $totalIn - $totalOut;
                $amount = ($item->cost > 0) ? ($remaining * $item->cost) : 0.00;

                $totalInSum += $totalIn;
                $totalOutSum += $totalOut;
                $totalRemainingSum += $remaining;
                $totalValuation += $amount;

                if ($item->min_stock > 0 && $remaining <= $item->min_stock) {
                    $lowStockCount++;
                }

                $matrixData[$cat->id]['items'][] = [
                    'item' => $item,
                    'beginning' => $beginning,
                    'daily' => $dayLogs,
                    'total_in' => $totalIn,
                    'total_out' => $totalOut,
                    'remaining' => $remaining,
                    'amount' => $amount,
                ];
            }
        }

        $allCategoriesList = ConsumableCategory::orderBy('sort_order')->orderBy('name')->get();
        $allItemsList = ConsumableItem::where('is_active', true)->orderBy('category_id')->orderBy('name')->get();

        return view('admin.consumables.index', compact(
            'matrixData',
            'year',
            'month',
            'monthName',
            'daysInMonth',
            'categoryId',
            'search',
            'allCategoriesList',
            'allItemsList',
            'totalItemsCount',
            'totalBeginningSum',
            'totalInSum',
            'totalOutSum',
            'totalRemainingSum',
            'totalValuation',
            'lowStockCount'
        ));
    }

    /**
     * Store a Daily In / Out Log Entry
     */
    public function storeDailyLog(Request $request)
    {
        $validated = $request->validate([
            'consumable_item_id' => 'required|exists:consumable_items,id',
            'log_date' => 'required|date',
            'in_qty' => 'nullable|integer|min:0',
            'out_qty' => 'nullable|integer|min:0',
            'reference_no' => 'nullable|string|max:100',
            'remarks' => 'nullable|string|max:255',
        ]);

        $inQty = (int) ($validated['in_qty'] ?? 0);
        $outQty = (int) ($validated['out_qty'] ?? 0);

        if ($inQty === 0 && $outQty === 0) {
            return redirect()->back()->with('error', 'Please specify either an IN or OUT quantity greater than 0.');
        }

        ConsumableDailyLog::create([
            'consumable_item_id' => $validated['consumable_item_id'],
            'log_date' => $validated['log_date'],
            'in_qty' => $inQty,
            'out_qty' => $outQty,
            'reference_no' => $validated['reference_no'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'user_id' => Auth::id(),
        ]);

        $item = ConsumableItem::find($validated['consumable_item_id']);
        $logDate = Carbon::parse($validated['log_date']);

        return redirect()->route('admin.consumables.index', [
            'year' => $logDate->year,
            'month' => $logDate->month,
        ])->with('success', "Daily transaction recorded for '{$item->name}'.");
    }

    /**
     * Set / Update Beginning Stock for a given Month
     */
    public function updateBeginningStock(Request $request)
    {
        $validated = $request->validate([
            'consumable_item_id' => 'required|exists:consumable_items,id',
            'year' => 'required|integer|min:2020|max:2099',
            'month' => 'required|integer|min:1|max:12',
            'beginning_stock' => 'required|integer|min:0',
        ]);

        ConsumableMonthlyStock::updateOrCreate(
            [
                'consumable_item_id' => $validated['consumable_item_id'],
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'beginning_stock' => $validated['beginning_stock'],
            ]
        );

        $item = ConsumableItem::find($validated['consumable_item_id']);

        return redirect()->route('admin.consumables.index', [
            'year' => $validated['year'],
            'month' => $validated['month'],
        ])->with('success', "Beginning stock updated for '{$item->name}'.");
    }

    /**
     * Carry over remaining stocks from previous month to current month
     */
    public function carryOverPreviousMonth(Request $request)
    {
        $targetYear = (int) $request->input('year', now()->year);
        $targetMonth = (int) $request->input('month', now()->month);

        $targetDate = Carbon::createFromDate($targetYear, $targetMonth, 1);
        $prevDate = $targetDate->copy()->subMonth();
        $prevYear = $prevDate->year;
        $prevMonth = $prevDate->month;
        $prevMonthName = $prevDate->format('F Y');
        $targetMonthName = $targetDate->format('F Y');

        // Fetch all active items
        $items = ConsumableItem::where('is_active', true)->get();
        $itemIds = $items->pluck('id')->toArray();

        // Get beginning stocks of previous month
        $prevBeginningStocks = ConsumableMonthlyStock::whereIn('consumable_item_id', $itemIds)
            ->where('year', $prevYear)
            ->where('month', $prevMonth)
            ->pluck('beginning_stock', 'consumable_item_id')
            ->toArray();

        // Get all daily logs of previous month
        $prevStart = $prevDate->copy()->startOfMonth()->format('Y-m-d');
        $prevEnd = $prevDate->copy()->endOfMonth()->format('Y-m-d');

        $prevDailyLogs = ConsumableDailyLog::whereIn('consumable_item_id', $itemIds)
            ->whereBetween('log_date', [$prevStart, $prevEnd])
            ->selectRaw('consumable_item_id, SUM(in_qty) as total_in, SUM(out_qty) as total_out')
            ->groupBy('consumable_item_id')
            ->get()
            ->keyBy('consumable_item_id');

        $updatedCount = 0;

        foreach ($items as $item) {
            $prevBeg = $prevBeginningStocks[$item->id] ?? 0;
            $logs = $prevDailyLogs->get($item->id);
            $prevIn = $logs ? (int)$logs->total_in : 0;
            $prevOut = $logs ? (int)$logs->total_out : 0;

            // Total In = Beg + Daily In; Remaining = Total In - Total Out
            $remaining = ($prevBeg + $prevIn) - $prevOut;
            if ($remaining < 0) {
                $remaining = 0;
            }

            ConsumableMonthlyStock::updateOrCreate(
                [
                    'consumable_item_id' => $item->id,
                    'year' => $targetYear,
                    'month' => $targetMonth,
                ],
                [
                    'beginning_stock' => $remaining,
                ]
            );

            $updatedCount++;
        }

        return redirect()->route('admin.consumables.index', [
            'year' => $targetYear,
            'month' => $targetMonth,
        ])->with('success', "Successfully rolled over remaining stocks from {$prevMonthName} as Beginning Stocks for {$targetMonthName} ({$updatedCount} items updated).");
    }

    /**
     * List all Daily Transaction Logs
     */
    public function logs(Request $request)
    {
        $search = $request->get('search');
        $itemId = $request->get('item_id');
        $categoryId = $request->get('category_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = ConsumableDailyLog::with(['item.category', 'user']);

        if ($itemId) {
            $query->where('consumable_item_id', $itemId);
        }

        if ($categoryId) {
            $query->whereHas('item', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($startDate) {
            $query->where('log_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('log_date', '<=', $endDate);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%")
                  ->orWhereHas('item', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->orderBy('log_date', 'desc')->orderBy('id', 'desc')->paginate(30)->withQueryString();
        $categories = ConsumableCategory::orderBy('name')->get();
        $items = ConsumableItem::where('is_active', true)->orderBy('name')->get();

        return view('admin.consumables.logs.index', compact('logs', 'categories', 'items', 'search', 'itemId', 'categoryId', 'startDate', 'endDate'));
    }

    /**
     * Delete a Daily Log
     */
    public function destroyDailyLog($id)
    {
        $log = ConsumableDailyLog::findOrFail($id);
        $itemName = $log->item ? $log->item->name : 'Item';
        $log->delete();

        return redirect()->back()->with('success', "Transaction log for '{$itemName}' deleted successfully.");
    }

    /**
     * Printable Monthly Consumable Inventory Sheet (matches Excel layout)
     */
    public function print(Request $request)
    {
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $dateObj = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $dateObj->daysInMonth;
        $monthName = $dateObj->format('F Y');

        $categories = ConsumableCategory::with(['items' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
        }])->orderBy('sort_order')->orderBy('name')->get();

        $allItemIds = $categories->pluck('items')->flatten()->pluck('id')->toArray();

        $monthlyStocks = ConsumableMonthlyStock::whereIn('consumable_item_id', $allItemIds)
            ->where('year', $year)
            ->where('month', $month)
            ->pluck('beginning_stock', 'consumable_item_id')
            ->toArray();

        $startDate = $dateObj->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $dateObj->copy()->endOfMonth()->format('Y-m-d');

        $dailyLogs = ConsumableDailyLog::whereIn('consumable_item_id', $allItemIds)
            ->whereBetween('log_date', [$startDate, $endDate])
            ->get();

        $itemDailyLogs = [];
        foreach ($dailyLogs as $log) {
            $day = (int) Carbon::parse($log->log_date)->format('j');
            if (!isset($itemDailyLogs[$log->consumable_item_id])) {
                $itemDailyLogs[$log->consumable_item_id] = [];
            }
            if (!isset($itemDailyLogs[$log->consumable_item_id][$day])) {
                $itemDailyLogs[$log->consumable_item_id][$day] = ['in' => 0, 'out' => 0];
            }
            $itemDailyLogs[$log->consumable_item_id][$day]['in'] += $log->in_qty;
            $itemDailyLogs[$log->consumable_item_id][$day]['out'] += $log->out_qty;
        }

        $matrixData = [];
        $totalBeginningSum = 0;
        $totalInSum = 0;
        $totalOutSum = 0;
        $totalRemainingSum = 0;
        $totalValuation = 0.00;

        foreach ($categories as $cat) {
            $matrixData[$cat->id] = [
                'category' => $cat,
                'items' => [],
            ];

            foreach ($cat->items as $item) {
                $beginning = $monthlyStocks[$item->id] ?? 0;
                $totalBeginningSum += $beginning;

                $dayLogs = $itemDailyLogs[$item->id] ?? [];
                $monthDailyIn = 0;
                $monthDailyOut = 0;

                for ($d = 1; $d <= $daysInMonth; $d++) {
                    if (isset($dayLogs[$d])) {
                        $monthDailyIn += $dayLogs[$d]['in'];
                        $monthDailyOut += $dayLogs[$d]['out'];
                    }
                }

                $totalIn = $beginning + $monthDailyIn;
                $totalOut = $monthDailyOut;
                $remaining = $totalIn - $totalOut;
                $amount = ($item->cost > 0) ? ($remaining * $item->cost) : 0.00;

                $totalInSum += $totalIn;
                $totalOutSum += $totalOut;
                $totalRemainingSum += $remaining;
                $totalValuation += $amount;

                $matrixData[$cat->id]['items'][] = [
                    'item' => $item,
                    'beginning' => $beginning,
                    'daily' => $dayLogs,
                    'total_in' => $totalIn,
                    'total_out' => $totalOut,
                    'remaining' => $remaining,
                    'amount' => $amount,
                ];
            }
        }

        return view('admin.consumables.print', compact(
            'matrixData',
            'year',
            'month',
            'monthName',
            'daysInMonth',
            'totalBeginningSum',
            'totalInSum',
            'totalOutSum',
            'totalRemainingSum',
            'totalValuation'
        ));
    }
}
