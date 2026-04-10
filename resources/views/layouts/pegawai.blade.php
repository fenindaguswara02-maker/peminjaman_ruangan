<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Pegawai') - RoomBooking</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans">
    <!-- Pegawai Layout dengan Sidebar -->
    <div class="flex h-screen">
        <!-- Sidebar untuk Desktop, Hidden untuk Mobile -->
        <div id="sidebar" class="hidden md:flex w-64 bg-gradient-to-b from-primary-800 via-primary-700 to-primary-900 text-white shadow-xl flex-col">
            <div class="p-6">
                <!-- Header Sidebar dengan Logo Kecil -->
                <div class="flex items-center mb-8">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mr-3 shadow-sm">
                        <i class="fas fa-building text-lg text-primary-700"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">RoomBooking</h1>
                        <p class="text-xs text-blue-200">Panel Pegawai</p>
                    </div>
                </div>

                <nav class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('pegawai.dashboard') }}" 
                       class="flex items-center space-x-3 p-3 {{ request()->routeIs('pegawai.dashboard') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200">
                        <i class="fas fa-tachometer-alt w-5 text-center"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <!-- Peminjaman Ruangan dengan Badge Notifikasi HARI INI -->
                    <a href="{{ route('pegawai.peminjaman-ruangan.index') }}" 
                       class="flex items-center justify-between p-3 {{ request()->routeIs('pegawai.peminjaman-ruangan*') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-door-open w-5 text-center"></i>
                            <span class="font-medium">Peminjaman Ruangan</span>
                        </div>
                        @php
                            // Peminjaman menunggu persetujuan untuk HARI INI
                            $pendingCountToday = \App\Models\PeminjamanRuangan::where('status', 'menunggu')
                                ->whereDate('tanggal_mulai', now()->format('Y-m-d'))
                                ->count();
                        @endphp
                        @if($pendingCountToday > 0)
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">
                                {{ $pendingCountToday }}
                            </span>
                        @endif
                    </a>

                    <!-- Jadwal Ruangan dengan Badge Notifikasi AKAN DATANG & SEDANG BERLANGSUNG (HARI INI) -->
                    <a href="{{ route('pegawai.jadwal-ruangan.index') }}" 
                       class="flex items-center justify-between p-3 {{ request()->routeIs('pegawai.jadwal-ruangan*') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-calendar-check w-5 text-center"></i>
                            <span class="font-medium">Jadwal Ruangan</span>
                        </div>
                        @php
                            // Count approved bookings with real-time status 'akan_datang' or 'sedang_berlangsung' for TODAY
                            $activeSchedules = \App\Models\PeminjamanRuangan::where('status', 'disetujui')
                                ->whereIn('status_real_time', ['akan_datang', 'sedang_berlangsung'])
                                ->whereDate('tanggal_mulai', now()->format('Y-m-d'))
                                ->count();
                        @endphp
                        @if($activeSchedules > 0)
                            <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">
                                {{ $activeSchedules }}
                            </span>
                        @endif
                    </a>

                    <!-- Laporan -->
                    <a href="{{ route('pegawai.laporan.index') }}" 
                       class="flex items-center space-x-3 p-3 {{ request()->routeIs('pegawai.laporan*') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200">
                        <i class="fas fa-chart-bar w-5 text-center"></i>
                        <span class="font-medium">Laporan</span>
                    </a>
                </nav>
            </div>
            
            <!-- User Profile dengan Menu Profil -->
            <div class="mt-auto p-4 border-t border-blue-700">
                <!-- Tombol Profil dengan Foto -->
                <a href="{{ route('pegawai.profil.index') }}" 
                   class="flex items-center space-x-3 p-3 mb-2 {{ request()->routeIs('pegawai.profil*') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200 group">
                    <div class="relative">
                        @if(auth()->user()->foto)
                            <img src="{{ asset('storage/' . auth()->user()->foto) }}" 
                                 alt="{{ auth()->user()->name }}"
                                 class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm group-hover:scale-105 transition-transform duration-200">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center shadow-sm border-2 border-white group-hover:scale-105 transition-transform duration-200">
                                <span class="text-white font-semibold text-sm">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <!-- Status Online Indicator -->
                        <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate group-hover:text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-blue-300 truncate flex items-center">
                            <i class="fas fa-id-card mr-1 text-[10px]"></i>
                            {{ auth()->user()->role }}
                        </p>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-blue-300 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </a>
                
                <!-- Tombol Logout dengan Modal Konfirmasi -->
                <button type="button" onclick="showLogoutModal()" class="w-full flex items-center space-x-3 p-3 text-left text-blue-300 hover:text-white hover:bg-blue-700 hover:bg-opacity-30 rounded-lg transition-all duration-200 group">
                    <div class="w-8 h-8 flex items-center justify-center">
                        <i class="fas fa-sign-out-alt w-5 text-center group-hover:scale-110 transition-transform"></i>
                    </div>
                    <span class="text-sm font-medium">Keluar</span>
                </button>
            </div>
        </div>

        <!-- Mobile Sidebar Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

        <!-- Mobile Sidebar -->
        <div id="mobileSidebar" class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-primary-800 via-primary-700 to-primary-900 text-white shadow-xl transform -translate-x-full transition-transform duration-300 ease-in-out z-50 md:hidden">
            <div class="p-6">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mr-3 shadow-sm">
                            <i class="fas fa-building text-lg text-primary-700"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold">RoomBooking</h1>
                            <p class="text-xs text-blue-200">Panel Pegawai</p>
                        </div>
                    </div>
                    <button id="closeSidebar" class="text-white hover:text-blue-300 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <nav class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('pegawai.dashboard') }}" 
                       class="flex items-center space-x-3 p-3 {{ request()->routeIs('pegawai.dashboard') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200" 
                       onclick="closeMobileSidebar()">
                        <i class="fas fa-tachometer-alt w-5 text-center"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <!-- Peminjaman Ruangan dengan Badge Notifikasi HARI INI -->
                    <a href="{{ route('pegawai.peminjaman-ruangan.index') }}" 
                       class="flex items-center justify-between p-3 {{ request()->routeIs('pegawai.peminjaman-ruangan*') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200" 
                       onclick="closeMobileSidebar()">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-door-open w-5 text-center"></i>
                            <span class="font-medium">Peminjaman Ruangan</span>
                        </div>
                        @php
                            $pendingCountTodayMobile = \App\Models\PeminjamanRuangan::where('status', 'menunggu')
                                ->whereDate('tanggal_mulai', now()->format('Y-m-d'))
                                ->count();
                        @endphp
                        @if($pendingCountTodayMobile > 0)
                            <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">
                                {{ $pendingCountTodayMobile }}
                            </span>
                        @endif
                    </a>

                    <!-- Jadwal Ruangan dengan Badge Notifikasi AKAN DATANG & SEDANG BERLANGSUNG (HARI INI) -->
                    <a href="{{ route('pegawai.jadwal-ruangan.index') }}" 
                       class="flex items-center justify-between p-3 {{ request()->routeIs('pegawai.jadwal-ruangan*') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200" 
                       onclick="closeMobileSidebar()">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-calendar-check w-5 text-center"></i>
                            <span class="font-medium">Jadwal Ruangan</span>
                        </div>
                        @php
                            $activeSchedulesMobile = \App\Models\PeminjamanRuangan::where('status', 'disetujui')
                                ->whereIn('status_real_time', ['akan_datang', 'sedang_berlangsung'])
                                ->whereDate('tanggal_mulai', now()->format('Y-m-d'))
                                ->count();
                        @endphp
                        @if($activeSchedulesMobile > 0)
                            <span class="bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">
                                {{ $activeSchedulesMobile }}
                            </span>
                        @endif
                    </a>

                    <!-- Laporan -->
                    <a href="{{ route('pegawai.laporan.index') }}" 
                       class="flex items-center space-x-3 p-3 {{ request()->routeIs('pegawai.laporan*') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200" 
                       onclick="closeMobileSidebar()">
                        <i class="fas fa-chart-bar w-5 text-center"></i>
                        <span class="font-medium">Laporan</span>
                    </a>
                </nav>
            </div>
            
            <!-- User Profile Mobile dengan Menu Profil -->
            <div class="mt-auto p-4 border-t border-blue-700">
                <!-- Tombol Profil dengan Foto -->
                <a href="{{ route('pegawai.profil.index') }}" 
                   class="flex items-center space-x-3 p-3 mb-2 {{ request()->routeIs('pegawai.profil*') ? 'bg-blue-700 bg-opacity-50 border-l-4 border-white' : 'hover:bg-blue-700 hover:bg-opacity-30 border-l-4 border-transparent' }} rounded-r-lg transition-all duration-200 group" 
                   onclick="closeMobileSidebar()">
                    <div class="relative">
                        @if(auth()->user()->foto)
                            <img src="{{ asset('storage/' . auth()->user()->foto) }}" 
                                 alt="{{ auth()->user()->name }}"
                                 class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                        @else
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center shadow-sm border-2 border-white">
                                <span class="text-white font-semibold text-sm">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-blue-300 truncate">{{ auth()->user()->role }}</p>
                    </div>
                </a>
                
                <!-- Tombol Logout dengan Modal Konfirmasi -->
                <button type="button" onclick="showLogoutModal()" class="w-full flex items-center space-x-3 p-3 text-left text-blue-300 hover:text-white hover:bg-blue-700 hover:bg-opacity-30 rounded-lg transition-all duration-200">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span class="text-sm font-medium">Keluar</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden w-full">
            <!-- Top Bar dengan Logo Pojok Kanan Atas -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex justify-between items-center px-4 md:px-6 py-3">
                    <div class="flex items-center space-x-4">
                        <!-- Hamburger Menu untuk Mobile -->
                        <button id="openSidebar" class="md:hidden text-gray-600 hover:text-blue-600 transition-colors p-2 hover:bg-blue-50 rounded-lg">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <div>
                            <h2 class="text-base md:text-lg font-semibold text-gray-800">
                                @yield('page-title', 'Dashboard Pegawai')
                            </h2>
                            <p class="text-xs text-gray-500 hidden md:block">
                                {{ now()->format('l, d F Y') }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Logo dan Nama di Pojok Kanan Atas -->
                    <div class="flex items-center space-x-2">
                        <div class="hidden md:flex flex-col items-end mr-2">
                            <span class="text-xs font-medium text-gray-600">RoomBooking</span>
                            <span class="text-xs text-gray-400">Panel Pegawai</span>
                        </div>
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-building text-blue-600 text-sm md:text-base"></i>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                <!-- Notifikasi Toast Container -->
                <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-3"></div>

                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-4 p-3 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-r-lg shadow-sm">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2 text-sm"></i>
                            <span class="text-sm">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2 text-sm"></i>
                            <span class="text-sm">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                <!-- Logout Success Notification -->
                @if(session('logout_success'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showToast('success', '{{ session('logout_success') }}', 'Anda akan dialihkan ke halaman utama...');
                        });
                    </script>
                @endif

                <!-- Content -->
                @yield('content')
            </main>

            <!-- Footer Minimal -->
            <footer class="bg-white border-t border-gray-200 py-3 px-4 md:px-6">
                <div class="flex justify-between items-center text-xs text-gray-500">
                    <div>
                        &copy; {{ date('Y') }} RoomBooking
                    </div>
                    <div>
                        {{ auth()->user()->name }} • {{ now()->format('H:i') }}
                    </div>
                </div>
            </footer>

            <!-- Mobile Bottom Navigation dengan Badge Notifikasi -->
            <div class="md:hidden bg-white border-t border-gray-200 py-2 px-4 shadow-inner">
                <div class="flex justify-between items-center">
                    <!-- Dashboard -->
                    <a href="{{ route('pegawai.dashboard') }}" 
                       class="flex flex-col items-center {{ request()->routeIs('pegawai.dashboard') ? 'text-blue-600 font-medium' : 'text-gray-600' }} transition-colors">
                        <i class="fas fa-home text-sm mb-1"></i>
                        <span class="text-xs">Dashboard</span>
                    </a>
                    
                    <!-- Peminjaman Ruangan dengan Badge HARI INI -->
                    <a href="{{ route('pegawai.peminjaman-ruangan.index') }}" 
                       class="flex flex-col items-center relative {{ request()->routeIs('pegawai.peminjaman-ruangan*') ? 'text-blue-600 font-medium' : 'text-gray-600' }} transition-colors">
                        <div class="relative">
                            <i class="fas fa-door-open text-sm mb-1"></i>
                            @php
                                $pendingCountTodayBottom = \App\Models\PeminjamanRuangan::where('status', 'menunggu')
                                    ->whereDate('tanggal_mulai', now()->format('Y-m-d'))
                                    ->count();
                            @endphp
                            @if($pendingCountTodayBottom > 0)
                                <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 animate-pulse">
                                    {{ $pendingCountTodayBottom > 9 ? '9+' : $pendingCountTodayBottom }}
                                </span>
                            @endif
                        </div>
                        <span class="text-xs">Ruangan</span>
                    </a>
                    
                    <!-- Jadwal Ruangan dengan Badge AKAN DATANG & SEDANG BERLANGSUNG (HARI INI) -->
                    <a href="{{ route('pegawai.jadwal-ruangan.index') }}" 
                       class="flex flex-col items-center relative {{ request()->routeIs('pegawai.jadwal-ruangan*') ? 'text-blue-600 font-medium' : 'text-gray-600' }} transition-colors">
                        <div class="relative">
                            <i class="fas fa-calendar text-sm mb-1"></i>
                            @php
                                $activeSchedulesBottom = \App\Models\PeminjamanRuangan::where('status', 'disetujui')
                                    ->whereIn('status_real_time', ['akan_datang', 'sedang_berlangsung'])
                                    ->whereDate('tanggal_mulai', now()->format('Y-m-d'))
                                    ->count();
                            @endphp
                            @if($activeSchedulesBottom > 0)
                                <span class="absolute -top-2 -right-3 bg-green-500 text-white text-xs font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 animate-pulse">
                                    {{ $activeSchedulesBottom > 9 ? '9+' : $activeSchedulesBottom }}
                                </span>
                            @endif
                        </div>
                        <span class="text-xs">Jadwal</span>
                    </a>
                    
                    <!-- Laporan -->
                    <a href="{{ route('pegawai.laporan.index') }}" 
                       class="flex flex-col items-center {{ request()->routeIs('pegawai.laporan*') ? 'text-blue-600 font-medium' : 'text-gray-600' }} transition-colors">
                        <i class="fas fa-chart-bar text-sm mb-1"></i>
                        <span class="text-xs">Laporan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Logout -->
    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4" onclick="if(event.target === this) hideLogoutModal()">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-blue-100 rounded-full mb-4">
                    <i class="fas fa-sign-out-alt text-2xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Konfirmasi Logout</h3>
                <p class="text-gray-600 text-center mb-6">
                    Apakah Anda yakin ingin keluar dari sistem? Anda perlu login kembali untuk mengakses dashboard.
                </p>
                <form id="logoutForm" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <div class="flex space-x-3">
                        <button type="button" onclick="hideLogoutModal()" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-gradient-to-r from-primary-600 to-primary-700 text-white font-medium rounded-lg hover:from-primary-700 hover:to-primary-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                            Ya, Logout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        /* Focus styles */
        button:focus, a:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Animasi untuk toast notification */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .toast-enter {
            animation: slideIn 0.3s ease-out forwards;
        }

        .toast-exit {
            animation: slideOut 0.3s ease-in forwards;
        }

        /* Toast notification styles */
        .toast-notification {
            min-width: 320px;
            max-width: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            border-left: 4px solid;
            overflow: hidden;
        }

        .toast-success {
            border-left-color: #10b981;
        }

        .toast-success .toast-icon {
            color: #10b981;
        }

        .toast-info {
            border-left-color: #3b82f6;
        }

        .toast-info .toast-icon {
            color: #3b82f6;
        }

        .toast-warning {
            border-left-color: #f59e0b;
        }

        .toast-warning .toast-icon {
            color: #f59e0b;
        }

        .toast-error {
            border-left-color: #ef4444;
        }

        .toast-error .toast-icon {
            color: #ef4444;
        }

        /* Animasi hover untuk foto profil */
        .group:hover .group-hover\:scale-105 {
            transform: scale(1.05);
        }

        /* Animasi pulse untuk badge */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.9;
            }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>

    <script>
        // Sidebar Toggle untuk Mobile
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openMobileSidebar() {
            mobileSidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            mobileSidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        if (openSidebarBtn) {
            openSidebarBtn.addEventListener('click', openMobileSidebar);
        }
        
        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeMobileSidebar);
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }

        // Close sidebar saat klik link
        document.querySelectorAll('#mobileSidebar a').forEach(link => {
            link.addEventListener('click', closeMobileSidebar);
        });

        // Responsive behavior
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                closeMobileSidebar();
            }
        });

        // Escape key untuk close sidebar dan modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileSidebar();
                hideLogoutModal();
            }
        });

        // Auto-hide flash messages
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.bg-green-50, .bg-red-50');
            flashMessages.forEach(message => {
                if (message) {
                    message.style.opacity = '0';
                    message.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        if (message.parentNode) {
                            message.remove();
                        }
                    }, 500);
                }
            });
        }, 5000);

        // Fungsi untuk modal logout
        function showLogoutModal() {
            const modal = document.getElementById('logoutModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function hideLogoutModal() {
            const modal = document.getElementById('logoutModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // Fungsi untuk menampilkan toast notification
        function showToast(type, title, message = '', duration = 5000) {
            const container = document.getElementById('toastContainer');
            const toastId = 'toast-' + Date.now();
            
            let icon = '';
            let bgColor = '';
            let borderColor = '';
            
            if (type === 'success') {
                icon = '<i class="fas fa-check-circle text-green-500 text-xl"></i>';
                bgColor = 'bg-green-50';
                borderColor = 'border-green-500';
            } else if (type === 'info') {
                icon = '<i class="fas fa-info-circle text-blue-500 text-xl"></i>';
                bgColor = 'bg-blue-50';
                borderColor = 'border-blue-500';
            } else if (type === 'warning') {
                icon = '<i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>';
                bgColor = 'bg-yellow-50';
                borderColor = 'border-yellow-500';
            } else if (type === 'error') {
                icon = '<i class="fas fa-exclamation-circle text-red-500 text-xl"></i>';
                bgColor = 'bg-red-50';
                borderColor = 'border-red-500';
            }

            const toastHTML = `
                <div id="${toastId}" class="toast-notification ${bgColor} border-l-4 ${borderColor} shadow-lg rounded-lg p-4 toast-enter">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 mr-3">
                            ${icon}
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-800">${title}</p>
                            ${message ? `<p class="text-xs text-gray-600 mt-1">${message}</p>` : ''}
                        </div>
                        <button onclick="removeToast('${toastId}')" class="ml-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="absolute bottom-0 left-0 h-1 bg-gradient-to-r from-blue-500 to-primary-600" style="width: 100%; animation: progressBar ${duration}ms linear forwards;"></div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', toastHTML);

            setTimeout(() => {
                removeToast(toastId);
            }, duration);
        }

        function removeToast(toastId) {
            const toast = document.getElementById(toastId);
            if (toast) {
                toast.classList.remove('toast-enter');
                toast.classList.add('toast-exit');
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }
        }

        // Global function
        window.showLogoutModal = showLogoutModal;
        window.hideLogoutModal = hideLogoutModal;
        window.showToast = showToast;
        window.removeToast = removeToast;

        // Tambahkan style untuk progress bar
        const style = document.createElement('style');
        style.textContent = `
            @keyframes progressBar {
                from { width: 100%; }
                to { width: 0%; }
            }
        `;
        document.head.appendChild(style);
    </script>
    
    @stack('scripts')
</body>
</html>