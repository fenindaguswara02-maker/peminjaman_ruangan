<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        // Cek role user
        if ($role == 'admin' && !$user->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
        
        if ($role == 'pegawai' && !$user->isPegawai()) {
            abort(403, 'Unauthorized access.');
        }
        
        if ($role == 'user' && !$user->isUser()) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}