@extends('layouts.admin')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('header-actions')
<div class="flex items-center space-x-3">
    <button onclick="openEditProfilModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow">
        <i class="fas fa-edit mr-2"></i>
        Edit Profil
    </button>
    <button onclick="openPasswordModal()" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow">
        <i class="fas fa-key mr-2"></i>
        Ubah Password
    </button>
</div>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-900">Profil Saya</h1>
                <p class="text-gray-600">Informasi lengkap pengguna</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Foto Profil & Info Dasar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Foto Profil -->
                <div class="p-6 text-center border-b border-gray-200">
                    <div class="relative inline-block">
                        <div class="w-32 h-32 rounded-full border-4 border-primary-100 overflow-hidden mx-auto" id="profileImageContainer">
                            @if(auth()->user()->foto)
                                <img src="{{ asset('storage/' . auth()->user()->foto) }}" 
                                     alt="{{ auth()->user()->name }}" 
                                     class="w-full h-full object-cover"
                                     id="profileImage">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center" id="profileImagePlaceholder">
                                    <i class="fas fa-user text-5xl text-primary-600"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Tombol Ganti Foto -->
                        <label for="photoUpload" 
                               class="absolute bottom-0 right-0 w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center shadow-lg hover:bg-primary-700 transition-colors duration-200 cursor-pointer border-2 border-white">
                            <i class="fas fa-camera text-white text-xs"></i>
                            <input type="file" id="photoUpload" class="hidden" accept="image/jpeg,image/png,image/jpg,image/gif">
                        </label>
                        
                        <!-- Tombol Hapus Foto (hanya muncul jika ada foto) -->
                        @if(auth()->user()->foto)
                        <button onclick="confirmDeletePhoto()"
                                class="absolute top-0 right-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors duration-200 cursor-pointer border-2 border-white">
                            <i class="fas fa-trash text-white text-xs"></i>
                        </button>
                        @endif
                        
                        <!-- Progress Upload -->
                        <div id="uploadProgress" class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center hidden">
                            <div class="text-center">
                                <div class="w-8 h-8 border-3 border-white border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>
                    </div>
                    
                    <h2 class="text-xl font-bold text-gray-900 mt-4" id="displayName">{{ auth()->user()->name }}</h2>
                    <p class="text-sm text-gray-500" id="displayEmail">{{ auth()->user()->email }}</p>
                    <p class="text-sm text-blue-600 mt-1" id="displayUsername">
                        <i class="fas fa-user-circle mr-1"></i> {{ auth()->user()->username ?? '-' }}
                    </p>
                </div>
                
                <!-- Info Dasar -->
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Username</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-user-circle text-primary-600 mr-2"></i>
                            {{ auth()->user()->username ?? '-' }}
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Role</p>
                        <p class="font-medium text-gray-900 flex items-center">
                            <i class="fas fa-user-tag text-primary-600 mr-2"></i>
                            {{ ucfirst(auth()->user()->role) }}
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Status</p>
                        <p class="font-medium text-green-600 flex items-center">
                            <i class="fas fa-circle text-green-600 text-xs mr-2"></i>
                            Aktif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Informasi Detail -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informasi Kontak -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-address-card text-primary-600 mr-3"></i>
                        Informasi Kontak
                    </h3>
                    <button onclick="openEditProfilModal()" class="text-sm text-primary-600 hover:text-primary-700 flex items-center">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Username</p>
                            <p class="font-medium text-gray-900 flex items-center" id="viewUsername">
                                <i class="fas fa-user-circle text-gray-400 w-5 mr-2"></i>
                                {{ auth()->user()->username ?? '-' }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Nama Lengkap</p>
                            <p class="font-medium text-gray-900 flex items-center" id="viewName">
                                <i class="fas fa-user text-gray-400 w-5 mr-2"></i>
                                {{ auth()->user()->name ?? '-' }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Email</p>
                            <p class="font-medium text-gray-900 flex items-center break-all" id="viewEmail">
                                <i class="fas fa-envelope text-gray-400 w-5 mr-2"></i>
                                {{ auth()->user()->email ?? '-' }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 mb-1">NIP</p>
                            <p class="font-medium text-gray-900 flex items-center" id="viewNimNip">
                                <i class="fas fa-id-card text-gray-400 w-5 mr-2"></i>
                                {{ auth()->user()->nim_nip ?? 'Tidak ada' }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 mb-1">No. Telepon</p>
                            <p class="font-medium text-gray-900 flex items-center" id="viewNoTelepon">
                                <i class="fas fa-phone-alt text-gray-400 w-5 mr-2"></i>
                                {{ auth()->user()->no_telepon ?? 'Tidak ada' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-primary-600 mr-3"></i>
                        Informasi Tambahan
                    </h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Dibuat pada</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-calendar-plus text-gray-400 w-5 mr-2"></i>
                                {{ auth()->user()->created_at ? \Carbon\Carbon::parse(auth()->user()->created_at)->translatedFormat('j F Y \p\u\k\u\l H.i') : '-' }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Terakhir diperbarui</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-calendar-check text-gray-400 w-5 mr-2"></i>
                                {{ auth()->user()->updated_at ? \Carbon\Carbon::parse(auth()->user()->updated_at)->translatedFormat('j F Y \p\u\k\u\l H.i') : '-' }}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Terakhir login</p>
                            <p class="font-medium text-gray-900 flex items-center">
                                <i class="fas fa-clock text-gray-400 w-5 mr-2"></i>
                                {{ auth()->user()->last_login_at ? \Carbon\Carbon::parse(auth()->user()->last_login_at)->translatedFormat('j F Y \p\u\k\u\l H.i') : now()->translatedFormat('j F Y \p\u\k\u\l H.i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Keamanan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-shield-alt text-primary-600 mr-3"></i>
                        Keamanan Akun
                    </h3>
                    <button onclick="openPasswordModal()" class="text-sm text-primary-600 hover:text-primary-700 flex items-center">
                        <i class="fas fa-key mr-1"></i> Ubah Password
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Strength Meter -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm text-gray-600">Kekuatan Password</p>
                                <span id="passwordStrengthText" class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700">Kuat</span>
                            </div>
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div id="passwordStrengthBar" class="h-full bg-green-500" style="width: 80%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Password terakhir diubah {{ auth()->user()->password_changed_at ? \Carbon\Carbon::parse(auth()->user()->password_changed_at)->diffForHumans() : 'belum pernah' }}
                            </p>
                        </div>

                        <!-- Riwayat Login -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <i class="fas fa-history text-gray-400 mr-2"></i>
                                Riwayat Login Terakhir
                            </p>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">IP Address saat ini</span>
                                    <span class="font-medium text-gray-900">{{ request()->ip() }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Browser</span>
                                    <span class="font-medium text-gray-900">{{ substr(request()->userAgent(), 0, 50) }}...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT PROFIL -->
<div id="editProfilModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="relative mx-auto my-8 p-4 w-full max-w-2xl">
        <div class="bg-white rounded-xl shadow-xl">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-circle text-primary-600 mr-3"></i>
                    Edit Profil
                </h3>
                <button onclick="closeEditProfilModal()" 
                        class="text-gray-400 hover:text-gray-600 text-2xl bg-transparent border-0">
                    &times;
                </button>
            </div>
            
            <!-- Modal Content -->
            <div class="p-6">
                <form id="editProfilForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-5">
                        <!-- Username -->
                        <div>
                            <label for="edit_username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="edit_username" 
                                   name="username" 
                                   value="{{ auth()->user()->username }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   required>
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="edit_name" 
                                   name="name" 
                                   value="{{ auth()->user()->name }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="edit_email" 
                                   name="email" 
                                   value="{{ auth()->user()->email }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   required>
                        </div>

                        <!-- NIP -->
                        <div>
                            <label for="edit_nim_nip" class="block text-sm font-medium text-gray-700 mb-2">
                                NIP
                            </label>
                            <input type="text" 
                                   id="edit_nim_nip" 
                                   name="nim_nip" 
                                   value="{{ auth()->user()->nim_nip }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   placeholder="Masukkan NIP">
                        </div>

                        <!-- No Telepon -->
                        <div>
                            <label for="edit_no_telepon" class="block text-sm font-medium text-gray-700 mb-2">
                                No. Telepon
                            </label>
                            <input type="text" 
                                   id="edit_no_telepon" 
                                   name="no_telepon" 
                                   value="{{ auth()->user()->no_telepon }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   placeholder="Contoh: 081234567890">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 mt-8 pt-5 border-t border-gray-200">
                        <button type="button" 
                                onclick="closeEditProfilModal()"
                                class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                id="submitEditBtn"
                                class="px-8 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL UBAH PASSWORD -->
<div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="relative mx-auto my-8 p-4 w-full max-w-md">
        <div class="bg-white rounded-xl shadow-xl">
            <!-- Modal Header -->
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-key text-primary-600 mr-3"></i>
                    Ubah Password
                </h3>
                <button onclick="closePasswordModal()" 
                        class="text-gray-400 hover:text-gray-600 text-2xl bg-transparent border-0">
                    &times;
                </button>
            </div>
            
            <!-- Modal Content -->
            <div class="p-6">
                <form id="passwordForm">
                    @csrf
                    
                    <div class="space-y-4">
                        <!-- Password Lama -->
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password Lama <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-10"
                                       required>
                                <button type="button" 
                                        onclick="togglePassword('current_password', 'currentPasswordIcon')"
                                        class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye" id="currentPasswordIcon"></i>
                                </button>
                            </div>
                            <p class="text-xs text-red-500 mt-1 hidden" id="currentPasswordError"></p>
                        </div>

                        <!-- Password Baru -->
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-10"
                                       onkeyup="checkPasswordStrength(this.value)"
                                       required>
                                <button type="button" 
                                        onclick="togglePassword('new_password', 'newPasswordIcon')"
                                        class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye" id="newPasswordIcon"></i>
                                </button>
                            </div>
                            
                            <!-- Password Strength Indicator -->
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-gray-500">Kekuatan Password:</span>
                                    <span id="passwordStrength" class="font-medium text-gray-700">Belum dimasukkan</span>
                                </div>
                                <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div id="passwordStrengthProgress" class="h-full bg-gray-300" style="width: 0%"></div>
                                </div>
                                
                                <!-- Password Requirements -->
                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div class="flex items-center text-xs">
                                        <i class="fas fa-circle text-gray-300 mr-1" id="req-length" style="font-size: 6px;"></i>
                                        <span class="text-gray-500" id="req-length-text">Minimal 8 karakter</span>
                                    </div>
                                    <div class="flex items-center text-xs">
                                        <i class="fas fa-circle text-gray-300 mr-1" id="req-upper" style="font-size: 6px;"></i>
                                        <span class="text-gray-500" id="req-upper-text">Huruf besar (A-Z)</span>
                                    </div>
                                    <div class="flex items-center text-xs">
                                        <i class="fas fa-circle text-gray-300 mr-1" id="req-lower" style="font-size: 6px;"></i>
                                        <span class="text-gray-500" id="req-lower-text">Huruf kecil (a-z)</span>
                                    </div>
                                    <div class="flex items-center text-xs">
                                        <i class="fas fa-circle text-gray-300 mr-1" id="req-number" style="font-size: 6px;"></i>
                                        <span class="text-gray-500" id="req-number-text">Angka (0-9)</span>
                                    </div>
                                    <div class="flex items-center text-xs">
                                        <i class="fas fa-circle text-gray-300 mr-1" id="req-special" style="font-size: 6px;"></i>
                                        <span class="text-gray-500" id="req-special-text">Karakter khusus (!@#$%^&*)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="new_password_confirmation" 
                                       name="new_password_confirmation" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-10"
                                       onkeyup="validatePasswordMatch()"
                                       required>
                                <button type="button" 
                                        onclick="togglePassword('new_password_confirmation', 'confirmPasswordIcon')"
                                        class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                                </button>
                            </div>
                            <div class="flex items-center mt-1">
                                <i class="fas fa-circle text-gray-300 mr-1" id="match-indicator" style="font-size: 6px;"></i>
                                <p class="text-xs text-gray-500" id="match-text">Konfirmasi password</p>
                            </div>
                        </div>

                        <!-- Catatan Keamanan -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <i class="fas fa-shield-alt text-blue-500 mr-2 mt-0.5"></i>
                                <div class="text-xs text-blue-700">
                                    <p class="font-medium mb-1">Tips Keamanan:</p>
                                    <ul class="list-disc list-inside space-y-0.5">
                                        <li>Gunakan kombinasi huruf besar, kecil, angka, dan simbol</li>
                                        <li>Jangan gunakan password yang sama dengan akun lain</li>
                                        <li>Ganti password secara berkala untuk keamanan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 mt-8 pt-5 border-t border-gray-200">
                        <button type="button" 
                                onclick="closePasswordModal()"
                                class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                id="submitPasswordBtn"
                                class="px-8 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-save mr-2"></i>
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifikasi -->
<div id="toast" class="fixed top-5 right-5 z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl border-l-4 border-green-600 p-4 min-w-[320px] transform transition-all translate-x-0">
        <div class="flex items-start">
            <div id="toastIcon" class="flex-shrink-0 mr-3">
                <i class="fas fa-check-circle text-green-500 text-xl"></i>
            </div>
            <div class="flex-1">
                <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
            </div>
            <button onclick="hideToast()" class="ml-4 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// CSRF Token
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

// ==================== TOAST FUNCTIONS ====================
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');
    
    let icon = '';
    let borderColor = '';
    
    switch (type) {
        case 'success':
            icon = '<i class="fas fa-check-circle text-green-500 text-xl"></i>';
            borderColor = 'border-green-600';
            break;
        case 'error':
            icon = '<i class="fas fa-exclamation-circle text-red-500 text-xl"></i>';
            borderColor = 'border-red-600';
            break;
        default:
            icon = '<i class="fas fa-info-circle text-blue-500 text-xl"></i>';
            borderColor = 'border-blue-600';
    }
    
    toastIcon.innerHTML = icon;
    toast.querySelector('.bg-white').className = `bg-white rounded-xl shadow-2xl border-l-4 ${borderColor} p-4 min-w-[320px] transform transition-all translate-x-0`;
    toastMessage.textContent = message;
    
    toast.classList.remove('hidden');
    
    setTimeout(() => {
        hideToast();
    }, 5000);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}

// ==================== MODAL FUNCTIONS ====================
function openEditProfilModal() {
    document.getElementById('editProfilModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditProfilModal() {
    document.getElementById('editProfilModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openPasswordModal() {
    document.getElementById('passwordModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    resetPasswordForm();
}

function closePasswordModal() {
    document.getElementById('passwordModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    resetPasswordForm();
}

function resetPasswordForm() {
    document.getElementById('passwordForm').reset();
    document.getElementById('currentPasswordError').classList.add('hidden');
    
    // Reset password strength
    document.getElementById('passwordStrength').textContent = 'Belum dimasukkan';
    document.getElementById('passwordStrengthProgress').style.width = '0%';
    document.getElementById('passwordStrengthProgress').className = 'h-full bg-gray-300';
    
    // Reset requirements
    const reqs = ['req-length', 'req-upper', 'req-lower', 'req-number', 'req-special'];
    reqs.forEach(req => {
        document.getElementById(req).className = 'fas fa-circle text-gray-300 mr-1';
        document.getElementById(req + '-text').className = 'text-gray-500';
    });
    
    // Reset match indicator
    document.getElementById('match-indicator').className = 'fas fa-circle text-gray-300 mr-1';
    document.getElementById('match-text').textContent = 'Konfirmasi password';
    document.getElementById('match-text').className = 'text-xs text-gray-500';
}

// ==================== PASSWORD TOGGLE ====================
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// ==================== PASSWORD STRENGTH CHECKER ====================
function checkPasswordStrength(password) {
    const strengthText = document.getElementById('passwordStrength');
    const strengthProgress = document.getElementById('passwordStrengthProgress');
    
    // Requirements
    const hasLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    // Update requirement indicators
    updateRequirement('req-length', hasLength);
    updateRequirement('req-upper', hasUpper);
    updateRequirement('req-lower', hasLower);
    updateRequirement('req-number', hasNumber);
    updateRequirement('req-special', hasSpecial);
    
    if (password.length === 0) {
        strengthText.textContent = 'Belum dimasukkan';
        strengthText.className = 'font-medium text-gray-700';
        strengthProgress.style.width = '0%';
        strengthProgress.className = 'h-full bg-gray-300';
        return;
    }
    
    // Calculate strength
    let strength = 0;
    if (hasLength) strength += 20;
    if (hasUpper) strength += 20;
    if (hasLower) strength += 20;
    if (hasNumber) strength += 20;
    if (hasSpecial) strength += 20;
    
    // Update progress bar
    strengthProgress.style.width = strength + '%';
    
    // Update text and color
    if (strength <= 20) {
        strengthText.textContent = 'Sangat Lemah';
        strengthText.className = 'font-medium text-red-600';
        strengthProgress.className = 'h-full bg-red-500';
    } else if (strength <= 40) {
        strengthText.textContent = 'Lemah';
        strengthText.className = 'font-medium text-orange-600';
        strengthProgress.className = 'h-full bg-orange-500';
    } else if (strength <= 60) {
        strengthText.textContent = 'Sedang';
        strengthText.className = 'font-medium text-yellow-600';
        strengthProgress.className = 'h-full bg-yellow-500';
    } else if (strength <= 80) {
        strengthText.textContent = 'Kuat';
        strengthText.className = 'font-medium text-blue-600';
        strengthProgress.className = 'h-full bg-blue-500';
    } else {
        strengthText.textContent = 'Sangat Kuat';
        strengthText.className = 'font-medium text-green-600';
        strengthProgress.className = 'h-full bg-green-500';
    }
    
    validatePasswordMatch();
}

function updateRequirement(elementId, isMet) {
    const icon = document.getElementById(elementId);
    const text = document.getElementById(elementId + '-text');
    
    if (isMet) {
        icon.className = 'fas fa-circle text-green-500 mr-1';
        icon.style.fontSize = '6px';
        text.className = 'text-green-600';
    } else {
        icon.className = 'fas fa-circle text-gray-300 mr-1';
        icon.style.fontSize = '6px';
        text.className = 'text-gray-500';
    }
}

function validatePasswordMatch() {
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('new_password_confirmation').value;
    const matchIndicator = document.getElementById('match-indicator');
    const matchText = document.getElementById('match-text');
    const submitBtn = document.getElementById('submitPasswordBtn');
    
    if (confirmPass.length > 0) {
        if (newPass !== confirmPass) {
            matchIndicator.className = 'fas fa-circle text-red-500 mr-1';
            matchIndicator.style.fontSize = '6px';
            matchText.textContent = 'Password tidak cocok';
            matchText.className = 'text-xs text-red-600';
        } else {
            matchIndicator.className = 'fas fa-circle text-green-500 mr-1';
            matchIndicator.style.fontSize = '6px';
            matchText.textContent = 'Password cocok';
            matchText.className = 'text-xs text-green-600';
        }
    } else {
        matchIndicator.className = 'fas fa-circle text-gray-300 mr-1';
        matchIndicator.style.fontSize = '6px';
        matchText.textContent = 'Konfirmasi password';
        matchText.className = 'text-xs text-gray-500';
    }
    
    // Check if all requirements are met
    const hasLength = newPass.length >= 8;
    const hasUpper = /[A-Z]/.test(newPass);
    const hasLower = /[a-z]/.test(newPass);
    const hasNumber = /[0-9]/.test(newPass);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(newPass);
    const passwordsMatch = newPass === confirmPass && confirmPass.length > 0;
    
    submitBtn.disabled = !(hasLength && hasUpper && hasLower && hasNumber && hasSpecial && passwordsMatch);
}

// ==================== UPLOAD FOTO ====================
document.getElementById('photoUpload')?.addEventListener('change', function(e) {
    const file = this.files[0];
    if (!file) return;
    
    // Validasi tipe file
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        showToast('Format file tidak didukung. Gunakan JPG, PNG, atau GIF', 'error');
        return;
    }
    
    // Validasi ukuran file (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        showToast('Ukuran file maksimal 2MB', 'error');
        return;
    }
    
    // Tampilkan progress
    document.getElementById('uploadProgress').classList.remove('hidden');
    
    // Upload file
    const formData = new FormData();
    formData.append('foto', file);
    formData.append('_token', csrfToken);
    
    fetch('{{ route("admin.profil.upload-photo") }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('uploadProgress').classList.add('hidden');
        
        if (data.success) {
            // Update foto profil
            const container = document.getElementById('profileImageContainer');
            const placeholder = document.getElementById('profileImagePlaceholder');
            const existingImg = document.getElementById('profileImage');
            
            if (existingImg) {
                existingImg.src = data.photo_url + '?t=' + new Date().getTime();
            } else {
                // Hapus placeholder dan buat img baru
                if (placeholder) placeholder.remove();
                const img = document.createElement('img');
                img.id = 'profileImage';
                img.className = 'w-full h-full object-cover';
                img.src = data.photo_url + '?t=' + new Date().getTime();
                img.alt = '{{ auth()->user()->name }}';
                container.innerHTML = '';
                container.appendChild(img);
            }
            
            // Tampilkan tombol hapus jika belum ada
            if (!document.querySelector('button[onclick="confirmDeletePhoto()"]')) {
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'absolute top-0 right-0 w-8 h-8 bg-red-500 rounded-full flex items-center justify-center shadow-lg hover:bg-red-600 transition-colors duration-200 cursor-pointer border-2 border-white';
                deleteBtn.setAttribute('onclick', 'confirmDeletePhoto()');
                deleteBtn.innerHTML = '<i class="fas fa-trash text-white text-xs"></i>';
                document.querySelector('.relative.inline-block').appendChild(deleteBtn);
            }
            
            showToast('Foto profil berhasil diperbarui', 'success');
        } else {
            showToast(data.message || 'Gagal mengupload foto', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('uploadProgress').classList.add('hidden');
        showToast('Terjadi kesalahan saat mengupload foto', 'error');
    });
});

// ==================== HAPUS FOTO ====================
function confirmDeletePhoto() {
    if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
        fetch('{{ route("admin.profil.delete-photo") }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Kembalikan ke placeholder
                const container = document.getElementById('profileImageContainer');
                const img = document.getElementById('profileImage');
                
                if (img) {
                    img.remove();
                }
                
                const placeholder = document.createElement('div');
                placeholder.id = 'profileImagePlaceholder';
                placeholder.className = 'w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center';
                placeholder.innerHTML = '<i class="fas fa-user text-5xl text-primary-600"></i>';
                container.appendChild(placeholder);
                
                // Hapus tombol hapus
                const deleteBtn = document.querySelector('button[onclick="confirmDeletePhoto()"]');
                if (deleteBtn) {
                    deleteBtn.remove();
                }
                
                showToast('Foto profil berhasil dihapus', 'success');
            } else {
                showToast(data.message || 'Gagal menghapus foto', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menghapus foto', 'error');
        });
    }
}

// ==================== EDIT PROFIL ====================
document.getElementById('editProfilForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Disable button
    const submitBtn = document.getElementById('submitEditBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
    
    fetch('{{ route("admin.profil.update") }}', {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        if (data.success) {
            // Update tampilan
            document.getElementById('displayName').textContent = data.data.name;
            document.getElementById('displayUsername').innerHTML = '<i class="fas fa-user-circle mr-1"></i> ' + (data.data.username || '-');
            document.getElementById('viewUsername').innerHTML = '<i class="fas fa-user-circle text-gray-400 w-5 mr-2"></i>' + (data.data.username || '-');
            document.getElementById('viewName').innerHTML = `<i class="fas fa-user text-gray-400 w-5 mr-2"></i>${data.data.name || '-'}`;
            document.getElementById('viewEmail').innerHTML = `<i class="fas fa-envelope text-gray-400 w-5 mr-2"></i>${data.data.email || '-'}`;
            document.getElementById('viewNimNip').innerHTML = `<i class="fas fa-id-card text-gray-400 w-5 mr-2"></i>${data.data.nim_nip || 'Tidak ada'}`;
            document.getElementById('viewNoTelepon').innerHTML = `<i class="fas fa-phone-alt text-gray-400 w-5 mr-2"></i>${data.data.no_telepon || 'Tidak ada'}`;
            
            // Update username di sidebar jika ada
            const sidebarUsername = document.querySelector('#sidebarUsername');
            if (sidebarUsername) {
                sidebarUsername.textContent = data.data.username || '-';
            }
            
            closeEditProfilModal();
            showToast('Profil berhasil diperbarui', 'success');
        } else {
            showToast(data.message || 'Gagal memperbarui profil', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        showToast('Terjadi kesalahan saat memperbarui profil', 'error');
    });
});

// ==================== UBAH PASSWORD ====================
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validasi password
    const newPass = document.getElementById('new_password').value;
    const confirmPass = document.getElementById('new_password_confirmation').value;
    
    if (newPass !== confirmPass) {
        showToast('Password tidak cocok', 'error');
        return;
    }
    
    // Validasi kekuatan password
    const hasLength = newPass.length >= 8;
    const hasUpper = /[A-Z]/.test(newPass);
    const hasLower = /[a-z]/.test(newPass);
    const hasNumber = /[0-9]/.test(newPass);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(newPass);
    
    if (!(hasLength && hasUpper && hasLower && hasNumber && hasSpecial)) {
        showToast('Password harus memenuhi semua kriteria keamanan', 'error');
        return;
    }
    
    // Disable button
    const submitBtn = document.getElementById('submitPasswordBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    fetch('{{ route("admin.profil.change-password") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        
        if (data.success) {
            closePasswordModal();
            showToast('Password berhasil diubah', 'success');
            
            // Update password strength indicator di halaman utama
            document.getElementById('passwordStrengthBar').style.width = '80%';
            document.getElementById('passwordStrengthText').textContent = 'Kuat';
            const passwordInfo = document.querySelector('.text-gray-500.mt-2');
            if (passwordInfo) {
                passwordInfo.innerHTML = '<i class="fas fa-info-circle mr-1"></i>Password terakhir diubah beberapa detik yang lalu';
            }
        } else {
            if (data.errors && data.errors.current_password) {
                document.getElementById('currentPasswordError').textContent = data.errors.current_password[0];
                document.getElementById('currentPasswordError').classList.remove('hidden');
            } else {
                showToast(data.message || 'Gagal mengubah password', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        showToast('Terjadi kesalahan saat mengubah password', 'error');
    });
});

// ==================== CLOSE MODAL ON OUTSIDE CLICK ====================
window.addEventListener('click', function(e) {
    const editModal = document.getElementById('editProfilModal');
    const passwordModal = document.getElementById('passwordModal');
    
    if (e.target === editModal) {
        closeEditProfilModal();
    }
    if (e.target === passwordModal) {
        closePasswordModal();
    }
});

// ==================== ESC KEY ====================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEditProfilModal();
        closePasswordModal();
    }
});

// ==================== SUCCESS/ERROR MESSAGES FROM SESSION ====================
@if(session('success'))
    showToast("{{ session('success') }}", 'success');
@endif

@if(session('error'))
    showToast("{{ session('error') }}", 'error');
@endif
</script>
@endpush

@push('styles')
<style>
/* Modal animations */
#editProfilModal,
#passwordModal {
    transition: opacity 0.3s ease;
}

#editProfilModal .bg-white,
#passwordModal .bg-white {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Toast animation */
#toast {
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Loading spinner */
.border-3 {
    border-width: 3px;
}

/* Password strength meter */
#passwordStrengthProgress {
    transition: width 0.3s ease;
}

/* Requirement indicators */
.fa-circle {
    font-size: 6px;
    transition: color 0.3s ease;
}

/* Disabled button */
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Form input focus */
input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Password match indicator */
#match-indicator {
    transition: color 0.3s ease;
}

/* Security tips box */
.bg-blue-50 {
    transition: all 0.3s ease;
}

.bg-blue-50:hover {
    background-color: #eff6ff;
    border-color: #3b82f6;
}

/* Custom scrollbar for modal */
#passwordModal .overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f1f5f9;
}

#passwordModal .overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

#passwordModal .overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
}

#passwordModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #cbd5e0;
    border-radius: 3px;
}
</style>
@endpush