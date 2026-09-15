<?php

namespace App\Http\Controllers\Encoder;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index()
    {
        $clients = User::where('role', 'client')->latest()->paginate(20);
        return view('encoder.clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'nullable|string|min:6',
        ]);

        $client = User::create([
            'name' => $request->input('name'),
            'company_name' => trim($request->input('company_name')),
            'username' => trim($request->input('username')),
            'email' => $request->input('email') ?: (strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->input('username'))) . '@dftm-client.local'),
            'phone' => $request->input('phone'),
            'role' => 'client',
            'status' => 'active',
            'password' => Hash::make($request->input('password') ?: 'password123'),
        ]);

        ActivityLog::log('CLIENT_ACCOUNT_CREATED', "Encoder created client account {$client->name} ({$client->company_name}, username: {$client->username}).");

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Client account for {$client->company_name} registered successfully!",
                'client' => $client,
            ]);
        }

        return back()->with('success', "Client account for {$client->company_name} registered successfully! Username: {$client->username}");
    }
}
