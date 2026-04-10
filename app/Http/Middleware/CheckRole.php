<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Jika belum login, redirect ke login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Log untuk debugging (bisa dihapus setelah berhasil)
        \Log::info("CheckRole Middleware - User: {$user->name}, Role: {$user->role}, Required: {$role}");
        
        // Jika user adalah admin, beri akses ke semua route
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Untuk role lainnya
        if ($user->role !== $role) {
            abort(403, 'Unauthorized access. Your role: ' . $user->role . ', Required: ' . $role);
        }

        return $next($request);
    }
}