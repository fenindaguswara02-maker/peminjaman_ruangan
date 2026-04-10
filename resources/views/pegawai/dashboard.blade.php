@extends('layouts.pegawai')

@section('title', 'Dashboard Pegawai - Scheduler')
@section('page-title', 'Dashboard Pegawai')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Welcome Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name }}!</h1>
                <div class="flex items-center mt-2">
                    <i class="fas fa-at text-cyan-500 mr-1 text-sm"></i>
                    <p class="text-cyan-600 font-mono text-sm">{{ auth()->user()->username ?? 'Username belum diatur' }}</p>
                </div>
                <p class="text-gray-600 mt-1">Kelola jadwal peminjaman ruangan dan aktivitas kantor</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Hari ini</p>
                <p class="text-lg font-semibold text-primary-600">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </div>

    @if(isset($error))
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">{{ $error }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-primary-100">Peminjaman Hari Ini</p>
                    <p class="text-3xl font-bold mt-2">{{ $todayPeminjaman ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100">Sedang Berlangsung</p>
                    <p class="text-3xl font-bold mt-2">{{ $peminjamanBerlangsung ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-play-circle text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100">Akan Datang</p>
                    <p class="text-3xl font-bold mt-2">{{ $peminjamanAkanDatang ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100">Total Disetujui</p>
                    <p class="text-3xl font-bold mt-2">{{ $totalPeminjaman ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Hari Ini & Aktivitas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Jadwal Hari Ini -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Peminjaman Ruangan Hari Ini</h2>
                <span class="px-3 py-1 bg-primary-100 text-primary-800 text-sm rounded-full">{{ now()->translatedFormat('d F Y') }}</span>
            </div>
            
            @if(isset($todayPeminjamanList) && $todayPeminjamanList->count() > 0)
            <div class="space-y-4">
                @foreach($todayPeminjamanList as $peminjaman)
                @php
                    $currentTime = \Carbon\Carbon::now();
                    $tanggalMulai = \Carbon\Carbon::parse($peminjaman->tanggal_mulai);
                    $tanggalSelesai = \Carbon\Carbon::parse($peminjaman->tanggal_selesai);
                    $jamMulai = \Carbon\Carbon::parse($peminjaman->jam_mulai);
                    $jamSelesai = \Carbon\Carbon::parse($peminjaman->jam_selesai);
                    
                    if ($currentTime->between($jamMulai, $jamSelesai)) {
                        $status = 'Berlangsung';
                        $bgColor = 'bg-blue-50';
                        $borderColor = 'border-blue-500';
                        $textColor = 'text-blue-800';
                        $iconBgColor = 'bg-blue-100';
                        $iconColor = 'text-blue-600';
                    } elseif ($currentTime->lt($jamMulai)) {
                        $status = 'Akan Datang';
                        $bgColor = 'bg-green-50';
                        $borderColor = 'border-green-500';
                        $textColor = 'text-green-800';
                        $iconBgColor = 'bg-green-100';
                        $iconColor = 'text-green-600';
                    } else {
                        $status = 'Selesai';
                        $bgColor = 'bg-gray-50';
                        $borderColor = 'border-gray-500';
                        $textColor = 'text-gray-800';
                        $iconBgColor = 'bg-gray-100';
                        $iconColor = 'text-gray-600';
                    }
                    
                    // Tentukan ikon berdasarkan acara
                    $acara = strtolower($peminjaman->acara ?? '');
                    if (str_contains($acara, 'rapat') || str_contains($acara, 'meeting')) {
                        $kegiatanIcon = 'fa-users';
                    } elseif (str_contains($acara, 'seminar') || str_contains($acara, 'workshop')) {
                        $kegiatanIcon = 'fa-chalkboard-teacher';
                    } elseif (str_contains($acara, 'presentasi') || str_contains($acara, 'presentation')) {
                        $kegiatanIcon = 'fa-presentation';
                    } elseif (str_contains($acara, 'ujian') || str_contains($acara, 'test') || str_contains($acara, 'exam')) {
                        $kegiatanIcon = 'fa-file-alt';
                    } elseif (str_contains($acara, 'kuliah') || str_contains($acara, 'lecture')) {
                        $kegiatanIcon = 'fa-chalkboard';
                    } else {
                        $kegiatanIcon = 'fa-door-closed';
                    }
                    
                    // Nama ruangan
                    $namaRuangan = $peminjaman->ruangan->nama_ruangan ?? 'Ruangan Tidak Diketahui';
                    $kodeRuangan = $peminjaman->ruangan->kode_ruangan ?? '';
                    
                    // Nama pengaju
                    $namaPengaju = $peminjaman->nama_pengaju ?? 'Tidak diketahui';
                    $usernamePengaju = $peminjaman->user->username ?? '-';
                    
                    // Format tanggal
                    $tanggalDisplay = '';
                    if ($tanggalMulai->format('Y-m-d') === $tanggalSelesai->format('Y-m-d')) {
                        $tanggalDisplay = $tanggalMulai->translatedFormat('d M Y');
                    } else {
                        $tanggalDisplay = $tanggalMulai->translatedFormat('d M') . ' - ' . $tanggalSelesai->translatedFormat('d M Y');
                    }
                @endphp
                
                <div class="flex items-center justify-between p-4 {{ $bgColor }} rounded-lg border-l-4 {{ $borderColor }}">
                    <div class="flex items-center flex-1">
                        <div class="w-10 h-10 {{ $iconBgColor }} rounded-full flex items-center justify-center">
                            <i class="fas {{ $kegiatanIcon }} {{ $iconColor }}"></i>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">{{ $peminjaman->acara }}</p>
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-clock mr-1"></i>{{ $jamMulai->format('H:i') }} - {{ $jamSelesai->format('H:i') }}
                                <span class="mx-1">•</span>
                                <i class="fas fa-door-closed mr-1"></i>{{ $namaRuangan }}
                                @if($kodeRuangan)
                                <span class="text-xs">({{ $kodeRuangan }})</span>
                                @endif
                            </p>
                            <div class="flex flex-wrap gap-2 mt-1 text-xs text-gray-500">
                                <span><i class="fas fa-user mr-1"></i>{{ $namaPengaju }}</span>
                                <!-- TAMBAHKAN USERNAME -->
                                <span class="text-cyan-600"><i class="fas fa-at mr-1"></i>{{ $usernamePengaju }}</span>
                                <span><i class="fas fa-calendar-alt mr-1"></i>{{ $tanggalDisplay }}</span>
                                @if($peminjaman->jumlah_peserta)
                                <span><i class="fas fa-users mr-1"></i>{{ $peminjaman->jumlah_peserta }} peserta</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <span class="px-3 py-1 {{ $iconBgColor }} {{ $textColor }} text-sm rounded-full whitespace-nowrap ml-2">
                        {{ $status }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <i class="fas fa-door-closed text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">Tidak ada peminjaman ruangan untuk hari ini</p>
                <p class="text-sm text-gray-400 mt-1">Semua peminjaman yang disetujui akan tampil di sini</p>
            </div>
            @endif
            
            @if(isset($todayPeminjamanList) && $todayPeminjamanList->count() > 0)
            <div class="mt-6">
                <a href="{{ route('pegawai.peminjaman-ruangan.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition duration-200">
                    <i class="fas fa-list mr-2"></i>
                    Lihat Semua Peminjaman
                </a>
            </div>
            @endif
        </div>

        <!-- Peminjaman Mendatang & Ringkasan -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Peminjaman Mendatang</h2>
            </div>
            
            @if(isset($upcomingPeminjaman) && $upcomingPeminjaman->count() > 0)
            <div class="space-y-3 mb-6">
                @foreach($upcomingPeminjaman as $peminjaman)
                @php
                    $tanggalMulai = \Carbon\Carbon::parse($peminjaman->tanggal_mulai);
                    $namaRuangan = $peminjaman->ruangan->nama_ruangan ?? 'Ruangan';
                    $kodeRuangan = $peminjaman->ruangan->kode_ruangan ?? '';
                    $usernamePengaju = $peminjaman->user->username ?? '-';
                @endphp
                
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition duration-150 border border-gray-100">
                    <div class="flex items-center flex-1">
                        <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-calendar text-primary-600 text-xs"></i>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="font-medium text-gray-800 text-sm truncate">{{ $peminjaman->acara }}</p>
                            <div class="flex items-center text-xs text-gray-500 mt-1">
                                <span class="flex items-center mr-3">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    {{ $tanggalMulai->translatedFormat('d M') }}
                                </span>
                                <span class="flex items-center mr-3">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ \Carbon\Carbon::parse($peminjaman->jam_mulai)->format('H:i') }}
                                </span>
                                <!-- TAMBAHKAN USERNAME -->
                                <span class="flex items-center text-cyan-600">
                                    <i class="fas fa-at mr-1"></i>
                                    {{ $usernamePengaju }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="ml-2 text-right flex-shrink-0">
                        <span class="text-xs text-gray-500 truncate max-w-[100px] block" title="{{ $namaRuangan }}">
                            {{ Str::limit($namaRuangan, 12) }}
                        </span>
                        @if($kodeRuangan)
                        <span class="text-xs text-gray-400">({{ $kodeRuangan }})</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-6">
                <i class="fas fa-calendar-alt text-gray-300 text-3xl mb-3"></i>
                <p class="text-gray-500 text-sm">Tidak ada peminjaman mendatang</p>
                <p class="text-xs text-gray-400 mt-1">Peminjaman dalam 7 hari ke depan</p>
            </div>
            @endif
            
            <!-- Ringkasan Minggu Ini -->
            <div class="pt-6 border-t border-gray-200">
                <h3 class="font-semibold text-gray-700 mb-4">Ringkasan Minggu Ini</h3>
                
                @if(isset($daysOfWeek) && !empty($daysOfWeek))
                <div class="grid grid-cols-7 gap-2">
                    @foreach($daysOfWeek as $day)
                    <div class="text-center">
                        <div class="text-xs text-gray-500 mb-1">{{ $day['day_name'] ?? '?' }}</div>
                        <div class="relative">
                            <div class="w-8 h-8 mx-auto rounded-full flex items-center justify-center 
                                {{ isset($day['is_today']) && $day['is_today'] ? 'bg-primary-100 text-primary-600 font-semibold' : 
                                  (isset($day['count']) && $day['count'] > 0 ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500') }}">
                                {{ $day['day_number'] ?? '?' }}
                            </div>
                            @if(isset($day['count']) && $day['count'] > 0)
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-xs">{{ $day['count'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-4 text-sm text-gray-600">
                    <p>Total peminjaman minggu ini: <span class="font-semibold">{{ $weeklyStats->sum('count') ?? 0 }}</span></p>
                </div>
                @else
                <p class="text-gray-500 text-sm">Data ringkasan mingguan belum tersedia</p>
                @endif
            </div>
            
            <!-- Statistik Status -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="font-semibold text-gray-700 mb-3">Statistik Status</h3>
                
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                <span class="text-sm text-yellow-700">Menunggu</span>
                            </div>
                            <span class="font-semibold text-yellow-800">{{ $statusStats['menunggu'] ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-sm text-green-700">Disetujui</span>
                            </div>
                            <span class="font-semibold text-green-800">{{ $statusStats['disetujui'] ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-sm text-red-700">Ditolak</span>
                            </div>
                            <span class="font-semibold text-red-800">{{ $statusStats['ditolak'] ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                <span class="text-sm text-blue-700">Selesai</span>
                            </div>
                            <span class="font-semibold text-blue-800">{{ $statusStats['selesai'] ?? 0 }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 col-span-2">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-gray-500 rounded-full mr-2"></div>
                                <span class="text-sm text-gray-700">Dibatalkan</span>
                            </div>
                            <span class="font-semibold text-gray-800">{{ $statusStats['dibatalkan'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ruangan Terpopuler -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="font-semibold text-gray-700 mb-3">Ruangan Terpopuler</h3>
                
                @if(isset($popularRuangan) && $popularRuangan->count() > 0)
                <div class="space-y-3">
                    @foreach($popularRuangan as $ruangan)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-700">
                                {{ $ruangan->nama_ruangan ?? 'Ruangan' }} 
                                @if(isset($ruangan->kode_ruangan))
                                <span class="text-gray-500">({{ $ruangan->kode_ruangan }})</span>
                                @endif
                            </span>
                            <span class="font-medium text-primary-600">{{ $ruangan->jadwal_count ?? 0 }} peminjaman</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            @php
                                $maxCount = max(($popularRuangan->max('jadwal_count') ?? 0), 1);
                                $currentCount = $ruangan->jadwal_count ?? 0;
                                $percentage = ($currentCount / $maxCount) * 100;
                            @endphp
                            <div class="bg-primary-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        @if(isset($ruangan->kapasitas))
                        <div class="text-xs text-gray-500 mt-1 flex justify-between">
                            <span>Kapasitas: {{ $ruangan->kapasitas }} orang</span>
                            <span>{{ $currentCount }} kali dipinjam</span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fas fa-door-open text-gray-300 text-2xl mb-2"></i>
                    <p class="text-gray-500 text-sm">Belum ada data ruangan</p>
                    <p class="text-xs text-gray-400 mt-1">Data akan muncul setelah ada peminjaman</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Animasi untuk badge */
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .5;
        }
    }
</style>
@endpush