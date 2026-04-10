@extends('layouts.user')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('header-actions')
<div class="flex items-center space-x-3">
    <button onclick="openUsernameModal()" class="inline-flex items-center px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow">
        <i class="fas fa-at mr-2"></i>
        Ubah Username
    </button>
    <button onclick="openPasswordModal()" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow">
        <i class="fas fa-key mr-2"></i>
        Ubah Password
    </button>
</div>
@endsection

@section('content')
<div class="p-6">
    <!-- PROFILE HEADER - HERO SECTION -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-2xl p-8 mb-8 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mt-20 -mr-20"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -mb-16 -ml-16"></div>
        
        <div class="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-8">
            <!-- Foto Profil dengan Upload -->
            <div class="flex-shrink-0">
                <div class="relative group">
                    <div class="w-32 h-32 rounded-2xl border-4 border-white shadow-xl overflow-hidden bg-white" id="profileImageContainer">
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
                    
                    <!-- Tombol Ganti Foto dengan Upload -->
                    <label for="photoUpload" 
                           class="absolute -bottom-2 -right-2 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer group">
                        <i class="fas fa-camera text-primary-600 group-hover:scale-110 transition-transform"></i>
                        <input type="file" id="photoUpload" class="hidden" accept="image/jpeg,image/png,image/jpg,image/gif">
                    </label>
                    
                    <!-- Progress Upload (hidden by default) -->
                    <div id="uploadProgress" class="absolute inset-0 bg-black bg-opacity-50 rounded-2xl flex items-center justify-center hidden">
                        <div class="text-center">
                            <div class="w-10 h-10 border-4 border-white border-t-transparent rounded-full animate-spin mb-2"></div>
                            <p class="text-white text-xs">Mengupload...</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Info Utama -->
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-3xl font-bold mb-2">{{ auth()->user()->name }}</h2>
                <!-- USERNAME - BISA DIUBAH -->
                <div class="mb-3">
                    <div class="inline-flex items-center px-4 py-1.5 bg-white bg-opacity-20 rounded-full text-sm font-medium backdrop-blur-sm">
                        <i class="fas fa-at mr-2"></i>
                        @username: {{ auth()->user()->username ?? 'Belum diatur' }}
                        <span class="ml-2 text-xs bg-blue-500 text-white px-2 py-0.5 rounded-full">
                            <i class="fas fa-edit mr-1"></i> Dapat diubah
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                    <span class="px-4 py-1.5 bg-white bg-opacity-20 rounded-full text-sm font-medium backdrop-blur-sm">
                        <i class="fas fa-id-card mr-2"></i>
                        {{ auth()->user()->nim_nip ?? 'NIM/NIP belum diisi' }}
                    </span>
                    <span class="px-4 py-1.5 bg-white bg-opacity-20 rounded-full text-sm font-medium backdrop-blur-sm">
                        <i class="fas fa-tag mr-2"></i>
                        {{ ucfirst(auth()->user()->jenis_pengaju ?? auth()->user()->role) }}
                    </span>
                    @if(auth()->user()->role)
                    <span class="px-4 py-1.5 bg-white bg-opacity-20 rounded-full text-sm font-medium backdrop-blur-sm">
                        <i class="fas fa-user-tag mr-2"></i>
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TABS NAVIGATION -->
    <div class="mb-6 border-b border-gray-200">
        <div class="flex flex-wrap -mb-px">
            <button type="button" 
                    onclick="switchTab('pribadi')" 
                    id="tabPribadiBtn"
                    class="inline-flex items-center px-5 py-3 border-b-2 border-primary-600 text-primary-600 font-medium text-sm transition-colors duration-200">
                <i class="fas fa-user mr-2"></i>
                Informasi Pribadi
            </button>
            <button type="button" 
                    onclick="switchTab('akademik')" 
                    id="tabAkademikBtn"
                    class="inline-flex items-center px-5 py-3 border-b-2 border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-700 font-medium text-sm transition-colors duration-200">
                <i class="fas fa-graduation-cap mr-2"></i>
                Data Akademik
            </button>
            <button type="button" 
                    onclick="switchTab('status')" 
                    id="tabStatusBtn"
                    class="inline-flex items-center px-5 py-3 border-b-2 border-transparent hover:border-gray-300 text-gray-500 hover:text-gray-700 font-medium text-sm transition-colors duration-200">
                <i class="fas fa-shield-alt mr-2"></i>
                Status Akun
            </button>
        </div>
    </div>

    <!-- TAB CONTENT: INFORMASI PRIBADI -->
    <div id="tabPribadi" class="tab-content block">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-user-circle text-primary-600 mr-3 text-xl"></i>
                        Informasi Pribadi
                    </h3>
                    <button onclick="openPribadiModal()" class="text-sm text-primary-600 hover:text-primary-700 flex items-center">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kolom Kiri -->
                    <div class="space-y-5">
                        <!-- USERNAME - BISA DIEDIT -->
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-at text-cyan-600"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs text-gray-500 mb-1">Username</p>
                                    <button onclick="openUsernameModal()" class="text-xs text-cyan-600 hover:text-cyan-700">
                                        <i class="fas fa-edit mr-1"></i> Ubah
                                    </button>
                                </div>
                                <p class="font-medium text-gray-900">{{ auth()->user()->username ?? '-' }}</p>
                                <p class="text-xs text-gray-400 mt-1">Klik "Ubah" untuk mengganti username</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">Nama Lengkap</p>
                                <p class="font-medium text-gray-900">{{ auth()->user()->name ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-id-card text-purple-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">NIM / NIP</p>
                                <p class="font-medium text-gray-900">{{ auth()->user()->nim_nip ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-envelope text-indigo-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">Email</p>
                                <p class="font-medium text-gray-900 break-all">{{ auth()->user()->email ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kolom Kanan -->
                    <div class="space-y-5">
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-phone-alt text-red-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">No. Telepon</p>
                                <p class="font-medium text-gray-900">{{ auth()->user()->phone ?? auth()->user()->no_telepon ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-tag text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">Jenis Pengaju</p>
                                <p class="font-medium text-gray-900">{{ ucfirst(auth()->user()->jenis_pengaju ?? '-') }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-calendar text-yellow-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">Member Sejak</p>
                                <p class="font-medium text-gray-900">
                                    {{ auth()->user()->created_at ? \Carbon\Carbon::parse(auth()->user()->created_at)->format('d F Y') : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB CONTENT: DATA AKADEMIK -->
    <div id="tabAkademik" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-graduation-cap text-green-600 mr-3 text-xl"></i>
                        Data Akademik
                    </h3>
                    <button type="button" 
                            onclick="openAkademikModal()" 
                            class="text-sm text-green-600 hover:text-green-700 flex items-center">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username -->
                    <div class="flex items-start p-4 bg-gradient-to-r from-cyan-50 to-teal-50 rounded-xl border border-cyan-100">
                        <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-at text-cyan-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500 mb-1">Username</p>
                                <button onclick="openUsernameModal()" class="text-xs text-cyan-600 hover:text-cyan-700">
                                    <i class="fas fa-edit mr-1"></i> Ubah
                                </button>
                            </div>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ auth()->user()->username ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Klik "Ubah" untuk mengganti username</p>
                        </div>
                    </div>

                    <!-- Status / Jenis Pengaju -->
                    <div class="flex items-start p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-100">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-user-tag text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Status / Jenis Pengaju</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ ucfirst(auth()->user()->jenis_pengaju ?? 'Belum diatur') }}
                            </p>
                            @if(!auth()->user()->jenis_pengaju)
                                <p class="text-xs text-red-500 mt-1">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Harap lengkapi data akademik
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- NIM / NIP -->
                    <div class="flex items-start p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-id-card text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">NIM / NIP</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ auth()->user()->nim_nip ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Fakultas -->
                    <div class="flex items-start p-4 bg-purple-50 rounded-xl border border-purple-100">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-university text-purple-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Fakultas</p>
                            <p class="font-medium text-gray-900 text-lg">
                                {{ auth()->user()->fakultas ?? '-' }}
                            </p>
                        </div>
                    </div>

                    <!-- Program Studi -->
                    <div class="flex items-start p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-book text-indigo-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Program Studi</p>
                            <p class="font-medium text-gray-900 text-lg">
                                {{ auth()->user()->prodi ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Akademik -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-3">
                            <i class="fas fa-info-circle text-primary-600 text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Ringkasan Akademik</h4>
                            <p class="text-sm text-gray-600">
                                @if(auth()->user()->jenis_pengaju == 'mahasiswa')
                                    Anda terdaftar sebagai mahasiswa 
                                    @if(auth()->user()->prodi) Program Studi {{ auth()->user()->prodi }} @endif
                                    @if(auth()->user()->fakultas) Fakultas {{ auth()->user()->fakultas }} @endif.
                                @elseif(auth()->user()->jenis_pengaju == 'dosen')
                                    Anda terdaftar sebagai dosen
                                    @if(auth()->user()->prodi) di Program Studi {{ auth()->user()->prodi }} @endif
                                    @if(auth()->user()->fakultas) Fakultas {{ auth()->user()->fakultas }} @endif.
                                @elseif(auth()->user()->jenis_pengaju == 'pegawai')
                                    Anda terdaftar sebagai pegawai.
                                @else
                                    Silakan lengkapi data akademik Anda untuk pengajuan peminjaman ruangan.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB CONTENT: STATUS AKUN -->
    <div id="tabStatus" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-shield-alt text-primary-600 mr-3 text-xl"></i>
                    Status Akun
                </h3>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username -->
                    <div class="flex items-start p-4 bg-gradient-to-r from-cyan-50 to-teal-50 rounded-xl border border-cyan-100">
                        <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-at text-cyan-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500 mb-1">Username</p>
                                <button onclick="openUsernameModal()" class="text-xs text-cyan-600 hover:text-cyan-700">
                                    <i class="fas fa-edit mr-1"></i> Ubah
                                </button>
                            </div>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ auth()->user()->username ?? '-' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Klik "Ubah" untuk mengganti username</p>
                        </div>
                    </div>

                    <!-- Status Akun -->
                    <div class="flex items-start p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-100">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Status Akun</p>
                            <p class="text-lg font-semibold text-gray-900">
                                @if(auth()->user()->is_active ?? true)
                                    <span class="flex items-center">
                                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                        Aktif
                                    </span>
                                @else
                                    <span class="flex items-center">
                                        <i class="fas fa-times-circle text-red-600 mr-2"></i>
                                        Nonaktif
                                    </span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Aktivitas akun Anda</p>
                        </div>
                    </div>

                    <!-- Verifikasi Email -->
                    <div class="flex items-start p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-envelope text-blue-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Verifikasi Email</p>
                            <p class="text-lg font-semibold text-gray-900">
                                @if(auth()->user()->email_verified_at)
                                    <span class="flex items-center">
                                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                        Terverifikasi
                                    </span>
                                @else
                                    <span class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                        Belum Verifikasi
                                    </span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Status verifikasi email</p>
                        </div>
                    </div>

                    <!-- Member Sejak -->
                    <div class="flex items-start p-4 bg-purple-50 rounded-xl border border-purple-100">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Member Sejak</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ auth()->user()->created_at ? \Carbon\Carbon::parse(auth()->user()->created_at)->format('d/m/Y') : '-' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Tanggal registrasi akun</p>
                        </div>
                    </div>

                    <!-- Terakhir Login -->
                    <div class="flex items-start p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-clock text-indigo-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs text-gray-500 mb-1">Terakhir Login</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ auth()->user()->last_login_at ? \Carbon\Carbon::parse(auth()->user()->last_login_at)->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Aktivitas terakhir Anda</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT USERNAME -->
<div id="usernameModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="relative mx-auto my-8 p-4 w-full max-w-md">
        <div class="bg-white rounded-xl shadow-xl">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-at text-cyan-600 mr-3"></i>
                    Ubah Username
                </h3>
                <button onclick="closeUsernameModal()" 
                        class="text-gray-400 hover:text-gray-600 text-2xl bg-transparent border-0">
                    &times;
                </button>
            </div>
            
            <div class="p-6">
                <form id="usernameForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-4">
                        <!-- Username saat ini -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Username Saat Ini
                            </label>
                            <input type="text" 
                                   value="{{ auth()->user()->username ?? 'Belum diatur' }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-gray-100 cursor-not-allowed"
                                   readonly
                                   disabled>
                        </div>
                        
                        <!-- Username Baru -->
                        <div>
                            <label for="new_username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username Baru <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="new_username" 
                                   name="username" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   placeholder="Contoh: john123, andri90"
                                   required
                                   autocomplete="off">
                            
                            <!-- Feedback messages -->
                            <div id="usernameFeedback" class="mt-2"></div>
                            
                            <div class="mt-2 p-3 bg-blue-50 rounded-lg">
                                <p class="text-xs text-blue-700">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Persyaratan Username:</strong><br>
                                    • Minimal 3 karakter<br>
                                    • Maksimal 255 karakter<br>
                                    • Harus mengandung huruf DAN angka<br>
                                    • Hanya huruf dan angka (tanpa spasi atau simbol)<br>
                                    • Bersifat unik (tidak boleh sama dengan user lain)
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contoh username yang valid -->
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600 mb-2">
                            <i class="fas fa-lightbulb mr-1"></i>
                            <strong>Contoh username yang valid:</strong>
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded cursor-pointer" onclick="setExampleUsername('john123')">john123</span>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded cursor-pointer" onclick="setExampleUsername('andri90')">andri90</span>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded cursor-pointer" onclick="setExampleUsername('sari456')">sari456</span>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded cursor-pointer" onclick="setExampleUsername('budi2024')">budi2024</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 mt-6 pt-5 border-t border-gray-200">
                        <button type="button" 
                                onclick="closeUsernameModal()"
                                class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                id="submitUsernameBtn"
                                class="px-8 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors flex items-center">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Username
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDIT INFORMASI PRIBADI -->
<div id="pribadiModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="relative mx-auto my-8 p-4 w-full max-w-2xl">
        <div class="bg-white rounded-xl shadow-xl">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-circle text-primary-600 mr-3"></i>
                    Edit Informasi Pribadi
                </h3>
                <button onclick="closePribadiModal()" 
                        class="text-gray-400 hover:text-gray-600 text-2xl bg-transparent border-0">
                    &times;
                </button>
            </div>
            
            <div class="p-6">
                <form action="{{ route('profil.update-pribadi') }}" method="POST" id="pribadiForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-5">
                        <!-- Username - Hanya ditampilkan -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Username
                            </label>
                            <div class="flex items-center gap-2">
                                <input type="text" 
                                       value="{{ auth()->user()->username ?? 'Belum diatur' }}" 
                                       class="flex-1 border border-gray-300 rounded-lg px-4 py-3 bg-gray-100 cursor-not-allowed"
                                       readonly
                                       disabled>
                                <button type="button" 
                                        onclick="closePribadiModal(); openUsernameModal();"
                                        class="px-4 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Klik tombol edit untuk mengubah username
                            </p>
                        </div>

                        <!-- Nama Lengkap -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ auth()->user()->name }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ auth()->user()->email }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   required>
                        </div>

                        <!-- No Telepon -->
                        <div>
                            <label for="no_telepon" class="block text-sm font-medium text-gray-700 mb-2">
                                No. Telepon
                            </label>
                            <input type="text" 
                                   id="no_telepon" 
                                   name="no_telepon" 
                                   value="{{ auth()->user()->no_telepon ?? auth()->user()->phone }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   placeholder="Contoh: 081234567890">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 mt-8 pt-5 border-t border-gray-200">
                        <button type="button" 
                                onclick="closePribadiModal()"
                                class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
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

<!-- MODAL EDIT DATA AKADEMIK -->
<div id="akademikModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden overflow-y-auto">
    <div class="relative mx-auto my-8 p-4 w-full max-w-2xl">
        <div class="bg-white rounded-xl shadow-xl">
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-graduation-cap text-green-600 mr-3"></i>
                    Edit Data Akademik
                </h3>
                <button onclick="closeAkademikModal()" 
                        class="text-gray-400 hover:text-gray-600 text-2xl bg-transparent border-0">
                    &times;
                </button>
            </div>
            
            <div class="p-6">
                <form action="{{ route('profil.update-akademik') }}" method="POST" id="akademikForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-5">
                        <div>
                            <label for="jenis_pengaju" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select id="jenis_pengaju" 
                                    name="jenis_pengaju" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                    required>
                                <option value="">Pilih Status</option>
                                <option value="mahasiswa" {{ auth()->user()->jenis_pengaju == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                                <option value="dosen" {{ auth()->user()->jenis_pengaju == 'dosen' ? 'selected' : '' }}>Dosen</option>
                                <option value="pegawai" {{ auth()->user()->jenis_pengaju == 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Pilih status Anda saat ini
                            </p>
                        </div>

                        <div>
                            <label for="nim_nip" class="block text-sm font-medium text-gray-700 mb-2">
                                NIM / NIP
                            </label>
                            <input type="text" 
                                   id="nim_nip" 
                                   name="nim_nip" 
                                   value="{{ auth()->user()->nim_nip }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   placeholder="Masukkan NIM atau NIP">
                        </div>

                        <div>
                            <label for="fakultas" class="block text-sm font-medium text-gray-700 mb-2">
                                Fakultas
                            </label>
                            <input type="text" 
                                   id="fakultas" 
                                   name="fakultas" 
                                   value="{{ auth()->user()->fakultas }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   placeholder="Contoh: Fakultas Teknik">
                        </div>

                        <div>
                            <label for="prodi" class="block text-sm font-medium text-gray-700 mb-2">
                                Program Studi
                            </label>
                            <input type="text" 
                                   id="prodi" 
                                   name="prodi" 
                                   value="{{ auth()->user()->prodi }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                                   placeholder="Contoh: Teknik Informatika">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-3 mt-8 pt-5 border-t border-gray-200">
                        <button type="button" 
                                onclick="closeAkademikModal()"
                                class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="px-8 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center">
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
            
            <div class="p-6">
                <form id="passwordForm">
                    @csrf
                    @method('POST')
                    
                    <div class="space-y-4">
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

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-10"
                                       required>
                                <button type="button" 
                                        onclick="togglePassword('new_password', 'newPasswordIcon')"
                                        class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye" id="newPasswordIcon"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <div class="flex items-center text-xs text-gray-500 mb-1">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Password harus mengandung:
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div id="lengthCheck" class="text-gray-500">
                                        <i class="fas fa-times-circle mr-1 text-red-500"></i> Min. 8 karakter
                                    </div>
                                    <div id="uppercaseCheck" class="text-gray-500">
                                        <i class="fas fa-times-circle mr-1 text-red-500"></i> Huruf besar
                                    </div>
                                    <div id="lowercaseCheck" class="text-gray-500">
                                        <i class="fas fa-times-circle mr-1 text-red-500"></i> Huruf kecil
                                    </div>
                                    <div id="numberCheck" class="text-gray-500">
                                        <i class="fas fa-times-circle mr-1 text-red-500"></i> Angka
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Konfirmasi Password Baru <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="new_password_confirmation" 
                                       name="new_password_confirmation" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors pr-10"
                                       required>
                                <button type="button" 
                                        onclick="togglePassword('new_password_confirmation', 'confirmPasswordIcon')"
                                        class="absolute right-3 top-3 text-gray-500 hover:text-gray-700">
                                    <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                                </button>
                            </div>
                            <p class="text-xs text-red-500 mt-1 hidden" id="confirmPasswordError">Password tidak cocok</p>
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
                                class="px-8 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors flex items-center">
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

<!-- Form Delete Photo (hidden) -->
<form id="deletePhotoForm" method="POST" action="{{ route('profil.delete-photo') }}" class="hidden">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
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
        case 'warning':
            icon = '<i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>';
            borderColor = 'border-yellow-600';
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

// ==================== TAB NAVIGATION ====================
function switchTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    document.getElementById('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).classList.remove('hidden');
    
    const tabButtons = {
        pribadi: document.getElementById('tabPribadiBtn'),
        akademik: document.getElementById('tabAkademikBtn'),
        status: document.getElementById('tabStatusBtn')
    };
    
    Object.values(tabButtons).forEach(btn => {
        if (btn) {
            btn.classList.remove('border-primary-600', 'text-primary-600');
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        }
    });
    
    const activeButton = tabButtons[tabName];
    if (activeButton) {
        activeButton.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        activeButton.classList.add('border-primary-600', 'text-primary-600');
    }
    
    localStorage.setItem('activeProfileTab', tabName);
}

// ==================== MODAL USERNAME ====================
let usernameCheckTimeout;

function openUsernameModal() {
    document.getElementById('usernameModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    resetUsernameForm();
}

function closeUsernameModal() {
    document.getElementById('usernameModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    resetUsernameForm();
}

function resetUsernameForm() {
    document.getElementById('usernameForm').reset();
    const feedback = document.getElementById('usernameFeedback');
    feedback.innerHTML = '';
}

function setExampleUsername(username) {
    document.getElementById('new_username').value = username;
    checkUsernameAvailability();
}

function checkUsernameAvailability() {
    const username = document.getElementById('new_username').value;
    const feedback = document.getElementById('usernameFeedback');
    
    if (username.length < 3) {
        feedback.innerHTML = '<div class="text-red-500 text-xs"><i class="fas fa-times-circle mr-1"></i> Username minimal 3 karakter</div>';
        return false;
    }
    
    // Validasi format huruf dan angka
    if (!/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/.test(username)) {
        feedback.innerHTML = '<div class="text-red-500 text-xs"><i class="fas fa-times-circle mr-1"></i> Username harus mengandung huruf DAN angka</div>';
        return false;
    }
    
    // Tampilkan loading
    feedback.innerHTML = '<div class="text-blue-500 text-xs"><i class="fas fa-spinner fa-spin mr-1"></i> Memeriksa ketersediaan...</div>';
    
    clearTimeout(usernameCheckTimeout);
    usernameCheckTimeout = setTimeout(() => {
        fetch('{{ route("profil.check-username") }}?username=' + encodeURIComponent(username), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                feedback.innerHTML = '<div class="text-green-500 text-xs"><i class="fas fa-check-circle mr-1"></i> ' + data.message + '</div>';
            } else {
                feedback.innerHTML = '<div class="text-red-500 text-xs"><i class="fas fa-times-circle mr-1"></i> ' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            feedback.innerHTML = '<div class="text-red-500 text-xs"><i class="fas fa-exclamation-circle mr-1"></i> Gagal memeriksa ketersediaan</div>';
        });
    }, 500);
    
    return true;
}

// ==================== MODAL PRIBADI ====================
function openPribadiModal() {
    document.getElementById('pribadiModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePribadiModal() {
    document.getElementById('pribadiModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// ==================== MODAL AKADEMIK ====================
function openAkademikModal() {
    document.getElementById('akademikModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAkademikModal() {
    document.getElementById('akademikModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// ==================== MODAL PASSWORD ====================
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
    document.getElementById('confirmPasswordError').classList.add('hidden');
    
    document.querySelectorAll('#lengthCheck, #uppercaseCheck, #lowercaseCheck, #numberCheck').forEach(el => {
        el.innerHTML = el.innerHTML.replace('fa-check-circle', 'fa-times-circle');
        el.classList.remove('text-green-600');
        el.classList.add('text-gray-500');
    });
}

// ==================== PASSWORD STRENGTH CHECK ====================
function checkPasswordStrength(password) {
    const lengthCheck = document.getElementById('lengthCheck');
    const uppercaseCheck = document.getElementById('uppercaseCheck');
    const lowercaseCheck = document.getElementById('lowercaseCheck');
    const numberCheck = document.getElementById('numberCheck');
    
    if (password.length >= 8) {
        lengthCheck.innerHTML = '<i class="fas fa-check-circle mr-1 text-green-500"></i> Min. 8 karakter';
        lengthCheck.classList.add('text-green-600');
        lengthCheck.classList.remove('text-gray-500');
    } else {
        lengthCheck.innerHTML = '<i class="fas fa-times-circle mr-1 text-red-500"></i> Min. 8 karakter';
        lengthCheck.classList.remove('text-green-600');
        lengthCheck.classList.add('text-gray-500');
    }
    
    if (/[A-Z]/.test(password)) {
        uppercaseCheck.innerHTML = '<i class="fas fa-check-circle mr-1 text-green-500"></i> Huruf besar';
        uppercaseCheck.classList.add('text-green-600');
        uppercaseCheck.classList.remove('text-gray-500');
    } else {
        uppercaseCheck.innerHTML = '<i class="fas fa-times-circle mr-1 text-red-500"></i> Huruf besar';
        uppercaseCheck.classList.remove('text-green-600');
        uppercaseCheck.classList.add('text-gray-500');
    }
    
    if (/[a-z]/.test(password)) {
        lowercaseCheck.innerHTML = '<i class="fas fa-check-circle mr-1 text-green-500"></i> Huruf kecil';
        lowercaseCheck.classList.add('text-green-600');
        lowercaseCheck.classList.remove('text-gray-500');
    } else {
        lowercaseCheck.innerHTML = '<i class="fas fa-times-circle mr-1 text-red-500"></i> Huruf kecil';
        lowercaseCheck.classList.remove('text-green-600');
        lowercaseCheck.classList.add('text-gray-500');
    }
    
    if (/[0-9]/.test(password)) {
        numberCheck.innerHTML = '<i class="fas fa-check-circle mr-1 text-green-500"></i> Angka';
        numberCheck.classList.add('text-green-600');
        numberCheck.classList.remove('text-gray-500');
    } else {
        numberCheck.innerHTML = '<i class="fas fa-times-circle mr-1 text-red-500"></i> Angka';
        numberCheck.classList.remove('text-green-600');
        numberCheck.classList.add('text-gray-500');
    }
}

// ==================== UPLOAD FOTO PROFIL ====================
document.getElementById('photoUpload').addEventListener('change', function(e) {
    const file = this.files[0];
    if (!file) return;
    
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    if (!validTypes.includes(file.type)) {
        showToast('Format file tidak didukung. Gunakan JPG, PNG, atau GIF', 'error');
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        showToast('Ukuran file maksimal 2MB', 'error');
        return;
    }
    
    document.getElementById('uploadProgress').classList.remove('hidden');
    
    const formData = new FormData();
    formData.append('foto', file);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("profil.upload-photo") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('uploadProgress').classList.add('hidden');
        
        if (data.success) {
            const container = document.getElementById('profileImageContainer');
            const placeholder = document.getElementById('profileImagePlaceholder');
            const existingImg = document.getElementById('profileImage');
            
            if (existingImg) {
                existingImg.src = data.photo_url + '?t=' + new Date().getTime();
            } else {
                if (placeholder) placeholder.remove();
                const img = document.createElement('img');
                img.id = 'profileImage';
                img.className = 'w-full h-full object-cover';
                img.src = data.photo_url + '?t=' + new Date().getTime();
                img.alt = '{{ auth()->user()->name }}';
                container.innerHTML = '';
                container.appendChild(img);
            }
            
            if (!document.querySelector('button[onclick="deletePhoto()"]')) {
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'absolute -top-2 -right-2 w-10 h-10 bg-red-500 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-shadow duration-200 cursor-pointer group';
                deleteBtn.setAttribute('onclick', 'deletePhoto()');
                deleteBtn.innerHTML = '<i class="fas fa-trash text-white group-hover:scale-110 transition-transform"></i>';
                document.querySelector('.relative.group').appendChild(deleteBtn);
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

// ==================== PASSWORD VISIBILITY TOGGLE ====================
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

// ==================== FORM SUBMISSION ====================
document.addEventListener('DOMContentLoaded', function() {
    // Username form submission
    const usernameForm = document.getElementById('usernameForm');
    if (usernameForm) {
        const usernameInput = document.getElementById('new_username');
        
        usernameInput.addEventListener('input', function() {
            checkUsernameAvailability();
        });
        
        usernameForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newUsername = usernameInput.value;
            
            // Validasi akhir
            if (newUsername.length < 3) {
                showToast('Username minimal 3 karakter', 'error');
                return;
            }
            
            if (!/^(?=.*[a-zA-Z])(?=.*\d)[a-zA-Z0-9]+$/.test(newUsername)) {
                showToast('Username harus mengandung huruf DAN angka', 'error');
                return;
            }
            
            const submitBtn = document.getElementById('submitUsernameBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            
            fetch('{{ route("profil.update-username") }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    username: newUsername
                })
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Simpan Username';
                
                if (data.success) {
                    showToast(data.message || 'Username berhasil diperbarui!', 'success');
                    closeUsernameModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    if (data.errors) {
                        const firstError = Object.values(data.errors)[0];
                        showToast(firstError[0], 'error');
                    } else {
                        showToast(data.message || 'Gagal memperbarui username', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Simpan Username';
                showToast('Terjadi kesalahan saat memperbarui username', 'error');
            });
        });
    }

    // Pribadi form submission
    const pribadiForm = document.getElementById('pribadiForm');
    if (pribadiForm) {
        pribadiForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Data pribadi berhasil diperbarui', 'success');
                    closePribadiModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message || 'Gagal memperbarui data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat memperbarui data', 'error');
            });
        });
    }

    // Akademik form submission
    const akademikForm = document.getElementById('akademikForm');
    if (akademikForm) {
        akademikForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Data akademik berhasil diperbarui', 'success');
                    closeAkademikModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showToast(data.message || 'Gagal memperbarui data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Terjadi kesalahan saat memperbarui data', 'error');
            });
        });
    }

    // Password form submission
    const passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        document.getElementById('new_password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });
        
        document.getElementById('new_password_confirmation').addEventListener('input', function() {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = this.value;
            const errorEl = document.getElementById('confirmPasswordError');
            
            if (newPass !== confirmPass) {
                errorEl.classList.remove('hidden');
            } else {
                errorEl.classList.add('hidden');
            }
        });
        
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('new_password_confirmation').value;
            
            if (newPass !== confirmPass) {
                document.getElementById('confirmPasswordError').classList.remove('hidden');
                return;
            }
            
            if (newPass.length < 8 || !/[A-Z]/.test(newPass) || !/[a-z]/.test(newPass) || !/[0-9]/.test(newPass)) {
                showToast('Password harus memenuhi semua kriteria keamanan', 'error');
                return;
            }
            
            const formData = new FormData(this);
            
            const submitBtn = document.getElementById('submitPasswordBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
            
            fetch('{{ route("profil.change-password") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Ubah Password';
                
                if (data.success) {
                    showToast(data.message || 'Password berhasil diubah', 'success');
                    closePasswordModal();
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
                submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Ubah Password';
                showToast('Terjadi kesalahan saat mengubah password', 'error');
            });
        });
    }

    const activeTab = localStorage.getItem('activeProfileTab') || 'pribadi';
    switchTab(activeTab);
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePribadiModal();
            closeAkademikModal();
            closePasswordModal();
            closeUsernameModal();
        }
    });
    
    window.addEventListener('click', function(e) {
        const pribadiModal = document.getElementById('pribadiModal');
        const akademikModal = document.getElementById('akademikModal');
        const passwordModal = document.getElementById('passwordModal');
        const usernameModal = document.getElementById('usernameModal');
        
        if (e.target === pribadiModal) {
            closePribadiModal();
        }
        if (e.target === akademikModal) {
            closeAkademikModal();
        }
        if (e.target === passwordModal) {
            closePasswordModal();
        }
        if (e.target === usernameModal) {
            closeUsernameModal();
        }
    });

    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif
});
</script>
@endpush

@push('styles')
<style>
    .tab-content {
        transition: opacity 0.3s ease-in-out;
    }
    
    #pribadiModal, #akademikModal, #passwordModal, #usernameModal {
        transition: opacity 0.3s ease;
    }
    
    #pribadiModal .bg-white, #akademikModal .bg-white, #passwordModal .bg-white, #usernameModal .bg-white {
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
    
    .example-username {
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .example-username:hover {
        background-color: #d1fae5 !important;
        transform: scale(1.05);
    }
    
    @media (max-width: 768px) {
        .tab-buttons button {
            flex: 1 1 auto;
            padding: 12px 8px;
            font-size: 12px;
        }
    }
</style>
@endpush