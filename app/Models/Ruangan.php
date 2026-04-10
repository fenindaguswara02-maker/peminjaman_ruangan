<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan';
    
    protected $fillable = [
        'kode_ruangan',
        'nama_ruangan',
        'kapasitas',
        'fasilitas',
        'lokasi',
        'status',
        'keterangan',
        'gambar'
    ];

    protected $casts = [
        'kapasitas' => 'integer'
    ];

    // Constant untuk status - SESUAIKAN DENGAN DATABASE
    const STATUS_TESEDIA = 'tersedia';
    const STATUS_DIBOOKING = 'dibooking';
    const STATUS_DIPAKAI = 'dipakai';
    const STATUS_MAINTENANCE = 'maintenance';

    // Helper method untuk mendapatkan semua status
    public static function getStatusOptions()
    {
        return [
            self::STATUS_TESEDIA => 'Tersedia',
            self::STATUS_DIBOOKING => 'Dibooking',
            self::STATUS_DIPAKAI => 'Dipakai',
            self::STATUS_MAINTENANCE => 'Maintenance'
        ];
    }

    // Helper untuk mendapatkan class CSS berdasarkan status
    public static function getStatusClass($status)
    {
        $classes = [
            self::STATUS_TESEDIA => 'bg-green-100 text-green-800',
            self::STATUS_DIBOOKING => 'bg-blue-100 text-blue-800',
            self::STATUS_DIPAKAI => 'bg-yellow-100 text-yellow-800',
            self::STATUS_MAINTENANCE => 'bg-red-100 text-red-800',
        ];
        
        return $classes[$status] ?? 'bg-gray-100 text-gray-800';
    }

    // Relationship
    public function peminjaman()
    {
        return $this->hasMany(PeminjamanRuangan::class);
    }
    
    // Scope untuk status
    public function scopeTersedia($query)
    {
        return $query->where('status', self::STATUS_TESEDIA);
    }
    
    public function scopeDibooking($query)
    {
        return $query->where('status', self::STATUS_DIBOOKING);
    }
    
    public function scopeDipakai($query)
    {
        return $query->where('status', self::STATUS_DIPAKAI);
    }
    
    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }
    
    // Method untuk mendapatkan peminjaman aktif
    public function getPeminjamanAktifAttribute()
    {
        return $this->peminjaman()
            ->where('status', 'disetujui')
            ->where('status_real_time', '!=', 'selesai')
            ->orderBy('tanggal_mulai', 'asc')
            ->get();
    }
    
    // Method untuk cek apakah ruangan tersedia pada waktu tertentu
    public function isAvailableForTime($tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai, $excludePeminjamanId = null)
    {
        // Jika status maintenance atau dipakai, langsung false
        if (in_array($this->status, [self::STATUS_MAINTENANCE, self::STATUS_DIPAKAI])) {
            return false;
        }
        
        // Cek apakah ada peminjaman disetujui yang overlap
        $query = $this->peminjaman()
            ->where('status', 'disetujui')
            ->where(function($q) use ($tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai) {
                // Cek overlap tanggal
                $q->where(function($q2) use ($tanggalMulai, $tanggalSelesai) {
                    $q2->where('tanggal_mulai', '<=', $tanggalSelesai)
                       ->where('tanggal_selesai', '>=', $tanggalMulai);
                })
                // Cek overlap jam
                ->where(function($q2) use ($jamMulai, $jamSelesai) {
                    $q2->whereTime('jam_mulai', '<', $jamSelesai)
                       ->whereTime('jam_selesai', '>', $jamMulai);
                });
            });
        
        if ($excludePeminjamanId) {
            $query->where('id', '!=', $excludePeminjamanId);
        }
        
        return !$query->exists();
    }
    
    // Method untuk mendapatkan jadwal ruangan
    public function getJadwal($startDate = null, $endDate = null)
    {
        $query = $this->peminjaman()
            ->where('status', 'disetujui')
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('jam_mulai', 'asc');
            
        if ($startDate) {
            $query->where('tanggal_mulai', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('tanggal_selesai', '<=', $endDate);
        }
        
        return $query->get();
    }
    
    // ============ TAMBAHKAN METHOD BARU DI SINI ============
    
    /**
     * Get usage statistics for this room
     */
    public function getUsageStats()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        // Hitung total peminjaman bulan ini
        $totalBulanIni = $this->peminjaman()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
        
        // Hitung peminjaman disetujui bulan ini
        $disetujuiBulanIni = $this->peminjaman()
            ->where('status', 'disetujui')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();
        
        // Hitung rata-rata penggunaan per minggu
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        $mingguIni = $this->peminjaman()
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->count();
        
        // Hitung total semua waktu
        $totalSemuaWaktu = $this->peminjaman()->count();
        
        // Hitung persentase penggunaan
        $persentasePenggunaan = $totalSemuaWaktu > 0 ? 
            round(($disetujuiBulanIni / $totalSemuaWaktu) * 100, 2) : 0;
        
        return [
            'total_bulan_ini' => $totalBulanIni,
            'disetujui_bulan_ini' => $disetujuiBulanIni,
            'minggu_ini' => $mingguIni,
            'total_semua_waktu' => $totalSemuaWaktu,
            'persentase_penggunaan' => $persentasePenggunaan,
            'start_date' => $startOfMonth->format('Y-m-d'),
            'end_date' => $endOfMonth->format('Y-m-d')
        ];
    }
    
    /**
     * Get upcoming bookings
     */
    public function getUpcomingBookings($limit = 5)
    {
        return $this->peminjaman()
            ->where('status', 'disetujui')
            ->whereDate('tanggal_mulai', '>=', Carbon::today())
            ->orderBy('tanggal_mulai', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get room availability percentage
     */
    public function getAvailabilityPercentage()
    {
        // Hitung total hari dalam bulan ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $totalDays = $startOfMonth->diffInDays($endOfMonth) + 1;
        
        // Hitung hari yang sudah dipesan
        $bookedDays = $this->peminjaman()
            ->where('status', 'disetujui')
            ->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
            ->distinct('tanggal_mulai')
            ->count('tanggal_mulai');
        
        // Hitung persentase ketersediaan
        $availableDays = $totalDays - $bookedDays;
        $availabilityPercentage = $totalDays > 0 ? 
            round(($availableDays / $totalDays) * 100, 2) : 100;
        
        return [
            'available_days' => $availableDays,
            'booked_days' => $bookedDays,
            'total_days' => $totalDays,
            'percentage' => $availabilityPercentage
        ];
    }
}