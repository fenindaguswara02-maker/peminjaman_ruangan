<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // ============= STATISTIK PEMINJAMAN =============
        // Total peminjaman
        $totalPeminjaman = PeminjamanRuangan::count();
        
        // Menunggu persetujuan (yang butuh persetujuan)
        $pendingApproval = PeminjamanRuangan::where('status', 'menunggu')->count();
        
        // Disetujui
        $approved = PeminjamanRuangan::where('status', 'disetujui')->count();
        
        // Ditolak
        $rejected = PeminjamanRuangan::where('status', 'ditolak')->count();
        
        // ============= PEMINJAMAN YANG AKAN DATANG =============
        // Peminjaman dalam 7 hari ke depan
        $upcomingBookings = PeminjamanRuangan::with(['ruangan', 'user'])
            ->whereIn('status', ['disetujui', 'menunggu'])
            ->where('tanggal_mulai', '>=', now()->format('Y-m-d'))
            ->where('tanggal_mulai', '<=', now()->addDays(7)->format('Y-m-d'))
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->limit(10)
            ->get();
        
        $upcomingCount = PeminjamanRuangan::whereIn('status', ['disetujui', 'menunggu'])
            ->where('tanggal_mulai', '>=', now()->format('Y-m-d'))
            ->where('tanggal_mulai', '<=', now()->addDays(7)->format('Y-m-d'))
            ->count();
        
        // ============= PEMINJAMAN TERBARU =============
        $recentBookings = PeminjamanRuangan::with(['ruangan', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // ============= RUANGAN TERPOPULER =============
        $popularRooms = Ruangan::select(
            'ruangans.id', 
            'ruangans.nama_ruangan', 
            'ruangans.kode_ruangan',
            DB::raw('COUNT(peminjaman_ruangans.id) as total_peminjaman')
        )
            ->leftJoin('peminjaman_ruangans', 'ruangans.id', '=', 'peminjaman_ruangans.ruangan_id')
            ->where('peminjaman_ruangans.status', 'disetujui')
            ->groupBy('ruangans.id', 'ruangans.nama_ruangan', 'ruangans.kode_ruangan')
            ->orderBy('total_peminjaman', 'desc')
            ->limit(5)
            ->get();
        
        // ============= STATISTIK BERDASARKAN STATUS =============
        $statusStats = [
            'menunggu' => $pendingApproval,
            'disetujui' => $approved,
            'ditolak' => $rejected,
            'selesai' => PeminjamanRuangan::where('status', 'selesai')->count(),
            'dibatalkan' => PeminjamanRuangan::where('status', 'dibatalkan')->count(),
        ];
        
        // ============= PEMINJAMAN HARI INI =============
        $todayBookings = PeminjamanRuangan::with(['ruangan', 'user'])
            ->whereIn('status', ['disetujui', 'menunggu'])
            ->where('tanggal_mulai', now()->format('Y-m-d'))
            ->orderBy('jam_mulai', 'asc')
            ->get();
        
        $todayCount = $todayBookings->count();
        
        return view('pegawai.dashboard', compact(
            'user',
            'totalPeminjaman',
            'pendingApproval',
            'approved',
            'rejected',
            'upcomingBookings',
            'upcomingCount',
            'recentBookings',
            'popularRooms',
            'statusStats',
            'todayBookings',
            'todayCount'
        ));
    }
    
    // Method untuk AJAX refresh stats (opsional)
    public function refreshStats(Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'totalPeminjaman' => PeminjamanRuangan::count(),
                'pendingApproval' => PeminjamanRuangan::where('status', 'menunggu')->count(),
                'approved' => PeminjamanRuangan::where('status', 'disetujui')->count(),
                'rejected' => PeminjamanRuangan::where('status', 'ditolak')->count(),
                'upcomingCount' => PeminjamanRuangan::whereIn('status', ['disetujui', 'menunggu'])
                    ->where('tanggal_mulai', '>=', now()->format('Y-m-d'))
                    ->where('tanggal_mulai', '<=', now()->addDays(7)->format('Y-m-d'))
                    ->count(),
            ]);
        }
        
        return redirect()->route('pegawai.dashboard');
    }
    
    // Method untuk mendapatkan jumlah pending (untuk badge notifikasi)
    public function getPendingCount()
    {
        return response()->json([
            'pending_count' => PeminjamanRuangan::where('status', 'menunggu')->count()
        ]);
    }
}