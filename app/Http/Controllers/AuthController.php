<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('encoder.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $loginInput = $request->input('login');
        $password = $request->input('password');
        $remember = (bool) $request->input('remember');

        // Allow login via email or username
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $fieldType => $loginInput,
            'password' => $password,
        ];

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            ActivityLog::log('USER_LOGIN', "User {$user->name} ({$user->role}) logged in.");

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))->with('success', "Welcome back, {$user->name}!");
            }
            return redirect()->intended(route('encoder.dashboard'))->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withInput($request->only('login', 'remember'))->withErrors([
            'login' => 'Invalid email/username or password provided.',
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::log('USER_LOGOUT', "User {$user->name} logged out.");
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been successfully logged out.');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('auth.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = [
            'name' => $request->input('name', $user->name),
            'phone' => $request->input('phone', $user->phone),
            'email' => $request->input('email', $user->email),
            'username' => $request->input('username', $user->username),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }
}
