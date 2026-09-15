<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transmittal;
use App\Models\InventoryItem;
use App\Traits\ClientScopedQueries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomingController extends Controller
{
    use ClientScopedQueries;

    public function index(Request $request)
    {
        $companyName = Auth::user()->company_name;

        $query = $this->getClientTransmittalsQuery($companyName)->with('items')->latest();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('transmittal_no', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhereHas('items', function($iq) use ($search) {
                      $iq->where('serial_number', 'like', "%{$search}%")
                        ->orWhere('mac_address', 'like', "%{$search}%");
                  });
            });
        }

        $transmittals = $query->paginate(15)->withQueryString();

        return view('client.incoming.index', compact('transmittals', 'companyName'));
    }

    public function show($id)
    {
        $companyName = Auth::user()->company_name;

        $transmittal = Transmittal::with(['items' => function($q) {
            $q->orderBy('item_no', 'asc');
        }])->findOrFail($id);

        // Security check: client can only view their own transmittal
        $cleanClientCompany = strtolower(trim($companyName));
        $cleanTransmittalCompany = strtolower(trim($transmittal->company_name ?? ''));
        if ($cleanClientCompany !== $cleanTransmittalCompany && 
            !str_contains($cleanTransmittalCompany, $cleanClientCompany) &&
            !str_contains($cleanClientCompany, $cleanTransmittalCompany)) {
            abort(403, 'Unauthorized access: You cannot view transmittals belonging to other companies.');
        }

        return view('client.incoming.show', compact('transmittal'));
    }

    public function print(Request $request, $id)
    {
        $companyName = Auth::user()->company_name;

        $transmittal = Transmittal::with(['items', 'encoder'])->findOrFail($id);

        // Security check: client can only view their own transmittal
        $cleanClientCompany = strtolower(trim($companyName));
        $cleanTransmittalCompany = strtolower(trim($transmittal->company_name ?? ''));
        if ($cleanClientCompany !== $cleanTransmittalCompany && 
            !str_contains($cleanTransmittalCompany, $cleanClientCompany) &&
            !str_contains($cleanClientCompany, $cleanTransmittalCompany)) {
            abort(403, 'Unauthorized access to transmittal report.');
        }

        $filterModel = $request->query('model');

        $query = $transmittal->items();
        if ($filterModel) {
            $query->where('model', $filterModel);
        }

        $allItems = $query->orderBy('model')->orderBy('item_no')->get();

        // Group items by model for clear breakdown
        $itemsByModel = $allItems->groupBy(function($item) {
            return trim($item->model ?: 'Unassigned Model');
        });

        return view('print.incoming_slip', compact('transmittal', 'itemsByModel', 'allItems', 'filterModel'));
    }
}
