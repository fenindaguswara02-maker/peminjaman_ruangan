@extends('layouts.user')

@section('title', 'Detail Ruangan - Scheduler')
@section('page-title', 'Detail Ruangan')

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('user.ruangan.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600">
                    <i class="fas fa-home mr-2"></i>
                    Daftar Ruangan
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                    <span class="ml-1 md:ml-2 text-sm font-medium text-gray-500">{{ $ruangan->nama_ruangan }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Notifikasi Success/Error -->
    @if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-green-800 mb-1">Berhasil!</h4>
                <p class="text-green-600">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-times-circle text-red-500 text-xl mr-3"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-red-800 mb-1">Terjadi Kesalahan</h4>
                <p class="text-red-600">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $ruangan->nama_ruangan }}</h1>
                        <p class="text-gray-600">{{ $ruangan->kode_ruangan }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-4 py-2 rounded-full text-sm font-semibold 
                            {{ $ruangan->status == 'tersedia' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $ruangan->status == 'dipinjam' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $ruangan->status == 'maintenance' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ $ruangan->status == 'tersedia' ? 'Tersedia' : ($ruangan->status == 'dipinjam' ? 'Sedang Dipinjam' : 'Maintenance') }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">Status Ruangan</p>
                    </div>
                </div>

                <!-- Gallery with Fallback -->
                @if($ruangan->gambar)
                <div class="mb-8">
                    <div class="rounded-lg overflow-hidden">
                        <img src="{{ asset('storage/' . $ruangan->gambar) }}" 
                             alt="{{ $ruangan->nama_ruangan }}" 
                             class="w-full h-64 object-cover"
                             onerror="this.onerror=null; this.src='https://placehold.co/800x400/3B82F6/FFFFFF?text={{ urlencode($ruangan->kode_ruangan) }}'; this.classList.add('object-contain', 'bg-blue-50');">
                    </div>
                </div>
                @else
                <div class="mb-8">
                    <div class="rounded-lg overflow-hidden bg-gradient-to-r from-blue-50 to-blue-100 flex items-center justify-center h-64">
                        <div class="text-center">
                            <i class="fas fa-door-closed text-blue-300 text-6xl mb-2"></i>
                            <p class="text-blue-600 font-medium">{{ $ruangan->kode_ruangan }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Informasi Detail - DENGAN LOKASI -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Kapasitas -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-users text-primary-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Kapasitas</h3>
                                <p class="text-2xl font-bold text-primary-600">{{ $ruangan->kapasitas }} orang</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Maksimal jumlah peserta yang dapat ditampung</p>
                    </div>

                    <!-- LOKASI - Card Baru -->
                    @if($ruangan->lokasi)
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-map-marker-alt text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Lokasi</h3>
                                <p class="text-lg font-bold text-green-600">{{ $ruangan->lokasi }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Letak ruangan dalam gedung/kampus</p>
                    </div>
                    @endif

                    <!-- Tipe Ruangan -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center mb-2">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-door-open text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Tipe Ruangan</h3>
                                <p class="text-lg font-bold text-blue-600">
                                    @php
                                        $namaLower = strtolower($ruangan->nama_ruangan);
                                        if (str_contains($namaLower, 'aula')) echo 'Aula';
                                        elseif (str_contains($namaLower, 'lab')) echo 'Laboratorium';
                                        elseif (str_contains($namaLower, 'kelas')) echo 'Ruang Kelas';
                                        elseif (str_contains($namaLower, 'rapat') || str_contains($namaLower, 'meeting')) echo 'Ruang Rapat';
                                        else echo 'Ruang Serbaguna';
                                    @endphp
                                </p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Fungsi utama ruangan</p>
                    </div>
                </div>

                <!-- Fasilitas -->
                <div class="mb-8">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools mr-2 text-primary-600"></i>
                        Fasilitas
                    </h3>
                    @if($ruangan->fasilitas)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="prose max-w-none">
                            {!! nl2br(e($ruangan->fasilitas)) !!}
                        </div>
                    </div>
                    @else
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
                        <p class="text-gray-500">Tidak ada informasi fasilitas</p>
                    </div>
                    @endif
                </div>

                <!-- Jadwal Mendatang -->
                @if(isset($jadwal) && $jadwal->count() > 0)
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                        Jadwal Mendatang
                    </h3>
                    <div class="space-y-3">
                        @foreach($jadwal as $item)
                        <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-primary-500">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-gray-800">{{ $item->acara }}</h4>
                                    <p class="text-sm text-gray-600">{{ $item->nama_pengaju }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-gray-800">
                                        {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}
                                    </p>
                                </div>
                            </div>
                            @if($item->jumlah_peserta)
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-users mr-1"></i> {{ $item->jumlah_peserta }} peserta
                            </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div>
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                        Jadwal Mendatang
                    </h3>
                    <div class="bg-gray-50 p-6 rounded-lg text-center">
                        <i class="fas fa-calendar-check text-gray-400 text-3xl mb-2"></i>
                        <p class="text-gray-600">Tidak ada jadwal peminjaman dalam waktu dekat</p>
                        <p class="text-sm text-gray-500 mt-1">Ruangan tersedia untuk dipinjam</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- CEK KETERSEDIAAN -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-search mr-2 text-primary-600"></i>
                    Cek Ketersediaan
                </h3>
                
                <form id="availabilityForm" class="space-y-4" onsubmit="event.preventDefault(); checkAvailability();">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                        <input type="date" 
                               name="tanggal" 
                               id="checkTanggal"
                               class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               min="{{ date('Y-m-d') }}"
                               required>
                        <p class="text-xs text-red-500 mt-1 hidden" id="tanggalError">Tanggal tidak valid</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai</label>
                            <input type="time" 
                                   name="jam_mulai" 
                                   id="checkJamMulai"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   min="07:00"
                                   max="17:00"
                                   required>
                            <p class="text-xs text-red-500 mt-1 hidden" id="jamMulaiError">Jam mulai harus antara 07:00 - 17:00</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai</label>
                            <input type="time" 
                                   name="jam_selesai" 
                                   id="checkJamSelesai"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   min="07:00"
                                   max="17:00"
                                   required>
                            <p class="text-xs text-red-500 mt-1 hidden" id="jamSelesaiError">Jam selesai harus antara 07:00 - 17:00 dan setelah jam mulai</p>
                        </div>
                    </div>

                    <!-- Informasi Jam Operasional -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <i class="fas fa-clock text-blue-600 mr-2 mt-1"></i>
                            <div>
                                <p class="text-xs text-blue-800">
                                    <span class="font-bold">Jam Operasional:</span> 07:00 - 17:00
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Jeda 1 Jam -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-yellow-600 mr-2 mt-1"></i>
                            <div>
                                <p class="text-xs text-yellow-800">
                                    <span class="font-bold">Perhatian:</span> Wajib ada jeda minimal 1 jam antar peminjaman.
                                </p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    Contoh: Jika ada peminjaman 09:00-10:00, peminjaman berikutnya dapat dimulai pukul 11:00.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div id="availabilityResult" class="hidden"></div>

                    <button type="submit" 
                            id="checkButton"
                            class="w-full bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check-circle mr-2"></i> Cek Ketersediaan
                    </button>
                </form>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h3>
                
                <div class="space-y-3">
                    @if($ruangan->status == 'tersedia')
                    <a href="{{ route('user.peminjaman-ruangan.create', ['ruangan' => $ruangan->id]) }}" 
                       class="w-full bg-primary-600 hover:bg-primary-700 text-white px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center">
                        <i class="fas fa-calendar-plus mr-2"></i> Pinjam Ruangan Ini
                    </a>
                    @else
                    <div class="relative group">
                        <button class="w-full bg-gray-200 text-gray-500 px-4 py-3 rounded-lg font-medium cursor-not-allowed flex items-center justify-center" disabled>
                            <i class="fas fa-ban mr-2"></i> Ruangan Tidak Tersedia
                        </button>
                        <div class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 hidden group-hover:block w-64 z-10">
                            <div class="bg-gray-800 text-white text-xs rounded-lg py-2 px-3 shadow-lg">
                                Ruangan sedang {{ $ruangan->status == 'dipinjam' ? 'dipinjam' : 'dalam perawatan' }}
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2">
                                    <div class="w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-gray-800"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <a href="{{ route('user.lihat-jadwal', ['ruangan' => $ruangan->id]) }}" 
                       class="w-full bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center">
                        <i class="fas fa-calendar-alt mr-2"></i> Lihat Jadwal Lengkap
                    </a>

                    <a href="{{ route('user.ruangan.index') }}" 
                       class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-medium transition-colors flex items-center justify-center">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                    </a>
                </div>
            </div>

            <!-- Info Kontak -->
            <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl shadow-sm p-6 mt-6 text-white">
                <h3 class="font-bold text-lg mb-3">Butuh Bantuan?</h3>
                <p class="text-primary-100 mb-4">Hubungi admin untuk informasi lebih lanjut</p>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <i class="fas fa-envelope mr-2"></i>
                        <span>admin@inventory.com</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-phone mr-2"></i>
                        <span>0895353340104</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        <span>Senin - Minggu, 07:00 - 17:00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// FUNGSI CEK KETERSEDIAAN
document.addEventListener('DOMContentLoaded', function() {
    // Element references
    const tanggalInput = document.getElementById('checkTanggal');
    const jamMulaiInput = document.getElementById('checkJamMulai');
    const jamSelesaiInput = document.getElementById('checkJamSelesai');
    const checkButton = document.getElementById('checkButton');
    
    // Error elements
    const tanggalError = document.getElementById('tanggalError');
    const jamMulaiError = document.getElementById('jamMulaiError');
    const jamSelesaiError = document.getElementById('jamSelesaiError');
    
    // Konstanta jam operasional
    const MIN_JAM = "07:00";
    const MAX_JAM = "17:00";
    
    // Dapatkan waktu saat ini
    const now = new Date();
    const today = now.toISOString().split('T')[0];
    
    // Set min dan max untuk input time
    jamMulaiInput.min = MIN_JAM;
    jamMulaiInput.max = MAX_JAM;
    jamSelesaiInput.min = MIN_JAM;
    jamSelesaiInput.max = MAX_JAM;
    
    // Set default tanggal (besok)
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowFormatted = tomorrow.toISOString().split('T')[0];
    tanggalInput.value = tomorrowFormatted;
    tanggalInput.min = today;
    
    // Set default jam untuk besok
    jamMulaiInput.value = '08:00';
    jamSelesaiInput.value = '10:00';
    
    // Update min time untuk jam mulai berdasarkan tanggal
    function updateMinTimeForDate() {
        const selectedDate = tanggalInput.value;
        
        if (selectedDate === today) {
            const minTime = getMinTimeForToday();
            
            // Pastikan minTime tidak kurang dari MIN_JAM
            const finalMinTime = minTime < MIN_JAM ? MIN_JAM : minTime;
            
            jamMulaiInput.min = finalMinTime;
            
            if (jamMulaiInput.value && jamMulaiInput.value < finalMinTime) {
                jamMulaiInput.value = finalMinTime;
                updateMinSelesaiTime();
            }
        } else {
            jamMulaiInput.min = MIN_JAM;
        }
    }
    
    function getMinTimeForToday() {
        const now = new Date();
        now.setMinutes(now.getMinutes() + 30);
        
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = Math.ceil(now.getMinutes() / 5) * 5;
        
        if (minutes >= 60) {
            return (parseInt(hours) + 1).toString().padStart(2, '0') + ':00';
        }
        return hours + ':' + (minutes < 10 ? '0' + minutes : minutes);
    }
    
    function updateMinSelesaiTime() {
        if (jamMulaiInput.value) {
            const [hours, minutes] = jamMulaiInput.value.split(':').map(Number);
            let nextHour = hours;
            let nextMinutes = minutes + 30;
            
            if (nextMinutes >= 60) {
                nextHour += 1;
                nextMinutes -= 60;
            }
            
            if (nextHour >= 24 || nextHour > 17) {
                nextHour = 17;
                nextMinutes = 0;
            }
            
            const minSelesaiTime = nextHour.toString().padStart(2, '0') + ':' + 
                                  nextMinutes.toString().padStart(2, '0');
            jamSelesaiInput.min = minSelesaiTime;
            
            if (jamSelesaiInput.value && jamSelesaiInput.value < minSelesaiTime) {
                jamSelesaiInput.value = minSelesaiTime;
            }
            
            // Validasi jam selesai tidak boleh lebih dari MAX_JAM
            if (jamSelesaiInput.value && jamSelesaiInput.value > MAX_JAM) {
                jamSelesaiInput.value = MAX_JAM;
            }
        }
    }
    
    function validateAllInputs() {
        const tanggal = tanggalInput.value;
        const jamMulai = jamMulaiInput.value;
        const jamSelesai = jamSelesaiInput.value;
        
        tanggalError.classList.add('hidden');
        jamMulaiError.classList.add('hidden');
        jamSelesaiError.classList.add('hidden');
        
        let isValid = true;
        
        // Validasi tanggal
        if (!tanggal) {
            tanggalError.textContent = 'Tanggal harus diisi';
            tanggalError.classList.remove('hidden');
            isValid = false;
        } else if (tanggal < today) {
            tanggalError.textContent = 'Tanggal tidak boleh kurang dari hari ini';
            tanggalError.classList.remove('hidden');
            isValid = false;
        }
        
        // Validasi jam mulai
        if (!jamMulai) {
            jamMulaiError.textContent = 'Jam mulai harus diisi';
            jamMulaiError.classList.remove('hidden');
            isValid = false;
        } else if (jamMulai < MIN_JAM) {
            jamMulaiError.textContent = `Jam mulai tidak boleh kurang dari ${MIN_JAM}`;
            jamMulaiError.classList.remove('hidden');
            isValid = false;
        } else if (jamMulai > MAX_JAM) {
            jamMulaiError.textContent = `Jam mulai tidak boleh lebih dari ${MAX_JAM}`;
            jamMulaiError.classList.remove('hidden');
            isValid = false;
        } else if (tanggal === today) {
            const minTime = getMinTimeForToday();
            const finalMinTime = minTime < MIN_JAM ? MIN_JAM : minTime;
            if (jamMulai < finalMinTime) {
                jamMulaiError.textContent = `Jam mulai minimal untuk hari ini adalah ${finalMinTime}`;
                jamMulaiError.classList.remove('hidden');
                isValid = false;
            }
        }
        
        // Validasi jam selesai
        if (!jamSelesai) {
            jamSelesaiError.textContent = 'Jam selesai harus diisi';
            jamSelesaiError.classList.remove('hidden');
            isValid = false;
        } else if (jamSelesai < MIN_JAM) {
            jamSelesaiError.textContent = `Jam selesai tidak boleh kurang dari ${MIN_JAM}`;
            jamSelesaiError.classList.remove('hidden');
            isValid = false;
        } else if (jamSelesai > MAX_JAM) {
            jamSelesaiError.textContent = `Jam selesai tidak boleh lebih dari ${MAX_JAM}`;
            jamSelesaiError.classList.remove('hidden');
            isValid = false;
        } else if (jamMulai && jamSelesai && jamSelesai <= jamMulai) {
            jamSelesaiError.textContent = 'Jam selesai harus setelah jam mulai';
            jamSelesaiError.classList.remove('hidden');
            isValid = false;
        }
        
        // Validasi durasi minimal 30 menit
        if (jamMulai && jamSelesai && jamSelesai > jamMulai) {
            const [startHours, startMinutes] = jamMulai.split(':').map(Number);
            const [endHours, endMinutes] = jamSelesai.split(':').map(Number);
            
            const startTotal = startHours * 60 + startMinutes;
            const endTotal = endHours * 60 + endMinutes;
            
            if (endTotal - startTotal < 30) {
                jamSelesaiError.textContent = 'Durasi peminjaman minimal 30 menit';
                jamSelesaiError.classList.remove('hidden');
                isValid = false;
            }
        }
        
        checkButton.disabled = !isValid;
        return isValid;
    }
    
    tanggalInput.addEventListener('change', function() {
        updateMinTimeForDate();
        validateAllInputs();
    });
    
    jamMulaiInput.addEventListener('change', function() {
        updateMinSelesaiTime();
        validateAllInputs();
    });
    
    jamSelesaiInput.addEventListener('change', validateAllInputs);
    
    updateMinTimeForDate();
    updateMinSelesaiTime();
    validateAllInputs();
});

// FUNGSI CEK KETERSEDIAAN
function checkAvailability() {
    // Validasi input
    const tanggal = document.getElementById('checkTanggal').value;
    const jamMulai = document.getElementById('checkJamMulai').value;
    const jamSelesai = document.getElementById('checkJamSelesai').value;
    const resultDiv = document.getElementById('availabilityResult');
    const checkButton = document.getElementById('checkButton');
    
    if (!tanggal || !jamMulai || !jamSelesai) {
        alert('Harap isi semua field terlebih dahulu.');
        return;
    }
    
    if (jamSelesai <= jamMulai) {
        alert('Jam selesai harus setelah jam mulai.');
        return;
    }
    
    // Validasi jam operasional
    if (jamMulai < "07:00" || jamMulai > "17:00" || jamSelesai < "07:00" || jamSelesai > "17:00") {
        alert('Jam peminjaman harus antara 07:00 - 17:00');
        return;
    }
    
    // Tampilkan loading
    resultDiv.innerHTML = `
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-center">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
                <div>
                    <h4 class="font-bold text-blue-800">Memeriksa Ketersediaan...</h4>
                    <p class="text-blue-600 text-sm">Mohon tunggu sebentar</p>
                </div>
            </div>
        </div>
    `;
    resultDiv.classList.remove('hidden');
    
    // Disable tombol
    checkButton.disabled = true;
    checkButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
    
    // GUNAKAN ROUTE GLOBAL
    fetch('{{ route("peminjaman-ruangan.check-availability") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ruangan_id: {{ $ruangan->id }},
            tanggal_mulai: tanggal,
            tanggal_selesai: tanggal,
            jam_mulai: jamMulai,
            jam_selesai: jamSelesai
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        // Enable tombol
        checkButton.disabled = false;
        checkButton.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Cek Ketersediaan';
        
        // Format tanggal
        const formattedDate = formatDate(tanggal);
        
        if (data.available) {
    const ruanganId = {{ $ruangan->id }};
    const baseUrl = "{{ route('user.peminjaman-ruangan.create') }}";
    const params = new URLSearchParams({
        ruangan: ruanganId,
        tanggal: tanggal,
        jam_mulai: jamMulai,
        jam_selesai: jamSelesai
    }).toString();
    
    resultDiv.innerHTML = `
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div>
                    <h4 class="font-bold text-green-800">Ruangan Tersedia!</h4>
                    <p class="text-green-600 text-sm">${data.message || 'Ruangan dapat dipinjam pada waktu tersebut'}</p>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                <div class="bg-green-100 p-2 rounded">
                    <span class="text-green-800 font-medium">Tanggal:</span>
                    <p>${formattedDate}</p>
                </div>
                <div class="bg-green-100 p-2 rounded">
                    <span class="text-green-800 font-medium">Waktu:</span>
                    <p>${jamMulai} - ${jamSelesai}</p>
                </div>
            </div>
            
            <!-- LINK YANG SUDAH DIPERBAIKI -->
            <a href="${baseUrl}?${params}"
               class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center">
                <i class="fas fa-calendar-plus mr-2"></i> Ajukan Peminjaman
            </a>
        </div>
    `;
        } else {
            resultDiv.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-times text-red-600"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-red-800">Ruangan Tidak Tersedia</h4>
                            <p class="text-red-600 text-sm">${data.message || 'Ruangan sedang digunakan pada waktu tersebut'}</p>
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <p class="text-xs text-yellow-800">
                            <span class="font-bold">Tips:</span> Coba pilih tanggal atau jam yang berbeda. 
                            Pastikan ada jeda minimal 1 jam dari jadwal peminjaman lain.
                        </p>
                    </div>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Enable tombol
        checkButton.disabled = false;
        checkButton.innerHTML = '<i class="fas fa-check-circle mr-2"></i> Cek Ketersediaan';
        
        let errorMessage = 'Terjadi kesalahan saat memeriksa ketersediaan.';
        
        if (error.message) {
            errorMessage = error.message;
        } else if (error.errors) {
            errorMessage = Object.values(error.errors).flat().join(', ');
        }
        
        resultDiv.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-red-800">Gagal Memeriksa Ketersediaan</h4>
                        <p class="text-red-600 text-sm">${errorMessage}</p>
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button onclick="window.location.reload()" class="flex-1 text-sm bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg transition-colors">
                        <i class="fas fa-redo mr-1"></i> Refresh
                    </button>
                    <button onclick="checkAvailability()" class="flex-1 text-sm bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-3 py-2 rounded-lg transition-colors">
                        <i class="fas fa-sync-alt mr-1"></i> Coba Lagi
                    </button>
                </div>
            </div>
        `;
    });
}

// Fungsi format tanggal
function formatDate(dateString) {
    try {
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        const date = new Date(dateString + 'T12:00:00');
        const dayName = days[date.getDay()];
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        return `${dayName}, ${day} ${month} ${year}`;
    } catch (e) {
        return dateString;
    }
}
</script>

<style>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.group:hover .group-hover\:block {
    display: block;
}
</style>
@endsection