<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Transmittal;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomingController extends Controller
{
    public function index(Request $request)
    {
        $companyName = Auth::user()->company_name;

        $query = Transmittal::where('company_name', $companyName)->with('items')->latest();

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
        if ($transmittal->company_name !== $companyName) {
            abort(403, 'Unauthorized access: You cannot view transmittals belonging to other companies.');
        }

        return view('client.incoming.show', compact('transmittal'));
    }

    public function print($id)
    {
        $companyName = Auth::user()->company_name;

        $transmittal = Transmittal::with('items')->findOrFail($id);

        if ($transmittal->company_name !== $companyName) {
            abort(403, 'Unauthorized access to transmittal report.');
        }

        $items = $transmittal->items()->orderBy('item_no', 'asc')->get();

        return view('print.incoming_slip', compact('transmittal', 'items'));
    }
}
