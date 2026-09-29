<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transmittal;
use App\Models\Batch;
use App\Models\InventoryItem;
use App\Models\OutgoingSlip;
use App\Traits\ClientScopedQueries;
use Illuminate\Http\Request;

class CompanyFolderController extends Controller
{
    use ClientScopedQueries;

    public function index(Request $request)
    {
        // 1. Get all registered client user accounts
        $clientUsers = User::where('role', 'client')->get()->keyBy(function ($user) {
            return strtolower(trim($user->company_name));
        });

        // 2. Get all distinct companies from registered clients and transmittals
        $transmittalCompanies = Transmittal::whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->distinct()
            ->pluck('company_name');

        $allCompanyNames = collect();

        foreach ($clientUsers as $u) {
            if (!empty($u->company_name)) {
                $allCompanyNames->push(trim($u->company_name));
            }
        }

        foreach ($transmittalCompanies as $tc) {
            if (!empty($tc)) {
                $allCompanyNames->push(trim($tc));
            }
        }

        $allCompanyNames = $allCompanyNames->unique(function ($item) {
            return strtolower(trim($item));
        })->values();

        // Search filter
        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $allCompanyNames = $allCompanyNames->filter(function ($name) use ($search, $clientUsers) {
                $lowerName = strtolower($name);
                if (str_contains($lowerName, $search)) return true;

                $client = $clientUsers->get($lowerName);
                if ($client) {
                    if (str_contains(strtolower($client->name ?? ''), $search)) return true;
                    if (str_contains(strtolower($client->email ?? ''), $search)) return true;
                    if (str_contains(strtolower($client->username ?? ''), $search)) return true;
                }
                return false;
            });
        }

        // 3. Compile metrics for each company
        $companiesData = $allCompanyNames->map(function ($companyName) use ($clientUsers) {
            $lowerKey = strtolower(trim($companyName));
            $clientUser = $clientUsers->get($lowerKey);

            $transmittalsCount = $this->getClientTransmittalsQuery($companyName)->count();
            $totalUnitsCount = $this->getClientItemsQuery($companyName)->count();
            $inProcessCount = $this->getClientItemsQuery($companyName)
                ->whereIn('repair_status', ['In process', 'PENDING', 'IN_PROCESS'])
                ->count();
            $repairedCount = $this->getClientItemsQuery($companyName)
                ->where('repair_status', 'Repaired')
                ->count();
            $berCount = $this->getClientItemsQuery($companyName)
                ->where('repair_status', 'BER')
                ->count();
            $releasedCount = $this->getClientItemsQuery($companyName)
                ->whereIn('stock_status', ['OUTGOING', 'RELEASED'])
                ->count();
            $inStockCount = $this->getClientItemsQuery($companyName)
                ->where('stock_status', 'IN_STOCK')
                ->count();
            $batchesCount = $this->getClientBatchesQuery($companyName)->count();

            return (object)[
                'company_name' => $companyName,
                'client_user' => $clientUser,
                'user_id' => $clientUser ? $clientUser->id : null,
                'company_key' => $clientUser ? $clientUser->id : urlencode($companyName),
                'transmittals_count' => $transmittalsCount,
                'total_units_count' => $totalUnitsCount,
                'in_process_count' => $inProcessCount,
                'repaired_count' => $repairedCount,
                'ber_count' => $berCount,
                'released_count' => $releasedCount,
                'in_stock_count' => $inStockCount,
                'batches_count' => $batchesCount,
            ];
        })->sortByDesc('total_units_count')->values();

        // High-level summary totals
        $summary = (object)[
            'total_companies' => $companiesData->count(),
            'total_transmittals' => $companiesData->sum('transmittals_count'),
            'total_units' => $companiesData->sum('total_units_count'),
            'total_repaired' => $companiesData->sum('repaired_count'),
            'total_in_process' => $companiesData->sum('in_process_count'),
            'total_ber' => $companiesData->sum('ber_count'),
            'total_released' => $companiesData->sum('released_count'),
        ];

        return view('admin.companies.index', compact('companiesData', 'summary'));
    }

    public function dashboard(Request $request, $id)
    {
        $clientUser = null;
        $companyName = '';

        if (is_numeric($id)) {
            $clientUser = User::where('role', 'client')->find($id);
            if ($clientUser) {
                $companyName = $clientUser->company_name;
            }
        }

        if (empty($companyName)) {
            $companyName = urldecode($id);
            $clientUser = User::where('role', 'client')
                ->whereRaw('LOWER(TRIM(company_name)) = ?', [strtolower(trim($companyName))])
                ->first();
        }

        // Auto heal ownership for this client
        $this->autoHealClientOwnership($companyName);

        // Scoped Metrics for this specific company
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

        // Recent records scoped to this company
        $recentTransmittals = $this->getClientTransmittalsQuery($companyName)
            ->with('items')->latest()->take(5)->get();
        $recentBatches = $this->getClientBatchesQuery($companyName)
            ->with('items')->latest()->take(5)->get();
        $recentOutgoing = $this->getClientOutgoingSlipsQuery($companyName, $recentBatches)
            ->with('items')->latest()->take(5)->get();

        // Brand distribution for this company
        $brandDistribution = $this->getClientItemsQuery($companyName)
            ->selectRaw('COALESCE(brand, "Unassigned") as brand_name, count(*) as count')
            ->groupBy('brand_name')
            ->orderByDesc('count')
            ->get();

        // Recent Units (Latest 8 items)
        $recentItems = $this->getClientItemsQuery($companyName)
            ->with(['transmittal', 'batch', 'outgoingSlip'])
            ->latest('id')
            ->take(8)
            ->get();

        // Quick Serial / MAC Live Unit Search
        $searchResult = null;
        if ($request->filled('track_serial')) {
            $search = trim($request->input('track_serial'));
            $searchResult = $this->getClientItemsQuery($companyName)
                ->where(function ($q) use ($search) {
                    $q->where('serial_number', 'like', "%{$search}%")
                        ->orWhere('mac_address', 'like', "%{$search}%");
                })
                ->with(['transmittal', 'batch', 'outgoingSlip'])
                ->get();
        }

        // All companies for quick switcher dropdown
        $allCompanies = Transmittal::whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->distinct()
            ->pluck('company_name')
            ->merge(User::where('role', 'client')->whereNotNull('company_name')->pluck('company_name'))
            ->unique(function ($item) {
                return strtolower(trim($item));
            })->values();

        return view('admin.companies.dashboard', compact(
            'companyName',
            'clientUser',
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
            'recentItems',
            'brandDistribution',
            'searchResult',
            'allCompanies'
        ));
    }
}
