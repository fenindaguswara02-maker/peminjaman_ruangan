<?php

namespace App\Traits;

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    /**
     * Log aktivitas pengguna
     *
     * @param string $tipe login, logout, create, update, delete, approve, reject
     * @param string $aktivitas Deskripsi singkat
     * @param string $deskripsi Deskripsi detail (opsional)
     * @return void
     */
    public static function logActivity($tipe, $aktivitas, $deskripsi = null)
    {
        try {
            LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => $tipe,
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsi,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Jangan throw error jika logging gagal
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Log login user
     */
    public static function logLogin()
    {
        self::logActivity('login', 'User login ke sistem', 'Login berhasil');
    }
    
    /**
     * Log logout user
     */
    public static function logLogout()
    {
        self::logActivity('logout', 'User logout dari sistem', 'Logout berhasil');
    }
    
    /**
     * Log pembuatan data
     */
    public static function logCreate($modelName, $details = null)
    {
        self::logActivity('create', "Menambah data {$modelName}", $details);
    }
    
    /**
     * Log update data
     */
    public static function logUpdate($modelName, $details = null)
    {
        self::logActivity('update', "Memperbarui data {$modelName}", $details);
    }
    
    /**
     * Log delete data
     */
    public static function logDelete($modelName, $details = null)
    {
        self::logActivity('delete', "Menghapus data {$modelName}", $details);
    }
    
    /**
     * Log approval
     */
    public static function logApprove($modelName, $details = null)
    {
        self::logActivity('approve', "Menyetujui {$modelName}", $details);
    }
    
    /**
     * Log rejection
     */
    public static function logReject($modelName, $details = null)
    {
        self::logActivity('reject', "Menolak {$modelName}", $details);
    }
}