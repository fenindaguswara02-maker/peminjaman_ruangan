@extends('layouts.pegawai')

@section('title', 'Pegawai - Peminjaman Ruangan')
@section('page-title', 'Manajemen Peminjaman Ruangan')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header dan Filter -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-900 elegant-font">Manajemen Peminjaman Ruangan</h1>
                <p class="text-gray-600">Kelola semua permintaan peminjaman ruangan</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="updateStatusRealTime()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center space-x-2 transition-colors">
                    <i class="fas fa-sync-alt"></i>
                    <span>Update Status</span>
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="filter-status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        <option value="">Semua Status</option>
                        <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                        <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ruangan</label>
                    <select id="filter-ruangan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        <option value="">Semua Ruangan</option>
                        @foreach($ruangan as $room)
                            <option value="{{ $room->id }}" {{ request('ruangan_id') == $room->id ? 'selected' : '' }}>
                                {{ $room->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pengaju</label>
                    <select id="filter-jenis-pengaju" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        <option value="">Semua Jenis</option>
                        <option value="mahasiswa" {{ request('jenis_pengaju') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                        <option value="dosen" {{ request('jenis_pengaju') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                        <option value="staff" {{ request('jenis_pengaju') == 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="tamu" {{ request('jenis_pengaju') == 'tamu' ? 'selected' : '' }}>Tamu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas</label>
                    <input type="text" id="filter-fakultas" placeholder="Masukkan fakultas" value="{{ request('fakultas') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                    <input type="date" id="filter-tanggal-mulai" value="{{ request('tanggal_mulai') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                    <input type="date" id="filter-tanggal-selesai" value="{{ request('tanggal_selesai') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pengaju</label>
                    <input type="text" id="filter-nama-pengaju" placeholder="Masukkan nama" value="{{ request('nama_pengaju') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button onclick="applyFilters()" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center space-x-2 transition-colors">
                    <i class="fas fa-filter"></i>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Total</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-door-open text-blue-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Menunggu</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['menunggu'] }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Disetujui</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['disetujui'] }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-gray-600">Ditolak</h3>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['ditolak'] }}</p>
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
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['selesai'] }}</p>
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
                    <p class="text-2xl font-bold text-gray-600">{{ $stats['dibatalkan'] }}</p>
                </div>
                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-ban text-gray-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="bg-white p-6 rounded-lg shadow mb-6">
        <h3 class="text-lg font-semibold mb-4 text-primary-900 flex items-center">
            <i class="fas fa-clock mr-2 text-yellow-500"></i>
            Permintaan Menunggu Persetujuan
        </h3>
        
        @if($peminjaman->where('status', 'menunggu')->count() > 0)
            <div class="space-y-4">
                @foreach($peminjaman->where('status', 'menunggu') as $item)
                <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border border-yellow-200 transition-colors hover:bg-yellow-100">
                    <div class="flex items-center space-x-4 flex-1">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-door-open text-white"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $item->ruangan->kode_ruangan }} - {{ $item->acara }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $item->nama_pengaju }} 
                                <span class="px-1 py-0.5 bg-blue-100 text-blue-800 text-xs rounded ml-2">
                                    {{ ucfirst($item->jenis_pengaju) }}
                                </span>
                            </p>
                            <p class="text-xs text-cyan-600 mt-1">
                                <i class="fas fa-at mr-1"></i> Username: {{ $item->user->username ?? 'Tidak ada' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $item->fakultas }} • {{ $item->prodi ?: 'Tidak ada prodi' }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }} • 
                                {{ $item->jam_mulai }} - {{ $item->jam_selesai }} • 
                                Peserta: {{ $item->jumlah_peserta }} orang
                            </p>
                            @if($item->keterangan)
                                <p class="text-sm text-gray-500 mt-1">Keterangan: {{ $item->keterangan }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <form action="{{ route('pegawai.peminjaman-ruangan.approve', $item->id) }}" method="POST" class="inline">
                            @csrf
                            @method('POST')
                            <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg font-semibold hover:bg-green-600 flex items-center space-x-2 transition-colors" onclick="return confirm('Setujui peminjaman ini?')">
                                <i class="fas fa-check"></i>
                                <span>Setujui</span>
                            </button>
                        </form>
                        <button onclick="showRejectModalPegawai({{ $item->id }})" class="px-4 py-2 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 flex items-center space-x-2 transition-colors">
                            <i class="fas fa-times"></i>
                            <span>Tolak</span>
                        </button>
                        <button onclick="showDetailModalPegawai({{ $item->id }})" class="px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600 flex items-center space-x-2 transition-colors">
                            <i class="fas fa-eye"></i>
                            <span>Detail</span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                <p class="text-gray-600">Tidak ada permintaan yang menunggu persetujuan</p>
            </div>
        @endif
    </div>

    <!-- Main Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-primary-900">Daftar Semua Peminjaman</h3>
                <div class="flex space-x-2">
                    <input type="text" id="search-input" placeholder="Cari..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors w-64">
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-primary-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Pengaju</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Informasi Kontak</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Ruangan & Acara</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Jadwal</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="peminjaman-table-body">
                    @foreach($peminjaman as $item)
                    <tr class="hover:bg-gray-50 transition-colors"
                        data-detail="{{ json_encode([
                            'id' => $item->id,
                            'username' => $item->user->username ?? '-',
                            'pengaju' => $item->nama_pengaju,
                            'jenis' => $item->jenis_pengaju,
                            'nim' => $item->nim_nip,
                            'fakultas' => $item->fakultas,
                            'prodi' => $item->prodi ?? '-',
                            'email' => $item->email,
                            'telepon' => $item->no_telepon,
                            'ruangan' => $item->ruangan->nama_ruangan,
                            'kode_ruangan' => $item->ruangan->kode_ruangan,
                            'kapasitas' => $item->ruangan->kapasitas,
                            'lokasi' => $item->ruangan->lokasi ?? '-',
                            'acara' => $item->acara,
                            'hari' => $item->hari,
                            'tanggal' => \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
                            'tanggal_mulai' => \Carbon\Carbon::parse($item->tanggal_mulai)->translatedFormat('d F Y'),
                            'tanggal_selesai' => \Carbon\Carbon::parse($item->tanggal_selesai)->translatedFormat('d F Y'),
                            'jam_mulai' => $item->jam_mulai,
                            'jam_selesai' => $item->jam_selesai,
                            'peserta' => $item->jumlah_peserta,
                            'status' => $item->status,
                            'status_real_time' => $item->status_real_time ?? 'akan_datang',
                            'alasan_penolakan' => $item->alasan_penolakan,
                            'keterangan' => $item->keterangan,
                            'catatan' => $item->catatan ?? '', // TAMBAHKAN CATATAN
                            'lampiran_surat' => $item->lampiran_surat,
                            'created_at' => \Carbon\Carbon::parse($item->created_at)->translatedFormat('l, d F Y H:i'),
                        ]) }}">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                    @php
                                        $initial = strtoupper(substr($item->nama_pengaju, 0, 1));
                                    @endphp
                                    <span class="text-white text-sm font-medium">{{ $initial }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item->nama_pengaju }}</div>
                                    <div class="text-xs text-cyan-600">
                                        <i class="fas fa-at mr-1"></i> {{ $item->user->username ?? '-' }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <span class="px-1 py-0.5 bg-blue-100 text-blue-800 text-xs rounded">
                                            {{ ucfirst($item->jenis_pengaju) }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-400">{{ $item->nim_nip }}</div>
                                </div>
                            </div>
                         </div>
                        
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $item->fakultas }}</div>
                            <div class="text-sm text-gray-600">{{ $item->prodi ?: '-' }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $item->email }}</div>
                            <div class="text-xs text-gray-500">{{ $item->no_telepon }}</div>
                         </div>
                        
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $item->ruangan->kode_ruangan }} - {{ $item->ruangan->nama_ruangan }}</div>
                            <div class="text-sm text-gray-900 mt-1">{{ $item->acara }}</div>
                            <div class="text-sm text-gray-500">Peserta: {{ $item->jumlah_peserta }} orang</div>
                            @if($item->lampiran_surat)
                                <a href="{{ route('pegawai.peminjaman-ruangan.download-surat', $item->id) }}" class="text-xs text-blue-600 hover:text-blue-800 flex items-center mt-1 transition-colors">
                                    <i class="fas fa-paperclip mr-1"></i> Surat Peminjaman
                                </a>
                            @endif
                         </div>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="font-medium">{{ $item->hari }}</div>
                            <div class="text-gray-500">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</div>
                            <div class="text-sm text-gray-600">{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</div>
                         </div>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $status = $item->status;
                                $statusConfig = [
                                    'menunggu' => ['color' => 'yellow', 'icon' => 'clock', 'text' => 'Menunggu'],
                                    'disetujui' => ['color' => 'green', 'icon' => 'check', 'text' => 'Disetujui'],
                                    'ditolak' => ['color' => 'red', 'icon' => 'times', 'text' => 'Ditolak'],
                                    'dibatalkan' => ['color' => 'gray', 'icon' => 'ban', 'text' => 'Dibatalkan']
                                ];
                                $config = $statusConfig[$status] ?? $statusConfig['menunggu'];
                            @endphp
                            <span class="px-3 py-1 bg-{{ $config['color'] }}-100 text-{{ $config['color'] }}-800 text-xs rounded-full flex items-center w-fit">
                                <i class="fas fa-{{ $config['icon'] }} mr-1"></i>
                                {{ $config['text'] }}
                            </span>
                            @if($item->status == 'ditolak' && $item->alasan_penolakan)
                                <div class="text-xs text-red-600 mt-1" title="{{ $item->alasan_penolakan }}">
                                    <i class="fas fa-info-circle"></i> Ada alasan penolakan
                                </div>
                            @endif
                         </div>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button onclick="showDetailModalPegawai({{ $item->id }})" class="text-blue-600 hover:text-blue-900 transition-colors" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($item->status == 'menunggu')
                                    <form action="{{ route('pegawai.peminjaman-ruangan.approve', $item->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="text-green-600 hover:text-green-900 transition-colors" title="Setujui" onclick="return confirm('Setujui peminjaman ini?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <button onclick="showRejectModalPegawai({{ $item->id }})" class="text-red-600 hover:text-red-900 transition-colors" title="Tolak">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                            </div>
                         </div>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($peminjaman->isEmpty())
        <div class="text-center py-12">
            <i class="fas fa-door-open text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600">Belum ada data peminjaman ruangan</p>
        </div>
        @endif
    </div>
</div>

<!-- Modal Detail untuk Pegawai (DENGAN CATATAN) -->
<div id="detailModalPegawai" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Detail Peminjaman Ruangan</h3>
            <button onclick="closeDetailModalPegawai()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" id="detailContentPegawai">
            <!-- Content akan diisi oleh JavaScript -->
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat data...</p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button onclick="closeDetailModalPegawai()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Tolak untuk Pegawai -->
<div id="rejectModalPegawai" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <form id="rejectFormPegawai" action="" method="POST">
            @csrf
            @method('POST')
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Tolak Peminjaman</h3>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-3">Berikan alasan penolakan untuk peminjaman ini:</p>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan *</label>
                    <textarea name="alasan_penolakan" id="alasan_penolakan" rows="4" 
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors" 
                              placeholder="Masukkan alasan penolakan..." 
                              required></textarea>
                    <p class="text-xs text-gray-500 mt-1">Minimal 5 karakter, maksimal 1000 karakter</p>
                    <div id="alasan_error" class="text-red-500 text-xs mt-1 hidden"></div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                <button type="button" onclick="closeRejectModalPegawai()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Batal
                </button>
                <button type="submit" onclick="validateAndSubmitReject(event)" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                    <i class="fas fa-times mr-1"></i> Tolak Peminjaman
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Animasi untuk modal */
@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-animate {
    animation: modalFadeIn 0.3s ease-out;
}

/* Loading spinner */
.loading-spinner {
    border: 3px solid rgba(255, 255, 255, 0.3);
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
</style>
@endpush

@push('scripts')
<script>
// CSRF Token untuk AJAX
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Fungsi untuk Modal Detail Pegawai
function showDetailModalPegawai(id) {
    console.log('Opening detail modal for pegawai ID:', id);
    
    // Cari row dengan ID yang sesuai
    const row = document.querySelector(`tr[data-detail*='"id":${id}']`);
    if (!row) {
        alert('Data tidak ditemukan untuk ID: ' + id);
        return;
    }
    
    try {
        // Parse data dari atribut data-detail
        const detailData = JSON.parse(row.getAttribute('data-detail'));
        console.log('Detail data:', detailData);
        
        // Render konten modal
        const html = generateDetailHtml(detailData);
        document.getElementById('detailContentPegawai').innerHTML = html;
        
        // Show modal dengan animasi
        const modal = document.getElementById('detailModalPegawai');
        modal.classList.remove('hidden');
        modal.classList.add('flex', 'modal-animate');
        
    } catch (error) {
        console.error('Error parsing detail data:', error);
        document.getElementById('detailContentPegawai').innerHTML = `
            <div class="text-center py-12">
                <div class="mb-6">
                    <i class="fas fa-exclamation-triangle text-5xl text-red-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Gagal Memuat Data</h3>
                <p class="text-gray-600 mb-6">Terjadi kesalahan saat memuat data detail</p>
                <p class="text-sm text-gray-500 mb-8">ID Peminjaman: #${id}</p>
                <div class="flex justify-center">
                    <button onclick="closeDetailModalPegawai()" 
                            class="px-5 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium flex items-center">
                        <i class="fas fa-times mr-2"></i> Tutup
                    </button>
                </div>
            </div>
        `;
        
        const modal = document.getElementById('detailModalPegawai');
        modal.classList.remove('hidden');
        modal.classList.add('flex', 'modal-animate');
    }
}

// Fungsi untuk generate HTML detail (DENGAN CATATAN)
function generateDetailHtml(data) {
    // Map status ke warna
    const statusColors = {
        'menunggu': 'yellow',
        'disetujui': 'green',
        'ditolak': 'red',
        'dibatalkan': 'gray'
    };
    
    // Map status real-time ke warna
    const realTimeColors = {
        'akan_datang': 'blue',
        'berlangsung': 'orange',
        'selesai': 'purple'
    };
    
    const realTimeText = {
        'akan_datang': 'Akan Datang',
        'berlangsung': 'Berlangsung',
        'selesai': 'Selesai'
    };
    
    const statusColor = statusColors[data.status] || 'gray';
    const realTimeColor = realTimeColors[data.status_real_time] || 'gray';
    
    // CEK APAKAH ADA CATATAN
    const hasCatatan = data.catatan && data.catatan.trim() !== '';
    
    return `
        <div class="space-y-6">
            <!-- Header -->
            <div class="text-center border-b pb-4 mb-4">
                <div class="mb-2">
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">
                        ID: #${data.id}
                    </span>
                </div>
                <h4 class="text-xl font-bold text-primary-900">${escapeHtml(data.acara)}</h4>
                <p class="text-gray-600">${escapeHtml(data.kode_ruangan)} - ${escapeHtml(data.ruangan)}</p>
            </div>

            <!-- Informasi Pengaju -->
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

            <!-- Informasi Ruangan -->
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
                </div>
            </div>

            <!-- Jadwal Acara -->
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
                        <label class="block text-xs text-gray-500">Tanggal Acara</label>
                        <p class="font-medium">${escapeHtml(data.tanggal)}</p>
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

            <!-- Status & Informasi Lainnya -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-primary-600"></i>
                        Status Peminjaman
                    </h5>
                    <div class="mb-4">
                        <span class="px-3 py-1.5 bg-${statusColor}-100 text-${statusColor}-800 text-sm font-semibold rounded-full inline-flex items-center">
                            <i class="fas fa-circle text-${statusColor}-500 text-xs mr-2"></i>
                            ${capitalizeFirstLetter(data.status)}
                        </span>
                    </div>
                    
                    ${data.status === 'disetujui' ? `
                    <div class="mt-3">
                        <label class="block text-xs text-gray-500 mb-1">Status Real-time</label>
                        <span class="px-3 py-1 bg-${realTimeColor}-100 text-${realTimeColor}-800 text-sm rounded-full inline-flex items-center">
                            <i class="fas fa-clock text-${realTimeColor}-500 text-xs mr-2"></i>
                            ${realTimeText[data.status_real_time] || data.status_real_time}
                        </span>
                    </div>
                    ` : ''}

                    ${data.status === 'ditolak' && data.alasan_penolakan ? `
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <label class="block text-xs font-semibold text-red-800 mb-1">Alasan Penolakan</label>
                        <p class="text-sm text-red-700">${escapeHtml(data.alasan_penolakan)}</p>
                    </div>
                    ` : ''}
                </div>

                <!-- Lampiran & Keterangan -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-paperclip mr-2 text-primary-600"></i>
                        Lampiran & Keterangan
                    </h5>
                    
                    ${data.lampiran_surat ? `
                    <div class="mb-4">
                        <label class="block text-xs text-gray-500 mb-2">Lampiran Surat</label>
                        <a href="/pegawai/peminjaman-ruangan/download-surat/${data.id}" 
                           class="inline-flex items-center px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Download Surat Peminjaman
                        </a>
                    </div>
                    ` : ''}

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
                        <p class="text-xs text-gray-400 mt-1">Catatan akan muncul jika ditambahkan</p>
                    </div>
                `}
            </div>

            <!-- Tanggal Pengajuan -->
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

// Helper function untuk escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper function untuk capitalize first letter
function capitalizeFirstLetter(string) {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function closeDetailModalPegawai() {
    const modal = document.getElementById('detailModalPegawai');
    modal.style.opacity = '0';
    modal.style.transform = 'translateY(-20px)';
    modal.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex', 'modal-animate');
        modal.style.opacity = '';
        modal.style.transform = '';
        document.getElementById('detailContentPegawai').innerHTML = `
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat data...</p>
            </div>
        `;
    }, 300);
}

// Fungsi untuk Modal Tolak Pegawai
let currentRejectId = null;

function showRejectModalPegawai(id) {
    console.log('Opening reject modal for pegawai ID:', id);
    
    currentRejectId = id;
    
    const form = document.getElementById('rejectFormPegawai');
    form.action = `/pegawai/peminjaman-ruangan/reject/${id}`;
    form.reset();
    
    // Reset error message
    document.getElementById('alasan_error').classList.add('hidden');
    document.getElementById('alasan_error').textContent = '';
    
    const modal = document.getElementById('rejectModalPegawai');
    modal.classList.remove('hidden');
    modal.classList.add('flex', 'modal-animate');
}

function closeRejectModalPegawai() {
    const modal = document.getElementById('rejectModalPegawai');
    modal.style.opacity = '0';
    modal.style.transform = 'translateY(-20px)';
    modal.style.transition = 'all 0.3s ease';
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex', 'modal-animate');
        modal.style.opacity = '';
        modal.style.transform = '';
        currentRejectId = null;
    }, 300);
}

