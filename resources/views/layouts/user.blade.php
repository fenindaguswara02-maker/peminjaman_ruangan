<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- META TAGS UNTUK MENCEGAH CACHE -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <title>@yield('title', 'Dashboard User') - RoomBooking</title>
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
    <!-- User Layout dengan Navbar di Atas -->
    <div class="min-h-screen flex flex-col">
        <!-- Navbar Utama di Atas -->
        <nav class="bg-gradient-to-r from-primary-700 to-primary-800 text-white shadow-lg sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo dan Brand -->
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-building text-lg text-primary-700"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-lg font-bold">RoomBooking</h1>
                            <p class="text-xs text-blue-200">User Panel</p>
                        </div>
                    </div>

                    <!-- Desktop Navigation Menu -->
                    <div class="hidden lg:flex items-center space-x-1">
                        <a href="{{ route('user.dashboard') }}" class="flex items-center space-x-2 px-4 py-2 rounded-lg {{ request()->routeIs('user.dashboard') ? 'bg-blue-700 bg-opacity-50' : 'hover:bg-blue-700 hover:bg-opacity-30' }} transition-all duration-200">
                            <i class="fas fa-home w-5"></i>
                            <span class="font-medium">Home</span>
                        </a>

                        <a href="{{ route('user.ruangan.index') }}" class="flex items-center space-x-2 px-4 py-2 rounded-lg {{ request()->routeIs('user.peminjaman-ruangan.create') ? 'bg-blue-700 bg-opacity-50' : 'hover:bg-blue-700 hover:bg-opacity-30' }} transition-all duration-200">
                            <i class="fas fa-door-open w-5"></i>
                            <span class="font-medium">Ruangan</span>
                        </a>

                        <a href="{{ route('user.peminjaman-ruangan.riwayat') }}" class="flex items-center space-x-2 px-4 py-2 rounded-lg {{ request()->routeIs('user.peminjaman-ruangan.riwayat') ? 'bg-blue-700 bg-opacity-50' : 'hover:bg-blue-700 hover:bg-opacity-30' }} transition-all duration-200">
                            <i class="fas fa-history w-5"></i>
                            <span class="font-medium">Riwayat</span>
                        </a>

                        <a href="{{ route('user.lihat-jadwal') }}" class="flex items-center space-x-2 px-4 py-2 rounded-lg {{ request()->routeIs('user.lihat-jadwal') ? 'bg-blue-700 bg-opacity-50' : 'hover:bg-blue-700 hover:bg-opacity-30' }} transition-all duration-200">
                            <i class="fas fa-calendar-check w-5"></i>
                            <span class="font-medium">Jadwal</span>
                        </a>
                    </div>

                    <!-- User Profile dan Mobile Menu Button -->
                    <div class="flex items-center space-x-4">
                        <!-- User Info Desktop -->
                        <div class="hidden md:flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-sm font-medium truncate max-w-xs">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-blue-300 truncate hidden lg:block">{{ auth()->user()->email }}</p>
                            </div>
                            
                            <!-- DROPDOWN USER -->
                            <div class="relative" id="userDropdownContainer">
                                <button id="userDropdownButton" 
                                        type="button"
                                        class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm cursor-pointer hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-primary-700 transition-all duration-200 group"
                                        aria-expanded="false"
                                        aria-haspopup="true">
                                    @if(auth()->user()->foto)
                                        <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center">
                                            @php
                                                $initial = strtoupper(substr(auth()->user()->name, 0, 1));
                                            @endphp
                                            <span class="text-primary-700 font-bold text-lg">{{ $initial }}</span>
                                        </div>
                                    @endif
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div id="userDropdownMenu" 
                                     class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-xl border border-gray-200 hidden z-50"
                                     role="menu"
                                     aria-orientation="vertical"
                                     aria-labelledby="userDropdownButton">
                                    
                                    <!-- Header User Info dengan Username -->
                                    <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-primary-50 to-blue-50 rounded-t-lg">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                @if(auth()->user()->foto)
                                                    <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-14 h-14 rounded-full object-cover border-2 border-white shadow-md">
                                                @else
                                                    <div class="w-14 h-14 bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center shadow-md">
                                                        @php
                                                            $initial = strtoupper(substr(auth()->user()->name, 0, 1));
                                                        @endphp
                                                        <span class="text-primary-700 font-bold text-2xl">{{ $initial }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                                <!-- TAMBAHKAN USERNAME DI SINI -->
                                                <div class="flex items-center mt-1">
                                                    <i class="fas fa-at text-xs text-cyan-600 mr-1"></i>
                                                    <p class="text-xs text-cyan-700 font-mono truncate">{{ auth()->user()->username ?? 'Belum diatur' }}</p>
                                                </div>
                                                <p class="text-xs text-gray-500 truncate mt-1">{{ auth()->user()->email }}</p>
                                                <p class="text-xs mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ auth()->user()->role ?? 'User' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Menu Items -->
                                    <div class="py-1">
                                        <a href="{{ route('profil.index') }}" 
                                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150"
                                           role="menuitem">
                                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center mr-3">
                                                <i class="fas fa-user-circle text-blue-600"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900">Profil Saya</p>
                                                <p class="text-xs text-gray-500">Lihat dan edit profil Anda</p>
                                            </div>
                                        </a>
                                    </div>
                                    
                                    <!-- Footer - Logout -->
                                    <div class="border-t border-gray-100 p-2">
                                        <form method="POST" action="{{ route('logout') }}" id="logoutForm" onsubmit="return handleLogout(event)">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-150"
                                                    role="menuitem">
                                                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-sign-out-alt text-red-600"></i>
                                                </div>
                                                <div class="flex-1 text-left">
                                                    <p class="font-medium text-red-600">Logout</p>
                                                    <p class="text-xs text-red-500">Keluar dari sistem</p>
                                                </div>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Menu Button -->
                        <button id="mobileMenuButton" class="lg:hidden text-white hover:text-blue-300 transition-colors p-2">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Dropdown Menu -->
            <div id="mobileMenu" class="lg:hidden bg-primary-800 border-t border-blue-700 hidden">
                <div class="px-4 py-3 space-y-1">
                    <!-- User Info Mobile dengan Username -->
                    <div class="flex items-center space-x-3 pb-3 border-b border-blue-700">
                        <div class="flex-shrink-0">
                            @if(auth()->user()->foto)
                                <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-12 h-12 rounded-full object-cover border-2 border-white">
                            @else
                                <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm">
                                    @php
                                        $initial = strtoupper(substr(auth()->user()->name, 0, 1));
                                    @endphp
                                    <span class="text-primary-700 font-bold text-xl">{{ $initial }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate text-white">{{ auth()->user()->name }}</p>
                            <!-- TAMBAHKAN USERNAME DI MOBILE -->
                            <div class="flex items-center mt-1">
                                <i class="fas fa-at text-xs text-cyan-300 mr-1"></i>
                                <p class="text-xs text-cyan-200 truncate">{{ auth()->user()->username ?? 'Belum diatur' }}</p>
                            </div>
                            <p class="text-xs text-blue-300 truncate">{{ auth()->user()->email }}</p>
                            <p class="text-xs text-blue-200 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-600 text-white">
                                    {{ auth()->user()->role ?? 'User' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <!-- Mobile Navigation Links -->
                    <a href="{{ route('user.dashboard') }}" class="flex items-center space-x-3 p-3 hover:bg-primary-600 rounded-lg transition-colors {{ request()->routeIs('user.dashboard') ? 'bg-primary-600' : '' }}" onclick="closeMobileMenu()">
                        <i class="fas fa-home w-5"></i>
                        <span class="font-medium">Home</span>
                    </a>

                    <a href="{{ route('user.ruangan.index') }}" class="flex items-center space-x-3 p-3 hover:bg-primary-600 rounded-lg transition-colors {{ request()->routeIs('user.peminjaman-ruangan.create') ? 'bg-primary-600' : '' }}" onclick="closeMobileMenu()">
                        <i class="fas fa-door-open w-5"></i>
                        <span class="font-medium">Ruangan</span>
                    </a>

                    <a href="{{ route('user.peminjaman-ruangan.riwayat') }}" class="flex items-center space-x-3 p-3 hover:bg-primary-600 rounded-lg transition-colors {{ request()->routeIs('user.peminjaman-ruangan.riwayat') ? 'bg-primary-600' : '' }}" onclick="closeMobileMenu()">
                        <i class="fas fa-history w-5"></i>
                        <span class="font-medium">Riwayat</span>
                    </a>

                    <a href="{{ route('user.lihat-jadwal') }}" class="flex items-center space-x-3 p-3 hover:bg-primary-600 rounded-lg transition-colors {{ request()->routeIs('user.lihat-jadwal') ? 'bg-primary-600' : '' }}" onclick="closeMobileMenu()">
                        <i class="fas fa-calendar-check w-5"></i>
                        <span class="font-medium">Lihat Jadwal</span>
                    </a>

                    <!-- Menu Profil di Mobile -->
                    <div class="pt-3 mt-3 border-t border-blue-700">
                        <div class="text-xs font-semibold text-blue-300 px-3 mb-2">AKUN</div>
                        
                        <a href="{{ route('profil.index') }}" class="flex items-center space-x-3 p-3 hover:bg-primary-600 rounded-lg transition-colors {{ request()->routeIs('profil.index') ? 'bg-primary-600' : '' }}" onclick="closeMobileMenu()">
                            <div class="w-8 h-8 bg-blue-600 bg-opacity-30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-circle text-white"></i>
                            </div>
                            <div class="flex-1">
                                <span class="font-medium block">Profil Saya</span>
                                <span class="text-xs text-blue-300">Lihat dan edit profil</span>
                            </div>
                        </a>
                    </div>
                    
                    <!-- Logout Mobile -->
                    <div class="pt-3 mt-3 border-t border-blue-700">
                        <form method="POST" action="{{ route('logout') }}" onsubmit="return handleLogout(event)">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center space-x-2 bg-red-500 hover:bg-red-600 px-4 py-3 rounded-lg transition-colors">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="font-medium">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
            <!-- Page Header -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        <p class="text-gray-600 mt-1">
                            {{ now()->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-4 md:mt-0">
                        @yield('header-actions')
                    </div>
                </div>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-lg shadow-sm animate-fadeIn">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3 text-lg"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg shadow-sm animate-fadeIn">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-700 rounded-lg shadow-sm animate-fadeIn">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-3 text-lg"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                </div>
            @endif

            <!-- Content -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between text-sm text-gray-600">
                    <div class="mb-4 md:mb-0">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-building text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">RoomBooking System</p>
                                <p>&copy; {{ date('Y') }} - Sistem Peminjaman Ruangan</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <p class="font-medium text-gray-800">Pengguna Aktif</p>
                        <div class="flex items-center">
                            <div class="w-6 h-6 bg-primary-100 rounded-full flex items-center justify-center mr-2">
                                @if(auth()->user()->foto)
                                    <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="{{ auth()->user()->name }}" class="w-6 h-6 rounded-full object-cover">
                                @else
                                    <i class="fas fa-user text-xs text-primary-600"></i>
                                @endif
                            </div>
                            <span>{{ auth()->user()->name }}</span>
                            <span class="text-xs text-gray-400 ml-2">(@{{ auth()->user()->username ?? 'user' }})</span>
                        </div>
                        
                        <p class="text-gray-500 text-xs">
                            <i class="fas fa-clock mr-1"></i> 
                            Login terakhir: 
                            @php
                                $lastLogin = auth()->user()->last_login_at;
                            @endphp
                            
                            @if($lastLogin)
                                {{ \Carbon\Carbon::parse($lastLogin)->format('H:i') }}
                            @else
                                {{ now()->format('H:i') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Bottom Navigation untuk Mobile -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-2 px-4 z-30 shadow-lg">
            <div class="flex justify-between items-center">
                <a href="{{ route('user.dashboard') }}" class="flex flex-col items-center text-center {{ request()->routeIs('user.dashboard') ? 'text-primary-600 font-semibold' : 'text-gray-600' }}">
                    <i class="fas fa-home mb-1 text-lg"></i>
                    <span class="text-xs">Home</span>
                </a>
                <a href="{{ route('user.ruangan.index') }}" class="flex flex-col items-center text-center {{ request()->routeIs('user.peminjaman-ruangan.create') ? 'text-primary-600 font-semibold' : 'text-gray-600' }}">
                    <i class="fas fa-door-open mb-1 text-lg"></i>
                    <span class="text-xs">Ruangan</span>
                </a>
                <a href="{{ route('user.peminjaman-ruangan.riwayat') }}" class="flex flex-col items-center text-center {{ request()->routeIs('user.peminjaman-ruangan.riwayat') ? 'text-primary-600 font-semibold' : 'text-gray-600' }}">
                    <i class="fas fa-history mb-1 text-lg"></i>
                    <span class="text-xs">Riwayat</span>
                </a>
                <a href="{{ route('user.lihat-jadwal') }}" class="flex flex-col items-center text-center {{ request()->routeIs('user.lihat-jadwal') ? 'text-primary-600 font-semibold' : 'text-gray-600' }}">
                    <i class="fas fa-calendar-check mb-1 text-lg"></i>
                    <span class="text-xs">Jadwal</span>
                </a>
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
            transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease, opacity 0.2s ease;
        }

        /* Focus styles */
        button:focus, a:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out;
        }

        /* Hover card effects */
        .hover-card {
            transition: all 0.3s ease;
        }
        
        .hover-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -8px rgba(0, 0, 0, 0.1);
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .responsive-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            
            .card-responsive {
                margin-bottom: 1rem;
                padding: 1rem;
            }
        }

        /* Loading spinner */
        .spinner {
            border: 3px solid rgba(59, 130, 246, 0.2);
            border-radius: 50%;
            border-top: 3px solid #3b82f6;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Bottom navigation padding for mobile */
        @media (max-width: 1024px) {
            main {
                padding-bottom: 80px;
            }
        }

        /* Dropdown animation */
        #userDropdownMenu {
            transform-origin: top right;
            animation: dropdownFade 0.2s ease-out;
        }

        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Prevent flash of unstyled content */
        .no-fouc {
            display: none;
        }
        
        /* Hover effect untuk avatar */
        .group:hover .group-hover\:scale-110 {
            transform: scale(1.1);
        }
    </style>

    <script>
        // ==================== CEK SESSION SEBELUM HALAMAN DITAMPILKAN ====================
        (function() {
            // Push state baru untuk mencegah back button
            history.pushState(null, null, location.href);
            
            // Tangkap event popstate (back button)
            window.addEventListener('popstate', function(event) {
                history.pushState(null, null, location.href);
                location.reload();
            });
            
            // Deteksi jika halaman dimuat dari cache (back button)
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });
            
            // Cek session secara berkala
            setInterval(function() {
                fetch('/check-session', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.authenticated) {
                        window.location.replace('/login');
                    }
                })
                .catch(() => {
                    window.location.replace('/login');
                });
            }, 30000);
        })();

        // ==================== HANDLE LOGOUT ====================
        function handleLogout(event) {
            event.preventDefault();
            
            const form = event.target.closest('form');
            
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.replace(response.url);
                    } else {
                        window.location.replace('/');
                    }
                })
                .catch(() => {
                    window.location.replace('/');
                });
            }
            
            return false;
        }

        // ==================== DROPDOWN USER PROFILE ====================
        document.addEventListener('DOMContentLoaded', function() {
            const userDropdownContainer = document.getElementById('userDropdownContainer');
            const userDropdownButton = document.getElementById('userDropdownButton');
            const userDropdownMenu = document.getElementById('userDropdownMenu');
            
            if (userDropdownButton && userDropdownMenu && userDropdownContainer) {
                userDropdownButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userDropdownMenu.classList.toggle('hidden');
                    userDropdownButton.setAttribute('aria-expanded', !userDropdownMenu.classList.contains('hidden'));
                });
                
                document.addEventListener('click', function(event) {
                    if (!userDropdownContainer.contains(event.target)) {
                        if (!userDropdownMenu.classList.contains('hidden')) {
                            userDropdownMenu.classList.add('hidden');
                            userDropdownButton.setAttribute('aria-expanded', 'false');
                        }
                    }
                });
                
                userDropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        });

        // ==================== MOBILE MENU ====================
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');

        function toggleMobileMenu() {
            mobileMenu.classList.toggle('hidden');
            const icon = mobileMenuButton.querySelector('i');
            if (mobileMenu.classList.contains('hidden')) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        }

        function closeMobileMenu() {
            mobileMenu.classList.add('hidden');
            const icon = mobileMenuButton.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', toggleMobileMenu);
        }

        document.addEventListener('click', function(event) {
            if (mobileMenuButton && mobileMenu) {
                if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                    closeMobileMenu();
                }
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                closeMobileMenu();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMobileMenu();
                const userDropdownMenu = document.getElementById('userDropdownMenu');
                if (userDropdownMenu && !userDropdownMenu.classList.contains('hidden')) {
                    userDropdownMenu.classList.add('hidden');
                }
            }
        });

        // ==================== AUTO-HIDE FLASH MESSAGES ====================
        setTimeout(() => {
            const flashMessages = document.querySelectorAll('.bg-green-50, .bg-red-50, .bg-yellow-50');
            flashMessages.forEach(message => {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(() => message.remove(), 500);
            });
        }, 5000);

        // ==================== STICKY NAVBAR ====================
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 10) {
                nav.classList.add('shadow-lg');
            } else {
                nav.classList.remove('shadow-lg');
            }
        });
    </script>
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>