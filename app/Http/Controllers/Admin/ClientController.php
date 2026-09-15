<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'client')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->paginate(20)->withQueryString();
        return view('admin.clients.index', compact('clients'));
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

        $companyName = trim($request->input('company_name'));
        $username = trim($request->input('username'));
        $email = $request->input('email') ?: (strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)) . '@dftm-client.local');

        $client = User::create([
            'company_name' => $companyName,
            'name' => $request->input('name'),
            'username' => $username,
            'email' => $email,
            'phone' => $request->input('phone'),
            'role' => 'client',
            'status' => $request->input('status', 'active'),
            'password' => Hash::make($request->input('password') ?: 'password123'),
        ]);

        ActivityLog::log('CLIENT_ACCOUNT_CREATED', "Admin created client account {$client->name} for {$client->company_name} (Username: {$client->username}).");

        return back()->with('success', "Client account for {$client->company_name} created successfully! (Username: {$client->username})");
    }

    public function update(Request $request, $id)
    {
        $client = User::where('role', 'client')->findOrFail($id);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
        ]);

        $client->company_name = trim($request->input('company_name'));
        $client->name = $request->input('name');
        $client->username = trim($request->input('username'));
        if ($request->filled('email')) {
            $client->email = $request->input('email');
        }
        $client->phone = $request->input('phone');
        $client->status = $request->input('status', $client->status);

        if ($request->filled('password')) {
            $client->password = Hash::make($request->input('password'));
        }

        $client->save();

        ActivityLog::log('CLIENT_ACCOUNT_UPDATED', "Admin updated client account for {$client->company_name} ({$client->name}).");

        return back()->with('success', "Client account {$client->company_name} updated successfully!");
    }

    public function destroy($id)
    {
        $client = User::where('role', 'client')->findOrFail($id);
        $companyName = $client->company_name;
        $name = $client->name;

        $client->delete();

        ActivityLog::log('CLIENT_ACCOUNT_DELETED', "Admin deleted client account {$name} ({$companyName}).");

        return back()->with('success', "Client account for {$companyName} deleted successfully.");
    }
}
