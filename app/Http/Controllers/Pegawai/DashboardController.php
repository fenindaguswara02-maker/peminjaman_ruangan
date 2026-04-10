<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $now = Carbon::now();
        $user = auth()->user();
        
        // 1. PEMINJAMAN RUANGAN HARI INI
        // Gunakan kondisi yang lebih akurat untuk hari ini
        $todayPeminjamanList = PeminjamanRuangan::with('ruangan')
            ->where('status', 'disetujui') // status yang benar adalah 'disetujui'
            ->where(function($query) use ($today) {
                // Peminjaman yang berlangsung hari ini (tanggal_mulai <= today <= tanggal_selesai)
                $query->whereDate('tanggal_mulai', '<=', $today)
                      ->whereDate('tanggal_selesai', '>=', $today);
            })
            ->orderBy('jam_mulai', 'asc')
            ->get();
        
        // 2. STATISTIK
        // Total peminjaman yang disetujui
        $totalPeminjaman = PeminjamanRuangan::where('status', 'disetujui')->count();
        
        // Peminjaman berlangsung saat ini
        $peminjamanBerlangsung = PeminjamanRuangan::where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->where('jam_mulai', '<=', $now->format('H:i:s'))
            ->where('jam_selesai', '>=', $now->format('H:i:s'))
            ->count();
        
        // Peminjaman akan datang hari ini
        $peminjamanAkanDatang = PeminjamanRuangan::where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->where('jam_mulai', '>', $now->format('H:i:s'))
            ->count();
        
        // Peminjaman hari ini (total)
        $todayPeminjaman = $todayPeminjamanList->count();
        
        // 3. PEMINJAMAN MENDATANG (7 hari ke depan)
        $upcomingPeminjaman = PeminjamanRuangan::with('ruangan')
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '>=', $today)
            ->whereDate('tanggal_mulai', '<=', $today->copy()->addDays(7))
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->limit(5)
            ->get();
        
        // 4. RINGKASAN MINGGU INI
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        
        $daysOfWeek = [];
        $weeklyStats = collect();
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            
            // Hitung peminjaman yang berlangsung pada tanggal ini
            $count = PeminjamanRuangan::where('status', 'disetujui')
                ->whereDate('tanggal_mulai', '<=', $date)
                ->whereDate('tanggal_selesai', '>=', $date)
                ->count();
            
            $daysOfWeek[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->translatedFormat('D'),
                'day_number' => $date->format('d'),
                'count' => $count,
                'is_today' => $date->isToday(),
            ];
            
            $weeklyStats->push(['date' => $date, 'count' => $count]);
        }
        
        // 5. RUANGAN TERPOPULER - Perbaikan query untuk strict mode
        $popularRuangan = Ruangan::select([
                'ruangan.id',
                'ruangan.nama_ruangan',
                'ruangan.kode_ruangan',
                'ruangan.kapasitas',
                'ruangan.fasilitas',
                'ruangan.status',
                DB::raw('(
                    SELECT COUNT(*) 
                    FROM peminjaman_ruangan 
                    WHERE peminjaman_ruangan.ruangan_id = ruangan.id 
                    AND peminjaman_ruangan.status = "disetujui"
                ) as jadwal_count')
            ])
            ->orderBy('jadwal_count', 'desc')
            ->orderBy('nama_ruangan', 'asc')
            ->limit(5)
            ->get();
        
        // 6. STATISTIK PER STATUS - Sesuaikan dengan nilai status yang benar
        $statusStats = [
            'menunggu' => PeminjamanRuangan::where('status', 'menunggu')->count(),
            'disetujui' => PeminjamanRuangan::where('status', 'disetujui')->count(),
            'ditolak' => PeminjamanRuangan::where('status', 'ditolak')->count(),
            'selesai' => PeminjamanRuangan::where('status', 'selesai')->count(),
            'dibatalkan' => PeminjamanRuangan::where('status', 'dibatalkan')->count(),
        ];
        
        return view('pegawai.dashboard', compact(
            'todayPeminjaman',
            'totalPeminjaman',
            'peminjamanBerlangsung',
            'peminjamanAkanDatang',
            'todayPeminjamanList',
            'upcomingPeminjaman',
            'daysOfWeek',
            'weeklyStats',
            'popularRuangan',
            'statusStats'
        ));
    }
}