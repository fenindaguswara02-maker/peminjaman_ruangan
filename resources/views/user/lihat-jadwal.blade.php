@extends('layouts.user')

@section('title', 'Lihat Jadwal - Scheduler')
@section('page-title', 'Lihat Jadwal Ruangan')

@section('content')
@php
    // Helper functions
    $helpers = [
        'getKegiatanName' => function($data) {
            if (isset($data->acara)) return $data->acara;
            if (isset($data->nama_kegiatan)) return $data->nama_kegiatan;
            if (isset($data->keperluan)) return $data->keperluan;
            return 'Kegiatan';
        },
        
        'getWaktuMulai' => function($data) {
            if (isset($data->jam_mulai)) return $data->jam_mulai;
            if (isset($data->start_time)) return $data->start_time;
            return null;
        },
        
        'getWaktuSelesai' => function($data) {
            if (isset($data->jam_selesai)) return $data->jam_selesai;
            if (isset($data->end_time)) return $data->end_time;
            return null;
        },
        
        'getStatus' => function($data) {
            if (isset($data->status)) return $data->status;
            return 'menunggu';
        },
        
        'getStatusRealTime' => function($data) {
            // JIKA STATUS PEMINJAMAN = DIBATALKAN ATAU DITOLAK, MAKA STATUS REAL-TIME KOSONG
            if (isset($data->status)) {
                if ($data->status == 'dibatalkan' || $data->status == 'ditolak') {
                    return null; // Status real-time kosong
                }
            }
            
            if (isset($data->status_real_time)) return $data->status_real_time;
            
            // Jika tidak ada status real-time, tentukan berdasarkan waktu
            $currentTime = now();
            $tanggalMulai = $data->tanggal_mulai ?? $data->tanggal ?? $data->created_at;
            $tanggalSelesai = $data->tanggal_selesai ?? $data->tanggal_mulai ?? $data->created_at;
            $jamMulai = $helpers['getWaktuMulai']($data);
            $jamSelesai = $helpers['getWaktuSelesai']($data);
            
            if ($tanggalMulai && $jamMulai && $tanggalSelesai && $jamSelesai) {
                $startDateTime = \Carbon\Carbon::parse($tanggalMulai . ' ' . $jamMulai);
                $endDateTime = \Carbon\Carbon::parse($tanggalSelesai . ' ' . $jamSelesai);
                
                if ($currentTime->lt($startDateTime)) {
                    return 'akan_datang';
                } elseif ($currentTime->between($startDateTime, $endDateTime)) {
                    return 'berlangsung';
                } elseif ($currentTime->gt($endDateTime)) {
                    return 'selesai';
                }
            }
            
            return 'akan_datang';
        },
        
        'getKodeRuangan' => function($data) {
            if (isset($data->ruangan->kode_ruangan)) return $data->ruangan->kode_ruangan;
            if (isset($data->kode_ruangan)) return $data->kode_ruangan;
            return '';
        },
        
        'getNamaRuangan' => function($data) {
            if (isset($data->ruangan->nama_ruangan)) return $data->ruangan->nama_ruangan;
            if (isset($data->nama_ruangan)) return $data->nama_ruangan;
            return 'Ruangan';
        },
        
        'getKapasitas' => function($data) {
            if (isset($data->ruangan->kapasitas)) return $data->ruangan->kapasitas;
            if (isset($data->kapasitas)) return $data->kapasitas;
            return 0;
        }
    ];

    // Ambil tanggal dari URL atau gunakan hari ini
    $selectedDate = request('date', now()->format('Y-m-d'));
    $selectedDateObj = \Carbon\Carbon::parse($selectedDate);
    
    // Ambil semua ruangan
    $ruangan = \App\Models\Ruangan::orderBy('kode_ruangan')->get();
    
    // ============================================
    // DATA UNTUK TABEL JADWAL UTAMA (SEMUA USER)
    // ============================================
    // Ambil SEMUA peminjaman untuk tanggal yang dipilih 
    // HANYA yang statusnya disetujui atau selesai (tidak termasuk dibatalkan/ditolak)
    $allPeminjamanForDate = \App\Models\PeminjamanRuangan::with('ruangan')
        ->where(function($query) {
            $query->where('status', 'disetujui')
                  ->orWhere('status', 'selesai');
        })
        ->where(function($query) use ($selectedDate) {
            $query->whereDate('tanggal_mulai', '<=', $selectedDate)
                  ->whereDate('tanggal_selesai', '>=', $selectedDate);
        })
        ->get();
    
    // ============================================
    // DATA UNTUK BAGIAN "KEGIATAN SAYA" (HANYA USER LOGIN)
    // ============================================
    $user = auth()->user();
    $myPeminjamanForDate = \App\Models\PeminjamanRuangan::with('ruangan')
        ->where('nama_pengaju', $user->name) // Filter berdasarkan nama pengaju
        ->where(function($query) {
            // HANYA TAMPILKAN YANG STATUSNYA BUKAN DIBATALKAN ATAU DITOLAK
            $query->where('status', 'disetujui')
                  ->orWhere('status', 'menunggu')
                  ->orWhere('status', 'selesai');
        })
        ->where(function($query) use ($selectedDate) {
            $query->whereDate('tanggal_mulai', '<=', $selectedDate)
                  ->whereDate('tanggal_selesai', '>=', $selectedDate);
        })
        ->orderBy('jam_mulai')
        ->get();
    
    // Hitung ruangan yang terpakai per jam (untuk semua user)
    $ruanganTerpakai = [];
    $currentTime = now();
    
    foreach ($allPeminjamanForDate as $booking) {
        $ruanganId = $booking->ruangan_id;
        if (!isset($ruanganTerpakai[$ruanganId])) {
            $ruanganTerpakai[$ruanganId] = [];
        }
        
        // Parse jam mulai dan selesai
        $jamMulai = (int) substr($booking->jam_mulai, 0, 2);
        $jamSelesai = (int) substr($booking->jam_selesai, 0, 2);
        
        // Tentukan status real-time (akan null jika dibatalkan/ditolak)
        $statusRealTime = $helpers['getStatusRealTime']($booking);
        
        // Hanya tambahkan ke jadwal jika status real-time tidak null (tidak dibatalkan/ditolak)
        if ($statusRealTime !== null) {
            // Tambahkan ke array untuk setiap jam
            for ($jam = $jamMulai; $jam < $jamSelesai; $jam++) {
                if (!isset($ruanganTerpakai[$ruanganId][$jam])) {
                    $ruanganTerpakai[$ruanganId][$jam] = [];
                }
                
                $ruanganTerpakai[$ruanganId][$jam][] = [
                    'id' => $booking->id,
                    'acara' => $booking->acara,
                    'jam_mulai' => $booking->jam_mulai,
                    'jam_selesai' => $booking->jam_selesai,
                    'status' => $booking->status,
                    'status_real_time' => $statusRealTime
                ];
            }
        }
    }
    
    // Helper untuk styling status
    $statusColors = [
        'akan_datang' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'berlangsung' => 'bg-purple-100 text-purple-800 border-purple-200 animate-pulse',
        'selesai' => 'bg-green-100 text-green-800 border-green-200'
    ];
    
    $statusIcons = [
        'akan_datang' => 'fa-clock',
        'berlangsung' => 'fa-play-circle',
        'selesai' => 'fa-check-circle'
    ];
    
    $statusTexts = [
        'akan_datang' => 'Akan Datang',
        'berlangsung' => 'Sedang Berlangsung',
        'selesai' => 'Selesai'
    ];
    
    // Helper untuk styling status peminjaman
    $peminjamanStatusColors = [
        'disetujui' => 'bg-green-100 text-green-800',
        'menunggu' => 'bg-yellow-100 text-yellow-800',
        'ditolak' => 'bg-red-100 text-red-800',
        'dibatalkan' => 'bg-gray-100 text-gray-800',
        'selesai' => 'bg-blue-100 text-blue-800'
    ];
