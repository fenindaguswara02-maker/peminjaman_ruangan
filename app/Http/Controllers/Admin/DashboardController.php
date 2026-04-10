<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ruangan;
use App\Models\PeminjamanRuangan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard admin
     */
    public function index()
    {
        // ============================================
        // STATISTIK UMUM
        // ============================================
        
        // Total Users
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->month)->count();
        
        // Total Pegawai
        $totalPegawai = User::where('role', 'pegawai')->where('status', 'active')->count();
        
        // ============================================
        // STATISTIK RUANGAN
        // ============================================
        
        // Total Ruangan
        $totalRuangan = Ruangan::count();
        $ruanganTersedia = Ruangan::where('status', 'tersedia')->count();
        $ruanganDipinjam = Ruangan::where('status', 'dipinjam')->count();
        $ruanganMaintenance = Ruangan::where('status', 'maintenance')->count();
        
        // ============================================
        // STATISTIK PEMINJAMAN
        // ============================================
        
        // Peminjaman Ruangan
        $totalPeminjaman = PeminjamanRuangan::count();
        $totalDisetujui = PeminjamanRuangan::where('status', 'disetujui')->count();
        
        // Peminjaman hari ini
        $peminjamanHariIni = PeminjamanRuangan::whereDate('tanggal', Carbon::today())->count();
        
        // Peminjaman bulan ini
        $peminjamanBulanIni = PeminjamanRuangan::whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();
        
        // Growth percentage (bulan ini vs bulan lalu)
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthCount = PeminjamanRuangan::whereMonth('tanggal', $lastMonth->month)
            ->whereYear('tanggal', $lastMonth->year)
            ->count();
        
        $growthPercentage = $lastMonthCount > 0 ? 
            round((($peminjamanBulanIni - $lastMonthCount) / $lastMonthCount) * 100, 1) : 100;
        
        // Rata-rata peminjaman per bulan
        $avgPerMonth = $totalPeminjaman > 0 ? 
            round($totalPeminjaman / 12, 1) : 0;
        
        // ============================================
        // RUANGAN POPULER - VERSI SEDERHANA
        // ============================================
        
        // Popular Rooms - Versi sederhana tanpa group by kompleks
        $popularRooms = Ruangan::withCount(['peminjaman' => function($query) {
            $query->where('status', 'disetujui');
        }])
        ->orderBy('peminjaman_count', 'desc')
        ->limit(5)
        ->get();
        
        $popularRoomsCount = $popularRooms->count();
        $maxPeminjaman = $popularRooms->max('peminjaman_count') ?? 1;
        
        // ============================================
        // JADWAL HARI INI
        // ============================================
        
        // Jadwal hari ini
        $jadwalHariIni = PeminjamanRuangan::with(['ruangan', 'user'])
            ->whereDate('tanggal', Carbon::today())
            ->where('status', 'disetujui')
            ->orderBy('jam_mulai')
            ->get();
        
        $jadwalHariIniCount = $jadwalHariIni->count();
        
        // ============================================
        // PENGUNJUNG TERBARU
        // ============================================
        
        // Recent Users
        $recentUsers = User::orderBy('created_at', 'desc')->limit(6)->get();
        
        // ============================================
        // STATISTIK BULANAN
        // ============================================
        
        // Monthly Stats (6 bulan terakhir)
        $monthlyStats = [];
        for ($i = 0; $i < 6; $i++) {
            $month = Carbon::now()->subMonths($i);
            $count = PeminjamanRuangan::whereMonth('tanggal', $month->month)
                ->whereYear('tanggal', $month->year)
                ->count();
            $monthlyStats[$month->month] = $count;
        }
        
        // ============================================
        // DATA UNTUK KOMPATIBILITAS
        // ============================================
        
        $totalPeminjamanRuangan = $totalPeminjaman;
        $totalPeminjamanVidotron = 0;
        
        $jadwalDisetujui = PeminjamanRuangan::where('status', 'disetujui')->get();
        $vidotronDisetujui = collect([]);
        
        $jadwalMendatang = PeminjamanRuangan::where('status', 'disetujui')
            ->where('tanggal', '>=', Carbon::today())
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->limit(10)
            ->get();
        
        // ============================================
        // RETURN VIEW DENGAN SEMUA DATA
        // ============================================
        
        return view('admin.dashboard', compact(
            // Statistik Umum
            'totalUsers',
            'activeUsers',
            'newUsersThisMonth',
            'totalPegawai',
            
            // Statistik Ruangan
            'totalRuangan',
            'ruanganTersedia',
            'ruanganDipinjam',
            'ruanganMaintenance',
            
            // Statistik Peminjaman
            'totalPeminjaman',
            'totalDisetujui',
            'peminjamanHariIni',
            'peminjamanBulanIni',
            'growthPercentage',
            'avgPerMonth',
            
            // Ruangan Populer
            'popularRooms',
            'popularRoomsCount',
            'maxPeminjaman',
            
            // Jadwal
            'jadwalHariIni',
            'jadwalHariIniCount',
            
            // Pengguna
            'recentUsers',
            
            // Statistik Bulanan
            'monthlyStats',
            
            // Data untuk kompatibilitas
            'totalPeminjamanRuangan',
            'totalPeminjamanVidotron',
            'jadwalDisetujui',
            'vidotronDisetujui',
            'jadwalMendatang'
        ));
    }
}