<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role' => $request->input('role', 'encoder'),
            'status' => $request->input('status', 'active'),
            'password' => Hash::make($request->input('password', 'password123')),
        ]);

        ActivityLog::log('USER_CREATED', "Admin created user {$user->name} ({$user->role}).");

        return back()->with('success', "User {$user->name} created successfully!");
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = [
            'name' => $request->input('name', $user->name),
            'username' => $request->input('username', $user->username),
            'email' => $request->input('email', $user->email),
            'phone' => $request->input('phone', $user->phone),
            'role' => $request->input('role', $user->role),
            'status' => $request->input('status', $user->status),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        ActivityLog::log('USER_UPDATED', "Admin updated user {$user->name}.");

        return back()->with('success', "User {$user->name} updated successfully!");
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::log('USER_DELETED', "Admin deleted user {$name}.");

        return back()->with('success', "User {$name} deleted successfully.");
    }
}
