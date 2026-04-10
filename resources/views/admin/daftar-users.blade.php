@extends('layouts.admin')

@section('title', 'Manajemen Akun')
@section('page-title', 'Manajemen Akun')

@section('content')
<!-- Header dengan Tombol Tambah -->
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-primary-900 elegant-font">Kelola Akun</h1>
        <p class="text-gray-600">Buat dan kelola akun user, pegawai, dan administrator</p>
    </div>
    <button onclick="showAddModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center space-x-2 transition-all duration-300 hover:scale-105">
        <i class="fas fa-plus"></i>
        <span>Tambah Akun Baru</span>
    </button>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
    <i class="fas fa-check-circle mr-3"></i>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
    <i class="fas fa-exclamation-circle mr-3"></i>
    <span>{{ session('error') }}</span>
</div>
@endif

<!-- Filter dan Search -->
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Cari nama, username atau email..." 
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
        </div>
        <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">Semua Role</option>
            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
            <option value="pegawai" {{ request('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">Semua Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300">
            <i class="fas fa-filter mr-2"></i>Filter
        </button>
        @if(request()->has('search') || request()->has('role') || request()->has('status'))
        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 flex items-center">
            <i class="fas fa-times mr-2"></i>Reset
        </a>
        @endif
    </form>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-users text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Total Akun</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-user-check text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Aktif</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-user-times text-red-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Nonaktif</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['inactive'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-user-shield text-purple-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Admin</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['admin'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-user-tie text-blue-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">Pegawai</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['pegawai'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                <i class="fas fa-user text-green-600"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500">User</p>
                <p class="text-2xl font-bold text-gray-900">{{ $stats['user'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Akun -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-primary-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">No</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">NIM/NIP</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Terdaftar</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-primary-900 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $index => $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-mono text-sm font-semibold text-blue-700 bg-blue-50 px-2 py-1 rounded inline-block">
                            <i class="fas fa-user-circle mr-1 text-blue-500"></i>
                            {{ $user->username ?? '-' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="w-8 h-8 {{ $user->role === 'admin' ? 'bg-purple-500' : ($user->role === 'pegawai' ? 'bg-blue-500' : 'bg-green-500') }} rounded-full flex items-center justify-center mr-3">
                                <span class="text-white text-sm font-medium">{{ substr($user->name, 0, 2) }}</span>
                            </div>
                            <div class="font-medium text-gray-900">{{ $user->name }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->nim_nip ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : ($user->role === 'pegawai' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }} text-xs rounded-full">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-2 py-1 {{ $user->status === 'active' ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }} text-xs rounded-full transition-colors">
                                {{ $user->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $user->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2">
                            <button onclick="showDetailModal({{ $user->id }})" 
                                    class="text-blue-600 hover:text-blue-900 transition-colors" 
                                    title="Detail">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="showEditModal({{ $user->id }})" 
                                    class="text-yellow-600 hover:text-yellow-900 transition-colors" 
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDelete({{ $user->id }}, '{{ $user->username }}')" 
                                    class="text-red-600 hover:text-red-900 transition-colors" 
                                    title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                        Tidak ada data akun ditemukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($users->hasPages())
<div class="flex justify-between items-center mt-6">
    <div class="text-sm text-gray-700">
        Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} akun
    </div>
    <div class="flex space-x-2">
        {{ $users->links() }}
    </div>
</div>
@endif

<!-- Modal Detail Akun -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-primary-900">Detail Akun</h3>
                        <p class="text-sm text-gray-600">Informasi lengkap pengguna</p>
                    </div>
                </div>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div id="detailContent" class="space-y-4">
                <div class="text-center py-8">
                    <div class="loading-spinner mx-auto"></div>
                    <p class="mt-4 text-gray-600">Memuat data...</p>
                </div>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t border-gray-200">
                <button onclick="closeDetailModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Akun -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-primary-900">Tambah Akun Baru</h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="addUserForm" action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                        <input type="text" name="username" id="addUsername" value="{{ old('username') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               placeholder="Hanya huruf dan angka (contoh: john123)" 
                               pattern="[A-Za-z0-9]+" 
                               title="Username hanya boleh berisi huruf dan angka"
                               onkeyup="validateUsername(this)" required>
                        <small class="text-gray-500 text-xs">Username hanya boleh berisi huruf dan angka</small>
                        <p id="usernameError" class="text-red-600 text-sm mt-1 hidden"></p>
                        @error('username')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" name="name" id="addName" value="{{ old('name') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               placeholder="Masukkan nama lengkap" required>
                        @error('name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" id="addEmail" value="{{ old('email') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               placeholder="Masukkan email" required>
                        @error('email')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIM/NIP</label>
                        <input type="text" name="nim_nip" id="addNim" value="{{ old('nim_nip') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               placeholder="Masukkan NIM/NIP (opsional)">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                        <input type="text" name="no_telepon" id="addPhone" value="{{ old('no_telepon') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               placeholder="Masukkan nomor telepon">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                        <select name="role" id="addRole" onchange="toggleUserFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors" required>
                            <option value="">Pilih Role</option>
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                            <option value="pegawai" {{ old('role') == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Field Khusus User -->
                    <div id="userSpecificFields" class="space-y-4" style="display: none;">
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-graduation-cap text-blue-600 mr-2"></i>
                                Informasi User
                            </h4>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pengaju</label>
                                <select name="jenis_pengaju" id="addJenisPengaju"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    <option value="mahasiswa" {{ old('jenis_pengaju') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                    <option value="dosen" {{ old('jenis_pengaju') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                                    <option value="staff" {{ old('jenis_pengaju') == 'staff' ? 'selected' : '' }}>Staff</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas</label>
                                    <input type="text" name="fakultas" id="addFakultas" value="{{ old('fakultas') }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                           placeholder="Masukkan fakultas">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                                    <input type="text" name="prodi" id="addProdi" value="{{ old('prodi') }}" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                           placeholder="Masukkan program studi">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                        <input type="password" name="password" id="addPassword" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               placeholder="Minimal 6 karakter" 
                               minlength="6" required>
                        @error('password')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password *</label>
                        <input type="password" name="password_confirmation" id="addPasswordConfirmation" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               placeholder="Konfirmasi password" required>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Akun -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-primary-900">Edit Akun</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                        <input type="text" name="username" id="editUsername" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               pattern="[A-Za-z0-9]+"
                               title="Username hanya boleh berisi huruf dan angka"
                               onkeyup="validateUsername(this)" required>
                        <small class="text-gray-500 text-xs">Username hanya boleh berisi huruf dan angka</small>
                        <p id="editUsernameError" class="text-red-600 text-sm mt-1 hidden"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                        <input type="text" name="name" id="editName" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                        <input type="email" name="email" id="editEmail" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">NIM/NIP</label>
                        <input type="text" name="nim_nip" id="editNim" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                               placeholder="Masukkan NIM/NIP (opsional)">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                        <input type="text" name="no_telepon" id="editPhone" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                        <select name="role" id="editRole" onchange="toggleEditUserFields()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors" required>
                            <option value="user">User</option>
                            <option value="pegawai">Pegawai</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <!-- Field Khusus User untuk Edit -->
                    <div id="editUserSpecificFields" class="space-y-4" style="display: none;">
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-graduation-cap text-blue-600 mr-2"></i>
                                Informasi User
                            </h4>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pengaju</label>
                                <select name="jenis_pengaju" id="editJenisPengaju"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    <option value="mahasiswa">Mahasiswa</option>
                                    <option value="dosen">Dosen</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas</label>
                                    <input type="text" name="fakultas" id="editFakultas" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                           placeholder="Masukkan fakultas">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                                    <input type="text" name="prodi" id="editProdi" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                           placeholder="Masukkan program studi">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select name="status" id="editStatus" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                            <i class="fas fa-key text-blue-600 mr-2"></i>
                            Ubah Password (Kosongkan jika tidak ingin mengubah)
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                <input type="password" name="password" id="editPassword" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                       placeholder="Masukkan password baru (opsional)"
                                       minlength="6">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" id="editPasswordConfirmation" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                       placeholder="Konfirmasi password baru">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Update Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Hapus Akun</h3>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin menghapus akun <span id="deleteUserName" class="font-semibold"></span>?</p>
            <div class="flex justify-center space-x-3">
                <button onclick="closeDeleteModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.elegant-font {
    font-family: 'Playfair Display', serif;
}

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
</style>

<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

// ==================== VALIDASI USERNAME ====================
function validateUsername(input) {
    const username = input.value;
    const regex = /^[A-Za-z0-9]*$/;
    const errorElement = input.id === 'addUsername' ? document.getElementById('usernameError') : document.getElementById('editUsernameError');
    
    if (username && !regex.test(username)) {
        if (errorElement) {
            errorElement.textContent = 'Username hanya boleh berisi huruf dan angka (tanpa spasi atau karakter khusus)';
            errorElement.classList.remove('hidden');
        }
        input.setCustomValidity('Username hanya boleh berisi huruf dan angka');
        return false;
    } else {
        if (errorElement) {
            errorElement.classList.add('hidden');
        }
        input.setCustomValidity('');
        return true;
    }
}

// ==================== TOGGLE FIELDS ====================
function toggleUserFields() {
    const role = document.getElementById('addRole').value;
    const userFields = document.getElementById('userSpecificFields');
    
    if (role === 'user') {
        userFields.style.display = 'block';
    } else {
        userFields.style.display = 'none';
    }
}

function toggleEditUserFields() {
    const role = document.getElementById('editRole').value;
    const userFields = document.getElementById('editUserSpecificFields');
    
    if (role === 'user') {
        userFields.style.display = 'block';
    } else {
        userFields.style.display = 'none';
    }
}

// ==================== MODAL TAMBAH ====================
function showAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('addUserForm').reset();
    document.getElementById('userSpecificFields').style.display = 'none';
    const usernameError = document.getElementById('usernameError');
    if (usernameError) usernameError.classList.add('hidden');
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}

// ==================== MODAL EDIT ====================
function showEditModal(userId) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editUserForm');
    
    // Tampilkan loading
    document.getElementById('editUsername').value = 'Loading...';
    document.getElementById('editName').value = 'Loading...';
    document.getElementById('editEmail').value = 'Loading...';
    
    modal.classList.remove('hidden');
    
    fetch(`/admin/users/${userId}/data`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            
            document.getElementById('editUsername').value = user.username || '';
            document.getElementById('editName').value = user.name || '';
            document.getElementById('editEmail').value = user.email || '';
            document.getElementById('editNim').value = user.nim_nip || '';
            document.getElementById('editPhone').value = user.no_telepon || '';
            document.getElementById('editRole').value = user.role || 'user';
            document.getElementById('editStatus').value = user.status || 'active';
            
            // Kosongkan field password (opsional)
            document.getElementById('editPassword').value = '';
            document.getElementById('editPasswordConfirmation').value = '';
            
            if (user.role === 'user') {
                document.getElementById('editJenisPengaju').value = user.jenis_pengaju || 'mahasiswa';
                document.getElementById('editFakultas').value = user.fakultas || '';
                document.getElementById('editProdi').value = user.prodi || '';
                document.getElementById('editUserSpecificFields').style.display = 'block';
            } else {
                document.getElementById('editUserSpecificFields').style.display = 'none';
            }
            
            form.action = `/admin/users/${userId}`;
            
            // Reset validasi username
            const editUsername = document.getElementById('editUsername');
            const editUsernameError = document.getElementById('editUsernameError');
            if (editUsernameError) editUsernameError.classList.add('hidden');
            editUsername.setCustomValidity('');
        } else {
            alert('Gagal memuat data user');
            closeEditModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memuat data');
        closeEditModal();
    });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editUserForm').reset();
}

// ==================== MODAL DETAIL ====================
function showDetailModal(userId) {
    const modal = document.getElementById('detailModal');
    const content = document.getElementById('detailContent');
    
    modal.classList.remove('hidden');
    content.innerHTML = `<div class="text-center py-8"><div class="loading-spinner mx-auto"></div><p class="mt-4">Memuat data...</p></div>`;
    
    fetch(`/admin/users/${userId}/data`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const user = data.user;
            const roleColor = user.role === 'admin' ? 'purple' : (user.role === 'pegawai' ? 'blue' : 'green');
            const roleIcon = user.role === 'admin' ? 'user-shield' : (user.role === 'pegawai' ? 'user-tie' : 'user');
            const statusColor = user.status === 'active' ? 'green' : 'red';
            const statusIcon = user.status === 'active' ? 'check-circle' : 'times-circle';
            const statusText = user.status === 'active' ? 'Aktif' : 'Nonaktif';
            
            const createdDate = user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
            }) : '-';
            
            const updatedDate = user.updated_at ? new Date(user.updated_at).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
            }) : '-';
            
            let html = `
                <div class="text-center border-b pb-4 mb-4">
                    <div class="w-20 h-20 mx-auto bg-${roleColor}-500 rounded-full flex items-center justify-center mb-3">
                        <i class="fas fa-${roleIcon} text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900">${escapeHtml(user.name)}</h3>
                    <p class="text-gray-600">${escapeHtml(user.email)}</p>
                    <p class="text-sm text-blue-600 mt-1"><i class="fas fa-user-circle mr-1"></i> ${escapeHtml(user.username || '-')}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-${roleColor}-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-${roleIcon} text-${roleColor}-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Role</p>
                                <span class="px-3 py-1.5 inline-flex text-sm font-semibold rounded-full bg-${roleColor}-100 text-${roleColor}-800">
                                    ${ucfirst(user.role)}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border">
                        <div class="flex items-start">
                            <div class="w-10 h-10 bg-${statusColor}-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-${statusIcon} text-${statusColor}-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status</p>
                                <span class="px-3 py-1.5 inline-flex text-sm font-semibold rounded-full bg-${statusColor}-100 text-${statusColor}-800">
                                    ${statusText}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg border mb-4">
                    <h4 class="font-semibold mb-3"><i class="fas fa-address-card mr-2 text-blue-600"></i>Informasi Kontak</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-xs text-gray-500">NIM/NIP</p><p class="font-medium">${escapeHtml(user.nim_nip) || '-'}</p></div>
                        <div><p class="text-xs text-gray-500">No. Telepon</p><p class="font-medium">${escapeHtml(user.no_telepon) || '-'}</p></div>
                    </div>
                </div>
            `;
            
            if (user.role === 'user') {
                html += `
                    <div class="bg-gray-50 p-4 rounded-lg border mb-4">
                        <h4 class="font-semibold mb-3"><i class="fas fa-graduation-cap mr-2 text-blue-600"></i>Informasi User</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div><p class="text-xs text-gray-500">Jenis Pengaju</p><p class="font-medium">${ucfirst(user.jenis_pengaju) || '-'}</p></div>
                            <div><p class="text-xs text-gray-500">Fakultas</p><p class="font-medium">${escapeHtml(user.fakultas) || '-'}</p></div>
                            <div><p class="text-xs text-gray-500">Program Studi</p><p class="font-medium">${escapeHtml(user.prodi) || '-'}</p></div>
                        </div>
                    </div>
                `;
            }
            
            html += `
                <div class="bg-gray-50 p-4 rounded-lg border">
                    <h4 class="font-semibold mb-3"><i class="fas fa-info-circle mr-2 text-blue-600"></i>Informasi Tambahan</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div><p class="text-xs text-gray-500">Dibuat pada</p><p class="font-medium">${createdDate}</p></div>
                        <div><p class="text-xs text-gray-500">Terakhir diperbarui</p><p class="font-medium">${updatedDate}</p></div>
                    </div>
                </div>
            `;
            
            content.innerHTML = html;
        } else {
            content.innerHTML = `<div class="text-center py-8 text-red-500">Gagal memuat data</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `<div class="text-center py-8 text-red-500">Terjadi kesalahan</div>`;
    });
}

function closeDetailModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

// ==================== MODAL HAPUS ====================
let currentDeleteId = null;

function confirmDelete(userId, username) {
    currentDeleteId = userId;
    document.getElementById('deleteUserName').textContent = username;
    document.getElementById('deleteForm').action = `/admin/users/${userId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    currentDeleteId = null;
}

// ==================== HELPER ====================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function ucfirst(string) {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
}

// ==================== VALIDASI FORM SEBELUM SUBMIT ====================
document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
    const username = document.getElementById('addUsername');
    const password = document.getElementById('addPassword');
    const passwordConfirmation = document.getElementById('addPasswordConfirmation');
    
    // Validasi username
    if (!validateUsername(username)) {
        e.preventDefault();
        return false;
    }
    
    // Validasi password match
    if (password.value !== passwordConfirmation.value) {
        alert('Password dan konfirmasi password tidak cocok!');
        e.preventDefault();
        return false;
    }
    
    return true;
});

document.getElementById('editUserForm')?.addEventListener('submit', function(e) {
    const username = document.getElementById('editUsername');
    const password = document.getElementById('editPassword');
    const passwordConfirmation = document.getElementById('editPasswordConfirmation');
    
    // Validasi username
    if (!validateUsername(username)) {
        e.preventDefault();
        return false;
    }
    
    // Validasi password match (hanya jika password diisi)
    if (password.value || passwordConfirmation.value) {
        if (password.value !== passwordConfirmation.value) {
            alert('Password dan konfirmasi password tidak cocok!');
            e.preventDefault();
            return false;
        }
        if (password.value.length < 6) {
            alert('Password minimal 6 karakter!');
            e.preventDefault();
            return false;
        }
    }
    
    return true;
});

// ==================== EVENT LISTENERS ====================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
        closeDetailModal();
        closeDeleteModal();
    }
});

document.querySelectorAll('#addModal, #editModal, #detailModal, #deleteModal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            if (this.id === 'addModal') closeAddModal();
            if (this.id === 'editModal') closeEditModal();
            if (this.id === 'detailModal') closeDetailModal();
            if (this.id === 'deleteModal') closeDeleteModal();
        }
    });
});

// Inisialisasi
document.addEventListener('DOMContentLoaded', function() {
    toggleUserFields();
    toggleEditUserFields();
});
</script>
@endsection