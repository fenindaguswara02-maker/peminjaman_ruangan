@extends('layouts.home')

@section('title', 'RoomBooking - Sistem Peminjaman Ruangan Digital')

@section('content')
<style>
    .hero-section {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    
    .card-shadow {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .hover-lift {
        transition: all 0.3s ease;
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .gradient-bg {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    }
    
    .logo-container {
        transition: all 0.3s ease;
        border-radius: 20px;
        overflow: hidden;
    }
    
    .logo-container:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }
    
    .roombooking-logo {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .room-card {
        border-left: 4px solid #1e40af;
        transition: all 0.3s ease;
    }
    
    .room-card:hover {
        border-left-color: #3b82f6;
        transform: translateX(5px);
    }
    
    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .available {
        background-color: #10b981;
    }
    
    .occupied {
        background-color: #ef4444;
    }
    
    .maintenance {
        background-color: #f59e0b;
    }
    
    .feature-icon {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        border-radius: 12px;
        padding: 15px;
    }
    
    .ruangan-highlight {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border: 2px solid #3b82f6;
        border-radius: 20px;
    }
    
    .room-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 15px;
    }
    
    .room-image-placeholder {
        width: 100%;
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
    }
    
    .fasilitas-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .fasilitas-tag {
        background-color: #eff6ff;
        color: #1d4ed8;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
</style>

<!-- Navigation -->
<nav class="gradient-bg text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center">
                <!-- Logo RoomBooking -->
                <div class="logo-container flex items-center justify-center w-16 h-16 mr-3 bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="roombooking-logo w-full h-full flex items-center justify-center">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                </div>
                <h1 class="text-2xl font-bold elegant-font">RoomBooking</h1>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="#ruangan" class="hover:text-blue-200 transition-colors">Ruangan</a>
                <a href="#features" class="hover:text-blue-200 transition-colors">Fitur</a>
                <a href="#panduan" class="hover:text-blue-200 transition-colors">Panduan</a>
                <a href="#contact" class="hover:text-blue-200 transition-colors">Kontak</a>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section text-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
        <div class="flex flex-col items-center">
            <!-- Logo Hero -->
            <div class="logo-container flex items-center justify-center w-48 h-48 bg-white rounded-2xl shadow-2xl overflow-hidden mb-8">
                <div class="roombooking-logo w-full h-full flex flex-col items-center justify-center p-8">
                    <i class="fas fa-building text-white text-6xl mb-4"></i>
                    <span class="text-2xl font-bold">RoomBooking</span>
                </div>
            </div>
            
            <h1 class="text-5xl md:text-6xl font-bold mb-6 elegant-font">
                Sistem Peminjaman Ruangan <span class="text-blue-200">Digital</span>
            </h1>
            <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-8 leading-relaxed">
                Kelola peminjaman ruangan secara digital dengan mudah, cepat, dan efisien
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="/login" 
                   class="bg-white text-blue-700 hover:bg-blue-50 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 hover-lift">
                    <i class="fas fa-sign-in-alt mr-3"></i> Masuk ke Sistem
                </a>
                <a href="#ruangan" 
                   class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-blue-700 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 hover-lift">
                    <i class="fas fa-search mr-3"></i> Lihat Ruangan Terbaru
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Ruangan Section -->
<section id="ruangan" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-blue-800 elegant-font mb-4">Ruangan Terbaru</h2>
            <p class="text-xl text-blue-600 max-w-3xl mx-auto">
                Lihat 3 ruangan terbaru yang tersedia di sistem kami
            </p>
        </div>
        
        @if(isset($ruangan) && count($ruangan) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
            @foreach($ruangan->sortByDesc('created_at')->take(3) as $room)
            <div class="room-card bg-white rounded-2xl card-shadow p-6 hover-lift">
                <!-- Foto Ruangan -->
                @if(isset($room->gambar) && $room->gambar)
                <img src="{{ asset('storage/' . $room->gambar) }}" alt="{{ $room->nama_ruangan }}" class="room-image">
                @else
                <div class="room-image-placeholder">
                    <i class="fas fa-building"></i>
                </div>
                @endif
                
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-blue-800">{{ $room->nama_ruangan }}</h3>
                        <div class="flex items-center mt-2">
                            @if($room->status == 'tersedia')
                            <span class="status-indicator available"></span>
                            <span class="text-green-600 font-medium">Tersedia</span>
                            @elseif($room->status == 'dipinjam')
                            <span class="status-indicator occupied"></span>
                            <span class="text-red-600 font-medium">Terpakai</span>
                            @else
                            <span class="status-indicator maintenance"></span>
                            <span class="text-yellow-600 font-medium">Maintenance</span>
                            @endif
                        </div>
                    </div>
                    <div class="feature-icon">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                </div>
                
                <p class="text-gray-600 mb-4 leading-relaxed">
                    {{ $room->fasilitas ? substr($room->fasilitas, 0, 100) . (strlen($room->fasilitas) > 100 ? '...' : '') : 'Ruangan dengan fasilitas lengkap untuk berbagai keperluan.' }}
                </p>
                
                <!-- Fasilitas -->
                @if($room->fasilitas)
                <div class="fasilitas-list mb-4">
                    @php
                        $fasilitas = explode(',', $room->fasilitas);
                        $limitedFasilitas = array_slice($fasilitas, 0, 3);
                    @endphp
                    @foreach($limitedFasilitas as $fasilitasItem)
                    <span class="fasilitas-tag">{{ trim($fasilitasItem) }}</span>
                    @endforeach
                    @if(count($fasilitas) > 3)
                    <span class="fasilitas-tag">+{{ count($fasilitas) - 3 }} lainnya</span>
                    @endif
                </div>
                @endif
                
                <div class="flex items-center justify-between">
                    <span class="text-blue-700 font-bold">Kapasitas: {{ $room->kapasitas }} orang</span>
                    @if($room->status == 'tersedia')
                    <a href="/login" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Pinjam Sekarang
                    </a>
                    @else
                    <span class="text-gray-400 px-4 py-2">
                        @if($room->status == 'dipinjam')
                        Sedang Dipinjam
                        @else
                        Dalam Perbaikan
                        @endif
                    </span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center py-12">
            <div class="inline-block p-8 bg-white rounded-2xl card-shadow">
                <i class="fas fa-door-closed text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-600 mb-2">Belum Ada Ruangan</h3>
                <p class="text-gray-500">Tidak ada data ruangan yang tersedia saat ini.</p>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-blue-800 elegant-font mb-4">Fitur RoomBooking</h2>
            <p class="text-xl text-blue-600 max-w-3xl mx-auto">
                Sistem peminjaman ruangan yang cerdas dengan fitur-fitur inovatif
            </p>
        </div>
        
       <div class="flex justify-center">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl">
        <!-- Fitur 1 -->
        <div class="bg-white rounded-2xl card-shadow p-8 hover-lift border border-blue-100">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-6 mx-auto shadow-lg">
                <i class="fas fa-calendar-check text-white text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-blue-800 mb-4 text-center">Peminjaman Online</h3>
            <p class="text-blue-600 text-center leading-relaxed">
                Pinjam ruangan kapan saja melalui sistem online dengan proses yang cepat dan mudah
            </p>
        </div>
        
        <!-- Fitur 2 -->
        <div class="bg-white rounded-2xl card-shadow p-8 hover-lift border border-blue-100">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-6 mx-auto shadow-lg">
                <i class="fas fa-bell text-white text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-blue-800 mb-4 text-center">Status Real-time</h3>
            <p class="text-blue-600 text-center leading-relaxed">
                Pantau status peminjaman secara langsung mulai dari pengajuan,persetujuan,hingga selesai 
            </p>
        </div>
        
        <!-- Fitur 3 -->
        <div class="bg-white rounded-2xl card-shadow p-8 hover-lift border border-blue-100">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center mb-6 mx-auto shadow-lg">
                <i class="fas fa-chart-bar text-white text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-blue-800 mb-4 text-center">Analitik Penggunaan</h3>
            <p class="text-blue-600 text-center leading-relaxed">
                Pantau statistik penggunaan ruangan untuk optimalisasi fasilitas
            </p>
        </div>
    </div>
</div>
</section>

<!-- Panduan Section -->
<section id="panduan" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-blue-800 elegant-font mb-4">Panduan Peminjaman</h2>
            <p class="text-xl text-blue-600 max-w-3xl mx-auto">
                Langkah mudah meminjam ruangan melalui RoomBooking
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Step 1 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                    1
                </div>
                <h3 class="text-xl font-bold text-blue-800 mb-3">Login/Register</h3>
                <p class="text-blue-600">
                    Akses sistem dengan akun Anda atau daftar terlebih dahulu
                </p>
            </div>
            
            <!-- Step 2 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                    2
                </div>
                <h3 class="text-xl font-bold text-blue-800 mb-3">Pilih Ruangan</h3>
                <p class="text-blue-600">
                    Lihat daftar ruangan tersedia dan pilih sesuai kebutuhan
                </p>
            </div>
            
            <!-- Step 3 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                    3
                </div>
                <h3 class="text-xl font-bold text-blue-800 mb-3">Atur Jadwal</h3>
                <p class="text-blue-600">
                    Tentukan tanggal dan waktu peminjaman yang diinginkan
                </p>
            </div>
            
            <!-- Step 4 -->
            <div class="text-center">
                <div class="w-16 h-16 rounded-full bg-blue-600 text-white flex items-center justify-center text-2xl font-bold mx-auto mb-4">
                    4
                </div>
                <h3 class="text-xl font-bold text-blue-800 mb-3">Konfirmasi</h3>
                <p class="text-blue-600">
                    Tunggu konfirmasi dan lakukan check-in saat hari H
                </p>
            </div>
        </div>
        
        <div class="mt-16 ruangan-highlight p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <div>
                    <h3 class="text-3xl font-bold text-blue-800 mb-4">Butuh Ruangan Mendadak?</h3>
                    <p class="text-blue-600 text-lg leading-relaxed mb-6">
                        RoomBooking menyediakan fitur peminjaman last-minute untuk kebutuhan mendesak. 
                        Cek ketersediaan ruangan real-time dan pinjam langsung melalui aplikasi.
                    </p>
                    <a href="/login" 
                       class="inline-flex items-center bg-blue-600 text-white hover:bg-blue-700 px-6 py-3 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-bolt mr-3"></i> Peminjaman Instan
                    </a>
                </div>
                <div class="text-center">
                    <div class="inline-block p-6 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl">
                        <i class="fas fa-clock text-blue-600 text-6xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="gradient-bg text-white py-20">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-4xl md:text-5xl font-bold elegant-font mb-6">Siap Meminjam Ruangan?</h2>
        <p class="text-xl text-blue-200 mb-8 max-w-2xl mx-auto">
            Bergabung dengan RoomBooking dan nikmati kemudahan peminjaman ruangan digital
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="/register" 
               class="bg-white text-blue-700 hover:bg-blue-50 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 hover-lift flex items-center justify-center">
                <i class="fas fa-user-plus mr-3"></i> Buat Akun Baru
            </a>
            <a href="/login" 
               class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-blue-700 px-8 py-4 rounded-xl font-semibold text-lg transition-all duration-300 hover-lift flex items-center justify-center">
                <i class="fas fa-sign-in-alt mr-3"></i> Login Sekarang
            </a>
        </div>
    </div>
</section>

<!-- Footer -->
<footer id="contact" class="bg-blue-900 text-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-2">
                <div class="flex items-center mb-6">
                    <div class="logo-container flex items-center justify-center w-16 h-16 mr-4 bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="roombooking-logo w-full h-full flex items-center justify-center">
                            <i class="fas fa-building text-white text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold elegant-font">RoomBooking</h3>
                        <p class="text-blue-300 text-sm">Sistem Peminjaman Ruangan Digital</p>
                    </div>
                </div>
                <p class="text-blue-300 mb-6 max-w-md text-lg leading-relaxed">
                    Platform digital untuk peminjaman ruangan yang efisien, transparan, dan mudah digunakan. 
                    Kelola semua kebutuhan ruangan Anda dalam satu sistem terintegrasi.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="w-12 h-12 rounded-xl bg-blue-800 flex items-center justify-center hover:bg-blue-700 transition-all duration-300 hover-lift">
                        <i class="fab fa-facebook-f text-lg"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-xl bg-blue-800 flex items-center justify-center hover:bg-blue-700 transition-all duration-300 hover-lift">
                        <i class="fab fa-twitter text-lg"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-xl bg-blue-800 flex items-center justify-center hover:bg-blue-700 transition-all duration-300 hover-lift">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                </div>
            </div>
            
            <div>
                <h4 class="text-xl font-semibold mb-6 elegant-font">Kontak Kami</h4>
                <ul class="space-y-4 text-blue-300">
                    <li class="flex items-start">
                        <i class="fas fa-envelope mr-4 mt-1"></i>
                        <span>admin@roombooking.com</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-phone mr-4 mt-1"></i>
                        <span>+62 21 1234 5678</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-map-marker-alt mr-4 mt-1"></i>
                        <span>Jl. Contoh No. 123<br>Jakarta 12345</span>
                    </li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-xl font-semibold mb-6 elegant-font">Tautan Cepat</h4>
                <ul class="space-y-3 text-blue-300">
                    <li><a href="#ruangan" class="hover:text-white transition-colors duration-300">Ruangan</a></li>
                    <li><a href="#features" class="hover:text-white transition-colors duration-300">Fitur</a></li>
                    <li><a href="#panduan" class="hover:text-white transition-colors duration-300">Panduan</a></li>
                    <li><a href="/login" class="hover:text-white transition-colors duration-300">Login</a></li>
                    <li><a href="/register" class="hover:text-white transition-colors duration-300">Daftar</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-blue-800 mt-12 pt-8 text-center">
            <p class="text-blue-400">&copy; 2025 RoomBooking - Sistem Peminjaman Ruangan Digital. Semua hak dilindungi.</p>
        </div>
    </div>
</footer>
@endsection