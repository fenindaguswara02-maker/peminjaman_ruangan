@extends('layouts.admin')

@section('title', 'Admin Dashboard - RoomBooking')
@section('page-title', 'Admin Dashboard')

@section('content')
<style>
    .dashboard-stat {
        transition: all 0.3s ease;
    }
    
    .dashboard-stat:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .quick-action-card {
        transition: all 0.3s ease;
        border-left: 4px solid;
    }
    
    .quick-action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .activity-item {
        transition: all 0.2s ease;
    }
    
    .activity-item:hover {
        background-color: #f8fafc;
    }
    
    .user-badge {
        font-size: 11px;
        padding: 3px 8px;
        border-radius: 12px;
    }
    
    /* Ranking badge colors */
    .ranking-1 {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
    }
    
    .ranking-2 {
        background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
        box-shadow: 0 4px 10px rgba(107, 114, 128, 0.3);
    }
    
    .ranking-3 {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        box-shadow: 0 4px 10px rgba(234, 88, 12, 0.3);
    }
    
    .ranking-default {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3);
    }
</style>

<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Selamat Datang, Admin</h1>
            <div class="flex items-center mt-2">
                <i class="fas fa-at text-cyan-500 mr-1 text-sm"></i>
                <p class="text-cyan-600 font-mono text-sm">{{ auth()->user()->username ?? 'Username belum diatur' }}</p>
            </div>
            <p class="text-gray-600 mt-1">Sistem Peminjaman Ruangan Digital - RoomBooking</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="text-right">
                <p class="text-sm text-gray-600">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
                <p class="text-lg font-semibold text-primary-600">{{ \Carbon\Carbon::now()->format('H:i') }}</p>
            </div>
            <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-shield text-primary-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users -->
    <div class="dashboard-stat bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Pengguna</p>
                <h3 class="text-3xl font-bold text-primary-700 mt-2">{{ $totalUsers ?? 0 }}</h3>
                <p class="text-xs text-gray-400 mt-1">Pengguna terdaftar</p>
            </div>
            <div class="w-14 h-14 bg-primary-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-users text-primary-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-user-check text-green-500 mr-2"></i>
                <span>Aktif: {{ $activeUsers ?? 0 }}</span>
                <span class="mx-2">•</span>
                <i class="fas fa-user-plus text-blue-500 mr-2"></i>
                <span>Baru bulan ini: {{ $newUsersThisMonth ?? 0 }}</span>
            </div>
        </div>
    </div>
    
    <!-- Total Ruangan -->
    <div class="dashboard-stat bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Ruangan</p>
                <h3 class="text-3xl font-bold text-blue-700 mt-2">{{ $totalRuangan ?? 0 }}</h3>
                <p class="text-xs text-gray-400 mt-1">Ruangan tersedia</p>
            </div>
            <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-door-open text-blue-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <span>Tersedia: {{ $ruanganTersedia ?? 0 }}</span>
                <span class="mx-2">•</span>
                <i class="fas fa-tools text-yellow-500 mr-2"></i>
                <span>Maintenance: {{ $ruanganMaintenance ?? 0 }}</span>
            </div>
        </div>
    </div>
    
    <!-- Peminjaman Bulan Ini -->
    <div class="dashboard-stat bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Peminjaman Bulan Ini</p>
                <h3 class="text-3xl font-bold text-green-700 mt-2">{{ $peminjamanBulanIni ?? 0 }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
            </div>
            <div class="w-14 h-14 bg-green-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-calendar-alt text-green-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-chart-line text-green-500 mr-2"></i>
                <span>Hari ini: {{ $peminjamanHariIni ?? 0 }}</span>
                <span class="mx-2">•</span>
                <i class="fas fa-trend-up text-blue-500 mr-2"></i>
                <span>↑ {{ $growthPercentage ?? 0 }}%</span>
            </div>
        </div>
    </div>
    
    <!-- Total Peminjaman -->
    <div class="dashboard-stat bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Peminjaman</p>
                <h3 class="text-3xl font-bold text-purple-700 mt-2">{{ $totalPeminjaman ?? 0 }}</h3>
                <p class="text-xs text-gray-400 mt-1">Semua waktu</p>
            </div>
            <div class="w-14 h-14 bg-purple-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-clipboard-check text-purple-600 text-2xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center text-sm text-gray-600">
                <i class="fas fa-calendar-check text-purple-500 mr-2"></i>
                <span>Disetujui: {{ $totalDisetujui ?? 0 }}</span>
                <span class="mx-2">•</span>
                <i class="fas fa-percentage text-orange-500 mr-2"></i>
                <span>Rata-rata: {{ $avgPerMonth ?? 0 }}/bulan</span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-900">Aksi Cepat</h2>
        <div class="flex items-center space-x-2">
            <i class="fas fa-bolt text-yellow-500"></i>
            <span class="text-sm text-gray-600">Akses cepat ke fitur utama</span>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Manajemen Pengguna -->
        <a href="{{ route('admin.users.index') }}" 
           class="quick-action-card bg-gradient-to-r from-primary-50 to-primary-100 p-5 rounded-lg border-l-4 border-primary-500">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-primary-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users-cog text-white text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-primary-900">Manajemen Akun</h3>
                    <p class="text-sm text-primary-600 mt-1">Kelola pengguna & hak akses</p>
                </div>
                <i class="fas fa-chevron-right text-primary-400"></i>
            </div>
        </a>
        
        <!-- Manajemen Ruangan -->
        <a href="{{ route('admin.ruangan.index') }}" 
           class="quick-action-card bg-gradient-to-r from-blue-50 to-blue-100 p-5 rounded-lg border-l-4 border-blue-500">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-door-open text-white text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-blue-900">Manajemen Ruangan</h3>
                    <p class="text-sm text-blue-600 mt-1">Tambah/edit ruangan</p>
                </div>
                <i class="fas fa-chevron-right text-blue-400"></i>
            </div>
        </a>
        
        <!-- Peminjaman Ruangan -->
        <a href="{{ route('admin.peminjaman-ruangan.index') }}" 
           class="quick-action-card bg-gradient-to-r from-green-50 to-green-100 p-5 rounded-lg border-l-4 border-green-500">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check text-white text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-green-900">Peminjaman</h3>
                    <p class="text-sm text-green-600 mt-1">Kelola peminjaman ruangan</p>
                </div>
                <i class="fas fa-chevron-right text-green-400"></i>
            </div>
        </a>
        
        <!-- Jadwal -->
        <a href="{{ route('admin.jadwal-peminjaman') }}" 
           class="quick-action-card bg-gradient-to-r from-purple-50 to-purple-100 p-5 rounded-lg border-l-4 border-purple-500">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-white text-lg"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-purple-900">Jadwal</h3>
                    <p class="text-sm text-purple-600 mt-1">Lihat jadwal lengkap</p>
                </div>
                <i class="fas fa-chevron-right text-purple-400"></i>
            </div>
        </a>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Ruangan Paling Populer -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Ruangan Paling Populer</h2>
                <p class="text-sm text-gray-600 mt-1">Ruangan dengan peminjaman terbanyak</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                    <i class="fas fa-trophy mr-1 text-yellow-500"></i>
                    Top Ruangan
                </span>
                @if(isset($popularRooms) && $popularRooms->count() > 0)
                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                    Total: {{ $popularRooms->sum('peminjaman_count') ?? 0 }}
                </span>
                @endif
            </div>
        </div>
        
        <div class="space-y-4">
            @if(isset($popularRooms) && $popularRooms->count() > 0)
                @php
                    $maxPeminjaman = $maxPeminjaman ?? 1;
                    $totalPeminjamanKeseluruhan = $totalPeminjaman ?? 1;
                    if ($totalPeminjamanKeseluruhan <= 0) $totalPeminjamanKeseluruhan = 1;
                @endphp
                
                @foreach($popularRooms as $index => $room)
                    @php
                        $totalPeminjamanRuangan = $room->peminjaman_count ?? 0;
                        $popularityPercentage = $maxPeminjaman > 0 ? round(($totalPeminjamanRuangan / $maxPeminjaman) * 100) : 0;
                        $kontribusiPercentage = $totalPeminjamanKeseluruhan > 0 
                            ? round(($totalPeminjamanRuangan / $totalPeminjamanKeseluruhan) * 100, 1) 
                            : 0;
                        
                        $progressColor = $index == 0 ? '#fbbf24' : ($index == 1 ? '#9ca3af' : ($index == 2 ? '#f97316' : '#3b82f6'));
                        $progressColorDark = $index == 0 ? '#f59e0b' : ($index == 1 ? '#6b7280' : ($index == 2 ? '#ea580c' : '#2563eb'));
                        $bgColor = $index == 0 ? '#fffbeb' : ($index == 1 ? '#f3f4f6' : ($index == 2 ? '#fff7ed' : '#eff6ff'));
                    @endphp
                    
                    <div class="activity-item p-4 rounded-lg border transition-all duration-200 hover:shadow-md"
                         style="background: linear-gradient(135deg, {{ $bgColor }} 0%, white 100%);">
                        
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 flex items-center justify-center rounded-full text-white font-bold text-lg
                                    {{ $index == 0 ? 'ranking-1' : ($index == 1 ? 'ranking-2' : ($index == 2 ? 'ranking-3' : 'ranking-default')) }}">
                                    {{ $index + 1 }}
                                </div>
                                
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $room->nama_ruangan }}</h4>
                                    <div class="flex items-center space-x-3 mt-1">
                                        <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                                            {{ $room->kode_ruangan }}
                                        </span>
                                        <span class="text-sm text-gray-600">
                                            <i class="fas fa-users text-blue-500 mr-1"></i>{{ $room->kapasitas }} orang
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <div class="flex items-center space-x-1">
                                    <span class="text-2xl font-bold text-primary-700">{{ $totalPeminjamanRuangan }}</span>
                                    <span class="text-sm text-gray-500">kali</span>
                                </div>
                                <div class="flex items-center justify-end mt-1 space-x-2">
                                    <span class="text-xs font-medium {{ $popularityPercentage > 80 ? 'text-green-600' : ($popularityPercentage > 50 ? 'text-yellow-600' : 'text-blue-600') }}">
                                        <i class="fas fa-chart-line mr-1"></i>{{ $popularityPercentage }}%
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <i class="fas fa-pie-chart mr-1"></i>{{ $kontribusiPercentage }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span class="flex items-center">
                                    <i class="fas fa-chart-line mr-1 text-primary-500"></i>
                                    Popularitas
                                </span>
                                <span class="font-medium">{{ $popularityPercentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full transition-all duration-500 ease-out"
                                     style="width: {{ $popularityPercentage }}%; 
                                            background: linear-gradient(90deg, {{ $progressColor }} 0%, {{ $progressColorDark }} 100%);">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4">
                                @if($room->lokasi)
                                <div class="flex items-center text-xs text-gray-600">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i>
                                    <span>{{ $room->lokasi }}</span>
                                </div>
                                @endif
                                <div class="flex items-center text-xs text-gray-600">
                                    <i class="fas fa-percentage text-gray-400 mr-1"></i>
                                    <span>Kontribusi: {{ $kontribusiPercentage }}%</span>
                                </div>
                                @if($room->status)
                                <div class="flex items-center text-xs text-gray-600">
                                    <i class="fas fa-circle text-{{ $room->status == 'tersedia' ? 'green' : ($room->status == 'dibooking' ? 'yellow' : 'red') }}-500 mr-1" style="font-size: 6px;"></i>
                                    <span>{{ ucfirst($room->status) }}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-gradient-to-r from-yellow-500 to-yellow-600 mr-2"></div>
                                <span class="text-xs text-gray-600">Juara 1</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-gradient-to-r from-gray-400 to-gray-500 mr-2"></div>
                                <span class="text-xs text-gray-600">Juara 2</span>
                            </div>
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-gradient-to-r from-orange-500 to-orange-600 mr-2"></div>
                                <span class="text-xs text-gray-600">Juara 3</span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-trophy text-blue-300 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Data Popularitas</h3>
                    <p class="text-gray-600 mb-4 max-w-md mx-auto">
                        Belum ada data peminjaman ruangan. Statistik popularitas akan muncul setelah ada peminjaman.
                    </p>
                    <div class="inline-flex items-center space-x-2 text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-lg">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <span>Ruangan dengan peminjaman terbanyak akan tampil di sini</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Jadwal Hari Ini (DIPERBAIKI - TIDAK DOUBLE TIME) -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Jadwal Hari Ini</h2>
                <p class="text-sm text-gray-600 mt-1">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            </div>
            <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                {{ $jadwalHariIniCount ?? 0 }} acara
            </span>
        </div>
        
        <div class="space-y-4">
            @if(isset($jadwalHariIni) && $jadwalHariIni->count() > 0)
                @foreach($jadwalHariIni as $jadwal)
                @php
                    $jamMulai = $jadwal->jam_mulai ? \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') : '--:--';
                    $jamSelesai = $jadwal->jam_selesai ? \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') : '--:--';
                    
                    $now = \Carbon\Carbon::now();
                    
                    // ========== PERBAIKAN: Ambil hanya tanggal (Y-m-d) bukan datetime ==========
                    // Pastikan $jadwal->tanggal hanya berisi format Y-m-d, bukan Y-m-d H:i:s
                    if ($jadwal->tanggal instanceof \Carbon\Carbon) {
                        $tanggalOnly = $jadwal->tanggal->format('Y-m-d');
                    } else {
                        $tanggalOnly = date('Y-m-d', strtotime($jadwal->tanggal));
                    }
                    
                    $waktuMulai = $jadwal->jam_mulai ? \Carbon\Carbon::parse($tanggalOnly . ' ' . $jadwal->jam_mulai) : null;
                    $waktuSelesai = $jadwal->jam_selesai ? \Carbon\Carbon::parse($tanggalOnly . ' ' . $jadwal->jam_selesai) : null;
                    
                    $statusClass = 'bg-yellow-100 text-yellow-800';
                    $statusText = 'Akan Datang';
                    
                    if ($waktuMulai && $waktuSelesai) {
                        if ($now->between($waktuMulai, $waktuSelesai)) {
                            $statusClass = 'bg-blue-100 text-blue-800';
                            $statusText = 'Berlangsung';
                        } elseif ($now->gt($waktuSelesai)) {
                            $statusClass = 'bg-gray-100 text-gray-800';
                            $statusText = 'Selesai';
                        }
                    }
                    
                    $usernamePengaju = $jadwal->user->username ?? '-';
                @endphp
                
                <div class="activity-item p-4 rounded-lg border border-gray-100 hover:border-green-200 transition-all duration-200"
                     style="background: linear-gradient(135deg, #f0fdf4 0%, white 100%);">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center shadow-sm">
                                <i class="fas fa-door-open text-white"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $jadwal->ruangan->nama_ruangan ?? 'Ruangan Tidak Diketahui' }}</h4>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span class="text-sm text-green-600 font-medium">
                                        <i class="far fa-clock mr-1"></i>
                                        {{ $jamMulai }} - {{ $jamSelesai }}
                                    </span>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ $jadwal->nama_pengaju ?? 'Tidak diketahui' }}</p>
                            <div class="flex items-center justify-end mt-1">
                                <i class="fas fa-at text-cyan-500 text-xs mr-1"></i>
                                <span class="text-xs text-cyan-600 font-mono">{{ $usernamePengaju }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-user-tag mr-1"></i>
                                {{ ucfirst($jadwal->jenis_pengaju ?? 'user') }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-t border-green-100">
                        <div class="flex justify-between items-center">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $jadwal->acara ?? 'Tanpa judul' }}</p>
                                @if($jadwal->keterangan)
                                <p class="text-xs text-gray-600 mt-1">
                                    <i class="far fa-sticky-note mr-1 text-gray-400"></i>
                                    {{ Str::limit($jadwal->keterangan, 80) }}
                                </p>
                                @endif
                            </div>
                            @if($jadwal->jumlah_peserta)
                            <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full ml-2">
                                <i class="fas fa-users mr-1"></i> {{ $jadwal->jumlah_peserta }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
                
                @if($jadwalHariIni->count() > 5)
                <div class="text-center pt-4">
                    <a href="{{ route('admin.jadwal-peminjaman') }}" 
                       class="text-primary-600 hover:text-primary-800 font-medium text-sm inline-flex items-center">
                        Lihat {{ $jadwalHariIni->count() - 5 }} lainnya
                        <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
                @endif
            @else
                <div class="text-center py-12">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-day text-green-300 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ada Jadwal Hari Ini</h3>
                    <p class="text-gray-600 mb-4 max-w-md mx-auto">
                        Tidak ada peminjaman ruangan yang dijadwalkan untuk hari ini.
                    </p>
                    <div class="inline-flex items-center space-x-2 text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-lg">
                        <i class="fas fa-info-circle text-green-500"></i>
                        <span>Peminjaman dengan status "disetujui" akan tampil di sini</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Statistik Bulanan -->
<div class="bg-white rounded-xl shadow-sm p-6 mt-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Statistik Peminjaman Bulanan</h2>
            <p class="text-sm text-gray-600 mt-1">Trend peminjaman {{ \Carbon\Carbon::now()->year }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="px-3 py-1 bg-primary-100 text-primary-800 text-sm font-medium rounded-full">
                {{ \Carbon\Carbon::now()->year }}
            </span>
        </div>
    </div>
    
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @php
            $monthlyData = isset($monthlyStats) && is_array($monthlyStats) ? $monthlyStats : [];
            $maxMonthlyCount = !empty($monthlyData) ? max($monthlyData) : 1;
        @endphp
        
        @for($i = 0; $i < 6; $i++)
            @php
                $month = \Carbon\Carbon::now()->subMonths($i);
                $monthName = $month->translatedFormat('M');
                $monthFull = $month->translatedFormat('F');
                $monthNumber = $month->month;
                $count = $monthlyData[$monthNumber] ?? 0;
                $percentage = $maxMonthlyCount > 0 ? round(($count / $maxMonthlyCount) * 100) : 0;
            @endphp
        <div class="bg-gray-50 p-4 rounded-lg text-center hover:shadow-md transition-all duration-300">
            <p class="text-sm text-gray-600 mb-2 font-medium">{{ $monthName }}</p>
            <p class="text-2xl font-bold {{ $count > 0 ? 'text-primary-700' : 'text-gray-400' }}">{{ $count }}</p>
            <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500 ease-out {{ $count > 0 ? 'bg-gradient-to-r from-primary-500 to-primary-600' : 'bg-gray-200' }}"
                         style="width: {{ $percentage }}%"></div>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">{{ $monthFull }}</p>
        </div>
        @endfor
    </div>
    
    <div class="mt-6 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-center space-x-8">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-gradient-to-r from-primary-500 to-primary-600 rounded"></div>
                <span class="text-sm text-gray-600">Jumlah Peminjaman</span>
            </div>
            <div class="flex items-center space-x-2">
                <i class="fas fa-arrow-up text-green-500"></i>
                <span class="text-sm text-gray-600">Trend meningkat</span>
            </div>
            <div class="flex items-center space-x-2">
                <i class="fas fa-minus text-gray-500"></i>
                <span class="text-sm text-gray-600">Stabil</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users -->
<div class="bg-white rounded-xl shadow-sm p-6 mt-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Pengguna Terbaru</h2>
            <p class="text-sm text-gray-600 mt-1">Pengguna yang baru terdaftar</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="text-primary-600 hover:text-primary-800 font-medium text-sm flex items-center space-x-1">
            <span>Lihat semua</span>
            <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                    <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @if(isset($recentUsers) && $recentUsers->count() > 0)
                    @foreach($recentUsers as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 text-white font-medium text-sm
                                    {{ $user->role == 'admin' ? 'bg-gradient-to-r from-purple-500 to-purple-600' : 
                                       ($user->role == 'pegawai' ? 'bg-gradient-to-r from-blue-500 to-blue-600' : 
                                       'bg-gradient-to-r from-green-500 to-green-600') }}">
                                    @php
                                        $initials = '';
                                        if (!empty($user->name)) {
                                            $names = explode(' ', $user->name);
                                            foreach ($names as $name) {
                                                if (!empty($name)) {
                                                    $initials .= strtoupper(substr($name, 0, 1));
                                                    if (strlen($initials) >= 2) break;
                                                }
                                            }
                                        }
                                        $initials = $initials ?: 'U';
                                    @endphp
                                    <span>{{ $initials }}</span>
                                </div>
                                <span class="font-medium text-gray-900">{{ $user->name }}</span>
                            </div>
                        </div>
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <i class="fas fa-at text-cyan-500 text-xs mr-1"></i>
                                <span class="text-sm text-cyan-600 font-mono">{{ $user->username ?? '-' }}</span>
                            </div>
                        </div>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $user->email }}</div>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $user->role == 'admin' ? 'bg-purple-100 text-purple-800' : 
                                   ($user->role == 'pegawai' ? 'bg-blue-100 text-blue-800' : 
                                   'bg-green-100 text-green-800') }}">
                                <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            <div>{{ \Carbon\Carbon::parse($user->created_at)->translatedFormat('d M Y') }}</div>
                            <div class="text-xs text-gray-400">
                                {{ \Carbon\Carbon::parse($user->created_at)->diffForHumans() }}
                            </div>
                        </div>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $user->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                <i class="fas fa-circle {{ $user->status == 'active' ? 'text-green-500' : 'text-gray-500' }} mr-1" style="font-size: 6px;"></i>
                                {{ $user->status == 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-gray-400"></i>
                            </div>
                            <p class="text-gray-900 font-medium">Belum ada pengguna terdaftar</p>
                            <p class="text-sm text-gray-600 mt-1">Pengguna akan muncul di sini setelah registrasi</p>
                        </div>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto refresh setiap 60 detik untuk update real-time
setTimeout(() => {
    location.reload();
}, 60000);

// Animasi untuk quick actions
document.querySelectorAll('.quick-action-card').forEach(card => {
    card.addEventListener('click', function(e) {
        const icon = this.querySelector('.fa-chevron-right');
        if (icon) {
            icon.classList.remove('fa-chevron-right');
            icon.classList.add('fa-spinner', 'fa-spin');
            
            setTimeout(() => {
                icon.classList.remove('fa-spinner', 'fa-spin');
                icon.classList.add('fa-chevron-right');
            }, 1000);
        }
    });
});

// Animasi hover untuk statistik
document.querySelectorAll('.dashboard-stat').forEach(stat => {
    stat.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.1)';
    });
    
    stat.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
    });
});

// Debug: Cek data popular rooms
console.log('Popular Rooms:', @json($popularRooms));
console.log('Max Peminjaman:', {{ $maxPeminjaman ?? 0 }});
console.log('Total Peminjaman:', {{ $totalPeminjaman ?? 0 }});
</script>
@endpush