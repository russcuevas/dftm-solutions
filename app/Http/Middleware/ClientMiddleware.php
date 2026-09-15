<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to access this portal.');
        }

        /** @var \App\Models\User $user */
        $user = auth()->user();

        if ($user->status === 'inactive') {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your client account is inactive. Please contact DFTM Solutions administrator.');
        }

        if (!$user->isClient()) {
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->isEncoder()) {
                return redirect()->route('encoder.dashboard');
            }
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
