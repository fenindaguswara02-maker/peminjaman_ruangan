<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    protected $table = 'log_activities';
    
    protected $fillable = [
        'user_id',
        'tipe',
        'aktivitas',
        'deskripsi',
        'ip_address',
        'user_agent'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Helper method untuk menentukan warna badge berdasarkan tipe
    public function getBadgeColorAttribute()
    {
        $colors = [
            'login' => 'blue',
            'logout' => 'indigo',
            'create' => 'green',
            'update' => 'yellow',
            'delete' => 'red',
            'approve' => 'emerald',
            'reject' => 'orange',
        ];
        
        return $colors[$this->tipe] ?? 'gray';
    }
    
    // Helper method untuk icon berdasarkan tipe
    public function getIconAttribute()
    {
        $icons = [
            'login' => 'fas fa-sign-in-alt',
            'logout' => 'fas fa-sign-out-alt',
            'create' => 'fas fa-plus-circle',
            'update' => 'fas fa-edit',
            'delete' => 'fas fa-trash-alt',
            'approve' => 'fas fa-check-circle',
            'reject' => 'fas fa-times-circle',
        ];
        
        return $icons[$this->tipe] ?? 'fas fa-info-circle';
    }
}