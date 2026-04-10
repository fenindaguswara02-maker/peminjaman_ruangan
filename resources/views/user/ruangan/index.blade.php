@extends('layouts.user')

@section('title', 'Daftar Ruangan - Scheduler')
@section('page-title', 'Daftar Ruangan')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100">Total Ruangan</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100">Tersedia</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['tersedia'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100">Dipinjam</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['dipinjam'] }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-door-closed text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-100">Maintenance</p>
                    <p class="text-3xl font-bold mt-2">{{ $stats['maintenance'] }}</p>
                </div>
                <div class="w-12 h-12 bg-gray-400 rounded-full flex items-center justify-center">
                    <i class="fas fa-tools text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <form method="GET" action="{{ route('user.ruangan.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Ruangan</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Semua Status</option>
                        <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kapasitas Minimum</label>
                    <select name="kapasitas_min" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Semua Kapasitas</option>
                        <option value="10" {{ request('kapasitas_min') == '10' ? 'selected' : '' }}>> 10 orang</option>
                        <option value="20" {{ request('kapasitas_min') == '20' ? 'selected' : '' }}>> 20 orang</option>
                        <option value="50" {{ request('kapasitas_min') == '50' ? 'selected' : '' }}>> 50 orang</option>
                        <option value="100" {{ request('kapasitas_min') == '100' ? 'selected' : '' }}>> 100 orang</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutkan Berdasarkan</label>
                    <select name="sort" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="nama_ruangan" {{ request('sort') == 'nama_ruangan' ? 'selected' : '' }}>Nama Ruangan</option>
                        <option value="kapasitas" {{ request('sort') == 'kapasitas' ? 'selected' : '' }}>Kapasitas</option>
                        <option value="kode_ruangan" {{ request('sort') == 'kode_ruangan' ? 'selected' : '' }}>Kode Ruangan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutan</label>
                    <select name="order" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>A-Z / Terkecil</option>
                        <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>Z-A / Terbesar</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <input type="text" 
                           name="search" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Cari ruangan (nama, kode, fasilitas, lokasi)..."
                           value="{{ request('search') }}">
                </div>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center">
                    <i class="fas fa-search mr-2"></i> Cari
                </button>
                <a href="{{ route('user.ruangan.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-semibold transition-colors flex items-center">
                    <i class="fas fa-redo mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Ruangan Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($ruangan as $room)
        <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
            @if($room->gambar)
            <div class="h-48 overflow-hidden">
                <img src="{{ asset('storage/' . $room->gambar) }}" 
                     alt="{{ $room->nama_ruangan }}" 
                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
            </div>
            @else
            <div class="h-48 bg-gradient-to-r from-primary-100 to-primary-200 flex items-center justify-center">
                <i class="fas fa-door-closed text-primary-400 text-6xl"></i>
            </div>
            @endif

            <div class="p-6">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">{{ $room->kode_ruangan }}</h3>
                        <h2 class="text-xl font-bold text-gray-800">{{ $room->nama_ruangan }}</h2>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $room->status == 'tersedia' ? 'bg-green-100 text-green-800' : ($room->status == 'dipinjam' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($room->status) }}
                    </span>
                </div>

                <div class="space-y-3 mb-4">
                    <!-- LOKASI - Ditambahkan di sini -->
                    @if($room->lokasi)
                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-map-marker-alt mr-2 text-primary-600"></i>
                        <span><strong>Lokasi:</strong> {{ $room->lokasi }}</span>
                    </div>
                    @endif

                    <div class="flex items-center text-gray-600">
                        <i class="fas fa-users mr-2 text-primary-600"></i>
                        <span><strong>Kapasitas:</strong> {{ $room->kapasitas }} orang</span>
                    </div>
                    
                    @if($room->fasilitas)
                    <div class="flex items-start text-gray-600">
                        <i class="fas fa-tools mr-2 text-primary-600 mt-1"></i>
                        <span class="text-sm"><strong>Fasilitas:</strong> {{ Str::limit($room->fasilitas, 80) }}</span>
                    </div>
                    @endif
                </div>

                <div class="flex justify-between items-center mt-6">
                    <a href="{{ route('user.ruangan.show', $room->id) }}" 
                       class="text-primary-600 hover:text-primary-800 font-medium flex items-center">
                        <i class="fas fa-eye mr-2"></i> Lihat Detail
                    </a>
                    
                    @if($room->status == 'tersedia')
                    <a href="{{ route('user.peminjaman-ruangan.create', ['ruangan' => $room->id]) }}" 
                       class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                        <i class="fas fa-calendar-plus mr-2"></i> Pinjam
                    </a>
                    @else
                    <button class="bg-gray-200 text-gray-500 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed flex items-center" disabled>
                        <i class="fas fa-ban mr-2"></i> Tidak Tersedia
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-door-closed text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Tidak ada ruangan ditemukan</h3>
            <p class="text-gray-500 mb-4">Coba ubah filter pencarian Anda.</p>
            <a href="{{ route('user.ruangan.index') }}" class="inline-flex items-center text-primary-600 hover:text-primary-800 font-medium">
                <i class="fas fa-redo mr-2"></i> Reset Filter
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($ruangan->hasPages())
    <div class="bg-white rounded-xl shadow-sm p-4">
        {{ $ruangan->links() }}
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl p-6 text-white">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-question-circle text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Butuh Bantuan?</h3>
                    <p class="text-primary-100">Kami siap membantu Anda</p>
                </div>
            </div>
            <a href="#" class="inline-flex items-center text-white hover:text-primary-100 font-medium">
                <i class="fas fa-headset mr-2"></i> Hubungi Admin
            </a>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
            <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Cek Ketersediaan</h3>
                    <p class="text-green-100">Cek jadwal ruangan secara real-time</p>
                </div>
            </div>
            <a href="{{ route('user.lihat-jadwal') }}" class="inline-flex items-center text-white hover:text-green-100 font-medium">
                <i class="fas fa-calendar-alt mr-2"></i> Lihat Jadwal
            </a>
        </div>
    </div>
</div>
@endsection