<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        
        // Set timezone ke Asia/Jakarta
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');
        
        // Tanggal dan waktu sekarang
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        $nowTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
        $now = Carbon::now('Asia/Jakarta');
        
        // Debug tanggal
        Log::info('Dashboard User - Tanggal Debug:', [
            'user_id' => $userId,
            'today_system' => $today,
            'now_time' => $nowTime,
            'now_full' => $now,
            'timezone' => config('app.timezone'),
            'php_timezone' => date_default_timezone_get()
        ]);
        
        // Stats untuk peminjaman ruangan
        $activeBookings = PeminjamanRuangan::where('user_id', $userId)
            ->where('status', 'disetujui')
            ->whereDate('tanggal', '>=', $today)
            ->count();
        
        $pendingBookings = PeminjamanRuangan::where('user_id', $userId)
            ->where('status', 'menunggu')
            ->count();
        
        $totalBookings = PeminjamanRuangan::where('user_id', $userId)->count();
        
        // Debug: cek data peminjaman
        $debugBookings = PeminjamanRuangan::where('user_id', $userId)
            ->whereDate('tanggal', $today)
            ->get(['id', 'ruangan_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'status']);
            
        Log::info('Peminjaman hari ini untuk user ' . $userId . ':', [
            'count' => $debugBookings->count(),
            'data' => $debugBookings->toArray()
        ]);
        
        // Aktivitas terbaru (hanya peminjaman ruangan) - 5 terbaru
        $recentActivities = PeminjamanRuangan::with('ruangan')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Peminjaman mendatang (yang status disetujui atau menunggu)
        $upcomingBookings = PeminjamanRuangan::with('ruangan')
            ->where('user_id', $userId)
            ->whereIn('status', ['disetujui', 'menunggu'])
            ->whereDate('tanggal', '>=', $today)
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->limit(5)
            ->get();
        
        // Debug: cek peminjaman mendatang
        Log::info('Peminjaman mendatang untuk user ' . $userId . ':', [
            'count' => $upcomingBookings->count(),
            'data' => $upcomingBookings->pluck('tanggal', 'acara')
        ]);
        
        // Ambil semua ruangan
        $rooms = Ruangan::all();
        $roomsStatus = [];
        
        // Debug: cek semua ruangan
        Log::info('Data ruangan:', [
            'total_ruangan' => $rooms->count(),
            'ruangan_list' => $rooms->pluck('nama_ruangan', 'status')->toArray()
        ]);
        
        // Cek status setiap ruangan untuk hari ini
        foreach ($rooms as $room) {
            // Debug query untuk ruangan ini
            $query = PeminjamanRuangan::with('user')
                ->where('ruangan_id', $room->id)
                ->whereDate('tanggal', $today)
                ->where('status', 'disetujui');
                
            $currentQuery = clone $query;
            $currentQuery->where(function($q) use ($nowTime) {
                $q->whereTime('jam_mulai', '<=', $nowTime)
                  ->whereTime('jam_selesai', '>=', $nowTime);
            });
            
            $currentBooking = $currentQuery->first();
            
            $upcomingQuery = clone $query;
            $upcomingQuery->whereTime('jam_mulai', '>', $nowTime)
                ->orderBy('jam_mulai', 'asc');
                
            $upcomingToday = $upcomingQuery->first();
            
            // Debug: cek peminjaman untuk ruangan ini
            $allBookingsToday = PeminjamanRuangan::where('ruangan_id', $room->id)
                ->whereDate('tanggal', $today)
                ->get(['id', 'acara', 'jam_mulai', 'jam_selesai', 'status']);
                
            Log::info('Peminjaman untuk ruangan ' . $room->nama_ruangan . ' hari ini:', [
                'room_id' => $room->id,
                'today' => $today,
                'current_booking' => $currentBooking ? $currentBooking->id : null,
                'upcoming_booking' => $upcomingToday ? $upcomingToday->id : null,
                'all_bookings_count' => $allBookingsToday->count(),
                'all_bookings' => $allBookingsToday->toArray()
            ]);
            
            // Tentukan status berdasarkan database dan peminjaman saat ini
            $statusDisplay = $room->status;
            $bookingInfo = null;
            
            // Override status jika ada peminjaman aktif sekarang
            if ($currentBooking) {
                $statusDisplay = 'dipinjam';
                $bookingInfo = [
                    'peminjam' => $currentBooking->nama_pengaju ?? $currentBooking->user->name ?? 'Pengguna',
                    'acara' => $currentBooking->acara,
                    'jam_mulai' => $this->formatTime($currentBooking->jam_mulai),
                    'jam_selesai' => $this->formatTime($currentBooking->jam_selesai),
                    'jumlah_peserta' => $currentBooking->jumlah_peserta
                ];
            } elseif ($upcomingToday && $room->status == 'tersedia') {
                // Jika ruangan tersedia tapi ada jadwal peminjaman nanti
                $statusDisplay = 'akan_dipinjam';
                $bookingInfo = [
                    'peminjam' => $upcomingToday->nama_pengaju ?? $upcomingToday->user->name ?? 'Pengguna',
                    'acara' => $upcomingToday->acara,
                    'jam_mulai' => $this->formatTime($upcomingToday->jam_mulai),
                    'jam_selesai' => $this->formatTime($upcomingToday->jam_selesai),
                    'jumlah_peserta' => $upcomingToday->jumlah_peserta
                ];
            } elseif ($room->status == 'maintenance') {
                $statusDisplay = 'maintenance';
            }
            
            $roomsStatus[] = [
                'id' => $room->id,
                'kode_ruangan' => $room->kode_ruangan,
                'nama_ruangan' => $room->nama_ruangan,
                'kapasitas' => $room->kapasitas,
                'fasilitas' => $room->fasilitas,
                'gambar' => $room->gambar,
                'status_db' => $room->status,
                'status_display' => $statusDisplay,
                'current_booking' => $bookingInfo,
                'debug_info' => [
                    'today' => $today,
                    'now_time' => $nowTime,
                    'has_current_booking' => !is_null($currentBooking),
                    'has_upcoming_booking' => !is_null($upcomingToday)
                ]
            ];
        }
        
        // Debug akhir
        Log::info('Dashboard data summary:', [
            'active_bookings' => $activeBookings,
            'pending_bookings' => $pendingBookings,
            'total_bookings' => $totalBookings,
            'recent_activities_count' => $recentActivities->count(),
            'upcoming_bookings_count' => $upcomingBookings->count(),
            'rooms_status_count' => count($roomsStatus),
            'today' => $today,
            'now_time' => $nowTime
        ]);
        
        return view('user.dashboard', compact(
            'activeBookings',
            'pendingBookings', 
            'totalBookings',
            'recentActivities',
            'upcomingBookings',
            'roomsStatus',
            'today' // tambahkan untuk debug di view
        ));
    }
    
    private function formatTime($time)
    {
        if (is_string($time)) {
            return substr($time, 0, 5);
        } elseif ($time instanceof \DateTime) {
            return $time->format('H:i');
        }
        return $time;
    }
}