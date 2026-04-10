<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';
    
    protected $fillable = [
        'user_id',
        'ruangan_id',
        'nama_kegiatan',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'kapasitas_peserta',
        'deskripsi',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Get the user that owns the schedule.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the ruangan that owns the schedule.
     */
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    /**
     * Accessor for lokasi (compatibility)
     */
    public function getLokasiAttribute()
    {
        if ($this->ruangan) {
            return $this->ruangan->kode_ruangan . ' - ' . $this->ruangan->nama_ruangan;
        }
        return 'Tidak ditentukan';
    }

    /**
     * Get waktu format attribute.
     */
    public function getWaktuFormatAttribute()
    {
        if ($this->waktu_mulai && $this->waktu_selesai) {
            return \Carbon\Carbon::parse($this->waktu_mulai)->format('H:i') . ' - ' . 
                   \Carbon\Carbon::parse($this->waktu_selesai)->format('H:i');
        }
        return 'Seluruh Hari';
    }

    /**
     * Get tanggal format attribute.
     */
    public function getTanggalFormatAttribute()
    {
        return \Carbon\Carbon::parse($this->tanggal)->translatedFormat('d F Y');
    }
}