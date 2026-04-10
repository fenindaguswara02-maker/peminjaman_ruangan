<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',     // Tambahkan username
        'name',
        'email',
        'password',
        'role',
        'no_telepon',
        'status',
        'nim_nip',
        'fakultas',
        'prodi',
        'jenis_pengaju',
        'foto',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to search users.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('username', 'like', "%{$search}%")  // Tambahkan pencarian username
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('nim_nip', 'like', "%{$search}%")
              ->orWhere('jenis_pengaju', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by role.
     */
    public function scopeRole($query, $role)
    {
        if ($role) {
            return $query->where('role', $role);
        }
        return $query;
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope a query to filter by jenis pengaju.
     */
    public function scopeJenisPengaju($query, $jenisPengaju)
    {
        if ($jenisPengaju) {
            return $query->where('jenis_pengaju', $jenisPengaju);
        }
        return $query;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is pegawai
     */
    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }
    
    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user has complete academic data
     */
    public function hasCompleteAcademicData(): bool
    {
        return !empty($this->nim_nip) && 
               !empty($this->fakultas) && 
               !empty($this->no_telepon) &&
               !empty($this->jenis_pengaju);
    }

    /**
     * Get the initial for avatar
     */
    public function getInitialsAttribute(): string
    {
        $names = explode(' ', $this->name);
        $initials = '';
        
        foreach ($names as $name) {
            $initials .= strtoupper(substr($name, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        
        return $initials;
    }

    /**
     * Get the role badge color
     */
    public function getRoleBadgeColorAttribute(): string
    {
        return match($this->role) {
            'admin' => 'purple',
            'pegawai' => 'blue',
            default => 'green'
        };
    }

    /**
     * Get the status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return $this->status === 'active' ? 'green' : 'red';
    }

    /**
     * Get the jenis pengaju badge color
     */
    public function getJenisPengajuBadgeColorAttribute(): string
    {
        return match($this->jenis_pengaju) {
            'mahasiswa' => 'blue',
            'dosen' => 'purple',
            'staff' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Get the jenis pengaju label
     */
    public function getJenisPengajuLabelAttribute(): string
    {
        return match($this->jenis_pengaju) {
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'staff' => 'Staff',
            default => 'Belum Dipilih'
        };
    }

    /**
     * Get peminjaman ruangan relationship
     */
    public function peminjamanRuangan()
    {
        return $this->hasMany(PeminjamanRuangan::class);
    }

    /**
     * ========== LOG ACTIVITIES RELATIONSHIP ==========
     * Relasi ke log aktivitas
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logActivities()
    {
        return $this->hasMany(LogActivity::class, 'user_id');
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute(): string
    {
        return $this->no_telepon ?: 'Belum diisi';
    }

    /**
     * Get formatted NIM/NIP
     */
    public function getFormattedNimNipAttribute(): string
    {
        return $this->nim_nip ?: 'Belum diisi';
    }

    /**
     * Get formatted faculty
     */
    public function getFormattedFakultasAttribute(): string
    {
        return $this->fakultas ?: 'Belum diisi';
    }

    /**
     * Get formatted study program
     */
    public function getFormattedProdiAttribute(): string
    {
        return $this->prodi ?: 'Belum diisi';
    }

    /**
     * Get formatted jenis pengaju
     */
    public function getFormattedJenisPengajuAttribute(): string
    {
        return $this->jenis_pengaju ? ucfirst($this->jenis_pengaju) : 'Belum dipilih';
    }

    /**
     * Get formatted username
     */
    public function getFormattedUsernameAttribute(): string
    {
        return $this->username ?: 'Belum diisi';
    }

    /**
     * Get academic data completeness status
     */
    public function getAcademicDataCompletenessAttribute(): array
    {
        $data = [
            'nim_nip' => !empty($this->nim_nip),
            'fakultas' => !empty($this->fakultas),
            'no_telepon' => !empty($this->no_telepon),
            'jenis_pengaju' => !empty($this->jenis_pengaju),
        ];

        $completed = count(array_filter($data));
        $total = count($data);
        $percentage = ($completed / $total) * 100;

        return [
            'data' => $data,
            'completed' => $completed,
            'total' => $total,
            'percentage' => $percentage,
            'is_complete' => $completed === $total
        ];
    }

    /**
     * Get missing academic data
     */
    public function getMissingAcademicDataAttribute(): array
    {
        $missing = [];
        
        if (empty($this->nim_nip)) $missing[] = 'NIM/NIP';
        if (empty($this->fakultas)) $missing[] = 'Fakultas';
        if (empty($this->no_telepon)) $missing[] = 'Nomor Telepon';
        if (empty($this->jenis_pengaju)) $missing[] = 'Jenis Pengaju';
        
        return $missing;
    }

    /**
     * Check if user can make room booking
     */
    public function canMakeRoomBooking(): bool
    {
        return $this->hasCompleteAcademicData() && $this->isActive();
    }

    /**
     * Get user's active bookings
     */
    public function activePeminjaman()
    {
        return $this->peminjamanRuangan()
            ->whereIn('status', ['disetujui', 'menunggu'])
            ->orderBy('tanggal_mulai', 'desc');
    }

    /**
     * Get user's booking history
     */
    public function peminjamanHistory()
    {
        return $this->peminjamanRuangan()
            ->whereIn('status', ['selesai', 'ditolak', 'dibatalkan'])
            ->orderBy('tanggal_mulai', 'desc');
    }

    /**
     * Get user's recent activities
     */
    public function recentActivities($limit = 5)
    {
        return $this->logActivities()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's activity count by type
     */
    public function getActivityCountByType($type = null)
    {
        $query = $this->logActivities();
        
        if ($type) {
            $query->where('tipe', $type);
        }
        
        return $query->count();
    }

    /**
     * Get user's last activity
     */
    public function getLastActivityAttribute()
    {
        return $this->logActivities()->latest()->first();
    }

    /**
     * Get user's login count
     */
    public function getLoginCountAttribute()
    {
        return $this->logActivities()
            ->where('tipe', 'login')
            ->count();
    }

    /**
     * Check if user has logged in today
     */
    public function hasLoggedInToday()
    {
        return $this->logActivities()
            ->where('tipe', 'login')
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Get user's activity timeline
     */
    public function activityTimeline($days = 7)
    {
        return $this->logActivities()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}