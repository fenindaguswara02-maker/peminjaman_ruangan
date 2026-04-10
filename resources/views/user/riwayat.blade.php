@extends('layouts.user')

@section('title', 'Riwayat Peminjaman - Scheduler')
@section('page-title', 'Riwayat Peminjaman Ruangan')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Riwayat Peminjaman Ruangan</h2>
                    <p class="text-sm text-gray-600 mt-1">Lihat semua riwayat peminjaman ruangan Anda</p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <div class="relative">
                        <select id="filter-status" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 pr-10 appearance-none">
                            <option value="">Semua Status</option>
                            <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                            <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <input type="month" id="filter-bulan" value="{{ request('bulan') }}" 
                               class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                            <i class="fas fa-calendar"></i>
                        </div>
                    </div>
                    
                    <button onclick="applyFilters()" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                    
                    @if(request()->has('status') || request()->has('bulan'))
                    <a href="{{ route('user.peminjaman-ruangan.riwayat') }}" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-gray-600">Total</h3>
                    <p class="text-xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-door-open text-blue-600 text-sm"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-gray-600">Menunggu</h3>
                    <p class="text-xl font-bold text-yellow-600">{{ $stats['menunggu'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-sm"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-gray-600">Disetujui</h3>
                    <p class="text-xl font-bold text-green-600">{{ $stats['disetujui'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600 text-sm"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-gray-600">Ditolak</h3>
                    <p class="text-xl font-bold text-red-600">{{ $stats['ditolak'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times text-red-600 text-sm"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-gray-600">Selesai</h3>
                    <p class="text-xl font-bold text-purple-600">{{ $stats['selesai'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-flag-checkered text-purple-600 text-sm"></i>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-gray-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-gray-600">Dibatalkan</h3>
                    <p class="text-xl font-bold text-gray-600">{{ $stats['dibatalkan'] ?? 0 }}</p>
                </div>
                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-ban text-gray-600 text-sm"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-primary-900">Daftar Riwayat Peminjaman</h3>
                <div class="flex space-x-2">
                    <input type="text" id="search-input" placeholder="Cari..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors w-64">
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            @if($peminjamanRuangan->count() > 0)
            <table class="w-full">
                <thead class="bg-primary-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Ruangan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Detail Acara</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Tanggal & Waktu</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Status Real-time</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="peminjaman-table-body">
                    @foreach($peminjamanRuangan as $item)
                    @php
                        // Gunakan status_real_time dari database jika ada
                        $statusRealTime = $item->status_real_time ?? 'akan_datang';
                        $statusRealTimeLabel = match($statusRealTime) {
                            'akan_datang' => 'Akan Datang',
                            'berlangsung' => 'Berlangsung',
                            'selesai' => 'Selesai',
                            default => 'Akan Datang'
                        };
                        $statusRealTimeColor = match($statusRealTime) {
                            'akan_datang' => 'blue',
                            'berlangsung' => 'orange',
                            'selesai' => 'green',
                            default => 'blue'
                        };
                        $statusRealTimeIcon = match($statusRealTime) {
                            'akan_datang' => 'hourglass-start',
                            'berlangsung' => 'play-circle',
                            'selesai' => 'check-circle',
                            default => 'hourglass-start'
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors"
                        data-detail="{{ json_encode([
                            'id' => $item->id,
                            'username' => $item->user->username ?? '-',
                            'pengaju' => $item->nama_pengaju,
                            'jenis' => $item->jenis_pengaju ?? 'mahasiswa',
                            'nim' => $item->nim_nip ?? '-',
                            'fakultas' => $item->fakultas,
                            'prodi' => $item->prodi ?? '-',
                            'email' => $item->email ?? '-',
                            'telepon' => $item->no_telepon ?? '-',
                            'ruangan' => $item->ruangan->nama_ruangan ?? '-',
                            'kode_ruangan' => $item->ruangan->kode_ruangan ?? '-',
                            'kapasitas' => $item->ruangan->kapasitas ?? '-',
                            'lokasi' => $item->ruangan->lokasi ?? '-',
                            'fasilitas' => $item->ruangan->fasilitas ?? '-',
                            'acara' => $item->acara,
                            'hari' => \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('l'),
                            'tanggal' => \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y'),
                            'tanggal_mulai' => \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y'),
                            'tanggal_selesai' => \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d F Y'),
                            'jam_mulai' => $item->jam_mulai,
                            'jam_selesai' => $item->jam_selesai,
                            'peserta' => $item->jumlah_peserta,
                            'status' => $item->status,
                            'status_real_time' => $statusRealTime,
                            'status_real_time_label' => $statusRealTimeLabel,
                            'status_real_time_color' => $statusRealTimeColor,
                            'status_real_time_icon' => $statusRealTimeIcon,
                            'alasan_penolakan' => $item->alasan_penolakan,
                            'keterangan' => $item->keterangan,
                            'catatan' => $item->catatan ?? '', // TAMBAHKAN CATATAN
                            'lampiran_surat' => $item->lampiran_surat,
                            'created_at' => \Carbon\Carbon::parse($item->created_at)->translatedFormat('l, d F Y H:i'),
                        ]) }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-door-open text-primary-600"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $item->ruangan->kode_ruangan ?? '-' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $item->ruangan->nama_ruangan ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                    @php
                                        $initial = strtoupper(substr($item->nama_pengaju, 0, 1));
                                    @endphp
                                    <span class="text-white text-sm font-medium">{{ $initial }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item->acara }}</div>
                                    <div class="text-sm text-gray-600">{{ $item->nama_pengaju }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->fakultas }} • {{ $item->prodi ?? '-' }}</div>
                                    @if($item->keterangan)
                                        <div class="text-xs text-gray-400 mt-1">{{ Str::limit($item->keterangan, 30) }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-medium">
                                {{ \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d M Y') }}
                                @if($item->tanggal_mulai != $item->tanggal_selesai)
                                    - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d M Y') }}
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">
                                <i class="far fa-clock mr-1"></i> {{ $item->jam_mulai }} - {{ $item->jam_selesai }}
                            </div>
                            <div class="text-xs text-gray-400 mt-1">
                                Peserta: {{ $item->jumlah_peserta }} orang
                            </div>
                        </div>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'menunggu' => 'bg-yellow-100 text-yellow-800',
                                    'disetujui' => 'bg-green-100 text-green-800',
                                    'ditolak' => 'bg-red-100 text-red-800',
                                    'selesai' => 'bg-purple-100 text-purple-800',
                                    'dibatalkan' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusIcons = [
                                    'menunggu' => 'clock',
                                    'disetujui' => 'check',
                                    'ditolak' => 'times',
                                    'selesai' => 'flag-checkered',
                                    'dibatalkan' => 'ban'
                                ];
                                $statusLabels = [
                                    'menunggu' => 'Menunggu',
                                    'disetujui' => 'Disetujui',
                                    'ditolak' => 'Ditolak',
                                    'selesai' => 'Selesai',
                                    'dibatalkan' => 'Dibatalkan'
                                ];
                                $colorClass = $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800';
                                $icon = $statusIcons[$item->status] ?? 'circle';
                                $label = $statusLabels[$item->status] ?? $item->status;
                            @endphp
                            <div class="flex flex-col gap-1">
                                <span class="px-3 py-1 {{ $colorClass }} text-xs rounded-full inline-flex items-center w-fit">
                                    <i class="fas fa-{{ $icon }} mr-1 text-xs"></i>
                                    {{ $label }}
                                </span>
                                
                                @if($item->status == 'ditolak' && $item->alasan_penolakan)
                                    <div class="text-xs text-red-600 cursor-pointer group relative mt-1">
                                        <i class="fas fa-info-circle"></i> Alasan
                                        <div class="hidden group-hover:block absolute z-10 w-48 p-2 bg-red-50 border border-red-200 rounded shadow-lg text-xs text-red-700 mt-1">
                                            {{ $item->alasan_penolakan }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- Kolom Status Real-time dari Database -->
                        <td class="px-6 py-4">
                            @if(in_array($item->status, ['ditolak', 'dibatalkan']))
                                <span class="px-3 py-1 bg-gray-100 text-gray-500 text-xs rounded-full inline-flex items-center w-fit">
                                    <i class="fas fa-minus-circle mr-1 text-xs"></i>
                                    -
                                </span>
                            @else
                                @php
                                    $rtColors = [
                                        'akan_datang' => 'bg-blue-100 text-blue-800',
                                        'berlangsung' => 'bg-orange-100 text-orange-800 animate-pulse',
                                        'selesai' => 'bg-green-100 text-green-800'
                                    ];
                                    $rtIcons = [
                                        'akan_datang' => 'hourglass-start',
                                        'berlangsung' => 'play-circle',
                                        'selesai' => 'check-circle'
                                    ];
                                    $rtLabels = [
                                        'akan_datang' => 'Akan Datang',
                                        'berlangsung' => 'Berlangsung',
                                        'selesai' => 'Selesai'
                                    ];
                                    $rtColor = $rtColors[$statusRealTime] ?? 'bg-blue-100 text-blue-800';
                                    $rtIcon = $rtIcons[$statusRealTime] ?? 'hourglass-start';
                                    $rtLabel = $rtLabels[$statusRealTime] ?? 'Akan Datang';
                                @endphp
                                <span class="px-3 py-1 {{ $rtColor }} text-xs rounded-full inline-flex items-center w-fit">
                                    <i class="fas fa-{{ $rtIcon }} mr-1 text-xs"></i>
                                    {{ $rtLabel }}
                                </span>
                            @endif
                        </div>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="showDetailModalUser({{ $item->id }})" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors p-2 rounded-lg hover:bg-blue-50" 
                                        title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                @if(in_array($item->status, ['menunggu', 'disetujui']) && $item->status_real_time != 'selesai')
                                <form action="{{ route('user.peminjaman-ruangan.cancel', $item->id) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirmCancel()">
                                    @csrf
                                    <button type="submit" 
                                            class="text-gray-600 hover:text-gray-900 transition-colors p-2 rounded-lg hover:bg-gray-50" 
                                            title="Batalkan Peminjaman">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            @if($peminjamanRuangan->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium">{{ $peminjamanRuangan->firstItem() }}</span> sampai 
                        <span class="font-medium">{{ $peminjamanRuangan->lastItem() }}</span> dari 
                        <span class="font-medium">{{ $peminjamanRuangan->total() }}</span> peminjaman
                    </div>
                    <div>
                        {{ $peminjamanRuangan->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            </div>
            @endif

            @else
            <!-- Empty State -->
            <div class="px-6 py-12 text-center">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-door-closed text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada riwayat peminjaman</h3>
                <p class="text-gray-500 mb-6">Anda belum melakukan peminjaman ruangan</p>
                <a href="{{ route('user.peminjaman-ruangan.create') }}" 
                   class="inline-flex items-center gap-2 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                    <i class="fas fa-plus"></i> Ajukan Peminjaman Ruangan
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Detail untuk User -->
<div id="detailModalUser" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-primary-50 to-primary-100 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Detail Peminjaman Ruangan</h3>
                    <p class="text-sm text-gray-600">Informasi lengkap peminjaman ruangan</p>
                </div>
            </div>
            <button onclick="closeDetailModalUser()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" id="detailContentUser">
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat data...</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button onclick="closeDetailModalUser()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Loading Spinner -->
<div id="globalLoading" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center hidden z-[60]">
    <div class="bg-white rounded-lg p-6 shadow-xl flex items-center gap-3">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        <span class="text-gray-700 font-medium">Memuat...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
// CSRF Token untuk AJAX
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// Fungsi untuk Modal Detail User
function showDetailModalUser(id) {
    console.log('Opening detail modal for user ID:', id);
    
    // Cari row dengan ID yang sesuai
    const rows = document.querySelectorAll('tr[data-detail]');
    let targetRow = null;
    
    for (let row of rows) {
        try {
            const data = JSON.parse(row.getAttribute('data-detail'));
            if (data.id === id) {
                targetRow = row;
                break;
            }
        } catch (e) {
            console.error('Error parsing row data:', e);
        }
    }
    
    if (!targetRow) {
        alert('Data tidak ditemukan untuk ID: ' + id);
        return;
    }
    
    try {
        const detailData = JSON.parse(targetRow.getAttribute('data-detail'));
        console.log('Detail data:', detailData);
        
        const html = generateDetailHtml(detailData);
        document.getElementById('detailContentUser').innerHTML = html;
        
        const modal = document.getElementById('detailModalUser');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        const modalContent = modal.querySelector('.bg-white');
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        modalContent.style.transition = 'all 0.3s ease';
        
        setTimeout(() => {
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }, 10);
        
    } catch (error) {
        console.error('Error parsing detail data:', error);
        document.getElementById('detailContentUser').innerHTML = `
            <div class="text-center py-12">
                <div class="mb-6">
                    <i class="fas fa-exclamation-triangle text-5xl text-red-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Gagal Memuat Data</h3>
                <p class="text-gray-600 mb-6">Terjadi kesalahan saat memuat data detail</p>
                <p class="text-sm text-gray-500 mb-8">ID Peminjaman: #${id}</p>
                <div class="flex justify-center">
                    <button onclick="closeDetailModalUser()" 
                            class="px-5 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium flex items-center">
                        <i class="fas fa-times mr-2"></i> Tutup
                    </button>
                </div>
            </div>
        `;
        
        const modal = document.getElementById('detailModalUser');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function generateDetailHtml(data) {
    const statusColors = {
        'menunggu': 'yellow',
        'disetujui': 'green',
        'ditolak': 'red',
        'selesai': 'purple',
        'dibatalkan': 'gray'
    };
    
    const statusIcons = {
        'menunggu': 'clock',
        'disetujui': 'check',
        'ditolak': 'times',
        'selesai': 'flag-checkered',
        'dibatalkan': 'ban'
    };
    
    const statusColor = statusColors[data.status] || 'gray';
    const statusIcon = statusIcons[data.status] || 'circle';
    
    // Gunakan data status_real_time yang sudah dihitung dari database
    const realTimeColors = {
        'akan_datang': 'blue',
        'berlangsung': 'orange',
        'selesai': 'green'
    };
    
    const realTimeIcons = {
        'akan_datang': 'hourglass-start',
        'berlangsung': 'play-circle',
        'selesai': 'check-circle'
    };
    
    const realTimeLabels = {
        'akan_datang': 'Akan Datang',
        'berlangsung': 'Berlangsung',
        'selesai': 'Selesai'
    };
    
    function showValue(value) {
        return value && value !== '' && value !== '-' ? value : '<span class="text-gray-400 italic">Tidak ada</span>';
    }
    
    // CEK APAKAH ADA CATATAN
    const hasCatatan = data.catatan && data.catatan.trim() !== '';
    
    return `
        <div class="space-y-6">
            <div class="text-center border-b pb-4 mb-4">
                <div class="mb-2">
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                        ID: #${data.id}
                    </span>
                </div>
                <h4 class="text-xl font-bold text-primary-900">${escapeHtml(data.acara)}</h4>
                <p class="text-gray-600">${escapeHtml(data.kode_ruangan)} - ${escapeHtml(data.ruangan)}</p>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-user-circle mr-2 text-primary-600"></i>
                    Informasi Pengaju
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-cyan-50 p-3 rounded-lg border border-cyan-200 md:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">
                            <i class="fas fa-at mr-1 text-cyan-600"></i>Username
                        </label>
                        <p class="font-medium text-cyan-800 text-lg">${escapeHtml(data.username)}</p>
                        <p class="text-xs text-gray-400 mt-1">Username unik untuk login</p>
                    </div>
                    
                    <div>
                        <label class="block text-xs text-gray-500">Nama Lengkap</label>
                        <p class="font-medium">${escapeHtml(data.pengaju)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Jenis Pengaju</label>
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                            ${capitalizeFirstLetter(data.jenis)}
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">NIM/NIP</label>
                        <p class="font-medium">${escapeHtml(data.nim)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Fakultas</label>
                        <p class="font-medium">${escapeHtml(data.fakultas)}</p>
                    </div>
                    ${data.prodi && data.prodi !== '-' ? `
                    <div>
                        <label class="block text-xs text-gray-500">Program Studi</label>
                        <p class="font-medium">${escapeHtml(data.prodi)}</p>
                    </div>
                    ` : ''}
                    <div>
                        <label class="block text-xs text-gray-500">Email</label>
                        <p class="font-medium">${escapeHtml(data.email)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">No. Telepon</label>
                        <p class="font-medium">${escapeHtml(data.telepon)}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-door-open mr-2 text-primary-600"></i>
                    Informasi Ruangan
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500">Kode Ruangan</label>
                        <p class="font-medium">${escapeHtml(data.kode_ruangan)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Nama Ruangan</label>
                        <p class="font-medium">${escapeHtml(data.ruangan)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Kapasitas</label>
                        <p class="font-medium">${escapeHtml(data.kapasitas)} orang</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Lokasi</label>
                        <p class="font-medium">${escapeHtml(data.lokasi)}</p>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500">Fasilitas</label>
                        <p class="font-medium whitespace-pre-line">${showValue(data.fasilitas)}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                    Jadwal Acara
                </h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500">Hari</label>
                        <p class="font-medium">${escapeHtml(data.hari)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Tanggal Mulai</label>
                        <p class="font-medium">${escapeHtml(data.tanggal_mulai)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Tanggal Selesai</label>
                        <p class="font-medium">${escapeHtml(data.tanggal_selesai)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Waktu</label>
                        <p class="font-medium">${escapeHtml(data.jam_mulai)} - ${escapeHtml(data.jam_selesai)}</p>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">Jumlah Peserta</label>
                        <p class="font-medium">${escapeHtml(data.peserta)} orang</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-primary-600"></i>
                        Status Peminjaman
                    </h5>
                    
                    <div class="mb-4">
                        <label class="block text-xs text-gray-500 mb-1">Status Administrasi</label>
                        <span class="px-3 py-1.5 bg-${statusColor}-100 text-${statusColor}-800 text-sm font-semibold rounded-full inline-flex items-center">
                            <i class="fas fa-${statusIcon} mr-2 text-xs"></i>
                            ${capitalizeFirstLetter(data.status)}
                        </span>
                    </div>

                    ${data.status === 'disetujui' ? `
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <label class="block text-xs text-gray-500 mb-1">Status Real-time</label>
                        <span class="px-3 py-1.5 bg-${realTimeColors[data.status_real_time]}-100 text-${realTimeColors[data.status_real_time]}-800 text-sm font-semibold rounded-full inline-flex items-center">
                            <i class="fas fa-${realTimeIcons[data.status_real_time]} mr-2 text-xs"></i>
                            ${realTimeLabels[data.status_real_time] || data.status_real_time}
                        </span>
                        ${data.status_real_time === 'berlangsung' ? `
                        <div class="mt-2 text-xs text-orange-600 animate-pulse flex items-center">
                            <i class="fas fa-circle mr-1"></i> Acara sedang berlangsung
                        </div>
                        ` : ''}
                        ${data.status_real_time === 'selesai' ? `
                        <div class="mt-2 text-xs text-green-600 flex items-center">
                            <i class="fas fa-check-circle mr-1"></i> Acara telah selesai
                        </div>
                        ` : ''}
                        ${data.status_real_time === 'akan_datang' ? `
                        <div class="mt-2 text-xs text-blue-600 flex items-center">
                            <i class="fas fa-hourglass-start mr-1"></i> Acara akan datang
                        </div>
                        ` : ''}
                    </div>
                    ` : ''}

                    ${data.status === 'ditolak' && data.alasan_penolakan ? `
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <label class="block text-xs font-semibold text-red-800 mb-1">Alasan Penolakan</label>
                        <p class="text-sm text-red-700">${escapeHtml(data.alasan_penolakan)}</p>
                    </div>
                    ` : ''}
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-paperclip mr-2 text-primary-600"></i>
                        Lampiran & Keterangan
                    </h5>
                    
                    ${data.lampiran_surat ? `
                    <div class="mb-4">
                        <label class="block text-xs text-gray-500 mb-2">Lampiran Surat</label>
                        <a href="/storage/${data.lampiran_surat}" 
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Download Surat Peminjaman
                        </a>
                    </div>
                    ` : `
                    <div class="mb-4">
                        <label class="block text-xs text-gray-500 mb-2">Lampiran Surat</label>
                        <p class="text-sm text-gray-500 italic">Tidak ada lampiran surat</p>
                    </div>
                    `}

                    ${data.keterangan ? `
                    <div>
                        <label class="block text-xs text-gray-500 mb-2">Keterangan Tambahan</label>
                        <p class="text-sm text-gray-700 bg-white p-3 rounded border">${escapeHtml(data.keterangan)}</p>
                    </div>
                    ` : ''}
                </div>
            </div>

            <!-- ========== BAGIAN CATATAN (DITAMBAHKAN) ========== -->
            <div class="bg-gradient-to-r from-teal-50 to-cyan-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-sticky-note mr-2 text-teal-600"></i>
                    Catatan Internal
                </h5>
                ${hasCatatan ? `
                    <div class="bg-white p-4 rounded-lg border border-teal-200">
                        <p class="text-gray-700 whitespace-pre-wrap font-mono text-sm">${escapeHtml(data.catatan)}</p>
                        <div class="mt-3 text-right">
                            <span class="text-xs text-gray-400">
                                <i class="fas fa-lock mr-1"></i> Catatan internal admin/pegawai
                            </span>
                        </div>
                    </div>
                ` : `
                    <div class="bg-white p-4 rounded-lg border border-gray-200 text-center">
                        <p class="text-gray-500">Belum ada catatan</p>
                        <p class="text-xs text-gray-400 mt-1">Catatan akan muncul jika admin menambahkannya</p>
                    </div>
                `}
            </div>

            <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-clock mr-2 text-primary-600"></i>
                    Informasi Pengajuan
                </h5>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Diajukan pada</label>
                    <p class="text-sm text-gray-700">${escapeHtml(data.created_at)}</p>
                </div>
            </div>
        </div>
    `;
}

function capitalizeFirstLetter(string) {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeDetailModalUser() {
    const modal = document.getElementById('detailModalUser');
    const modalContent = modal.querySelector('.bg-white');
    
    modalContent.style.transform = 'scale(0.95)';
    modalContent.style.opacity = '0';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('detailContentUser').innerHTML = `
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat data...</p>
            </div>
        `;
    }, 300);
}

function applyFilters() {
    const status = document.getElementById('filter-status')?.value;
    const bulan = document.getElementById('filter-bulan')?.value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (bulan) params.append('bulan', bulan);
    
    window.location.href = `{{ route('user.peminjaman-ruangan.riwayat') }}?${params.toString()}`;
}

function confirmCancel() {
    return confirm('Apakah Anda yakin ingin membatalkan peminjaman ruangan ini?');
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#peminjaman-table-body tr');
            
            let visibleCount = 0;
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            const tableBody = document.getElementById('peminjaman-table-body');
            let noResultsMessage = tableBody.querySelector('.no-results-message');
            
            if (visibleCount === 0 && searchTerm !== '') {
                if (!noResultsMessage) {
                    noResultsMessage = document.createElement('tr');
                    noResultsMessage.className = 'no-results-message';
                    noResultsMessage.innerHTML = `
                        <td colspan="6" class="px-6 py-12 text-center">
                            <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Tidak ditemukan data peminjaman untuk pencarian "${searchTerm}"</p>
                        </div>
                    `;
                    tableBody.appendChild(noResultsMessage);
                }
            } else if (noResultsMessage) {
                noResultsMessage.remove();
            }
        });
    }
    
    const modal = document.getElementById('detailModalUser');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDetailModalUser();
            }
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailModalUser();
        }
    });
    
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
@endpush

@push('styles')
<style>
    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .modal-animate {
        animation: modalFadeIn 0.3s ease-out;
    }
    
    .loading-spinner {
        border: 3px solid rgba(59, 130, 246, 0.1);
        border-radius: 50%;
        border-top: 3px solid #3b82f6;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
    
    .hover\:bg-blue-50:hover {
        background-color: rgba(59, 130, 246, 0.1);
    }
    
    .hover\:bg-gray-50:hover {
        background-color: rgba(107, 114, 128, 0.1);
    }
    
    .rounded-full {
        border-radius: 9999px;
    }
    
    tr:hover {
        background-color: #f9fafb;
    }
    
    #detailContentUser::-webkit-scrollbar {
        width: 8px;
    }
    
    #detailContentUser::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    #detailContentUser::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    #detailContentUser::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    .group:hover .group-hover\:block {
        display: block;
    }
</style>
@endpush

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{ session('error') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    });
</script>
@endif