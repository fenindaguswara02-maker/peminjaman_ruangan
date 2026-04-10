<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LogActivity;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user sudah login dan belum di-log
        if (Auth::check() && !session()->get('activity_logged')) {
            try {
                $user = Auth::user();
                
                // Log aktivitas login
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'login',
                    'aktivitas' => 'Login ke sistem',
                    'deskripsi' => "User {$user->name} ({$user->email}) berhasil login",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                session()->put('activity_logged', true);
                
            } catch (\Exception $e) {
                // Jangan gagalkan request jika logging error
                \Log::error('Failed to log login activity: ' . $e->getMessage());
            }
        }
        
        return $next($request);
    }
    
    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate($request, $response)
    {
        // Log logout jika user logout
        if (Auth::check() && $request->route()->getName() == 'logout') {
            try {
                $user = Auth::user();
                
                LogActivity::create([
                    'user_id' => $user->id,
                    'tipe' => 'logout',
                    'aktivitas' => 'Logout dari sistem',
                    'deskripsi' => "User {$user->name} ({$user->email}) logout",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                session()->forget('activity_logged');
                
            } catch (\Exception $e) {
                \Log::error('Failed to log logout activity: ' . $e->getMessage());
            }
        }
    }
}