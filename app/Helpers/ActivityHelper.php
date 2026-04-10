<?php

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

if (!function_exists('log_activity')) {
    /**
     * Helper function untuk log aktivitas
     */
    function log_activity($tipe, $aktivitas, $deskripsi = null, $userId = null)
    {
        try {
            LogActivity::create([
                'user_id' => $userId ?? Auth::id(),
                'tipe' => $tipe,
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsi,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            
            \Log::info('Activity logged: ' . $aktivitas);
            
        } catch (\Exception $e) {
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}

if (!function_exists('log_create')) {
    /**
     * Log pembuatan data
     */
    function log_create($model, $details = null)
    {
        $user = Auth::user();
        $deskripsi = "Data {$model} dibuat";
        if ($details) {
            $deskripsi .= " - " . $details;
        }
        if ($user) {
            $deskripsi .= " oleh {$user->name}";
        }
        
        log_activity('create', "Menambah data {$model}", $deskripsi);
    }
}

if (!function_exists('log_update')) {
    /**
     * Log update data
     */
    function log_update($model, $details = null)
    {
        $user = Auth::user();
        $deskripsi = "Data {$model} diperbarui";
        if ($details) {
            $deskripsi .= " - " . $details;
        }
        if ($user) {
            $deskripsi .= " oleh {$user->name}";
        }
        
        log_activity('update', "Memperbarui data {$model}", $deskripsi);
    }
}

if (!function_exists('log_delete')) {
    /**
     * Log delete data
     */
    function log_delete($model, $details = null)
    {
        $user = Auth::user();
        $deskripsi = "Data {$model} dihapus";
        if ($details) {
            $deskripsi .= " - " . $details;
        }
        if ($user) {
            $deskripsi .= " oleh {$user->name}";
        }
        
        log_activity('delete', "Menghapus data {$model}", $deskripsi);
    }
}

if (!function_exists('log_approve')) {
    /**
     * Log approval
     */
    function log_approve($model, $details = null)
    {
        $user = Auth::user();
        $deskripsi = "{$model} disetujui";
        if ($details) {
            $deskripsi .= " - " . $details;
        }
        if ($user) {
            $deskripsi .= " oleh {$user->name}";
        }
        
        log_activity('approve', "Menyetujui {$model}", $deskripsi);
    }
}

if (!function_exists('log_reject')) {
    /**
     * Log rejection
     */
    function log_reject($model, $details = null)
    {
        $user = Auth::user();
        $deskripsi = "{$model} ditolak";
        if ($details) {
            $deskripsi .= " - " . $details;
        }
        if ($user) {
            $deskripsi .= " oleh {$user->name}";
        }
        
        log_activity('reject', "Menolak {$model}", $deskripsi);
    }
}