// Validasi dan submit form penolakan
function validateAndSubmitReject(event) {
    event.preventDefault();
    
    const alasan = document.getElementById('alasan_penolakan').value.trim();
    const errorElement = document.getElementById('alasan_error');
    
    // Reset error
    errorElement.classList.add('hidden');
    errorElement.textContent = '';
    
    // Validasi
    if (alasan.length < 5) {
        errorElement.textContent = 'Alasan penolakan harus minimal 5 karakter';
        errorElement.classList.remove('hidden');
        document.getElementById('alasan_penolakan').focus();
        return false;
    }
    
    if (alasan.length > 1000) {
        errorElement.textContent = 'Alasan penolakan maksimal 1000 karakter';
        errorElement.classList.remove('hidden');
        document.getElementById('alasan_penolakan').focus();
        return false;
    }
    
    // Konfirmasi
    if (!confirm('Apakah Anda yakin ingin menolak peminjaman ini?')) {
        return false;
    }
    
    // Submit form
    const form = document.getElementById('rejectFormPegawai');
    
    // Tampilkan loading
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
    submitBtn.disabled = true;
    
    // Submit form
    form.submit();
    
    return false;
}

// Fungsi Filter
function applyFilters() {
    const status = document.getElementById('filter-status').value;
    const ruangan = document.getElementById('filter-ruangan').value;
    const jenisPengaju = document.getElementById('filter-jenis-pengaju').value;
    const fakultas = document.getElementById('filter-fakultas').value;
    const namaPengaju = document.getElementById('filter-nama-pengaju').value;
    const tanggalMulai = document.getElementById('filter-tanggal-mulai').value;
    const tanggalSelesai = document.getElementById('filter-tanggal-selesai').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (ruangan) params.append('ruangan_id', ruangan);
    if (jenisPengaju) params.append('jenis_pengaju', jenisPengaju);
    if (fakultas) params.append('fakultas', fakultas);
    if (namaPengaju) params.append('nama_pengaju', namaPengaju);
    if (tanggalMulai) params.append('tanggal_mulai', tanggalMulai);
    if (tanggalSelesai) params.append('tanggal_selesai', tanggalSelesai);
    
    window.location.href = '{{ route('pegawai.peminjaman-ruangan.index') }}?' + params.toString();
}

