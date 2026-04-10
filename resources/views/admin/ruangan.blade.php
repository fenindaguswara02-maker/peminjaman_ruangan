@extends('layouts.admin')

@section('title', 'Manajemen Ruangan')
@section('page-title', 'Manajemen Ruangan')

@section('content')

{{-- ================= STATISTIK ================= --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-600">
        <h3 class="text-sm text-gray-600">Total Ruangan</h3>
        <p class="text-3xl font-bold">{{ $statistics['total'] }}</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-green-500">
        <h3 class="text-sm text-gray-600">Tersedia</h3>
        <p class="text-3xl font-bold text-green-600">{{ $statistics['tersedia'] }}</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-yellow-500">
        <h3 class="text-sm text-gray-600">Dibooking</h3>
        <p class="text-3xl font-bold text-yellow-600">{{ $statistics['dibooking'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Dikelola pegawai</p>
    </div>
    <div class="bg-white p-6 rounded-xl shadow border-l-4 border-red-500">
        <h3 class="text-sm text-gray-600">Maintenance</h3>
        <p class="text-3xl font-bold text-red-600">{{ $statistics['maintenance'] }}</p>
        <p class="text-xs text-gray-500 mt-1">Dikelola pegawai</p>
    </div>
</div>

{{-- ================= TABEL ================= --}}
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Daftar Ruangan</h2>
                <p class="text-gray-600 text-sm mt-1">Total {{ $ruangan->total() }} ruangan</p>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    Status ruangan dikelola oleh pegawai
                </p>
            </div>
            <button type="button" onclick="openCreateModal()"
                class="mt-3 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center">
                <i class="fas fa-plus mr-2"></i> Tambah Ruangan
            </button>
        </div>
        
        {{-- Search & Filter --}}
        <form method="GET" action="{{ route('admin.ruangan.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="md:col-span-8 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="Cari kode atau nama ruangan...">
            <select name="status" class="md:col-span-3 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Semua Status</option>
                <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="dibooking" {{ request('status') == 'dibooking' ? 'selected' : '' }}>Dibooking</option>
                <option value="dipakai" {{ request('status') == 'dipakai' ? 'selected' : '' }}>Dipakai</option>
                <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
            </select>
            <button type="submit" class="md:col-span-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center justify-center p-2">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

    @if($ruangan->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fasilitas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($ruangan as $item)
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ ($ruangan->currentPage() - 1) * $ruangan->perPage() + $loop->iteration }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-blue-600">{{ $item->kode_ruangan }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ $item->nama_ruangan }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $item->kapasitas }} orang
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs truncate">
                            @if($item->fasilitas)
                                {{ $item->fasilitas }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusClasses = [
                                'tersedia' => 'bg-green-100 text-green-800',
                                'dibooking' => 'bg-yellow-100 text-yellow-800',
                                'dipakai' => 'bg-purple-100 text-purple-800',
                                'maintenance' => 'bg-red-100 text-red-800'
                            ];
                            $statusIcons = [
                                'tersedia' => 'fa-check-circle',
                                'dibooking' => 'fa-calendar-alt',
                                'dipakai' => 'fa-users',
                                'maintenance' => 'fa-tools'
                            ];
                            $statusLabels = [
                                'tersedia' => 'Tersedia',
                                'dibooking' => 'Dibooking',
                                'dipakai' => 'Dipakai',
                                'maintenance' => 'Maintenance'
                            ];
                        @endphp
                        <div>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $statusClasses[$item->status] ?? 'bg-gray-100 text-gray-800' }}">
                                <i class="fas {{ $statusIcons[$item->status] ?? 'fa-info-circle' }} mr-1"></i>
                                {{ $statusLabels[$item->status] ?? $item->status }}
                            </span>
                            @if(in_array($item->status, ['dibooking', 'dipakai', 'maintenance']))
                                <div class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-lock mr-1"></i>Dikelola pegawai
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button type="button" onclick="openDetailModal({{ $item->id }})"
                                class="text-blue-600 hover:text-blue-900 px-2 py-1 rounded hover:bg-blue-50"
                                title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" onclick="openEditModal({{ $item->id }})"
                                class="text-yellow-600 hover:text-yellow-900 px-2 py-1 rounded hover:bg-yellow-50"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" onclick="deleteRuangan({{ $item->id }}, '{{ $item->kode_ruangan }}')"
                                class="text-red-600 hover:text-red-900 px-2 py-1 rounded hover:bg-red-50"
                                title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Menampilkan {{ $ruangan->firstItem() ?: 0 }} sampai {{ $ruangan->lastItem() ?: 0 }} dari {{ $ruangan->total() }} ruangan
            </div>
            <div>
                {{ $ruangan->links() }}
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <i class="fas fa-door-closed text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-500">Tidak ada data ruangan</p>
        <button type="button" onclick="openCreateModal()"
            class="mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
            Tambah Ruangan Pertama
        </button>
    </div>
    @endif
</div>

{{-- ================= MODAL CREATE ================= --}}
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-lg">
        <div class="px-6 py-4 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">Tambah Ruangan Baru</h2>
                <button type="button" onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <form id="createForm" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Ruangan *</label>
                    <input type="text" name="kode_ruangan" id="create_kode_ruangan" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: R001">
                    <div id="create_kode_error" class="text-red-500 text-xs mt-1 hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i> Kode ruangan wajib diisi
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan *</label>
                    <input type="text" name="nama_ruangan" id="create_nama" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: Ruang Rapat A">
                    <div id="create_nama_error" class="text-red-500 text-xs mt-1 hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i> Nama ruangan wajib diisi
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas *</label>
                    <input type="number" name="kapasitas" id="create_kapasitas" min="1" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="10">
                    <div id="create_kapasitas_error" class="text-red-500 text-xs mt-1 hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i> Kapasitas wajib diisi (minimal 1)
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi *</label>
                    <input type="text" name="lokasi" id="create_lokasi" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: Lantai 2 Gedung A">
                    <div id="create_lokasi_error" class="text-red-500 text-xs mt-1 hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i> Lokasi ruangan wajib diisi
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <input type="hidden" name="status" value="tersedia">
                    <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Tersedia (Default)
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Status ruangan akan diatur oleh pegawai</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Ruangan</label>
                    <input type="file" name="gambar" accept="image/*" id="create_gambar"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Maksimal 2MB, format: JPG, PNG, GIF (Opsional)</p>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fasilitas *</label>
                <textarea name="fasilitas" id="create_fasilitas" rows="3" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Deskripsi fasilitas yang tersedia"></textarea>
                <div id="create_fasilitas_error" class="text-red-500 text-xs mt-1 hidden">
                    <i class="fas fa-exclamation-circle mr-1"></i> Fasilitas ruangan wajib diisi
                </div>
            </div>
            
            <div class="pt-4 border-t flex justify-end space-x-3">
                <button type="button" onclick="closeCreateModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Simpan Ruangan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================= MODAL EDIT ================= --}}
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-xl shadow-lg">
        <div class="px-6 py-4 border-b">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800">Edit Ruangan</h2>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <form id="editForm" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="editId" name="id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Ruangan *</label>
                    <input type="text" id="editKode" name="kode_ruangan" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div id="edit_kode_error" class="text-red-500 text-xs mt-1 hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i> Kode ruangan wajib diisi
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan *</label>
                    <input type="text" id="editNama" name="nama_ruangan" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div id="edit_nama_error" class="text-red-500 text-xs mt-1 hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i> Nama ruangan wajib diisi
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas *</label>
                    <input type="number" id="editKapasitas" name="kapasitas" min="1" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div id="edit_kapasitas_error" class="text-red-500 text-xs mt-1 hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i> Kapasitas wajib diisi (minimal 1)
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi *</label>
                    <input type="text" id="editLokasi" name="lokasi" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: Lantai 2 Gedung A">
                    <div id="edit_lokasi_error" class="text-red-500 text-xs mt-1 hidden">
                        <i class="fas fa-exclamation-circle mr-1"></i> Lokasi ruangan wajib diisi
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <input type="hidden" id="editStatus" name="status">
                    <div id="editStatusDisplay" class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg">
                        {{-- Akan diisi JavaScript --}}
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Status ruangan hanya dapat diubah oleh pegawai</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Ruangan</label>
                    <input type="file" name="gambar" accept="image/*" id="edit_gambar"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div id="currentImage" class="mt-2"></div>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah gambar (Opsional)</p>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fasilitas *</label>
                <textarea name="fasilitas" id="editFasilitas" rows="3" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                <div id="edit_fasilitas_error" class="text-red-500 text-xs mt-1 hidden">
                    <i class="fas fa-exclamation-circle mr-1"></i> Fasilitas ruangan wajib diisi
                </div>
            </div>
            
            <div class="pt-4 border-t flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Update Ruangan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================= MODAL DETAIL ================= --}}
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-xl shadow-lg max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-door-open text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Detail Ruangan</h2>
                        <p class="text-sm text-gray-600">Informasi lengkap tentang ruangan</p>
                    </div>
                </div>
                <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div id="detailContent" class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="mt-4 text-gray-600">Memuat data...</p>
            </div>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button type="button" onclick="closeDetailModal()"
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Tutup
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Base URL dan CSRF Token
const BASE_URL = '{{ url("/") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

// State variables
let currentRuanganId = null;

// Status configuration for display
const statusConfig = {
    'tersedia': { color: 'green', icon: 'check-circle', label: 'Tersedia' },
    'dibooking': { color: 'yellow', icon: 'calendar-alt', label: 'Dibooking' },
    'dipakai': { color: 'purple', icon: 'users', label: 'Dipakai' },
    'maintenance': { color: 'red', icon: 'tools', label: 'Maintenance' }
};

// ===================== VALIDASI FUNCTIONS =====================
function validateField(inputId, errorId, message) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    const value = input.value.trim();
    
    if (value === '') {
        error.classList.remove('hidden');
        input.classList.add('border-red-500');
        return false;
    } else {
        error.classList.add('hidden');
        input.classList.remove('border-red-500');
        return true;
    }
}

function validateNumberField(inputId, errorId, message, min = 1) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    const value = input.value.trim();
    
    if (value === '') {
        error.classList.remove('hidden');
        error.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> ${message}`;
        input.classList.add('border-red-500');
        return false;
    } else if (parseInt(value) < min) {
        error.classList.remove('hidden');
        error.innerHTML = `<i class="fas fa-exclamation-circle mr-1"></i> ${message} (minimal ${min})`;
        input.classList.add('border-red-500');
        return false;
    } else {
        error.classList.add('hidden');
        input.classList.remove('border-red-500');
        return true;
    }
}

function validateAllFields(type) {
    let isValid = true;
    
    if (type === 'create') {
        // Validasi kode ruangan
        if (!validateField('create_kode_ruangan', 'create_kode_error', 'Kode ruangan wajib diisi')) isValid = false;
        
        // Validasi nama ruangan
        if (!validateField('create_nama', 'create_nama_error', 'Nama ruangan wajib diisi')) isValid = false;
        
        // Validasi kapasitas
        if (!validateNumberField('create_kapasitas', 'create_kapasitas_error', 'Kapasitas wajib diisi', 1)) isValid = false;
        
        // Validasi lokasi
        if (!validateField('create_lokasi', 'create_lokasi_error', 'Lokasi ruangan wajib diisi')) isValid = false;
        
        // Validasi fasilitas
        if (!validateField('create_fasilitas', 'create_fasilitas_error', 'Fasilitas ruangan wajib diisi')) isValid = false;
    } else {
        // Validasi kode ruangan edit
        if (!validateField('editKode', 'edit_kode_error', 'Kode ruangan wajib diisi')) isValid = false;
        
        // Validasi nama ruangan edit
        if (!validateField('editNama', 'edit_nama_error', 'Nama ruangan wajib diisi')) isValid = false;
        
        // Validasi kapasitas edit
        if (!validateNumberField('editKapasitas', 'edit_kapasitas_error', 'Kapasitas wajib diisi', 1)) isValid = false;
        
        // Validasi lokasi edit
        if (!validateField('editLokasi', 'edit_lokasi_error', 'Lokasi ruangan wajib diisi')) isValid = false;
        
        // Validasi fasilitas edit
        if (!validateField('editFasilitas', 'edit_fasilitas_error', 'Fasilitas ruangan wajib diisi')) isValid = false;
    }
    
    return isValid;
}

// ===================== LIVE VALIDATION EVENT LISTENERS =====================
// Create modal live validation
document.getElementById('create_kode_ruangan')?.addEventListener('input', function() {
    validateField('create_kode_ruangan', 'create_kode_error', 'Kode ruangan wajib diisi');
});
document.getElementById('create_nama')?.addEventListener('input', function() {
    validateField('create_nama', 'create_nama_error', 'Nama ruangan wajib diisi');
});
document.getElementById('create_kapasitas')?.addEventListener('input', function() {
    validateNumberField('create_kapasitas', 'create_kapasitas_error', 'Kapasitas wajib diisi', 1);
});
document.getElementById('create_lokasi')?.addEventListener('input', function() {
    validateField('create_lokasi', 'create_lokasi_error', 'Lokasi ruangan wajib diisi');
});
document.getElementById('create_fasilitas')?.addEventListener('input', function() {
    validateField('create_fasilitas', 'create_fasilitas_error', 'Fasilitas ruangan wajib diisi');
});

// Edit modal live validation (akan diaktifkan setelah data dimuat)
function attachEditValidation() {
    document.getElementById('editKode')?.addEventListener('input', function() {
        validateField('editKode', 'edit_kode_error', 'Kode ruangan wajib diisi');
    });
    document.getElementById('editNama')?.addEventListener('input', function() {
        validateField('editNama', 'edit_nama_error', 'Nama ruangan wajib diisi');
    });
    document.getElementById('editKapasitas')?.addEventListener('input', function() {
        validateNumberField('editKapasitas', 'edit_kapasitas_error', 'Kapasitas wajib diisi', 1);
    });
    document.getElementById('editLokasi')?.addEventListener('input', function() {
        validateField('editLokasi', 'edit_lokasi_error', 'Lokasi ruangan wajib diisi');
    });
    document.getElementById('editFasilitas')?.addEventListener('input', function() {
        validateField('editFasilitas', 'edit_fasilitas_error', 'Fasilitas ruangan wajib diisi');
    });
}

// ===================== MODAL FUNCTIONS =====================

// CREATE MODAL
window.openCreateModal = function() { 
    document.getElementById('createModal').classList.remove('hidden');
    document.getElementById('createModal').classList.add('flex');
    // Reset all errors
    document.querySelectorAll('#createForm .text-red-500').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#createForm input, #createForm textarea').forEach(el => {
        el.classList.remove('border-red-500');
    });
}

window.closeCreateModal = function() { 
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createModal').classList.remove('flex');
    document.getElementById('createForm').reset();
    document.querySelectorAll('#createForm .text-red-500').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#createForm input, #createForm textarea').forEach(el => {
        el.classList.remove('border-red-500');
    });
}

// EDIT MODAL
window.openEditModal = function(id) {
    currentRuanganId = id;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
    
    // Reset errors
    document.querySelectorAll('#editForm .text-red-500').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#editForm input, #editForm textarea').forEach(el => {
        el.classList.remove('border-red-500');
    });
    
    // Tampilkan loading
    document.getElementById('editKode').disabled = true;
    document.getElementById('editNama').disabled = true;
    document.getElementById('editKapasitas').disabled = true;
    document.getElementById('editLokasi').disabled = true;
    document.getElementById('editFasilitas').disabled = true;
    
    // Fetch data ruangan
    fetch(`${BASE_URL}/admin/ruangan/${id}/detail`)
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            const data = res.data;
            
            document.getElementById('editId').value = id;
            document.getElementById('editKode').value = data.kode_ruangan;
            document.getElementById('editNama').value = data.nama_ruangan;
            document.getElementById('editKapasitas').value = data.kapasitas;
            document.getElementById('editFasilitas').value = data.fasilitas || '';
            document.getElementById('editLokasi').value = data.lokasi || '';
            
            document.getElementById('editStatus').value = data.status;
            
            const config = statusConfig[data.status] || { color: 'gray', icon: 'info-circle', label: data.status };
            document.getElementById('editStatusDisplay').innerHTML = `
                <span class="inline-flex items-center px-3 py-1 text-sm font-semibold rounded-full bg-${config.color}-100 text-${config.color}-800 border border-${config.color}-200">
                    <i class="fas fa-${config.icon} mr-2"></i>
                    ${config.label}
                </span>
            `;
            
            const currentImageDiv = document.getElementById('currentImage');
            if (data.gambar) {
                currentImageDiv.innerHTML = `
                    <p class="text-sm text-gray-600 mb-1">Gambar saat ini:</p>
                    <img src="${BASE_URL}/storage/${data.gambar}" class="w-20 h-20 object-cover rounded border">
                `;
            } else {
                currentImageDiv.innerHTML = '<p class="text-sm text-gray-500">Tidak ada gambar</p>';
            }
        } else {
            showNotification('Gagal memuat data ruangan', 'error');
            closeEditModal();
        }
    })
    .catch(err => {
        console.error('Error:', err);
        showNotification('Terjadi kesalahan saat memuat data', 'error');
        closeEditModal();
    })
    .finally(() => {
        document.getElementById('editKode').disabled = false;
        document.getElementById('editNama').disabled = false;
        document.getElementById('editKapasitas').disabled = false;
        document.getElementById('editLokasi').disabled = false;
        document.getElementById('editFasilitas').disabled = false;
        
        // Attach validation after data loaded
        attachEditValidation();
    });
}

window.closeEditModal = function() { 
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
    document.getElementById('editForm').reset();
    document.getElementById('currentImage').innerHTML = '';
    document.getElementById('editStatusDisplay').innerHTML = '';
    document.querySelectorAll('#editForm .text-red-500').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#editForm input, #editForm textarea').forEach(el => {
        el.classList.remove('border-red-500');
    });
    currentRuanganId = null;
}

// DETAIL MODAL
window.openDetailModal = function(id) {
    document.getElementById('detailModal').classList.remove('hidden');
    document.getElementById('detailModal').classList.add('flex');
    
    document.getElementById('detailContent').innerHTML = `
        <div class="text-center py-8">
            <div class="loading-spinner mx-auto"></div>
            <p class="mt-4 text-gray-600">Memuat data...</p>
        </div>
    `;
    
    fetch(`${BASE_URL}/admin/ruangan/${id}/detail`)
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            document.getElementById('detailContent').innerHTML = generateDetailHtml(res.data);
        } else {
            throw new Error('Gagal memuat data');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        document.getElementById('detailContent').innerHTML = `
            <div class="text-center py-12">
                <i class="fas fa-exclamation-triangle text-5xl text-red-400 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Gagal Memuat Data</h3>
                <p class="text-gray-600">Terjadi kesalahan saat memuat detail ruangan</p>
                <button onclick="closeDetailModal()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg">
                    Tutup
                </button>
            </div>
        `;
    });
}

window.closeDetailModal = function() { 
    document.getElementById('detailModal').classList.add('hidden');
    document.getElementById('detailModal').classList.remove('flex');
}

// ===================== HELPER FUNCTIONS =====================

function generateDetailHtml(data) {
    function showValue(value) {
        return value && value !== '' && value !== '-' ? value : '<span class="text-gray-400 italic">Tidak ada</span>';
    }
    
    const config = statusConfig[data.status] || { color: 'gray', icon: 'info-circle', label: data.status };
    
    return `
        <div class="space-y-6">
            <div class="text-center border-b pb-4 mb-2">
                <span class="px-4 py-2 bg-blue-100 text-blue-800 text-lg font-bold rounded-full inline-block mb-3">
                    ${data.kode_ruangan}
                </span>
                <h3 class="text-2xl font-bold text-gray-900">${data.nama_ruangan}</h3>
                
                <div class="mt-2 flex items-center justify-center text-gray-600">
                    <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                    <span class="text-base">${showValue(data.lokasi)}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Kapasitas</p>
                            <p class="text-lg font-bold text-gray-900">${data.kapasitas} <span class="text-sm font-normal text-gray-600">orang</span></p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-${config.color}-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-${config.icon} text-${config.color}-600"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Status</p>
                            <div class="mt-1">
                                <span class="px-3 py-1.5 inline-flex items-center text-sm font-semibold rounded-full bg-${config.color}-100 text-${config.color}-800 border border-${config.color}-200">
                                    <i class="fas fa-${config.icon} mr-2"></i>
                                    ${config.label}
                                </span>
                                ${['dibooking', 'dipakai', 'maintenance'].includes(data.status) ? `
                                    <div class="text-xs text-gray-500 mt-2">
                                        <i class="fas fa-lock mr-1"></i>Dikelola oleh pegawai
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-couch mr-2 text-blue-600"></i>
                    Fasilitas Ruangan
                </h4>
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <p class="text-gray-700 whitespace-pre-line">${showValue(data.fasilitas)}</p>
                </div>
            </div>

            ${data.gambar ? `
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-image mr-2 text-blue-600"></i>
                    Gambar Ruangan
                </h4>
                <div class="flex justify-center">
                    <img src="${BASE_URL}/storage/${data.gambar}" 
                         class="max-w-full max-h-64 object-cover rounded-lg border-2 border-gray-200 shadow-sm"
                         alt="${data.nama_ruangan}">
                </div>
            </div>
            ` : ''}

            ${data.peminjaman_aktif && data.peminjaman_aktif.length > 0 ? `
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                    Peminjaman Aktif
                </h4>
                <div class="space-y-2">
                    ${data.peminjaman_aktif.map(p => `
                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                            <p class="font-medium">${p.acara}</p>
                            <p class="text-sm text-gray-600">${p.tanggal} | ${p.jam_mulai} - ${p.jam_selesai}</p>
                            <p class="text-xs text-gray-500">Pengaju: ${p.nama_pengaju}</p>
                        </div>
                    `).join('')}
                </div>
            </div>
            ` : ''}

            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-blue-600"></i>
                    Informasi Tambahan
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Dibuat pada</p>
                        <p class="font-medium text-gray-900">${data.tanggal_dibuat || '-'}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Terakhir diperbarui</p>
                        <p class="font-medium text-gray-900">${data.tanggal_diupdate || '-'}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// ===================== FORM HANDLERS =====================

// Create Form
document.getElementById('createForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate all fields
    if (!validateAllFields('create')) {
        showNotification('Semua field wajib diisi!', 'error');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.ruangan.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Ruangan berhasil ditambahkan!', 'success');
            closeCreateModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            if (data.errors) {
                let errorMessage = Object.values(data.errors).flat().join('\n');
                showNotification(errorMessage, 'error');
            } else {
                showNotification(data.message || 'Gagal menambahkan ruangan', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menyimpan data', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Edit Form
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentRuanganId) {
        showNotification('ID ruangan tidak ditemukan', 'error');
        return;
    }
    
    // Validate all fields
    if (!validateAllFields('edit')) {
        showNotification('Semua field wajib diisi!', 'error');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memperbarui...';
    
    const formData = new FormData(this);
    
    fetch(`${BASE_URL}/admin/ruangan/${currentRuanganId}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-HTTP-Method-Override': 'PUT'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Ruangan berhasil diperbarui!', 'success');
            closeEditModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            if (data.errors) {
                let errorMessage = Object.values(data.errors).flat().join('\n');
                showNotification(errorMessage, 'error');
            } else {
                showNotification(data.message || 'Gagal memperbarui ruangan', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat memperbarui data', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Delete Function
window.deleteRuangan = function(id, kode) {
    fetch(`${BASE_URL}/admin/ruangan/${id}/check-status`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.boleh_hapus === false) {
                showNotification(data.message || `Ruangan "${kode}" tidak dapat dihapus.`, 'error');
                return;
            }
            
            if (confirm(`Apakah Anda yakin ingin menghapus ruangan "${kode}"?`)) {
                fetch(`${BASE_URL}/admin/ruangan/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Ruangan berhasil dihapus!', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showNotification(data.message || 'Gagal menghapus ruangan', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Terjadi kesalahan saat menghapus data', 'error');
                });
            }
        } else {
            showNotification(data.message || 'Gagal memeriksa status ruangan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Gagal memeriksa status ruangan', 'error');
    });
}

// ===================== NOTIFICATION FUNCTION =====================
function showNotification(message, type = 'success') {
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'fixed top-5 right-5 z-50 space-y-2';
        document.body.appendChild(container);
    }
    
    const notification = document.createElement('div');
    notification.className = `transform transition-all duration-300 ease-in-out translate-x-0`;
    
    const bgColor = type === 'success' ? 'bg-green-500' : (type === 'error' ? 'bg-red-500' : 'bg-blue-500');
    const icon = type === 'success' ? 'fa-check-circle' : (type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle');
    
    notification.innerHTML = `
        <div class="${bgColor} text-white rounded-lg shadow-lg p-4 min-w-[300px] flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <i class="fas ${icon} text-xl"></i>
                <span class="font-medium">${message}</span>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    container.appendChild(notification);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) notification.remove();
        }, 300);
    }, 3000);
}

// ===================== CLOSE MODALS ON CLICK OUTSIDE =====================

document.querySelectorAll('#createModal, #editModal, #detailModal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            if (this.id === 'createModal') closeCreateModal();
            if (this.id === 'editModal') closeEditModal();
            if (this.id === 'detailModal') closeDetailModal();
        }
    });
});

// Close on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateModal();
        closeEditModal();
        closeDetailModal();
    }
});

// ===================== LOADING SPINNER STYLE =====================
if (!document.getElementById('loading-spinner-style')) {
    const style = document.createElement('style');
    style.id = 'loading-spinner-style';
    style.textContent = `
        .loading-spinner {
            border: 3px solid rgba(0, 0, 0, 0.1);
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
        
        .border-red-500 {
            border-color: #ef4444 !important;
        }
    `;
    document.head.appendChild(style);
}
</script>
@endsection