<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PeminjamanRuangan extends Model
{
    protected $table = 'peminjaman_ruangan';
    
    protected $fillable = [
        'user_id',
        'jenis_pengaju',
        'nama_pengaju',
        'nim_nip',
        'fakultas',
        'prodi',
        'email',
        'no_telepon',
        'ruangan_id',
        'acara',
        'hari',
        'tanggal',
        'tanggal_mulai',
        'tanggal_selesai',
        'jam_mulai',
        'jam_selesai',
        'jumlah_peserta',
        'keterangan',
        'lampiran_surat',
        'status',
        'status_real_time',
        'alasan_penolakan',
        'catatan' // ✅ SUDAH ADA
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_peserta' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ TAMBAHKAN DEFAULT VALUE UNTUK STATUS_REAL_TIME
    protected $attributes = [
        'status_real_time' => 'akan_datang',
        'status' => 'menunggu',
    ];

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope untuk status
    public function scopeMenunggu($query)
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeDitolak($query)
    {
        return $query->where('status', 'ditolak');
    }
    
    // ✅ TAMBAHKAN SCOPE UNTUK FILTER CATATAN
    public function scopeDenganCatatan($query)
    {
        return $query->whereNotNull('catatan')->where('catatan', '!=', '');
    }
    
    public function scopeTanpaCatatan($query)
    {
        return $query->whereNull('catatan')->orWhere('catatan', '');
    }

    // Method untuk cek status
    public function isMenunggu()
    {
        return $this->status === 'menunggu';
    }

    public function isDisetujui()
    {
        return $this->status === 'disetujui';
    }

    public function isDitolak()
    {
        return $this->status === 'ditolak';
    }

    public function isDibatalkan()
    {
        return $this->status === 'dibatalkan';
    }
    
    // ✅ TAMBAHKAN METHOD UNTUK CEK CATATAN
    public function hasCatatan()
    {
        return !empty($this->catatan) && $this->catatan !== null && trim($this->catatan) !== '';
    }
    
    public function getCatatanPreviewAttribute()
    {
        if (!$this->hasCatatan()) {
            return null;
        }
        
        $preview = strip_tags($this->catatan);
        if (strlen($preview) > 50) {
            $preview = substr($preview, 0, 50) . '...';
        }
        return $preview;
    }

    // Getter untuk format tanggal
    public function getTanggalMulaiFormattedAttribute()
    {
        return $this->tanggal_mulai ? Carbon::parse($this->tanggal_mulai)->translatedFormat('d F Y') : '-';
    }

    public function getTanggalSelesaiFormattedAttribute()
    {
        return $this->tanggal_selesai ? Carbon::parse($this->tanggal_selesai)->translatedFormat('d F Y') : '-';
    }

    public function getWaktuFormattedAttribute()
    {
        return $this->jam_mulai . ' - ' . $this->jam_selesai;
    }
    
    // ✅ TAMBAHKAN ACCESSOR UNTUK MENAMPILKAN STATUS REAL-TIME DALAM BAHASA INDONESIA
    public function getStatusRealTimeTextAttribute()
    {
        $statuses = [
            'akan_datang' => 'Akan Datang',
            'berlangsung' => 'Berlangsung',
            'selesai' => 'Selesai',
        ];
        
        return $statuses[$this->status_real_time] ?? 'Akan Datang';
    }
    
    public function getStatusRealTimeColorAttribute()
    {
        $colors = [
            'akan_datang' => 'yellow',
            'berlangsung' => 'purple',
            'selesai' => 'green',
        ];
        
        return $colors[$this->status_real_time] ?? 'yellow';
    }
    
    // ✅ TAMBAHKAN ACCESSOR UNTUK STATUS ADMINISTRASI
    public function getStatusTextAttribute()
    {
        $statuses = [
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }
    
    public function getStatusColorAttribute()
    {
        $colors = [
            'menunggu' => 'yellow',
            'disetujui' => 'green',
            'ditolak' => 'red',
            'dibatalkan' => 'gray',
        ];
        
        return $colors[$this->status] ?? 'gray';
    }
}