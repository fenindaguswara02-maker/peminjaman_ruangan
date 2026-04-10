@extends('layouts.pegawai')

@section('title', 'Laporan Peminjaman Ruangan')
@section('page-title', 'Laporan & Statistik Peminjaman Ruangan')

@section('content')
@php
    // Default values untuk menghindari error
    $startDate = $startDate ?? request('start_date', date('Y-m-01'));
    $endDate = $endDate ?? request('end_date', date('Y-m-t'));
    $ruanganId = $ruanganId ?? request('ruangan_id', 'all');
    $ruangan = $ruangan ?? [];
    $peminjaman = $peminjaman ?? collect();
    $totalPeminjaman = $totalPeminjaman ?? 0;
    $statusCounts = $statusCounts ?? collect();
    $ruanganTerbanyak = $ruanganTerbanyak ?? 0;
    
    // Format tanggal untuk display
    $formattedStartDate = date('d M Y', strtotime($startDate));
    $formattedEndDate = date('d M Y', strtotime($endDate));
@endphp

<style>
    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    
    /* Status Badge Styles */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.5;
        letter-spacing: 0.02em;
        text-transform: capitalize;
        border: 1px solid transparent;
    }
    
    .status-badge i {
        margin-right: 6px;
        font-size: 0.7rem;
    }
    
    .status-badge.menunggu,
    .status-badge.pending {
        background-color: #FEF3C7;
        color: #92400E;
        border-color: #FCD34D;
    }
    
    .status-badge.disetujui,
    .status-badge.approved {
        background-color: #D1FAE5;
        color: #065F46;
        border-color: #6EE7B7;
    }
    
    .status-badge.ditolak,
    .status-badge.rejected {
        background-color: #FEE2E2;
        color: #991B1B;
        border-color: #FCA5A5;
    }
    
    .status-badge.selesai,
    .status-badge.completed {
        background-color: #DBEAFE;
        color: #1E40AF;
        border-color: #93C5FD;
    }
    
    .status-badge.dibatalkan,
    .status-badge.cancelled {
        background-color: #F3F4F6;
        color: #4B5563;
        border-color: #D1D5DB;
    }
    
    /* Table Styles */
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .data-table th {
        background-color: #F9FAFB;
        padding: 12px 16px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #6B7280;
        border-bottom: 1px solid #E5E7EB;
        white-space: nowrap;
    }
    
    .data-table td {
        padding: 16px;
        font-size: 0.875rem;
        border-bottom: 1px solid #F3F4F6;
        vertical-align: middle;
    }
    
    .data-table tbody tr:hover {
        background-color: #F9FAFB;
    }
    
    /* Info Card Styles */
    .info-card {
        background: linear-gradient(135deg, #667EEA 0%, #764BA2 100%);
        color: white;
        border-radius: 10px;
        padding: 16px 20px;
    }
    
    /* Print Styles */
    @media print {
        .filter-card, .no-print, button, .action-buttons {
            display: none !important;
        }
        
        body {
            background: white;
            font-size: 11pt;
        }
        
        @page {
            size: landscape;
            margin: 1.5cm;
        }
        
        .bg-white {
            box-shadow: none !important;
            border: 1px solid #E5E7EB;
        }
        
        .status-badge {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .data-table th {
            background-color: #F3F4F6 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<!-- Header Section -->
<div class="mb-6 no-print">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Laporan & Statistik Peminjaman Ruangan</h1>
            <p class="text-gray-600 mt-1">Analisis data dan statistik peminjaman ruangan</p>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-500">
                <i class="far fa-calendar-alt mr-1"></i>
                {{ date('d M Y H:i') }}
            </span>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="filter-card no-print">
    <form method="GET" action="{{ route('pegawai.laporan.index') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt mr-1 text-primary-600"></i> Tanggal Mulai
                </label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt mr-1 text-primary-600"></i> Tanggal Selesai
                </label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>

            <div class="col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-door-open mr-1 text-primary-600"></i> Ruangan
                </label>
                <select name="ruangan_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="all" {{ $ruanganId == 'all' ? 'selected' : '' }}>Semua Ruangan</option>
                    @foreach($ruangan as $room)
                        <option value="{{ $room->id }}" {{ $ruanganId == $room->id ? 'selected' : '' }}>
                            {{ $room->nama_ruangan }} ({{ $room->kode_ruangan }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-2 flex items-end space-x-2">
                <button type="submit" 
                        class="flex-1 bg-primary-600 hover:bg-primary-700 text-white rounded-lg px-6 py-2.5 transition flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i> Terapkan Filter
                </button>
                <a href="{{ route('pegawai.laporan.index') }}" 
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg px-6 py-2.5 transition flex items-center justify-center">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Statistik Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5 stat-card border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-blue-50 text-blue-600">
                <i class="fas fa-calendar-alt text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Total Peminjaman</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPeminjaman) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $formattedStartDate }} - {{ $formattedEndDate }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 stat-card border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-green-50 text-green-600">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Disetujui</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($statusCounts['approved'] ?? $statusCounts['disetujui'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $totalPeminjaman > 0 ? round((($statusCounts['approved'] ?? $statusCounts['disetujui'] ?? 0) / $totalPeminjaman) * 100) : 0 }}% dari total
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 stat-card border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-yellow-50 text-yellow-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Menunggu</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($statusCounts['pending'] ?? $statusCounts['menunggu'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Perlu diproses</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-5 stat-card border border-gray-100">
        <div class="flex items-center">
            <div class="p-3 rounded-lg bg-red-50 text-red-600">
                <i class="fas fa-times-circle text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Ditolak</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($statusCounts['rejected'] ?? $statusCounts['ditolak'] ?? 0) }}</p>
                <p class="text-xs text-gray-400 mt-1">Tidak disetujui</p>
            </div>
        </div>
    </div>
</div>

<!-- Informasi Ringkasan -->
@if($peminjaman->count() > 0)
<div class="info-card mb-6 no-print">
    <div class="flex flex-wrap items-center justify-between">
        <div class="flex items-center space-x-6">
            <div class="flex items-center">
                <i class="fas fa-door-open mr-3 text-white opacity-90"></i>
                <div>
                    <span class="text-xs text-white opacity-75">Ruangan Terbanyak</span>
                    <p class="font-semibold">{{ $ruanganTerbanyak }}</p>
                </div>
            </div>
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-white opacity-90"></i>
                <div>
                    <span class="text-xs text-white opacity-75">Tingkat Persetujuan</span>
                    <p class="font-semibold">
                        @php
                            $approved = $statusCounts['approved'] ?? $statusCounts['disetujui'] ?? 0;
                            $approvalRate = $totalPeminjaman > 0 ? round(($approved / $totalPeminjaman) * 100) : 0;
                        @endphp
                        {{ $approvalRate }}%
                    </p>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-2 action-buttons">
            <button onclick="window.print()" 
                    class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg transition flex items-center">
                <i class="fas fa-print mr-2"></i> Cetak Laporan
            </button>
            <a href="{{ route('pegawai.laporan.export.pdf') }}?start_date={{ $startDate }}&end_date={{ $endDate }}&ruangan_id={{ $ruanganId }}" 
               class="px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg transition flex items-center">
                <i class="fas fa-file-pdf mr-2"></i> PDF
            </a>
        </div>
    </div>
</div>
@endif

<!-- Tabel Data Peminjaman -->
<div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-table mr-2 text-primary-600"></i>
                    Data Peminjaman Ruangan
                </h2>
                <p class="text-sm text-gray-600 mt-1 flex items-center">
                    <i class="far fa-calendar-alt mr-1 text-gray-400"></i>
                    Periode: {{ $formattedStartDate }} - {{ $formattedEndDate }}
                    @if($ruanganId != 'all')
                        @php $selectedRoom = $ruangan->firstWhere('id', $ruanganId); @endphp
                        @if($selectedRoom)
                            <span class="mx-2">•</span>
                            <i class="fas fa-door-open mr-1 text-gray-400"></i>
                            {{ $selectedRoom->nama_ruangan }}
                        @endif
                    @endif
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="bg-primary-50 text-primary-700 px-4 py-1.5 rounded-full text-xs font-semibold border border-primary-200">
                    <i class="fas fa-database mr-1"></i>
                    {{ number_format($peminjaman->count()) }} Data
                </span>
            </div>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th>Ruangan</th>
                    <th>Peminjam & Username</th>
                    <th>Kontak</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Keperluan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjaman as $index => $item)
                <tr>
                    <td class="font-medium text-gray-900">{{ $index + 1 }}</td>
                    
                    <!-- Informasi Ruangan -->
                    <td>
                        <div class="font-medium text-gray-900">
                            {{ $item->ruangan->nama_ruangan ?? $item->ruangan_nama ?? '-' }}
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ $item->ruangan->kode_ruangan ?? $item->ruangan_kode ?? '' }}
                        </div>
                        @if(isset($item->ruangan->kapasitas))
                        <div class="text-xs text-gray-400 mt-0.5">
                            <i class="fas fa-users mr-1"></i> {{ $item->ruangan->kapasitas }} orang
                        </div>
                        @endif
                    </td>
                    
                    <!-- Informasi Peminjam dengan USERNAME -->
                    <td>
                        <div class="font-medium text-gray-900">
                            {{ $item->nama_pengaju ?? $item->user->name ?? '-' }}
                        </div>
                        <div class="text-xs text-cyan-600 flex items-center mt-1">
                            <i class="fas fa-at mr-1"></i>
                            <span class="font-mono">{{ $item->user->username ?? $item->username ?? '-' }}</span>
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $item->nim_nip ?? $item->user->nim_nip ?? '' }}
                        </div>
                        <div class="text-xs text-gray-400 mt-1 capitalize flex items-center">
                            <i class="fas fa-user-tag mr-1"></i>
                            {{ $item->jenis_pengaju ?? $item->user->jenis_pengaju ?? 'Umum' }}
                        </div>
                    </td>
                    
                    <!-- Kontak -->
                    <td>
                        <div class="space-y-1.5">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-envelope text-gray-400 w-4 mr-2 text-xs"></i>
                                <span class="text-xs">{{ $item->email ?? $item->user->email ?? '-' }}</span>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-phone-alt text-gray-400 w-4 mr-2 text-xs"></i>
                                <span class="text-xs">{{ $item->no_telepon ?? $item->user->no_telepon ?? '-' }}</span>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Tanggal -->
                    <td>
                        <div class="flex items-center text-sm text-gray-900">
                            <i class="far fa-calendar-alt text-gray-400 mr-1.5"></i>
                            {{ date('d M Y', strtotime($item->tanggal_mulai)) }}
                        </div>
                        @if($item->tanggal_mulai != $item->tanggal_selesai)
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                            <i class="fas fa-arrow-right text-gray-400 mr-1.5"></i>
                            {{ date('d M Y', strtotime($item->tanggal_selesai)) }}
                        </div>
                        @endif
                    </td>
                    
                    <!-- Waktu -->
                    <td>
                        <div class="flex items-center text-sm text-gray-900">
                            <i class="far fa-clock text-gray-400 mr-1.5"></i>
                            {{ \Carbon\Carbon::parse($item->jam_mulai ?? $item->waktu_mulai)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($item->jam_selesai ?? $item->waktu_selesai)->format('H:i') }}
                        </div>
                        @php
                            $jamMulai = \Carbon\Carbon::parse($item->jam_mulai ?? $item->waktu_mulai);
                            $jamSelesai = \Carbon\Carbon::parse($item->jam_selesai ?? $item->waktu_selesai);
                            $durasi = $jamSelesai->diffInMinutes($jamMulai);
                            $jam = floor($durasi / 60);
                            $menit = $durasi % 60;
                        @endphp
                        <div class="text-xs text-gray-400 mt-1">
                            <i class="fas fa-hourglass-half mr-1"></i>
                            {{ $jam > 0 ? $jam . ' jam' : '' }} {{ $menit > 0 ? $menit . ' menit' : '' }}
                        </div>
                    </td>
                    
                    <!-- Status -->
                    <td>
                        @php
                            $status = $item->status ?? 'unknown';
                            $statusClass = '';
                            $icon = '';
                            
                            switch($status) {
                                case 'menunggu':
                                case 'pending':
                                    $statusClass = 'menunggu';
                                    $icon = 'fa-clock';
                                    $label = 'Menunggu';
                                    break;
                                case 'disetujui':
                                case 'approved':
                                    $statusClass = 'disetujui';
                                    $icon = 'fa-check-circle';
                                    $label = 'Disetujui';
                                    break;
                                case 'ditolak':
                                case 'rejected':
                                    $statusClass = 'ditolak';
                                    $icon = 'fa-times-circle';
                                    $label = 'Ditolak';
                                    break;
                                case 'selesai':
                                case 'completed':
                                    $statusClass = 'selesai';
                                    $icon = 'fa-flag-checkered';
                                    $label = 'Selesai';
                                    break;
                                case 'dibatalkan':
                                case 'cancelled':
                                    $statusClass = 'dibatalkan';
                                    $icon = 'fa-ban';
                                    $label = 'Dibatalkan';
                                    break;
                                default:
                                    $statusClass = 'menunggu';
                                    $icon = 'fa-question-circle';
                                    $label = ucfirst($status);
                            }
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            <i class="fas {{ $icon }}"></i>
                            {{ $label }}
                        </span>
                    </td>
                    
                    <!-- Keperluan -->
                    <td>
                        <div class="font-medium text-gray-900">
                            {{ $item->acara ?? $item->keperluan ?? '-' }}
                        </div>
                        @if($item->keterangan)
                            <div class="text-xs text-gray-500 mt-1 italic">
                                <i class="fas fa-quote-left text-gray-300 mr-1"></i>
                                {{ Str::limit($item->keterangan, 40) }}
                            </div>
                        @endif
                        @if($item->jumlah_peserta)
                            <div class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-users mr-1"></i> {{ number_format($item->jumlah_peserta) }} peserta
                            </div>
                        @endif
                        @if($item->created_at)
                            <div class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-clock mr-1"></i> Pengajuan: {{ date('d/m/Y', strtotime($item->created_at)) }}
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12">
                        <div class="flex flex-col items-center">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-inbox text-4xl text-gray-400"></i>
                            </div>
                            <p class="text-lg font-medium text-gray-700">Tidak Ada Data Peminjaman</p>
                            <p class="text-sm text-gray-500 mt-1">Belum ada data peminjaman pada periode ini</p>
                            <div class="mt-6 no-print">
                                <a href="{{ route('pegawai.laporan.index') }}" 
                                   class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm transition inline-flex items-center">
                                    <i class="fas fa-redo mr-2"></i>Reset Filter
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($peminjaman->count() > 0)
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600 flex items-center">
                <i class="fas fa-info-circle text-gray-400 mr-2"></i>
                Menampilkan <span class="font-semibold text-gray-900 mx-1">{{ number_format($peminjaman->count()) }}</span> data peminjaman
                @if($totalPeminjaman > $peminjaman->count())
                    dari total <span class="font-semibold text-gray-900 mx-1">{{ number_format($totalPeminjaman) }}</span> peminjaman
                @endif
            </div>
            <div class="text-xs text-gray-400 flex items-center no-print">
                <i class="fas fa-print mr-1"></i>
                Gunakan tombol cetak untuk export laporan
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Footer untuk Print -->
<div class="no-print mt-6 text-center text-xs text-gray-400">
    <p>Laporan dihasilkan pada {{ date('d M Y H:i') }} oleh {{ auth()->user()->name ?? 'Pegawai' }}</p>
    <p class="mt-1">© {{ date('Y') }} Sistem Manajemen Ruangan</p>
</div>

<!-- Hidden Print Footer -->
<div class="print-only hidden">
    <div class="text-xs text-gray-500 mt-4 text-center">
        <p>Laporan ini digenerate pada {{ date('d M Y H:i') }}</p>
        <p>Periode: {{ $formattedStartDate }} - {{ $formattedEndDate }}</p>
    </div>
</div>

@push('styles')
<style>
    /* Print styles */
    @media print {
        .filter-card, .no-print, .action-buttons, button, 
        .info-card .action-buttons, .bg-primary-600, .bg-gray-100 {
            display: none !important;
        }
        
        body {
            background: white;
            font-size: 10pt;
            line-height: 1.4;
            padding: 0;
            margin: 0;
        }
        
        @page {
            size: landscape;
            margin: 1cm;
        }
        
        .bg-white, .rounded-xl, .shadow-sm, .border {
            box-shadow: none !important;
            border: 1px solid #E5E7EB !important;
            background: white !important;
        }
        
        .data-table th {
            background-color: #F3F4F6 !important;
            color: black !important;
            font-weight: bold !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .status-badge {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            border: 1px solid currentColor !important;
        }
        
        .stat-card {
            border: 1px solid #E5E7EB !important;
            page-break-inside: avoid;
        }
        
        .data-table td, .data-table th {
            page-break-inside: avoid;
        }
        
        .print-only {
            display: block !important;
        }
    }
    
    .print-only {
        display: none;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.onbeforeprint = function() {};
    window.onafterprint = function() {};
});
</script>
@endpush

@endsection