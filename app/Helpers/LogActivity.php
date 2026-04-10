<?php

namespace App\Helpers;

use App\Models\LogActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LogActivityHelper
{
    /**
     * Buat log aktivitas
     */
    public static function createLog($tipe, $aktivitas, $deskripsi = null)
    {
        try {
            LogActivity::create([
                'user_id'     => Auth::id(),
                'tipe'        => $tipe,
                'aktivitas'   => $aktivitas,
                'deskripsi'   => $deskripsi,
                'ip_address'  => Request::ip(),
                'user_agent'  => Request::userAgent()
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal membuat log: ' . $e->getMessage());
        }
    }

    /**
     * Log login user
     */
    public static function loginLog()
    {
        self::createLog(
            'login',
            'Login Sistem',
            Auth::user()->name . ' berhasil login'
        );
    }

    /**
     * Log logout user
     */
    public static function logoutLog()
    {
        self::createLog(
            'logout',
            'Logout Sistem',
            Auth::user()->name . ' melakukan logout'
        );
    }

    /**
     * Log create data
     */
    public static function createLogData($model, $dataId, $description = null)
    {
        $modelName = class_basename($model);
        self::createLog(
            'create',
            'Tambah Data ' . $modelName,
            $description ?: 'Menambahkan data ' . $modelName . ' dengan ID: ' . $dataId
        );
    }

    /**
     * Log update data
     */
    public static function updateLogData($model, $dataId, $description = null)
    {
        $modelName = class_basename($model);
        self::createLog(
            'update',
            'Update Data ' . $modelName,
            $description ?: 'Memperbarui data ' . $modelName . ' dengan ID: ' . $dataId
        );
    }

    /**
     * Log delete data
     */
    public static function deleteLogData($model, $dataId, $description = null)
    {
        $modelName = class_basename($model);
        self::createLog(
            'delete',
            'Hapus Data ' . $modelName,
            $description ?: 'Menghapus data ' . $modelName . ' dengan ID: ' . $dataId
        );
    }

    /**
     * Log approve peminjaman
     */
    public static function approveLog($peminjamanId, $description = null)
    {
        self::createLog(
            'approve',
            'Persetujuan Peminjaman',
            $description ?: 'Menyetujui peminjaman ruangan ID: ' . $peminjamanId
        );
    }

    /**
     * Log reject peminjaman
     */
    public static function rejectLog($peminjamanId, $description = null)
    {
        self::createLog(
            'reject',
            'Penolakan Peminjaman',
            $description ?: 'Menolak peminjaman ruangan ID: ' . $peminjamanId
        );
    }
}