@endphp

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Jadwal Ruangan</h1>
                <p class="text-gray-600">Lihat ketersediaan ruangan secara real-time</p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" id="filter-date" value="{{ $selectedDate }}" 
                           min="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ruangan</label>
                    <select id="filter-ruangan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        <option value="">Semua Ruangan</option>
                        @foreach($ruangan as $room)
                            <option value="{{ $room->id }}" {{ request('ruangan') == $room->id ? 'selected' : '' }}>
                                {{ $room->kode_ruangan }} - {{ $room->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="applyFilters()" class="w-full bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center justify-center space-x-2 transition-colors">
                        <i class="fas fa-filter"></i>
                        <span>Lihat Jadwal</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- STATISTIK SINGKAT -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-door-open text-blue-500 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-500">Total Ruangan</p>
                    <p class="text-xl font-bold text-gray-800">{{ $ruangan->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar-check text-purple-500 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-500">Total Peminjaman (Hari Ini)</p>
                    <p class="text-xl font-bold text-gray-800">{{ $allPeminjamanForDate->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-user text-green-500 text-2xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-500">Peminjaman Saya</p>
                    <p class="text-xl font-bold text-gray-800">{{ $myPeminjamanForDate->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Real-time Info -->
    <div class="mb-6 bg-white rounded-xl shadow-sm p-4 border-l-4 border-primary-500">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <span class="text-sm text-gray-700">Akan Datang</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-purple-500 rounded-full animate-pulse"></div>
                    <span class="text-sm text-gray-700">Sedang Berlangsung</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-700">Selesai</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-sm text-gray-700">Tersedia</span>
                </div>
            </div>
            <div class="text-sm text-gray-600">
                <i class="fas fa-sync-alt text-primary-600 mr-1"></i>
                Update real-time setiap 30 detik
            </div>
        </div>
    </div>

    <!-- Kalender/Grid Jadwal (UNTUK SEMUA USER) -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="p-4 border-b">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Jadwal Semua Ruangan - {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM YYYY') }}</h2>
                <div class="flex space-x-2">
                    <button onclick="previousDate()" class="p-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button onclick="todayDate()" class="px-4 py-2 border rounded-lg bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-medium">
                        Hari Ini
                    </button>
                    <button onclick="nextDate()" class="p-2 border rounded-lg hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        @for($i = 7; $i <= 17; $i++)
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ sprintf('%02d:00', $i) }}
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="scheduleTable">
                    @foreach($ruangan as $room)
                    @php
                        $bookingsForRoom = $allPeminjamanForDate->where('ruangan_id', $room->id);
                        
                        // Cek apakah ruangan sedang digunakan saat ini
                        $isBeingUsedNow = false;
                        $currentBookingInfo = null;
                        $currentTime = now();
                        
                        foreach($bookingsForRoom as $booking) {
                            $startDateTime = \Carbon\Carbon::parse($selectedDate . ' ' . $booking->jam_mulai);
                            $endDateTime = \Carbon\Carbon::parse($selectedDate . ' ' . $booking->jam_selesai);
                            
                            if ($selectedDate == $currentTime->format('Y-m-d') && 
                                $currentTime->between($startDateTime, $endDateTime)) {
                                $isBeingUsedNow = true;
                                $currentBookingInfo = $booking;
                                break;
                            }
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $room->kode_ruangan }}</div>
                            <div class="text-sm text-gray-500">{{ $room->nama_ruangan }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $room->kapasitas }} orang
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($room->status == 'tersedia')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Tersedia
                                </span>
                            @elseif($room->status == 'maintenance')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-tools mr-1"></i>
                                    Maintenance
                                </span>
                            @endif
                        </td>
                        
                        @for($hour = 7; $hour <= 17; $hour++)
                            @php
                                $hourStr = sprintf('%02d:00', $hour);
                                $isBooked = false;
                                $bookingInfo = null;
                                $isCurrentHour = $hour == date('H') && $selectedDate == date('Y-m-d');
                                
                                // Cek apakah ada peminjaman di jam ini (dari SEMUA user)
                                if (isset($ruanganTerpakai[$room->id][$hour])) {
                                    $bookings = $ruanganTerpakai[$room->id][$hour];
                                    if (count($bookings) > 0) {
                                        $isBooked = true;
                                        $bookingInfo = $bookings[0]; // Ambil yang pertama jika ada multiple
                                    }
                                }
                            @endphp
                            
                            <td class="px-4 py-3 text-center border-l relative group">
                                @if($room->status == 'maintenance')
                                    <div class="w-full h-8 bg-red-50 border border-red-200 rounded flex items-center justify-center" 
                                         title="Maintenance - Ruangan tidak dapat digunakan">
                                        <i class="fas fa-tools text-red-400 text-xs"></i>
                                    </div>
                                @elseif($isBooked && $bookingInfo)
                                    @php
                                        $statusRealTime = $bookingInfo['status_real_time'];
                                        $cellColor = match($statusRealTime) {
                                            'akan_datang' => 'bg-yellow-50 border-yellow-200',
                                            'berlangsung' => 'bg-purple-50 border-purple-200 animate-pulse',
                                            'selesai' => 'bg-green-50 border-green-200',
                                            default => 'bg-yellow-50 border-yellow-200'
                                        };
                                        $icon = $statusIcons[$statusRealTime] ?? 'fa-calendar-alt';
                                    @endphp
                                    
                                    <div class="w-full h-8 {{ $cellColor }} rounded flex items-center justify-center" 
                                         title="{{ $bookingInfo['acara'] }} ({{ $statusTexts[$statusRealTime] ?? $statusRealTime }})">
                                        <i class="fas {{ $icon }} text-xs"></i>
                                        
                                        <!-- Tooltip -->
                                        <div class="absolute z-10 invisible group-hover:visible bg-gray-900 text-white text-xs rounded py-1 px-2 bottom-full mb-2 left-1/2 transform -translate-x-1/2 whitespace-nowrap min-w-[150px]">
                                            <div class="font-semibold text-sm mb-1">{{ $bookingInfo['acara'] }}</div>
                                            <div class="text-gray-300 mb-1">
                                                <i class="fas fa-clock mr-1"></i>{{ $bookingInfo['jam_mulai'] }} - {{ $bookingInfo['jam_selesai'] }}
                                            </div>
                                            <div class="capitalize {{ 
                                                $statusRealTime == 'berlangsung' ? 'text-purple-400' : 
                                                ($statusRealTime == 'akan_datang' ? 'text-yellow-400' : 'text-green-400') 
                                            }}">
                                                <i class="fas fa-circle text-xs mr-1"></i>{{ $statusTexts[$statusRealTime] ?? $statusRealTime }}
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $cellClass = $isCurrentHour ? 'bg-primary-50 border-2 border-primary-300' : 'bg-gray-50 border border-gray-200';
                                    @endphp
                                    <div class="w-full h-8 {{ $cellClass }} rounded flex items-center justify-center"
                                         title="{{ $room->kode_ruangan }} tersedia pada {{ $hourStr }}">
                                        @if($isCurrentHour)
                                            <div class="w-2 h-2 bg-primary-500 rounded-full animate-pulse"></div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($ruangan->isEmpty())
            <div class="text-center py-12">
                <i class="fas fa-door-closed text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Tidak ada data ruangan</p>
            </div>
        @endif
    </div>

    <!-- Daftar KEGIATAN SAYA (HANYA UNTUK USER YANG LOGIN) - TIDAK MENAMPILKAN DIBATALKAN/DITOLAK -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="p-4 border-b bg-gradient-to-r from-primary-50 to-blue-50">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-user-circle text-primary-600 mr-2"></i>
                    Kegiatan Saya - {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM YYYY') }}
                </h2>
                <span class="text-sm bg-primary-100 text-primary-800 px-3 py-1 rounded-full">
                    {{ $myPeminjamanForDate->count() }} kegiatan
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acara</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peserta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Peminjaman</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Real-time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($myPeminjamanForDate as $key => $booking)
                        @php
                            $statusRealTime = $helpers['getStatusRealTime']($booking);
                            $isActiveNow = $selectedDate == now()->format('Y-m-d') && 
                                           now()->between(
                                               \Carbon\Carbon::parse($selectedDate . ' ' . $booking->jam_mulai),
                                               \Carbon\Carbon::parse($selectedDate . ' ' . $booking->jam_selesai)
                                           );
                            
                            // Status peminjaman dari database
                            $peminjamanStatus = $booking->status;
                            $peminjamanStatusColor = $peminjamanStatusColors[$peminjamanStatus] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <tr class="hover:bg-gray-50 {{ $isActiveNow ? 'bg-purple-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $key + 1 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $booking->ruangan->kode_ruangan }}</div>
                                <div class="text-sm text-gray-500">{{ $booking->ruangan->nama_ruangan }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $booking->acara }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }}</div>
                                <!-- KETERANGAN "Sedang berlangsung" DIHAPUS -->
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $booking->jumlah_peserta }} orang
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $peminjamanStatusColor }}">
                                    @if($peminjamanStatus == 'dibatalkan')
                                        <i class="fas fa-ban mr-1"></i>
                                    @elseif($peminjamanStatus == 'ditolak')
                                        <i class="fas fa-times-circle mr-1"></i>
                                    @elseif($peminjamanStatus == 'disetujui')
                                        <i class="fas fa-check-circle mr-1"></i>
                                    @elseif($peminjamanStatus == 'menunggu')
                                        <i class="fas fa-clock mr-1"></i>
                                    @elseif($peminjamanStatus == 'selesai')
                                        <i class="fas fa-flag-checkered mr-1"></i>
                                    @endif
                                    {{ ucfirst($peminjamanStatus) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($statusRealTime !== null && !in_array($peminjamanStatus, ['dibatalkan', 'ditolak']))
                                    @php
                                        $statusClass = $statusColors[$statusRealTime] ?? 'bg-gray-100 text-gray-800';
                                        $statusIcon = $statusIcons[$statusRealTime] ?? 'fa-info-circle';
                                        $statusText = $statusTexts[$statusRealTime] ?? ucfirst($statusRealTime);
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                        <i class="fas {{ $statusIcon }} mr-1"></i>
                                        {{ $statusText }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                        <i class="fas fa-ban mr-1"></i>
                                        Tidak Berlaku
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-calendar-times text-gray-400 text-2xl"></i>
                                    </div>
                                    <p class="text-gray-600 font-medium mb-2">Tidak ada kegiatan Anda pada tanggal ini</p>
                                    <p class="text-sm text-gray-500 mb-4">Anda belum memiliki peminjaman ruangan yang disetujui untuk tanggal {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('D MMMM YYYY') }}</p>
                                    <a href="{{ route('user.peminjaman-ruangan.create') }}" 
                                       class="inline-flex items-center bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg transition-colors text-sm font-medium">
                                        <i class="fas fa-plus-circle mr-2"></i> Ajukan Peminjaman
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Filter functions
    function applyFilters() {
        const date = document.getElementById('filter-date').value;
        const ruanganId = document.getElementById('filter-ruangan').value;
        
        let url = '{{ route("user.lihat-jadwal") }}?date=' + date;
        if (ruanganId) {
            url += '&ruangan=' + ruanganId;
        }
        
        window.location.href = url;
    }
    
    function previousDate() {
        const currentDate = new Date('{{ $selectedDate }}');
        currentDate.setDate(currentDate.getDate() - 1);
        const newDate = currentDate.toISOString().split('T')[0];
        
        let url = '{{ route("user.lihat-jadwal") }}?date=' + newDate;
        const ruanganId = document.getElementById('filter-ruangan').value;
        if (ruanganId) {
            url += '&ruangan=' + ruanganId;
        }
        
        window.location.href = url;
    }
    
    function todayDate() {
        const today = new Date().toISOString().split('T')[0];
        
        let url = '{{ route("user.lihat-jadwal") }}?date=' + today;
        const ruanganId = document.getElementById('filter-ruangan').value;
        if (ruanganId) {
            url += '&ruangan=' + ruanganId;
        }
        
        window.location.href = url;
    }
    
    function nextDate() {
        const currentDate = new Date('{{ $selectedDate }}');
        currentDate.setDate(currentDate.getDate() + 1);
        const newDate = currentDate.toISOString().split('T')[0];
        
        let url = '{{ route("user.lihat-jadwal") }}?date=' + newDate;
        const ruanganId = document.getElementById('filter-ruangan').value;
        if (ruanganId) {
            url += '&ruangan=' + ruanganId;
        }
        
        window.location.href = url;
    }
    
    // Auto-refresh untuk update real-time (setiap 30 detik)
    setInterval(function() {
        // Reload data tanpa reload halaman (AJAX)
        const currentUrl = window.location.href;
        
        fetch(currentUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse HTML dan update hanya bagian tabel
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update tabel jadwal utama
            const newScheduleTable = doc.getElementById('scheduleTable');
            const currentScheduleTable = document.getElementById('scheduleTable');
            if (newScheduleTable && currentScheduleTable) {
                currentScheduleTable.innerHTML = newScheduleTable.innerHTML;
            }
            
            // Update tabel kegiatan saya
            const newMyActivitiesTable = doc.querySelector('.bg-white.rounded-lg.shadow-lg.mb-6:nth-child(5) tbody');
            const currentMyActivitiesTable = document.querySelector('.bg-white.rounded-lg.shadow-lg.mb-6:nth-child(5) tbody');
            if (newMyActivitiesTable && currentMyActivitiesTable) {
                currentMyActivitiesTable.innerHTML = newMyActivitiesTable.innerHTML;
            }
            
            // Tampilkan notifikasi kecil
            showToast('Jadwal diperbarui');
        })
        .catch(error => console.error('Error refreshing data:', error));
    }, 30000); // Refresh setiap 30 detik
    
    // Fungsi untuk menampilkan toast notifikasi
    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg text-sm animate-fade-in-up z-50';
        toast.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> ' + message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    // Highlight current hour in grid
    document.addEventListener('DOMContentLoaded', function() {
        const currentHour = new Date().getHours();
        const today = new Date().toISOString().split('T')[0];
        const selectedDate = '{{ $selectedDate }}';
        
        if (selectedDate === today && currentHour >= 7 && currentHour <= 17) {
            // Hitung index kolom (kolom ke-4 adalah jam 07:00)
            const columnIndex = currentHour - 7 + 4;
            const cells = document.querySelectorAll(`td:nth-child(${columnIndex})`);
            cells.forEach(cell => {
                cell.classList.add('bg-blue-50');
            });
        }
        
        // Add click animation to buttons
        document.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });
    });
</script>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in-up {
        animation: fadeInUp 0.3s ease-out;
    }
    
    /* Tooltip styling */
    .group:hover .group-hover\:visible {
        visibility: visible;
    }
    
    /* Pulse animation */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    /* Hover effect untuk baris tabel */
    tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.05);
    }
    
    /* Styling untuk sel jam */
    td .bg-primary-50 {
        transition: all 0.2s ease;
    }
    
    td .bg-primary-50:hover {
        background-color: #dbeafe;
    }
</style>
@endsection