// Fungsi Update Status Real-time
function updateStatusRealTime() {
    if (!confirm('Apakah Anda yakin ingin memperbarui status peminjaman?')) {
        return;
    }
    
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
    btn.disabled = true;
    
    fetch('{{ route('pegawai.peminjaman-ruangan.update-status') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('text/html')) {
            return response.text().then(html => {
                console.error('Server returned HTML instead of JSON');
                throw new Error('Server mengembalikan halaman HTML. Silakan refresh halaman.');
            });
        }
        
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`HTTP error! status: ${response.status}`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Status berhasil diperbarui');
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            throw new Error(data.message || 'Gagal memperbarui status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Gagal memperbarui status: ' + error.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
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
    
    // Apply initial filters from URL
    const urlParams = new URLSearchParams(window.location.search);
    document.getElementById('filter-status').value = urlParams.get('status') || '';
    document.getElementById('filter-ruangan').value = urlParams.get('ruangan_id') || '';
    document.getElementById('filter-jenis-pengaju').value = urlParams.get('jenis_pengaju') || '';
    document.getElementById('filter-fakultas').value = urlParams.get('fakultas') || '';
    document.getElementById('filter-nama-pengaju').value = urlParams.get('nama_pengaju') || '';
    document.getElementById('filter-tanggal-mulai').value = urlParams.get('tanggal_mulai') || '';
    document.getElementById('filter-tanggal-selesai').value = urlParams.get('tanggal_selesai') || '';
    
    // Close modals when clicking outside
    const detailModal = document.getElementById('detailModalPegawai');
    if (detailModal) {
        detailModal.addEventListener('click', function(e) {
            if (e.target === this) closeDetailModalPegawai();
        });
    }
    
    const rejectModal = document.getElementById('rejectModalPegawai');
    if (rejectModal) {
        rejectModal.addEventListener('click', function(e) {
            if (e.target === this) closeRejectModalPegawai();
        });
    }
    
    // Escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDetailModalPegawai();
            closeRejectModalPegawai();
        }
    });
    
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
@endpush