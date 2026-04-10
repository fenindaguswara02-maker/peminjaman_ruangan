@extends('layouts.user')

@section('title', 'Dashboard - Peminjaman Ruangan')

@section('page-title', 'Dashboard Peminjaman Ruangan')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Welcome Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ auth()->user()->name }}!</h1>
                <p class="text-gray-600 mt-2">Kelola peminjaman ruangan dengan mudah dan efisien</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Hari ini</p>
                <p class="text-lg font-semibold text-primary-600">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-primary-100">Peminjaman Aktif</p>
                    <p class="text-3xl font-bold mt-2">{{ $activeBookings ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-door-open text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100">Menunggu Konfirmasi</p>
                    <p class="text-3xl font-bold mt-2">{{ $pendingBookings ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-clock text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100">Total Peminjaman</p>
                    <p class="text-3xl font-bold mt-2">{{ $totalBookings ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('user.peminjaman-ruangan.create') }}" class="bg-white rounded-xl shadow-sm p-6 text-center hover:shadow-md transition-shadow border border-gray-100">
            <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-plus-circle text-primary-600 text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">Ajukan Peminjaman</h3>
            <p class="text-sm text-gray-600">Ajukan peminjaman ruangan baru</p>
        </a>

        <a href="{{ route('user.peminjaman-ruangan.riwayat') }}" class="bg-white rounded-xl shadow-sm p-6 text-center hover:shadow-md transition-shadow border border-gray-100">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-history text-green-600 text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">Riwayat Peminjaman</h3>
            <p class="text-sm text-gray-600">Lihat semua history peminjaman</p>
        </a>

        <a href="{{ route('user.lihat-jadwal') }}" class="bg-white rounded-xl shadow-sm p-6 text-center hover:shadow-md transition-shadow border border-gray-100">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-search text-purple-600 text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-800 mb-2">Cek Ketersediaan</h3>
            <p class="text-sm text-gray-600">Periksa jadwal ruangan tersedia</p>
        </a>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Aktivitas Terbaru</h2>
            <a href="{{ route('user.peminjaman-ruangan.riwayat') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                Lihat Semua
            </a>
        </div>
        
        <div class="space-y-4">
            @forelse($recentActivities as $activity)
            <div class="flex items-center justify-between p-4 {{ $activity->status === 'disetujui' ? 'bg-primary-50' : ($activity->status === 'menunggu' ? 'bg-yellow-50' : ($activity->status === 'ditolak' ? 'bg-red-50' : 'bg-gray-50')) }} rounded-lg">
                <div class="flex items-center">
                    <div class="w-10 h-10 {{ $activity->status === 'disetujui' ? 'bg-primary-100' : ($activity->status === 'menunggu' ? 'bg-yellow-100' : ($activity->status === 'ditolak' ? 'bg-red-100' : 'bg-gray-100')) }} rounded-full flex items-center justify-center">
                        <i class="fas fa-door-open {{ $activity->status === 'disetujui' ? 'text-primary-600' : ($activity->status === 'menunggu' ? 'text-yellow-600' : ($activity->status === 'ditolak' ? 'text-red-600' : 'text-gray-600')) }}"></i>
                    </div>
                    <div class="ml-4">
                        <p class="font-semibold text-gray-800">
                            {{ $activity->ruangan->nama_ruangan ?? 'Ruangan' }} - {{ $activity->acara }}
                        </p>
                        <p class="text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($activity->tanggal)->translatedFormat('d M Y') }}, 
                            {{ substr($activity->jam_mulai, 0, 5) }} - {{ substr($activity->jam_selesai, 0, 5) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Peserta: {{ $activity->jumlah_peserta }} orang
                            • Diajukan: {{ \Carbon\Carbon::parse($activity->created_at)->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
                <span class="px-3 py-1 {{ $activity->status === 'disetujui' ? 'bg-green-100 text-green-800' : ($activity->status === 'menunggu' ? 'bg-yellow-100 text-yellow-800' : ($activity->status === 'ditolak' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }} text-sm rounded-full capitalize">
                    {{ $activity->status_label }}
                </span>
            </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-calendar-times text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">Belum ada aktivitas peminjaman</p>
                <a href="{{ route('user.peminjaman-ruangan.create') }}" class="inline-block mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Ajukan Peminjaman Pertama
                </a>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Peminjaman Mendatang -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Peminjaman Mendatang</h2>
            <a href="{{ route('user.peminjaman-ruangan.riwayat') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                Lihat Semua
            </a>
        </div>
        
        <div class="space-y-4">
            @forelse($upcomingBookings as $booking)
            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-100">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-door-closed text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="font-semibold text-gray-800">{{ $booking->ruangan->nama_ruangan ?? 'Ruangan' }}</p>
                        <p class="text-sm text-gray-600">
                            {{ \Carbon\Carbon::parse($booking->tanggal)->translatedFormat('d M Y') }}, 
                            {{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $booking->acara }} • {{ $booking->jumlah_peserta }} peserta
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-3 py-1 {{ $booking->status === 'disetujui' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} text-sm rounded-full capitalize">
                        {{ $booking->status_label }}
                    </span>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ \Carbon\Carbon::parse($booking->tanggal)->diffForHumans() }}
                    </p>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-calendar-day text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">Tidak ada peminjaman mendatang</p>
                <a href="{{ route('user.peminjaman-ruangan.create') }}" class="inline-block mt-4 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Ajukan Peminjaman
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection