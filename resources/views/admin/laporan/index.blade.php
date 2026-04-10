@extends('layouts.admin')

@section('title', 'Laporan Peminjaman Ruangan')
@section('page-title', 'Laporan & Analisis Data')

@section('content')
<style>
    .report-card {
        transition: all 0.3s ease;
        border-left: 4px solid;
    }
    
    .report-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stat-chart {
        height: 300px;
    }
    
    .filter-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
        animation: fadeIn 0.5s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .filter-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        background-color: #f3f4f6;
        border-radius: 20px;
        font-size: 12px;
        color: #374151;
        margin-right: 8px;
        margin-bottom: 8px;
    }
    
    .filter-badge i {
        margin-right: 5px;
    }
    
    .filter-badge button {
        margin-left: 8px;
        color: #6b7280;
    }
    
    .filter-badge button:hover {
        color: #ef4444;
    }
    
    .month-picker {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 15px;
        border-radius: 10px;
        color: white;
    }
</style>

<!-- Filter Section -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Filter Laporan</h2>
            <p class="text-sm text-gray-600 mt-1">Filter data berdasarkan periode dan kriteria tertentu</p>
        </div>
        <div class="flex items-center space-x-2">
            <i class="fas fa-filter text-purple-500"></i>
            <span class="text-sm text-gray-600">Custom Filter</span>
        </div>
    </div>
    
    <form method="GET" action="{{ route('admin.laporan.index') }}" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Tanggal Mulai -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-primary-500 mr-1"></i> Tanggal Mulai
                </label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Tanggal Selesai -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-primary-500 mr-1"></i> Tanggal Selesai
                </label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" 
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            
            <!-- Jenis Pengaju -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-users text-primary-500 mr-1"></i> Jenis Pengaju
                </label>
                <select name="jenis_pengaju" id="jenis_pengaju" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Semua Jenis</option>
                    <option value="mahasiswa" {{ $jenisPengaju == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                    <option value="dosen" {{ $jenisPengaju == 'dosen' ? 'selected' : '' }}>Dosen</option>
                    <option value="staff" {{ $jenisPengaju == 'staff' ? 'selected' : '' }}>Staff</option>
                    <option value="umum" {{ $jenisPengaju == 'umum' ? 'selected' : '' }}>Umum</option>
                </select>
            </div>
            
            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-check-circle text-primary-500 mr-1"></i> Status
                </label>
                <select name="status" id="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Semua Status</option>
                    <option value="menunggu" {{ $status == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ $status == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ $status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="dibatalkan" {{ $status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    <option value="selesai" {{ $status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <!-- Ruangan -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-door-open text-primary-500 mr-1"></i> Ruangan
                </label>
                <select name="ruangan_id" id="ruangan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Semua Ruangan</option>
                    @foreach($ruangan as $room)
                        <option value="{{ $room->id }}" {{ $ruanganId == $room->id ? 'selected' : '' }}>
                            {{ $room->kode_ruangan }} - {{ $room->nama_ruangan }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Periode Analisis -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-chart-line text-primary-500 mr-1"></i> Periode Analisis
                </label>
                <select name="periode_analisis" id="periodeAnalisis" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="harian" {{ $periodeAnalisis == 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="mingguan" {{ $periodeAnalisis == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ $periodeAnalisis == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="tahunan" {{ $periodeAnalisis == 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>
            
            <!-- Quick Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-bolt text-primary-500 mr-1"></i> Quick Filter
                </label>
                <select id="quickFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" onchange="applyQuickFilter(this.value)">
                    <option value="">Pilih Quick Filter</option>
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="last_week">Minggu Lalu</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                    <option value="this_year">Tahun Ini</option>
                    <option value="last_year">Tahun Lalu</option>
                </select>
            </div>
        </div>
        
        <!-- Active Filters -->
        <div class="flex flex-wrap items-center mb-4">
            <span class="text-sm font-medium text-gray-700 mr-3">Filter Aktif:</span>
            @if(request()->has('start_date') && request()->has('end_date'))
                <div class="filter-badge">
                    <i class="fas fa-calendar"></i>
                    {{ Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                    <button type="button" onclick="clearDateFilter()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            @if(!empty($jenisPengaju))
                <div class="filter-badge">
                    <i class="fas fa-user"></i>
                    {{ ucfirst($jenisPengaju) }}
                    <button type="button" onclick="clearFilter('jenis_pengaju')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            @if(!empty($status))
                <div class="filter-badge">
                    <i class="fas fa-check-circle"></i>
                    {{ ucfirst($status) }}
                    <button type="button" onclick="clearFilter('status')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            
            @if(!empty($ruanganId))
                <div class="filter-badge">
                    <i class="fas fa-door-open"></i>
                    @php
                        $room = $ruangan->firstWhere('id', $ruanganId);
                        echo $room ? $room->kode_ruangan : 'Ruangan';
                    @endphp
                    <button type="button" onclick="clearFilter('ruangan_id')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
        </div>
        
        <div class="flex justify-between items-center pt-4 border-t border-gray-200">
            <div>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 flex items-center">
                    <i class="fas fa-filter mr-2"></i> Terapkan Filter
                </button>
            </div>
            
            <div class="flex space-x-3">
                <a href="{{ route('admin.laporan.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center">
                    <i class="fas fa-redo mr-2"></i> Reset Semua
                </a>
                
                <button type="button" onclick="cetakPDF()" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Cetak PDF
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Statistik Ringkas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6 mb-8">
    <!-- Total Peminjaman -->
    <div class="report-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Peminjaman</p>
                <h3 class="text-3xl font-bold text-blue-700 mt-2">{{ $totalPeminjaman }}</h3>
                <p class="text-xs text-gray-400 mt-1">Periode terpilih</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clipboard-list text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Disetujui -->
    <div class="report-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Disetujui</p>
                <h3 class="text-3xl font-bold text-green-700 mt-2">{{ $totalDisetujui }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ $totalPeminjaman > 0 ? round(($totalDisetujui/$totalPeminjaman)*100) : 0 }}%</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Ditolak -->
    <div class="report-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-red-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Ditolak</p>
                <h3 class="text-3xl font-bold text-red-700 mt-2">{{ $totalDitolak }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ $totalPeminjaman > 0 ? round(($totalDitolak/$totalPeminjaman)*100) : 0 }}%</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-times-circle text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Menunggu -->
    <div class="report-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Menunggu</p>
                <h3 class="text-3xl font-bold text-yellow-700 mt-2">{{ $totalMenunggu }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ $totalPeminjaman > 0 ? round(($totalMenunggu/$totalPeminjaman)*100) : 0 }}%</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Dibatalkan -->
    <div class="report-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-gray-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Dibatalkan</p>
                <h3 class="text-3xl font-bold text-gray-700 mt-2">{{ $totalDibatalkan }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ $totalPeminjaman > 0 ? round(($totalDibatalkan/$totalPeminjaman)*100) : 0 }}%</p>
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-ban text-gray-600 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Selesai -->
    <div class="report-card bg-white p-6 rounded-xl shadow-sm border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Selesai</p>
                <h3 class="text-3xl font-bold text-purple-700 mt-2">{{ $totalSelesai ?? 0 }}</h3>
                <p class="text-xs text-gray-400 mt-1">{{ $totalPeminjaman > 0 ? round((($totalSelesai ?? 0)/$totalPeminjaman)*100) : 0 }}%</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-flag-checkered text-purple-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabs untuk Analisis -->
<div class="bg-white rounded-xl shadow-sm mb-8">
    <div class="border-b border-gray-200">
        <nav class="flex space-x-4 px-6 pt-4 overflow-x-auto" aria-label="Tabs">
            <button type="button" 
                    onclick="showTab('grafik-tab')" 
                    class="tab-button py-3 px-4 font-medium text-sm border-b-2 border-transparent hover:text-primary-600 hover:border-primary-300 transition-colors whitespace-nowrap {{ $activeTab == 'grafik' ? 'border-primary-500 text-primary-600' : 'text-gray-500' }}"
                    data-tab="grafik">
                <i class="fas fa-chart-line mr-2"></i> Grafik & Analisis
            </button>
            <button type="button" 
                    onclick="showTab('harian-tab')" 
                    class="tab-button py-3 px-4 font-medium text-sm border-b-2 border-transparent hover:text-primary-600 hover:border-primary-300 transition-colors whitespace-nowrap {{ $activeTab == 'harian' ? 'border-primary-500 text-primary-600' : 'text-gray-500' }}"
                    data-tab="harian">
                <i class="fas fa-calendar-day mr-2"></i> Harian
            </button>
            <button type="button" 
                    onclick="showTab('mingguan-tab')" 
                    class="tab-button py-3 px-4 font-medium text-sm border-b-2 border-transparent hover:text-primary-600 hover:border-primary-300 transition-colors whitespace-nowrap {{ $activeTab == 'mingguan' ? 'border-primary-500 text-primary-600' : 'text-gray-500' }}"
                    data-tab="mingguan">
                <i class="fas fa-calendar-week mr-2"></i> Mingguan
            </button>
            <button type="button" 
                    onclick="showTab('bulanan-tab')" 
                    class="tab-button py-3 px-4 font-medium text-sm border-b-2 border-transparent hover:text-primary-600 hover:border-primary-300 transition-colors whitespace-nowrap {{ $activeTab == 'bulanan' ? 'border-primary-500 text-primary-600' : 'text-gray-500' }}"
                    data-tab="bulanan">
                <i class="fas fa-calendar-alt mr-2"></i> Bulanan
            </button>
            <button type="button" 
                    onclick="showTab('tahunan-tab')" 
                    class="tab-button py-3 px-4 font-medium text-sm border-b-2 border-transparent hover:text-primary-600 hover:border-primary-300 transition-colors whitespace-nowrap {{ $activeTab == 'tahunan' ? 'border-primary-500 text-primary-600' : 'text-gray-500' }}"
                    data-tab="tahunan">
                <i class="fas fa-calendar mr-2"></i> Tahunan
            </button>
            <button type="button" 
                    onclick="showTab('statistik-tab')" 
                    class="tab-button py-3 px-4 font-medium text-sm border-b-2 border-transparent hover:text-primary-600 hover:border-primary-300 transition-colors whitespace-nowrap {{ $activeTab == 'statistik' ? 'border-primary-500 text-primary-600' : 'text-gray-500' }}"
                    data-tab="statistik">
                <i class="fas fa-chart-pie mr-2"></i> Statistik
            </button>
        </nav>
    </div>
    
    <!-- Tab Content: Grafik -->
    <div id="grafik-tab" class="tab-content p-6 {{ $activeTab == 'grafik' ? 'active' : '' }}">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Grafik Trend -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-primary-600 mr-2"></i>
                    Trend Peminjaman ({{ $periodeAnalisis == 'harian' ? 'Harian' : ($periodeAnalisis == 'mingguan' ? 'Mingguan' : ($periodeAnalisis == 'bulanan' ? 'Bulanan' : 'Tahunan')) }})
                </h3>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    <p><i class="fas fa-info-circle mr-2"></i> Grafik menunjukkan trend peminjaman ruangan berdasarkan periode yang dipilih</p>
                </div>
            </div>
            
            <!-- Grafik Status -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chart-pie text-primary-600 mr-2"></i>
                    Distribusi Status Peminjaman
                </h3>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-4">
                    @foreach($statusDistribution as $status => $data)
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $data['color'] }}"></div>
                        <span class="text-sm">{{ $data['label'] }}: {{ $data['count'] }} ({{ $data['percentage'] }}%)</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab Content: Harian -->
    <div id="harian-tab" class="tab-content p-6 {{ $activeTab == 'harian' ? 'active' : '' }}">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Analisis Harian</h3>
            <p class="text-gray-600">Detail peminjaman per hari dalam periode yang dipilih</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Statistik Harian -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="flex items-center mb-3">
                    <i class="fas fa-calendar-day text-blue-600 text-xl mr-3"></i>
                    <h4 class="font-bold text-blue-900">Hari Terpopuler</h4>
                </div>
                <p class="text-2xl font-bold text-blue-700 mb-2">{{ $hariTerpopuler['nama'] ?? 'Tidak ada data' }}</p>
                <p class="text-sm text-blue-600">{{ $hariTerpopuler['count'] ?? 0 }} peminjaman</p>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="flex items-center mb-3">
                    <i class="fas fa-clock text-green-600 text-xl mr-3"></i>
                    <h4 class="font-bold text-green-900">Jam Puncak</h4>
                </div>
                <p class="text-2xl font-bold text-green-700 mb-2">{{ $jamPuncak['jam'] ?? 'Tidak ada data' }}</p>
                <p class="text-sm text-green-600">{{ $jamPuncak['count'] ?? 0 }} peminjaman</p>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="flex items-center mb-3">
                    <i class="fas fa-chart-bar text-purple-600 text-xl mr-3"></i>
                    <h4 class="font-bold text-purple-900">Rata-rata Harian</h4>
                </div>
                <p class="text-2xl font-bold text-purple-700 mb-2">{{ $rataRataHarian }}</p>
                <p class="text-sm text-purple-600">peminjaman per hari</p>
            </div>
        </div>
        
        <!-- Tabel Harian -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hari</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Peminjaman</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disetujui</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ditolak</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menunggu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($analisisHarian as $harian)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($harian['tanggal'])->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $harian['hari'] }}
                        </td>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">
                            {{ $harian['total'] }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                {{ $harian['disetujui'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                {{ $harian['ditolak'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                {{ $harian['menunggu'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full bg-primary-500" 
                                     style="width: {{ $totalPeminjaman > 0 ? ($harian['total']/$totalPeminjaman)*100 : 0 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 text-right">
                                {{ $totalPeminjaman > 0 ? round(($harian['total']/$totalPeminjaman)*100, 1) : 0 }}%
                            </p>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                            <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar text-gray-400"></i>
                            </div>
                            <p class="text-gray-900 font-medium">Tidak ada data harian</p>
                            <p class="text-sm text-gray-600 mt-1">Coba ubah filter atau periode waktu</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Tab Content: Mingguan -->
    <div id="mingguan-tab" class="tab-content p-6 {{ $activeTab == 'mingguan' ? 'active' : '' }}">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Analisis Mingguan</h3>
            <p class="text-gray-600">Analisis peminjaman per minggu dalam periode yang dipilih</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Grafik Mingguan -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h4 class="font-bold text-gray-900 mb-4">Trend Mingguan</h4>
                <div class="chart-container">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
            
            <!-- Statistik Mingguan -->
            <div class="space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-600 font-medium">Minggu Terbaik</p>
                            <p class="text-2xl font-bold text-blue-900">{{ $mingguTerbaik['minggu'] ?? 'Tidak ada data' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-blue-900">{{ $mingguTerbaik['total'] ?? 0 }}</p>
                            <p class="text-sm text-blue-600">peminjaman</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-green-600 font-medium">Rata-rata per Minggu</p>
                            <p class="text-2xl font-bold text-green-900">{{ $rataRataMingguan }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-green-900">{{ $totalMinggu }}</p>
                            <p class="text-sm text-green-600">total minggu</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-purple-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-purple-600 font-medium">Pertumbuhan Mingguan</p>
                            <p class="text-2xl font-bold text-purple-900">{{ $pertumbuhanMingguan }}%</p>
                        </div>
                        <div class="text-right">
                            @if($pertumbuhanMingguan > 0)
                                <i class="fas fa-arrow-up text-green-500 text-xl"></i>
                            @elseif($pertumbuhanMingguan < 0)
                                <i class="fas fa-arrow-down text-red-500 text-xl"></i>
                            @else
                                <i class="fas fa-minus text-gray-500 text-xl"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabel Mingguan -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Minggu ke-</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Peminjaman</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disetujui</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tingkat Persetujuan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($analisisMingguan as $mingguan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-bold text-gray-900">
                            {{ $mingguan['minggu_ke'] }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $mingguan['periode'] }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 text-sm font-bold bg-primary-100 text-primary-800 rounded-full">
                                {{ $mingguan['total'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                {{ $mingguan['disetujui'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-32">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-green-500" 
                                         style="width: {{ $mingguan['total'] > 0 ? ($mingguan['disetujui']/$mingguan['total'])*100 : 0 }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $mingguan['total'] > 0 ? round(($mingguan['disetujui']/$mingguan['total'])*100, 1) : 0 }}%
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($mingguan['trend'] > 0)
                                <span class="text-green-600 font-medium flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> +{{ $mingguan['trend'] }}%
                                </span>
                            @elseif($mingguan['trend'] < 0)
                                <span class="text-red-600 font-medium flex items-center">
                                    <i class="fas fa-arrow-down mr-1"></i> {{ $mingguan['trend'] }}%
                                </span>
                            @else
                                <span class="text-gray-600 font-medium flex items-center">
                                    <i class="fas fa-minus mr-1"></i> Stabil
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-week text-gray-400"></i>
                            </div>
                            <p class="text-gray-900 font-medium">Tidak ada data mingguan</p>
                            <p class="text-sm text-gray-600 mt-1">Coba ubah filter atau periode waktu</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Tab Content: Bulanan -->
    <div id="bulanan-tab" class="tab-content p-6 {{ $activeTab == 'bulanan' ? 'active' : '' }}">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Analisis Bulanan</h3>
            <p class="text-gray-600">Analisis peminjaman per bulan dalam periode yang dipilih</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Statistik Bulanan -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg text-white">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                    <span class="text-3xl font-bold">{{ Carbon\Carbon::now()->format('M Y') }}</span>
                </div>
                <p class="text-sm opacity-90">Bulan Berjalan</p>
                <p class="text-4xl font-bold mt-2">{{ $peminjaman->filter(function($item) {
                    return Carbon\Carbon::parse($item->tanggal)->format('Y-m') == Carbon\Carbon::now()->format('Y-m');
                })->count() }}</p>
                <p class="text-sm mt-1">Total Peminjaman</p>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-lg text-white">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-trophy text-2xl"></i>
                    <span class="text-3xl font-bold">{{ $bulanTerbaik['bulan'] ?? 'N/A' }}</span>
                </div>
                <p class="text-sm opacity-90">Bulan Terbaik</p>
                <p class="text-4xl font-bold mt-2">{{ $bulanTerbaik['total'] ?? 0 }}</p>
                <p class="text-sm mt-1">Peminjaman</p>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-chart-line text-2xl"></i>
                    <span class="text-3xl font-bold">{{ $pertumbuhanBulanan }}%</span>
                </div>
                <p class="text-sm opacity-90">Pertumbuhan Bulanan</p>
                <p class="text-4xl font-bold mt-2">{{ $rataRataBulanan }}</p>
                <p class="text-sm mt-1">Rata-rata per Bulan</p>
            </div>
        </div>
        
        <!-- Grafik Bulanan -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h4 class="font-bold text-gray-900 mb-4">Trend Bulanan</h4>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        
        <!-- Tabel Bulanan -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bulan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Peminjaman</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disetujui</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ditolak</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Persentase Disetujui</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($analisisBulanan as $bulan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 text-sm font-bold bg-primary-100 text-primary-800 rounded-full">
                                {{ $bulan['bulan'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xl font-bold text-gray-900">
                            {{ $bulan['total'] }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                                {{ $bulan['disetujui'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                {{ $bulan['ditolak'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-32">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-green-500" 
                                         style="width: {{ $bulan['total'] > 0 ? ($bulan['disetujui']/$bulan['total'])*100 : 0 }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $bulan['total'] > 0 ? round(($bulan['disetujui']/$bulan['total'])*100, 1) : 0 }}%
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($bulan['trend'] > 0)
                                <span class="text-green-600 font-medium flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> +{{ $bulan['trend'] }}%
                                </span>
                            @elseif($bulan['trend'] < 0)
                                <span class="text-red-600 font-medium flex items-center">
                                    <i class="fas fa-arrow-down mr-1"></i> {{ $bulan['trend'] }}%
                                </span>
                            @else
                                <span class="text-gray-600 font-medium">
                                    -
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-gray-400"></i>
                            </div>
                            <p class="text-gray-900 font-medium">Tidak ada data bulanan</p>
                            <p class="text-sm text-gray-600 mt-1">Coba ubah filter atau periode waktu</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Tab Content: Tahunan -->
    <div id="tahunan-tab" class="tab-content p-6 {{ $activeTab == 'tahunan' ? 'active' : '' }}">
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Analisis Tahunan</h3>
            <p class="text-gray-600">Analisis peminjaman per tahun dan perbandingan antar tahun</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Statistik Tahunan -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-lg text-white">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                    <span class="text-3xl font-bold">{{ $tahunSaatIni }}</span>
                </div>
                <p class="text-sm opacity-90">Tahun Berjalan</p>
                <p class="text-4xl font-bold mt-2">{{ $totalTahunIni }}</p>
                <p class="text-sm mt-1">Total Peminjaman</p>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-lg text-white">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-trophy text-2xl"></i>
                    <span class="text-3xl font-bold">{{ $tahunTerbaik['tahun'] ?? 'N/A' }}</span>
                </div>
                <p class="text-sm opacity-90">Tahun Terbaik</p>
                <p class="text-4xl font-bold mt-2">{{ $tahunTerbaik['total'] ?? 0 }}</p>
                <p class="text-sm mt-1">Peminjaman</p>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-lg text-white">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-chart-line text-2xl"></i>
                    <span class="text-3xl font-bold">{{ $pertumbuhanTahunan }}%</span>
                </div>
                <p class="text-sm opacity-90">Pertumbuhan Tahunan</p>
                <p class="text-4xl font-bold mt-2">{{ $rataRataTahunan }}</p>
                <p class="text-sm mt-1">Rata-rata per Tahun</p>
            </div>
        </div>
        
        <!-- Grafik Tahunan -->
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h4 class="font-bold text-gray-900 mb-4">Perbandingan Tahunan</h4>
            <div class="chart-container">
                <canvas id="yearlyChart"></canvas>
            </div>
        </div>
        
        <!-- Tabel Tahunan -->
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tahun</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Peminjaman</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disetujui</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ditolak</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Persentase Disetujui</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pertumbuhan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($analisisTahunan as $tahun)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 text-sm font-bold bg-primary-100 text-primary-800 rounded-full">
                                {{ $tahun['tahun'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xl font-bold text-gray-900">
                            {{ $tahun['total'] }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                                {{ $tahun['disetujui'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                {{ $tahun['ditolak'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="w-32">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-green-500" 
                                         style="width: {{ $tahun['total'] > 0 ? ($tahun['disetujui']/$tahun['total'])*100 : 0 }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $tahun['total'] > 0 ? round(($tahun['disetujui']/$tahun['total'])*100, 1) : 0 }}%
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($tahun['pertumbuhan'] > 0)
                                <span class="text-green-600 font-medium flex items-center">
                                    <i class="fas fa-arrow-up mr-1"></i> +{{ $tahun['pertumbuhan'] }}%
                                </span>
                            @elseif($tahun['pertumbuhan'] < 0)
                                <span class="text-red-600 font-medium flex items-center">
                                    <i class="fas fa-arrow-down mr-1"></i> {{ $tahun['pertumbuhan'] }}%
                                </span>
                            @else
                                <span class="text-gray-600 font-medium">
                                    -
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar text-gray-400"></i>
                            </div>
                            <p class="text-gray-900 font-medium">Tidak ada data tahunan</p>
                            <p class="text-sm text-gray-600 mt-1">Coba ubah filter atau periode waktu</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Tab Content: Statistik -->
    <div id="statistik-tab" class="tab-content p-6 {{ $activeTab == 'statistik' ? 'active' : '' }}">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Statistik per Jenis Pengaju -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Statistik per Jenis Pengaju</h3>
                <div class="space-y-4">
                    @php
                        $filteredStatistik = array_filter($statistikJenis, function($count) {
                            return $count > 0;
                        });
                    @endphp
                    
                    @forelse($filteredStatistik as $jenis => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center 
                                {{ $jenis == 'mahasiswa' ? 'bg-blue-100 text-blue-600' : 
                                   ($jenis == 'dosen' ? 'bg-green-100 text-green-600' : 
                                   ($jenis == 'staff' ? 'bg-purple-100 text-purple-600' : 'bg-yellow-100 text-yellow-600')) }}">
                                @php
                                    $icon = match($jenis) {
                                        'mahasiswa' => 'fa-graduation-cap',
                                        'dosen' => 'fa-chalkboard-teacher',
                                        'staff' => 'fa-user-tie',
                                        default => 'fa-user'
                                    };
                                @endphp
                                <i class="fas {{ $icon }}"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ ucfirst($jenis) }}</p>
                                <p class="text-sm text-gray-500">{{ $count }} peminjaman</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold 
                                {{ $jenis == 'mahasiswa' ? 'text-blue-600' : 
                                   ($jenis == 'dosen' ? 'text-green-600' : 
                                   ($jenis == 'staff' ? 'text-purple-600' : 'text-yellow-600')) }}">
                                {{ $totalPeminjaman > 0 ? round(($count/$totalPeminjaman)*100) : 0 }}%
                            </p>
                        </div>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full 
                            {{ $jenis == 'mahasiswa' ? 'bg-blue-500' : 
                               ($jenis == 'dosen' ? 'bg-green-500' : 
                               ($jenis == 'staff' ? 'bg-purple-500' : 'bg-yellow-500')) }}" 
                             style="width: {{ $totalPeminjaman > 0 ? ($count/$totalPeminjaman)*100 : 0 }}%">
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-users text-4xl mb-3 text-gray-400"></i>
                        <p>Tidak ada data jenis pengaju</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Ruangan Terpopuler -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Ruangan Terpopuler</h3>
                <div class="space-y-4">
                    @forelse($statistikRuangan->take(5) as $index => $ruangan)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <!-- Ranking -->
                            <div class="w-8 h-8 flex items-center justify-center rounded-full
                                @switch($index + 1)
                                    @case(1) bg-yellow-500 @break
                                    @case(2) bg-gray-400 @break
                                    @case(3) bg-orange-500 @break
                                    @default bg-blue-500
                                @endswitch">
                                <span class="text-white font-bold text-sm">{{ $index + 1 }}</span>
                            </div>
                            
                            <!-- Room Info -->
                            <div>
                                <p class="font-medium text-gray-900">{{ $ruangan->kode_ruangan }} - {{ $ruangan->nama_ruangan }}</p>
                                <p class="text-sm text-gray-500">{{ $ruangan->lokasi ?? 'Tidak ada lokasi' }}</p>
                            </div>
                        </div>
                        
                        <!-- Count -->
                        <div class="text-right">
                            <p class="text-lg font-bold text-primary-700">{{ $ruangan->peminjaman_count }}</p>
                            <p class="text-xs text-gray-500">peminjaman</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-door-open text-4xl mb-3 text-gray-400"></i>
                        <p>Tidak ada data ruangan</p>
                    </div>
                    @endforelse
                </div>
                
                <!-- Grafik Ruangan -->
                <div class="mt-6">
                    <h4 class="font-bold text-gray-900 mb-4">Distribusi Penggunaan Ruangan</h4>
                    <div class="chart-container">
                        <canvas id="roomDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Baris kedua statistik -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            <!-- Distribusi Jam -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Distribusi Jam Operasional</h3>
                <div class="chart-container">
                    <canvas id="hourDistributionChart"></canvas>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    <p><i class="fas fa-info-circle mr-2"></i> Analisis jam paling populer untuk peminjaman ruangan</p>
                </div>
            </div>
            
            <!-- Perbandingan Fakultas -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Perbandingan Fakultas</h3>
                <div class="chart-container">
                    <canvas id="facultyChart"></canvas>
                </div>
                <div class="mt-4 text-sm text-gray-600">
                    <p><i class="fas fa-info-circle mr-2"></i> Distribusi peminjaman berdasarkan fakultas pengaju</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Data Peminjaman -->
<div class="bg-white rounded-xl shadow-sm p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Data Peminjaman</h2>
            <p class="text-sm text-gray-600 mt-1">Detail peminjaman berdasarkan filter yang dipilih</p>
        </div>
        <div class="text-sm text-gray-500">
            Menampilkan {{ $peminjaman->count() }} data
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pengaju</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruangan</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acara</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peserta</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($peminjaman as $index => $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                    <td class="px-4 py-3">
                        <span class="text-sm font-mono font-semibold text-primary-700 bg-primary-50 px-2 py-1 rounded">
                            {{ $item->user->username ?? $item->username ?? '-' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->user->name ?? $item->nama_pengaju ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $item->user->email ?? '-' }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ ($item->user->jenis_pengaju ?? $item->jenis_pengaju) == 'mahasiswa' ? 'bg-blue-100 text-blue-800' : 
                               (($item->user->jenis_pengaju ?? $item->jenis_pengaju) == 'dosen' ? 'bg-green-100 text-green-800' : 
                               (($item->user->jenis_pengaju ?? $item->jenis_pengaju) == 'staff' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($item->user->jenis_pengaju ?? $item->jenis_pengaju ?? '-') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->ruangan->kode_ruangan ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $item->ruangan->nama_ruangan ?? '-' }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->nama_kegiatan ?? $item->acara ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        <div>
                            <p>{{ \Carbon\Carbon::parse($item->tanggal ?? $item->tanggal_peminjaman)->translatedFormat('d F Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $item->waktu_mulai ?? $item->jam_mulai ?? '-' }} - {{ $item->waktu_selesai ?? $item->jam_selesai ?? '-' }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $statusColor = match($item->status) {
                                'disetujui' => 'bg-green-100 text-green-800',
                                'ditolak' => 'bg-red-100 text-red-800',
                                'menunggu' => 'bg-yellow-100 text-yellow-800',
                                'dibatalkan' => 'bg-gray-100 text-gray-800',
                                'selesai' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->jumlah_peserta ?? '-' }} orang</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                        <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-search text-gray-400 text-2xl"></i>
                        </div>
                        <p class="text-gray-900 font-medium text-lg">Tidak ada data ditemukan</p>
                        <p class="text-sm text-gray-600 mt-1">Coba ubah filter atau periode waktu untuk melihat data</p>
                        <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
                            <i class="fas fa-redo mr-2"></i> Reset Filter
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($peminjaman->count() > 0)
    <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            <i class="fas fa-database mr-2"></i> Total {{ $peminjaman->count() }} data peminjaman
        </div>
    </div>
    @endif
</div>

<!-- Summary -->
<div class="mb-8 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
    <div class="flex items-center mb-4">
        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
            <i class="fas fa-info-circle text-white text-xl"></i>
        </div>
        <h3 class="text-lg font-bold text-blue-900">Ringkasan Laporan</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white bg-opacity-70 p-4 rounded-lg">
            <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                Periode Laporan
            </h4>
            <p class="text-blue-900 font-medium">{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
            <p class="text-xs text-blue-600 mt-1">{{ \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1 }} hari</p>
        </div>
        
        <div class="bg-white bg-opacity-70 p-4 rounded-lg">
            <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                Rata-rata per Hari
            </h4>
            @php
                $start = \Carbon\Carbon::parse($startDate);
                $end = \Carbon\Carbon::parse($endDate);
                $days = $start->diffInDays($end) + 1;
                $avgPerDay = $totalPeminjaman > 0 ? round($totalPeminjaman / $days, 1) : 0;
            @endphp
            <p class="text-blue-900 text-2xl font-bold">{{ $avgPerDay }}</p>
            <p class="text-xs text-blue-600 mt-1">peminjaman/hari</p>
        </div>
        
        <div class="bg-white bg-opacity-70 p-4 rounded-lg">
            <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                <i class="fas fa-check-circle mr-2 text-blue-600"></i>
                Persentase Disetujui
            </h4>
            <p class="text-blue-900 text-2xl font-bold">{{ $totalPeminjaman > 0 ? round(($totalDisetujui/$totalPeminjaman)*100, 1) : 0 }}%</p>
            <p class="text-xs text-blue-600 mt-1">{{ $totalDisetujui }} dari {{ $totalPeminjaman }} peminjaman</p>
        </div>
        
        <div class="bg-white bg-opacity-70 p-4 rounded-lg">
            <h4 class="font-semibold text-blue-800 mb-2 flex items-center">
                <i class="fas fa-star mr-2 text-blue-600"></i>
                Tingkat Kepuasan
            </h4>
            <div class="flex items-center">
                <div class="flex mr-2">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= floor($ratingRataRata))
                            <i class="fas fa-star text-yellow-400"></i>
                        @elseif($i - 0.5 <= $ratingRataRata)
                            <i class="fas fa-star-half-alt text-yellow-400"></i>
                        @else
                            <i class="far fa-star text-gray-300"></i>
                        @endif
                    @endfor
                </div>
                <span class="text-blue-900 font-bold">{{ number_format($ratingRataRata, 1) }}</span>
                <span class="text-xs text-blue-600 ml-1">/5.0</span>
            </div>
            <p class="text-xs text-blue-600 mt-2">Berdasarkan {{ $peminjaman->where('status', 'selesai')->count() }} review</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ==================== FUNGSI CETAK ====================
// Fungsi untuk cetak PDF dengan filter
function cetakPDF() {
    // Ambil semua nilai filter dari form
    const form = document.getElementById('filterForm');
    
    // Buat URL dengan parameter filter lengkap
    let url = '{{ route("admin.laporan.cetak-pdf") }}?';
    url += 'start_date=' + encodeURIComponent(form.start_date.value);
    url += '&end_date=' + encodeURIComponent(form.end_date.value);
    url += '&jenis_pengaju=' + encodeURIComponent(form.jenis_pengaju.value);
    url += '&status=' + encodeURIComponent(form.status.value);
    url += '&ruangan_id=' + encodeURIComponent(form.ruangan_id.value);
    
    window.open(url, '_blank');
}

// Fungsi untuk cetak Excel dengan filter
function cetakExcel() {
    // Ambil semua nilai filter dari form
    const form = document.getElementById('filterForm');
    
    // Buat URL dengan parameter filter lengkap
    let url = '{{ route("admin.laporan.cetak-excel") }}?';
    url += 'start_date=' + encodeURIComponent(form.start_date.value);
    url += '&end_date=' + encodeURIComponent(form.end_date.value);
    url += '&jenis_pengaju=' + encodeURIComponent(form.jenis_pengaju.value);
    url += '&status=' + encodeURIComponent(form.status.value);
    url += '&ruangan_id=' + encodeURIComponent(form.ruangan_id.value);
    
    window.open(url, '_blank');
}

// ==================== FUNGSI FILTER ====================
// Fungsi untuk quick filter
function applyQuickFilter(value) {
    const form = document.getElementById('filterForm');
    const today = new Date();
    let startDate, endDate;
    
    switch(value) {
        case 'today':
            startDate = formatDate(today);
            endDate = formatDate(today);
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            startDate = formatDate(yesterday);
            endDate = formatDate(yesterday);
            break;
        case 'this_week':
            startDate = formatDate(getMonday(today));
            endDate = formatDate(today);
            break;
        case 'last_week':
            const lastWeek = new Date(today);
            lastWeek.setDate(lastWeek.getDate() - 7);
            startDate = formatDate(getMonday(lastWeek));
            endDate = formatDate(getSunday(lastWeek));
            break;
        case 'this_month':
            startDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
            endDate = formatDate(today);
            break;
        case 'last_month':
            startDate = formatDate(new Date(today.getFullYear(), today.getMonth() - 1, 1));
            endDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 0));
            break;
        case 'this_year':
            startDate = formatDate(new Date(today.getFullYear(), 0, 1));
            endDate = formatDate(today);
            break;
        case 'last_year':
            startDate = formatDate(new Date(today.getFullYear() - 1, 0, 1));
            endDate = formatDate(new Date(today.getFullYear() - 1, 11, 31));
            break;
        default:
            return;
    }
    
    form.start_date.value = startDate;
    form.end_date.value = endDate;
    
    // Submit form
    form.submit();
}

// Helper function format date
function formatDate(date) {
    const d = new Date(date);
    let month = '' + (d.getMonth() + 1);
    let day = '' + d.getDate();
    const year = d.getFullYear();
    
    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;
    
    return [year, month, day].join('-');
}

// Get Monday of week
function getMonday(date) {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(d.setDate(diff));
}

// Get Sunday of week
function getSunday(date) {
    const monday = getMonday(date);
    const sunday = new Date(monday);
    sunday.setDate(sunday.getDate() + 6);
    return sunday;
}

// Fungsi untuk clear filter
function clearFilter(filterName) {
    const element = document.querySelector(`[name="${filterName}"]`);
    if (element) {
        element.value = '';
        document.getElementById('filterForm').submit();
    }
}

function clearDateFilter() {
    document.querySelector('[name="start_date"]').value = '';
    document.querySelector('[name="end_date"]').value = '';
    document.getElementById('filterForm').submit();
}

// ==================== FUNGSI TAB ====================
// Fungsi untuk tab switching
function showTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Update active tab button
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-primary-500', 'text-primary-600');
        button.classList.add('text-gray-500');
    });
    
    // Update clicked button
    const clickedButton = document.querySelector(`[onclick="showTab('${tabId}')"]`);
    if (clickedButton) {
        clickedButton.classList.add('border-primary-500', 'text-primary-600');
        clickedButton.classList.remove('text-gray-500');
    }
    
    // Update URL parameter
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId.replace('-tab', ''));
    window.history.pushState({}, '', url);
    
    // Update charts based on active tab
    setTimeout(() => {
        if (window.updateCharts) {
            window.updateCharts();
        }
    }, 100);
}

// ==================== FUNGSI CHART ====================
// Chart variables
let trendChart, statusChart, weeklyChart, monthlyChart, yearlyChart, 
    roomDistributionChart, hourDistributionChart, facultyChart;

// Destroy all charts
function destroyCharts() {
    if (trendChart) trendChart.destroy();
    if (statusChart) statusChart.destroy();
    if (weeklyChart) weeklyChart.destroy();
    if (monthlyChart) monthlyChart.destroy();
    if (yearlyChart) yearlyChart.destroy();
    if (roomDistributionChart) roomDistributionChart.destroy();
    if (hourDistributionChart) hourDistributionChart.destroy();
    if (facultyChart) facultyChart.destroy();
}

// Initialize all charts
function initializeCharts() {
    // Trend Chart
    const trendCtx = document.getElementById('trendChart')?.getContext('2d');
    if (trendCtx) {
        @if(isset($trendData) && count($trendData['data'] ?? []) > 0)
            trendChart = new Chart(trendCtx, {
                type: '{{ $tipeGrafik ?? "line" }}',
                data: {
                    labels: {!! json_encode($trendData['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Jumlah Peminjaman',
                        data: {!! json_encode($trendData['data'] ?? []) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 2,
                        fill: {{ ($tipeGrafik ?? 'line') == 'line' ? 'true' : 'false' }},
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' peminjaman';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        @else
            // No data placeholder
            const ctx = trendCtx;
            ctx.font = '14px Arial';
            ctx.fillStyle = '#6b7280';
            ctx.textAlign = 'center';
            ctx.fillText('Tidak ada data untuk ditampilkan', ctx.canvas.width/2, ctx.canvas.height/2);
        @endif
    }

    // Status Chart
    const statusCtx = document.getElementById('statusChart')?.getContext('2d');
    if (statusCtx) {
        @if(isset($statusDistribution) && count($statusDistribution) > 0)
            const statusLabels = {!! json_encode(array_column($statusDistribution, 'label')) !!};
            const statusData = {!! json_encode(array_column($statusDistribution, 'count')) !!};
            const statusColors = {!! json_encode(array_column($statusDistribution, 'color')) !!};
            
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusData,
                        backgroundColor: statusColors,
                        borderWidth: 1,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });
        @endif
    }

    // Weekly Chart
    const weeklyCtx = document.getElementById('weeklyChart')?.getContext('2d');
    if (weeklyCtx) {
        @if(isset($weeklyData) && count($weeklyData['data'] ?? []) > 0)
            weeklyChart = new Chart(weeklyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($weeklyData['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Peminjaman per Minggu',
                        data: {!! json_encode($weeklyData['data'] ?? []) !!},
                        backgroundColor: 'rgba(139, 92, 246, 0.7)',
                        borderColor: 'rgb(139, 92, 246)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        @endif
    }

    // Monthly Chart
    const monthlyCtx = document.getElementById('monthlyChart')?.getContext('2d');
    if (monthlyCtx) {
        @if(isset($monthlyData) && count($monthlyData['data'] ?? []) > 0)
            monthlyChart = new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyData['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Peminjaman per Bulan',
                        data: {!! json_encode($monthlyData['data'] ?? []) !!},
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        @endif
    }

    // Yearly Chart
    const yearlyCtx = document.getElementById('yearlyChart')?.getContext('2d');
    if (yearlyCtx) {
        @if(isset($yearlyData) && count($yearlyData['data'] ?? []) > 0)
            yearlyChart = new Chart(yearlyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($yearlyData['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Peminjaman per Tahun',
                        data: {!! json_encode($yearlyData['data'] ?? []) !!},
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        @endif
    }

    // Room Distribution Chart
    const roomCtx = document.getElementById('roomDistributionChart')?.getContext('2d');
    if (roomCtx) {
        @if(isset($statistikRuangan) && $statistikRuangan->count() > 0)
            roomDistributionChart = new Chart(roomCtx, {
                type: 'pie',
                data: {
                    labels: {!! json_encode($statistikRuangan->take(5)->pluck('kode_ruangan')->toArray()) !!},
                    datasets: [{
                        data: {!! json_encode($statistikRuangan->take(5)->pluck('peminjaman_count')->toArray()) !!},
                        backgroundColor: [
                            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        @endif
    }

    // Hour Distribution Chart
    const hourCtx = document.getElementById('hourDistributionChart')?.getContext('2d');
    if (hourCtx) {
        @if(isset($hourlyData) && count($hourlyData) > 0)
            hourDistributionChart = new Chart(hourCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($hourlyData)) !!},
                    datasets: [{
                        label: 'Jumlah Peminjaman per Jam',
                        data: {!! json_encode(array_values($hourlyData)) !!},
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgb(255, 99, 132)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgb(255, 99, 132)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        @endif
    }

    // Faculty Chart
    const facultyCtx = document.getElementById('facultyChart')?.getContext('2d');
    if (facultyCtx) {
        @if(isset($facultyData) && count($facultyData) > 0)
            facultyChart = new Chart(facultyCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($facultyData)) !!},
                    datasets: [{
                        label: 'Jumlah Peminjaman per Fakultas',
                        data: {!! json_encode(array_values($facultyData)) !!},
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgb(75, 192, 192)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        @endif
    }
}

// Update charts function
function updateCharts() {
    destroyCharts();
    initializeCharts();
}

// ==================== INITIALIZATION ====================
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    
    // Set active tab from URL or default
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || '{{ $activeTab }}';
    showTab(activeTab + '-tab');
});

// ==================== UTILITY FUNCTIONS ====================
// Export chart as image
function saveChartAsImage(chartId, fileName) {
    const chartCanvas = document.getElementById(chartId);
    if (chartCanvas) {
        const link = document.createElement('a');
        link.download = fileName + '_' + new Date().getTime() + '.png';
        link.href = chartCanvas.toDataURL('image/png');
        link.click();
    }
}

// Print current page
function printPage() {
    window.print();
}

// Export to CSV
function exportToCSV() {
    const table = document.querySelector('table');
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/,/g, ';');
            row.push('"' + data + '"');
        }
        
        csv.push(row.join(','));
    }
    
    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = 'laporan_peminjaman_' + new Date().getTime() + '.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.click();
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Moment.js -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/locale/id.js"></script>
<script>
    // Set locale to Indonesian
    moment.locale('id');
</script>
@endpush