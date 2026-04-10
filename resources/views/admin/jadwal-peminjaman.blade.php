@extends('layouts.admin')

@section('title', 'Jadwal Peminjaman')
@section('page-title', 'Jadwal Peminjaman')

@section('content')
<!-- Modal Detail -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Detail Peminjaman Ruangan</h3>
                        <p class="text-sm text-gray-600">Informasi lengkap peminjaman ruangan</p>
                    </div>
                </div>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" id="modalContent">
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat data...</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button onclick="closeDetailModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-yellow-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-edit text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Edit Peminjaman</h3>
                        <p class="text-sm text-gray-600">Ubah data peminjaman ruangan</p>
                    </div>
                </div>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" id="editModalContent">
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat data...</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
            <button onclick="closeEditModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Batal
            </button>
            <button onclick="submitEditForm()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-red-50 to-red-100">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-trash-alt text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Konfirmasi Hapus</h3>
                        <p class="text-sm text-gray-600">Hapus data peminjaman</p>
                    </div>
                </div>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <p class="text-gray-700">Apakah Anda yakin ingin menghapus peminjaman berikut?</p>
            <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                <p class="font-medium text-gray-900" id="deleteAcaraName">-</p>
                <p class="text-sm text-gray-500 mt-1" id="deleteInfo">ID: -</p>
            </div>
            <p class="text-sm text-red-600 mt-3 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Tindakan ini tidak dapat dibatalkan!
            </p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Batal
            </button>
            <button onclick="confirmDeleteAction()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <i class="fas fa-trash-alt mr-2"></i> Hapus
            </button>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Jadwal Peminjaman</h1>
            <p class="text-gray-600">Kelola jadwal dan status peminjaman ruangan</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('admin.jadwal-peminjaman') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Acara, peminjam, ruangan..."
                       class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Semua</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ruangan</label>
                <select name="ruangan_id" class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="all" {{ request('ruangan_id') == 'all' || !request('ruangan_id') ? 'selected' : '' }}>Semua</option>
                    @foreach($ruanganOptions as $ruangan)
                        <option value="{{ $ruangan->id }}" {{ request('ruangan_id') == $ruangan->id ? 'selected' : '' }}>
                            {{ $ruangan->kode_ruangan }} - {{ $ruangan->nama_ruangan }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg h-10">
                    Filter
                </button>
                <a href="{{ route('admin.jadwal-peminjaman') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg h-10 flex items-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Total</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $overallStats['total_ruangan'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-door-open text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Disetujui</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $overallStats['disetujui_ruangan'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Menunggu</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ $overallStats['menunggu_ruangan'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Ditolak</h3>
                    <p class="text-2xl font-bold text-red-600">{{ $overallStats['ditolak_ruangan'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times text-red-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Selesai</h3>
                    <p class="text-2xl font-bold text-purple-600">{{ $overallStats['selesai_ruangan'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-flag-checkered text-purple-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-gray-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Dibatalkan</h3>
                    <p class="text-2xl font-bold text-gray-600">{{ $overallStats['dibatalkan_ruangan'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-ban text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Daftar Jadwal</h3>
                <span class="text-sm text-gray-500">
                    @if($peminjamanRuangan->total() > 0)
                        {{ $peminjamanRuangan->firstItem() }}-{{ $peminjamanRuangan->lastItem() }} dari {{ $peminjamanRuangan->total() }}
                    @endif
                </span>
            </div>
        </div>

        @if($peminjamanRuangan->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acara</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal & Jam</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Real-time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="peminjaman-table-body">
                        @foreach($peminjamanRuangan as $item)
                        @php
                            // Status real-time dari database
                            $statusRealTimeFromDb = $item->status_real_time;
                            $statusPeminjaman = $item->status;
                            
                            // Jika status ditolak atau dibatalkan, tampilkan "-"
                            $isRejectedOrCancelled = ($statusPeminjaman == 'ditolak' || $statusPeminjaman == 'dibatalkan');
                            
                            // Mapping status real-time
                            $statusRealTimeLabels = [
                                'akan_datang' => 'Akan Datang',
                                'berlangsung' => 'Berlangsung',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                                'menunggu' => 'Menunggu',
                                'ditolak' => 'Ditolak'
                            ];
                            $statusRealTimeColors = [
                                'akan_datang' => 'purple',
                                'berlangsung' => 'orange',
                                'selesai' => 'blue',
                                'dibatalkan' => 'gray',
                                'menunggu' => 'yellow',
                                'ditolak' => 'red'
                            ];
                            $statusRealTimeIcons = [
                                'akan_datang' => 'hourglass-start',
                                'berlangsung' => 'play-circle',
                                'selesai' => 'flag-checkered',
                                'dibatalkan' => 'ban',
                                'menunggu' => 'clock',
                                'ditolak' => 'times-circle'
                            ];
                            
                            // Jika status ditolak atau dibatalkan, tampilkan "-"
                            if ($isRejectedOrCancelled) {
                                $statusRealTimeLabel = '-';
                                $statusRealTimeColor = 'gray';
                                $statusRealTimeIcon = 'minus-circle';
                            } else {
                                $statusRealTimeLabel = $statusRealTimeLabels[$statusRealTimeFromDb] ?? 'Menunggu';
                                $statusRealTimeColor = $statusRealTimeColors[$statusRealTimeFromDb] ?? 'gray';
                                $statusRealTimeIcon = $statusRealTimeIcons[$statusRealTimeFromDb] ?? 'clock';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors" data-id="{{ $item->id }}"
                            data-detail="{{ json_encode([
                                'id' => $item->id,
                                'acara' => $item->acara,
                                'keterangan' => $item->keterangan,
                                'catatan' => $item->catatan ?? '',
                                'ruangan_id' => $item->ruangan_id,
                                'kode_ruangan' => $item->ruangan->kode_ruangan ?? '-',
                                'nama_ruangan' => $item->ruangan->nama_ruangan ?? '-',
                                'lokasi_ruangan' => $item->ruangan->lokasi ?? '-',
                                'kapasitas' => $item->ruangan->kapasitas ?? '-',
                                'fasilitas' => $item->ruangan->fasilitas ?? '-',
                                'nama_pengaju' => $item->nama_pengaju ?? $item->user->name ?? '-',
                                'nim_nip' => $item->nim_nip ?? $item->user->nim_nip ?? '-',
                                'jenis_pengaju' => $item->jenis_pengaju ?? $item->user->jenis_pengaju ?? '-',
                                'fakultas' => $item->fakultas ?? $item->user->fakultas ?? '-',
                                'prodi' => $item->prodi ?? $item->user->prodi ?? '-',
                                'email' => $item->email ?? $item->user->email ?? '-',
                                'telepon' => $item->no_telepon ?? $item->user->telepon ?? '-',
                                'hari' => $item->hari,
                                'tanggal' => $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') : '-',
                                'jam_mulai' => $item->jam_mulai ? substr($item->jam_mulai, 0, 5) : '-',
                                'jam_selesai' => $item->jam_selesai ? substr($item->jam_selesai, 0, 5) : '-',
                                'jumlah_peserta' => $item->jumlah_peserta ?? '0',
                                'status' => $item->status,
                                'status_real_time' => $statusRealTimeFromDb,
                                'status_real_time_label' => $statusRealTimeLabel,
                                'status_real_time_color' => $statusRealTimeColor,
                                'status_real_time_icon' => $statusRealTimeIcon,
                                'is_rejected_or_cancelled' => $isRejectedOrCancelled,
                                'alasan_penolakan' => $item->alasan_penolakan,
                                'lampiran_surat' => $item->lampiran_surat,
                                'created_at' => \Carbon\Carbon::parse($item->created_at)->translatedFormat('l, d F Y H:i'),
                                'updated_at' => \Carbon\Carbon::parse($item->updated_at)->translatedFormat('l, d F Y H:i'),
                                'user_foto' => $item->user && $item->user->foto ? asset('storage/' . $item->user->foto) : null
                            ]) }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $loop->iteration + ($peminjamanRuangan->currentPage() - 1) * $peminjamanRuangan->perPage() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 px-2 py-1 rounded">
                                        <i class="fas fa-user-circle mr-1 text-blue-500"></i>
                                        {{ $item->user->username ?? $item->username ?? '-' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ Str::limit($item->acara, 30) }}</div>
                                @if($item->keterangan)
                                    <div class="text-sm text-gray-500">{{ Str::limit($item->keterangan, 20) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-door-open mr-1 text-gray-400"></i>
                                    {{ $item->ruangan->kode_ruangan ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $item->ruangan->nama_ruangan ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($item->user && $item->user->foto)
                                        <img src="{{ asset('storage/' . $item->user->foto) }}" 
                                             class="h-8 w-8 rounded-full mr-3 object-cover" 
                                             alt="{{ $item->nama_pengaju ?? $item->user->name }}">
                                    @else
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $item->nama_pengaju ?? $item->user->name ?? '-' }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->nim_nip ?? $item->user->nim_nip ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <i class="fas fa-calendar-alt mr-1 text-gray-400"></i>
                                    {{ $item->tanggal ? date('d/m/Y', strtotime($item->tanggal)) : '-' }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1 text-gray-400"></i>
                                    @if($item->jam_mulai && $item->jam_selesai)
                                        {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'disetujui' => 'bg-green-100 text-green-800',
                                        'menunggu' => 'bg-yellow-100 text-yellow-800',
                                        'ditolak' => 'bg-red-100 text-red-800',
                                        'selesai' => 'bg-blue-100 text-blue-800',
                                        'dibatalkan' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $statusLabels = [
                                        'disetujui' => 'Disetujui',
                                        'menunggu' => 'Menunggu',
                                        'ditolak' => 'Ditolak',
                                        'selesai' => 'Selesai',
                                        'dibatalkan' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$item->status] ?? $item->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isRejectedOrCancelled)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-400 flex items-center space-x-1 w-fit">
                                        <i class="fas fa-minus-circle mr-1"></i>
                                        <span>-</span>
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $statusRealTimeColor }}-100 text-{{ $statusRealTimeColor }}-800 flex items-center space-x-1 w-fit">
                                        <i class="fas fa-{{ $statusRealTimeIcon }} mr-1"></i>
                                        <span>{{ $statusRealTimeLabel }}</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex space-x-2">
                                    <button onclick="showDetail({{ $item->id }})" 
                                            class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50 transition-colors"
                                            title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="showEditModal({{ $item->id }})" 
                                            class="text-yellow-600 hover:text-yellow-900 px-2 py-1 rounded hover:bg-yellow-50 transition-colors"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="showDeleteModal({{ $item->id }}, '{{ addslashes($item->acara) }}')" 
                                            class="text-red-600 hover:text-red-900 px-2 py-1 rounded hover:bg-red-50 transition-colors"
                                            title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </div>
            </div>

            @if($peminjamanRuangan->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $peminjamanRuangan->appends(request()->query())->links() }}
            </div>
            @endif
        @else
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">Tidak ada data jadwal peminjaman</p>
                @if(request()->hasAny(['status', 'ruangan_id', 'search']))
                    <p class="text-sm text-gray-400 mt-2">
                        Coba <a href="{{ route('admin.jadwal-peminjaman') }}" class="text-blue-600 hover:text-blue-800">reset filter</a>
                    </p>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
/* Style yang sama seperti yang Anda miliki */
@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
.modal-animate { animation: modalFadeIn 0.3s ease-out; }
.loading-spinner {
    border: 3px solid rgba(0,0,0,0.1);
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
.bg-purple-100 { background-color: #f3e8ff; }
.text-purple-800 { color: #6b21a5; }
.bg-orange-100 { background-color: #ffedd5; }
.text-orange-800 { color: #9a3412; }
.bg-blue-100 { background-color: #dbeafe; }
.text-blue-800 { color: #1e40af; }
.bg-yellow-100 { background-color: #fef3c7; }
.text-yellow-800 { color: #92400e; }
.bg-red-100 { background-color: #fee2e2; }
.text-red-800 { color: #991b1b; }
.bg-gray-100 { background-color: #f3f4f6; }
.text-gray-800 { color: #1f2937; }
@media (max-width: 768px) {
    .overflow-x-auto { -webkit-overflow-scrolling: touch; }
    table { min-width: 1200px; }
}
</style>
@endpush

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
let currentDeleteId = null;
let currentEditId = null;

// ==================== FUNGSI DETAIL ====================
function showDetail(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    if (!row) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Data tidak ditemukan untuk ID: ' + id });
        return;
    }
    try {
        const detailData = JSON.parse(row.getAttribute('data-detail'));
        const html = generateDetailHtml(detailData);
        document.getElementById('modalContent').innerHTML = html;
        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex', 'modal-animate');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('modalContent').innerHTML = `<div class="text-center py-12"><i class="fas fa-exclamation-triangle text-5xl text-red-400"></i><p class="text-gray-600 mt-4">Gagal memuat data detail</p></div>`;
        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function generateDetailHtml(data) {
    const statusColors = { 'menunggu': 'yellow', 'disetujui': 'green', 'ditolak': 'red', 'dibatalkan': 'gray', 'selesai': 'blue' };
    const statusColor = statusColors[data.status] || 'gray';
    const isRejectedOrCancelled = (data.status === 'ditolak' || data.status === 'dibatalkan');
    const displayStatusRealTime = isRejectedOrCancelled ? '-' : data.status_real_time_label;
    const displayStatusRealTimeColor = isRejectedOrCancelled ? 'gray' : data.status_real_time_color;
    const displayStatusRealTimeIcon = isRejectedOrCancelled ? 'minus-circle' : data.status_real_time_icon;
    
    function capitalize(str) { if (!str) return '-'; return str.charAt(0).toUpperCase() + str.slice(1); }
    function showValue(value) { return value && value !== '' && value !== '-' ? value : '<span class="text-gray-400 italic">Tidak ada</span>'; }
    
    return `
        <div class="space-y-6">
            <div class="text-center border-b pb-4 mb-4">
                <div class="mb-2"><span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">ID: #${data.id}</span></div>
                <h4 class="text-xl font-bold text-gray-900">${escapeHtml(data.acara)}</h4>
                <p class="text-gray-600">${escapeHtml(data.kode_ruangan)} - ${escapeHtml(data.nama_ruangan)}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3"><i class="fas fa-user-circle mr-2 text-blue-600"></i>Informasi Pengaju</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-xs text-gray-500">Nama Lengkap</label><p class="font-medium">${escapeHtml(data.nama_pengaju)}</p></div>
                    <div><label class="block text-xs text-gray-500">Jenis Pengaju</label><span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">${capitalize(data.jenis_pengaju)}</span></div>
                    <div><label class="block text-xs text-gray-500">NIM/NIP</label><p class="font-medium">${escapeHtml(data.nim_nip)}</p></div>
                    <div><label class="block text-xs text-gray-500">Fakultas</label><p class="font-medium">${escapeHtml(data.fakultas)}</p></div>
                    ${data.prodi && data.prodi !== '-' ? `<div><label class="block text-xs text-gray-500">Program Studi</label><p class="font-medium">${escapeHtml(data.prodi)}</p></div>` : ''}
                    <div><label class="block text-xs text-gray-500">Email</label><p class="font-medium">${escapeHtml(data.email)}</p></div>
                    <div><label class="block text-xs text-gray-500">No. Telepon</label><p class="font-medium">${escapeHtml(data.telepon)}</p></div>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3"><i class="fas fa-door-open mr-2 text-blue-600"></i>Informasi Ruangan</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-xs text-gray-500">Kode Ruangan</label><p class="font-medium">${escapeHtml(data.kode_ruangan)}</p></div>
                    <div><label class="block text-xs text-gray-500">Nama Ruangan</label><p class="font-medium">${escapeHtml(data.nama_ruangan)}</p></div>
                    <div><label class="block text-xs text-gray-500">Lokasi</label><p class="font-medium"><i class="fas fa-map-marker-alt text-blue-500 mr-1"></i>${showValue(data.lokasi_ruangan)}</p></div>
                    <div><label class="block text-xs text-gray-500">Kapasitas</label><p class="font-medium">${escapeHtml(data.kapasitas)} orang</p></div>
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3"><i class="fas fa-calendar-alt mr-2 text-blue-600"></i>Jadwal Acara</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-xs text-gray-500">Hari</label><p class="font-medium">${escapeHtml(data.hari)}</p></div>
                    <div><label class="block text-xs text-gray-500">Tanggal</label><p class="font-medium">${escapeHtml(data.tanggal)}</p></div>
                    <div><label class="block text-xs text-gray-500">Waktu</label><p class="font-medium">${escapeHtml(data.jam_mulai)} - ${escapeHtml(data.jam_selesai)}</p></div>
                    <div><label class="block text-xs text-gray-500">Jumlah Peserta</label><p class="font-medium">${escapeHtml(data.jumlah_peserta)} orang</p></div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h5 class="font-semibold text-gray-800 mb-3"><i class="fas fa-info-circle mr-2 text-blue-600"></i>Status Peminjaman</h5>
                    <div class="mb-4"><label class="block text-xs text-gray-500 mb-1">Status Administrasi</label><span class="px-3 py-1.5 bg-${statusColor}-100 text-${statusColor}-800 text-sm font-semibold rounded-full">${capitalize(data.status)}</span></div>
                    <div class="mt-4 pt-4 border-t border-gray-200"><label class="block text-xs text-gray-500 mb-2">Status Real-time</label><span class="px-3 py-1.5 bg-${displayStatusRealTimeColor}-100 text-${displayStatusRealTimeColor}-800 text-sm font-semibold rounded-full"><i class="fas fa-${displayStatusRealTimeIcon} mr-1"></i>${displayStatusRealTime}</span></div>
                    ${data.status === 'ditolak' && data.alasan_penolakan ? `<div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg"><label class="text-xs font-semibold text-red-800">Alasan Penolakan</label><p class="font-medium text-red-700">${escapeHtml(data.alasan_penolakan)}</p></div>` : ''}
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h5 class="font-semibold text-gray-800 mb-3"><i class="fas fa-paperclip mr-2 text-blue-600"></i>Lampiran & Keterangan</h5>
                    ${data.lampiran_surat ? `<div class="mb-4"><a href="/admin/peminjaman-ruangan/download-surat/${data.id}" class="inline-flex items-center px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors"><i class="fas fa-download mr-2"></i> Download Surat</a></div>` : ''}
                    ${data.keterangan ? `<div><label class="block text-xs text-gray-500">Keterangan</label><p class="font-medium bg-white p-3 rounded border">${escapeHtml(data.keterangan)}</p></div>` : ''}
                </div>
            </div>
            <div class="bg-gradient-to-r from-teal-50 to-cyan-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center"><i class="fas fa-sticky-note mr-2 text-teal-600"></i> Catatan Internal</h5>
                ${data.catatan && data.catatan.trim() !== '' ? `<div class="bg-white p-4 rounded-lg border border-teal-200"><p class="text-gray-700 whitespace-pre-wrap font-mono text-sm">${escapeHtml(data.catatan)}</p><div class="mt-3 text-right"><span class="text-xs text-gray-400"><i class="fas fa-lock mr-1"></i> Catatan internal</span></div></div>` : `<div class="bg-white p-4 rounded-lg border border-gray-200 text-center"><p class="text-gray-500">Belum ada catatan</p><p class="text-xs text-gray-400 mt-1">Catatan akan muncul jika ditambahkan</p></div>`}
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-semibold text-gray-800 mb-3"><i class="fas fa-clock mr-2 text-blue-600"></i>Informasi Pengajuan</h5>
                <div class="mt-4 pt-4 border-t"><label class="block text-xs text-gray-500">Diajukan pada</label><p class="font-medium">${escapeHtml(data.created_at)}</p></div>
            </div>
        </div>
    `;
}

// ==================== FUNGSI EDIT ====================
function showEditModal(id) {
    currentEditId = id;
    const modal = document.getElementById('editModal');
    const content = document.getElementById('editModalContent');
    content.innerHTML = `<div class="text-center py-8"><div class="loading-spinner mx-auto"></div><p class="mt-4">Memuat data...</p></div>`;
    modal.classList.remove('hidden');
    modal.classList.add('flex', 'modal-animate');
    document.body.style.overflow = 'hidden';
    
    fetch(`/admin/peminjaman-ruangan/${id}/edit-data`, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) { renderEditForm(data.data); }
        else { content.innerHTML = `<div class="text-center py-8 text-red-500">Gagal memuat data: ${data.message}</div>`; }
    })
    .catch(error => { content.innerHTML = `<div class="text-center py-8 text-red-500">Terjadi kesalahan: ${error.message}</div>`; });
}

function renderEditForm(data) {
    const ruanganOptions = data.ruanganOptions || [];
    const statusRealtimeOptions = [
        { value: 'akan_datang', label: ' Akan Datang' }, { value: 'berlangsung', label: ' Berlangsung' },
        { value: 'selesai', label: ' Selesai' }
    ];
    const currentStatusRealtime = data.status_real_time || 'menunggu';
    const isRejectedOrCancelled = (data.status === 'ditolak' || data.status === 'dibatalkan');
    
    const html = `
        <form id="editForm" onsubmit="event.preventDefault(); submitEditForm();">
            <input type="hidden" name="_token" value="${csrfToken}">
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="id" value="${data.id}">
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Acara / Kegiatan <span class="text-red-500">*</span></label><input type="text" name="acara" value="${escapeHtml(data.acara)}" class="w-full border border-gray-300 rounded-lg px-3 py-2" required></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Ruangan <span class="text-red-500">*</span></label><select name="ruangan_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required><option value="">Pilih Ruangan</option>${ruanganOptions.map(r => `<option value="${r.id}" ${r.id == data.ruangan_id ? 'selected' : ''}>${escapeHtml(r.kode_ruangan)} - ${escapeHtml(r.nama_ruangan)}</option>`).join('')}</select></div>
                <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label><input type="date" name="tanggal" value="${data.tanggal || ''}" class="w-full border border-gray-300 rounded-lg px-3 py-2" required></div><div><label class="block text-sm font-medium text-gray-700 mb-1">Hari <span class="text-red-500">*</span></label><input type="text" name="hari" value="${escapeHtml(data.hari)}" class="w-full border border-gray-300 rounded-lg px-3 py-2" required></div></div>
                <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label><input type="time" name="jam_mulai" value="${data.jam_mulai || ''}" class="w-full border border-gray-300 rounded-lg px-3 py-2" required></div><div><label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label><input type="time" name="jam_selesai" value="${data.jam_selesai || ''}" class="w-full border border-gray-300 rounded-lg px-3 py-2" required></div></div>
                <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Peserta</label><input type="number" name="jumlah_peserta" value="${data.jumlah_peserta || 0}" class="w-full border border-gray-300 rounded-lg px-3 py-2" min="1"></div><div><label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label><select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 status-select" required><option value="menunggu" ${data.status == 'menunggu' ? 'selected' : ''}>Menunggu</option><option value="disetujui" ${data.status == 'disetujui' ? 'selected' : ''}>Disetujui</option><option value="ditolak" ${data.status == 'ditolak' ? 'selected' : ''}>Ditolak</option><option value="dibatalkan" ${data.status == 'dibatalkan' ? 'selected' : ''}>Dibatalkan</option><option value="selesai" ${data.status == 'selesai' ? 'selected' : ''}>Selesai</option></select></div></div>
                <div class="border-t border-gray-200 pt-4 mt-2" id="statusRealtimeContainer">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-clock text-teal-600 mr-1"></i> Status Real-Time</label>
                    ${isRejectedOrCancelled ? `<div class="bg-gray-100 rounded-lg px-3 py-2 text-gray-500"><i class="fas fa-info-circle mr-1"></i> Status real-time tidak tersedia karena peminjaman ${data.status === 'ditolak' ? 'ditolak' : 'dibatalkan'}</div><input type="hidden" name="status_real_time" value="">` : `<select name="status_real_time" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500">${statusRealtimeOptions.map(opt => `<option value="${opt.value}" ${opt.value == currentStatusRealtime ? 'selected' : ''}>${opt.label}</option>`).join('')}</select><p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i> Status real-time dapat diubah kapan saja secara manual</p>`}
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label><textarea name="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2">${escapeHtml(data.keterangan || '')}</textarea></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-sticky-note mr-1 text-teal-600"></i> Catatan Internal</label><textarea name="catatan" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 font-mono text-sm">${escapeHtml(data.catatan || '')}</textarea><p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i> Catatan internal untuk peminjaman ini</p></div>
                <div id="alasanPenolakanContainer" style="display: ${data.status == 'ditolak' ? 'block' : 'none'};"><label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label><textarea name="alasan_penolakan" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2">${escapeHtml(data.alasan_penolakan || '')}</textarea></div>
            </div>
        </form>
    `;
    document.getElementById('editModalContent').innerHTML = html;
    
    const statusSelect = document.querySelector('#editModalContent select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const alasanContainer = document.getElementById('alasanPenolakanContainer');
            if (alasanContainer) alasanContainer.style.display = this.value === 'ditolak' ? 'block' : 'none';
            const statusRealtimeContainer = document.getElementById('statusRealtimeContainer');
            if (statusRealtimeContainer) {
                if (this.value === 'ditolak' || this.value === 'dibatalkan') {
                    statusRealtimeContainer.innerHTML = `<label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-clock text-teal-600 mr-1"></i> Status Real-Time</label><div class="bg-gray-100 rounded-lg px-3 py-2 text-gray-500"><i class="fas fa-info-circle mr-1"></i> Status real-time tidak tersedia karena peminjaman ${this.value === 'ditolak' ? 'ditolak' : 'dibatalkan'}</div><input type="hidden" name="status_real_time" value="">`;
                } else {
                    statusRealtimeContainer.innerHTML = `<label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-clock text-teal-600 mr-1"></i> Status Real-Time</label><select name="status_real_time" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-teal-500 focus:border-teal-500"><option value="akan_datang">Akan Datang</option><option value="berlangsung">Berlangsung</option><option value="selesai">Selesai</option></select><p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle mr-1"></i> Status real-time dapat diubah kapan saja secara manual</p>`;
                }
            }
        });
    }
}

function submitEditForm() {
    const form = document.getElementById('editForm');
    if (!form) { Swal.fire({ icon: 'error', title: 'Error!', text: 'Form edit tidak ditemukan.' }); return; }
    const formData = new FormData(form);
    const id = formData.get('id');
    Swal.fire({ title: 'Menyimpan...', text: 'Mohon tunggu', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    fetch(`/admin/peminjaman-ruangan/${id}`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }, body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) { Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Data peminjaman berhasil diperbarui', showConfirmButton: false, timer: 1500 }).then(() => { closeEditModal(); location.reload(); }); }
        else { Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Terjadi kesalahan saat menyimpan data' }); }
    })
    .catch(error => { Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan pada server: ' + error.message }); });
}

// ==================== FUNGSI HAPUS ====================
function showDeleteModal(id, acaraName) {
    currentDeleteId = id;
    document.getElementById('deleteAcaraName').textContent = acaraName;
    document.getElementById('deleteInfo').textContent = 'ID: #' + id;
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex', 'modal-animate');
    document.body.style.overflow = 'hidden';
}

function confirmDeleteAction() {
    if (!currentDeleteId) return;
    Swal.fire({ title: 'Menghapus...', text: 'Mohon tunggu', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    fetch(`/admin/peminjaman-ruangan/api/${currentDeleteId}`, { method: 'DELETE', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' } })
    .then(response => response.json())
    .then(data => {
        if (data.success) { Swal.fire({ icon: 'success', title: 'Terhapus!', text: 'Data peminjaman berhasil dihapus', showConfirmButton: false, timer: 1500 }).then(() => { closeDeleteModal(); location.reload(); }); }
        else { Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Terjadi kesalahan saat menghapus data' }); }
    })
    .catch(error => { Swal.fire({ icon: 'error', title: 'Gagal!', text: error.message || 'Terjadi kesalahan pada server' }); });
}

// ==================== FUNGSI TUTUP MODAL ====================
function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex', 'modal-animate');
        modal.style.opacity = '';
        document.getElementById('modalContent').innerHTML = `<div class="text-center py-8"><div class="loading-spinner mx-auto"></div><p class="mt-4">Memuat data...</p></div>`;
        document.body.style.overflow = 'auto';
    }, 200);
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex', 'modal-animate');
        modal.style.opacity = '';
        document.getElementById('editModalContent').innerHTML = `<div class="text-center py-8"><div class="loading-spinner mx-auto"></div><p class="mt-4">Memuat data...</p></div>`;
        document.body.style.overflow = 'auto';
        currentEditId = null;
    }, 200);
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.style.opacity = '0';
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex', 'modal-animate');
        modal.style.opacity = '';
        document.body.style.overflow = 'auto';
        currentDeleteId = null;
    }, 200);
}

function escapeHtml(text) { if (!text) return ''; const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }

// Event Listeners
document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeDetailModal(); closeEditModal(); closeDeleteModal(); } });
window.addEventListener('click', (e) => { const detailModal = document.getElementById('detailModal'); const editModal = document.getElementById('editModal'); const deleteModal = document.getElementById('deleteModal'); if (detailModal && e.target === detailModal) closeDetailModal(); if (editModal && e.target === editModal) closeEditModal(); if (deleteModal && e.target === deleteModal) closeDeleteModal(); });
document.addEventListener('DOMContentLoaded', function() { document.querySelectorAll('button, a').forEach(btn => { btn.addEventListener('click', function(e) { if (!e.target.closest('a[href]')) { this.style.transform = 'scale(0.98)'; setTimeout(() => { this.style.transform = ''; }, 150); } }); }); });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush