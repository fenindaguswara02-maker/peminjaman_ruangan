@extends('layouts.user')

@section('title', 'Peminjaman Ruangan - Scheduler')
@section('page-title', 'Peminjaman Ruangan')

@section('content')
<div class="max-w-7xl mx-auto">
    @php
        // ✅ AMBIL PARAMETER DARI URL
        $selectedTanggal = request('tanggal');
        $selectedJamMulai = request('jam_mulai');
        $selectedJamSelesai = request('jam_selesai');
        $selectedRuanganId = request('ruangan');
        
        // ✅ AMBIL DATA USER DENGAN USERNAME
        $user = auth()->user();
        $isDataLengkap = $user->nim_nip && $user->fakultas && $user->no_telepon && $user->jenis_pengaju;
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Peminjaman -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Form Peminjaman Ruangan</h2>
                
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- PERINGATAN JIKA DATA AKADEMIK BELUM LENGKAP -->
                @if(!$isDataLengkap)
                <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        <h3 class="font-semibold text-yellow-800">Data Pribadi Belum Lengkap</h3>
                    </div>
                    <p class="text-sm text-yellow-700 mt-2">
                        Harap lengkapi data berikut untuk melanjutkan peminjaman:
                    </p>
                    <ul class="text-sm text-yellow-700 mt-1 list-disc list-inside">
                        @if(!$user->nim_nip)<li>NIM/NIP</li>@endif
                        @if(!$user->fakultas)<li>Fakultas</li>@endif
                        @if(!$user->no_telepon)<li>Nomor Telepon</li>@endif
                        @if(!$user->jenis_pengaju)<li>Jenis Pengaju</li>@endif
                    </ul>
                    <div class="mt-3">
                        <a href="{{ route('profil.akademik') }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                            <i class="fas fa-user-edit mr-1"></i> Lengkapi Profil Akademik
                        </a>
                    </div>
                </div>
                @endif

                <form action="{{ route('user.peminjaman-ruangan.store') }}" method="POST" enctype="multipart/form-data" id="peminjamanForm">
                    @csrf
                    
                    @php
                        $selectedRuangan = $selectedRuanganId ? $ruangan->firstWhere('id', $selectedRuanganId) : null;
                    @endphp
                    
                    <!-- Bagian Informasi Pengaju -->
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-user-circle mr-2 text-primary-600"></i>
                            Informasi Pengaju
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- USERNAME - TAMBAHKAN FIELD INI -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                                <input type="text" 
                                       name="username_display" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50" 
                                       value="{{ $user->username ?? 'Belum diatur' }}"
                                       readonly
                                       disabled>
                                    
                                <!-- Input hidden untuk mengirim nilai ke server jika diperlukan -->
                                <input type="hidden" name="username" value="{{ $user->username }}">
                                    
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-check text-green-500 mr-1"></i>Data dari profil
                                </p>
                            </div>

                            <!-- JENIS PENGAJU - Ambil dari kolom jenis_pengaju di tabel users -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Pengaju *</label>
                                <div class="relative">
                                    <input type="text" 
                                           name="jenis_pengaju_display" 
                                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50" 
                                           value="{{ $user->jenis_pengaju ? ucfirst($user->jenis_pengaju) : 'Belum diatur' }}"
                                           readonly
                                           disabled>
                                    
                                    <!-- Input hidden untuk mengirim nilai ke server -->
                                    <input type="hidden" name="jenis_pengaju" value="{{ $user->jenis_pengaju }}">
                                    
                                    <div class="absolute right-3 top-3">
                                        @if($user->jenis_pengaju)
                                            @php
                                                $badgeColor = match($user->jenis_pengaju) {
                                                    'mahasiswa' => 'bg-blue-100 text-blue-800',
                                                    'dosen' => 'bg-purple-100 text-purple-800',
                                                    'staff' => 'bg-orange-100 text-orange-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                };
                                            @endphp
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">
                                                {{ ucfirst($user->jenis_pengaju) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1 {{ $user->jenis_pengaju ? '' : 'text-yellow-600' }}">
                                    @if($user->jenis_pengaju)
                                        <i class="fas fa-check text-green-500 mr-1"></i>Data dari profil
                                    @else
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Harap isi jenis pengaju di profil
                                    @endif
                                </p>
                                @if(!$user->jenis_pengaju)
                                <div class="mt-2">
                                    <a href="{{ route('profil.akademik') }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium flex items-center">
                                        <i class="fas fa-user-edit mr-1"></i> Atur Jenis Pengaju di Profil
                                    </a>
                                </div>
                                @endif
                                @error('jenis_pengaju')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NAMA LENGKAP -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap *</label>
                                <input type="text" name="nama_pengaju" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50" 
                                       value="{{ old('nama_pengaju', $user->name) }}" 
                                       readonly required>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-check text-green-500 mr-1"></i>Data dari profil
                                </p>
                                @error('nama_pengaju')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NIM/NIP -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">NIM/NIP *</label>
                                <input type="text" name="nim_nip" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 {{ $user->nim_nip ? 'bg-gray-50' : '' }}" 
                                       placeholder="Masukkan NIM atau NIP" 
                                       value="{{ old('nim_nip', $user->nim_nip) }}" 
                                       {{ $user->nim_nip ? 'readonly' : '' }} 
                                       required>
                                <p class="text-xs text-gray-500 mt-1 {{ $user->nim_nip ? '' : 'text-yellow-600' }}">
                                    @if($user->nim_nip)
                                        <i class="fas fa-check text-green-500 mr-1"></i>Data dari profil
                                    @else
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Harap isi NIM/NIP
                                    @endif
                                </p>
                                @error('nim_nip')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- FAKULTAS -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fakultas *</label>
                                <input type="text" name="fakultas" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 {{ $user->fakultas ? 'bg-gray-50' : '' }}" 
                                       placeholder="Masukkan nama fakultas" 
                                       value="{{ old('fakultas', $user->fakultas) }}" 
                                       {{ $user->fakultas ? 'readonly' : '' }} 
                                       required>
                                <p class="text-xs text-gray-500 mt-1 {{ $user->fakultas ? '' : 'text-yellow-600' }}">
                                    @if($user->fakultas)
                                        <i class="fas fa-check text-green-500 mr-1"></i>Data dari profil
                                    @else
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Harap isi fakultas
                                    @endif
                                </p>
                                @error('fakultas')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- PROGRAM STUDI -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                                <input type="text" name="prodi" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 {{ $user->prodi ? 'bg-gray-50' : '' }}" 
                                       placeholder="Masukkan program studi" 
                                       value="{{ old('prodi', $user->prodi) }}"
                                       {{ $user->prodi ? 'readonly' : '' }}>
                                <p class="text-xs text-gray-500 mt-1 {{ $user->prodi ? '' : 'text-gray-500' }}">
                                    @if($user->prodi)
                                        <i class="fas fa-check text-green-500 mr-1"></i>Data dari profil
                                    @else
                                        (Opsional)
                                    @endif
                                </p>
                                @error('prodi')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- EMAIL -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="email" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50" 
                                       value="{{ old('email', $user->email) }}" 
                                       readonly required>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-check text-green-500 mr-1"></i>Data dari profil
                                </p>
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NO TELEPON -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon *</label>
                                <input type="tel" name="no_telepon" 
                                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 {{ $user->no_telepon ? 'bg-gray-50' : '' }}" 
                                       placeholder="Masukkan nomor telepon" 
                                       value="{{ old('no_telepon', $user->no_telepon) }}" 
                                       {{ $user->no_telepon ? 'readonly' : '' }} 
                                       required>
                                <p class="text-xs text-gray-500 mt-1 {{ $user->no_telepon ? '' : 'text-yellow-600' }}">
                                    @if($user->no_telepon)
                                        <i class="fas fa-check text-green-500 mr-1"></i>Data dari profil
                                    @else
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Harap isi nomor telepon
                                    @endif
                                </p>
                                @error('no_telepon')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Informasi Peminjaman -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- NAMA ACARA -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Acara/Kegiatan *</label>
                            <input type="text" name="acara" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                                   placeholder="Masukkan nama acara/kegiatan" 
                                   value="{{ old('acara') }}" 
                                   required>
                            @error('acara')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- PILIH RUANGAN -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Ruangan *</label>
                            <select name="ruangan_id" id="ruanganSelect" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                                <option value="">Pilih Ruangan</option>
                                @foreach($ruangan as $room)
                                    <option value="{{ $room->id }}" 
                                        {{ (old('ruangan_id') == $room->id || $selectedRuanganId == $room->id) ? 'selected' : '' }}
                                        data-kapasitas="{{ $room->kapasitas }}"
                                        data-status="{{ $room->status }}">
                                        {{ $room->kode_ruangan }} - {{ $room->nama_ruangan }} (Kapasitas: {{ $room->kapasitas }})
                                    </option>
                                @endforeach
                            </select>
                            
                            <!-- Container untuk status ketersediaan real-time -->
                            <div id="availabilityStatus" class="mt-2"></div>
                            
                            @error('ruangan_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- TANGGAL MULAI - DENGAN NILAI DEFAULT DARI PARAMETER -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                            <input type="date" name="tanggal_mulai" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 tanggal-input" 
                                   id="tanggal_mulai"
                                   min="{{ date('Y-m-d') }}" 
                                   value="{{ old('tanggal_mulai', $selectedTanggal) }}" 
                                   required>
                            @error('tanggal_mulai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-red-500 mt-1 hidden" id="tanggal_mulai_error">Tanggal 31 Januari 2026 tidak tersedia</p>
                        </div>

                        <!-- TANGGAL SELESAI - DENGAN NILAI DEFAULT DARI PARAMETER -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                            <input type="date" name="tanggal_selesai" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 tanggal-input" 
                                   id="tanggal_selesai"
                                   min="{{ date('Y-m-d') }}" 
                                   value="{{ old('tanggal_selesai', $selectedTanggal) }}" 
                                   required>
                            @error('tanggal_selesai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-red-500 mt-1 hidden" id="tanggal_selesai_error">Tanggal 31 Januari 2026 tidak tersedia</p>
                        </div>

                        <!-- JAM MULAI - DENGAN NILAI DEFAULT DARI PARAMETER -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Mulai *</label>
                            <input type="time" name="jam_mulai" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 jam-input" 
                                   id="jam_mulai"
                                   min="07:00"
                                   max="17:00"
                                   value="{{ old('jam_mulai', $selectedJamMulai) }}" 
                                   required>
                            @error('jam_mulai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-red-500 mt-1 hidden" id="jam_mulai_error">Waktu minimal 07:00, sudah terlewat, atau melebihi jam 17:00</p>
                        </div>

                        <!-- JAM SELESAI - DENGAN NILAI DEFAULT DARI PARAMETER -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jam Selesai *</label>
                            <input type="time" name="jam_selesai" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 jam-input" 
                                   id="jam_selesai"
                                   min="07:00"
                                   max="17:00"
                                   value="{{ old('jam_selesai', $selectedJamSelesai) }}" 
                                   required>
                            @error('jam_selesai')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-red-500 mt-1 hidden" id="jam_selesai_error">Waktu minimal 07:00, sudah terlewat, kurang dari jam mulai, atau melebihi jam 17:00</p>
                        </div>

                        <!-- HARI (AUTO FILL) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hari</label>
                            <input type="text" name="hari" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50" 
                                   id="hari"
                                   value="{{ old('hari') }}" 
                                   readonly>
                            @error('hari')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- JUMLAH PESERTA -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Peserta *</label>
                            <input type="number" name="jumlah_peserta" 
                                   id="jumlahPeserta"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                                   placeholder="Masukkan jumlah peserta" 
                                   value="{{ old('jumlah_peserta') }}" 
                                   min="1" 
                                   required>
                            @error('jumlah_peserta')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-red-500 mt-1 hidden" id="kapasitasError">
                                Jumlah peserta melebihi kapasitas ruangan
                            </p>
                        </div>

                        <!-- KETERANGAN -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <textarea name="keterangan" 
                                      rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                                      placeholder="Tambahkan keterangan mengenai acara...">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- LAMPIRAN SURAT -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran Surat (Opsional)</label>
                            <input type="file" name="lampiran_surat" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                                   accept=".pdf,.doc,.docx,.jpg,.png">
                            <p class="text-sm text-gray-500 mt-1">Format: PDF, DOC, DOCX, JPG, PNG (Maks. 2MB)</p>
                            @error('lampiran_surat')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- ⚠️ PERINGATAN TENTANG ATURAN JEDA 1 JAM DAN BATAS JAM 07:00-17:00 -->
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-clock text-yellow-500 mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-semibold text-yellow-800 mb-2">Peraturan Peminjaman Ruangan</h4>
                                <ul class="text-sm text-yellow-700 space-y-1">
                                    <li><strong>• JEDA 1 JAM WAJIB:</strong> Harus ada jeda minimal 1 jam antara peminjaman</li>
                                    <li><strong>• JAM OPERASIONAL:</strong> Peminjaman hanya dari jam 07:00 - 17:00</li>
                                    <li>• Durasi peminjaman minimal 15 menit</li>
                                    <li>• Peminjaman tidak boleh overlap dengan peminjaman lain</li>
                                    <li>• Sistem akan otomatis menolak jika melanggar aturan di atas</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- TOMBOL SUBMIT -->
                    <div class="mt-6 flex justify-between items-center">
                        <div>
                            @if(!$isDataLengkap)
                                <div class="flex items-center text-yellow-600 text-sm">
                                    <i class="fas fa-exclamation-circle mr-2"></i>
                                    <span>Lengkapi data profil terlebih dahulu</span>
                                </div>
                            @endif
                        </div>
                        <button type="submit" 
                                id="submitButton"
                                class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center {{ !$isDataLengkap ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ !$isDataLengkap ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane mr-2"></i> Ajukan Peminjaman
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Ruangan -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Informasi Ruangan</h3>
                
                <!-- Info Ruangan Terpilih -->
                @if($selectedRuangan)
                <div class="mb-6 p-4 bg-gradient-to-r from-primary-50 to-blue-50 border border-primary-200 rounded-lg">
                    <h4 class="font-bold text-primary-800 mb-2 flex items-center">
                        <i class="fas fa-star mr-2 text-yellow-500"></i>
                        Ruangan Yang Dipilih
                    </h4>
                    <div class="space-y-2">
                        <p class="text-sm">
                            <span class="font-semibold text-gray-700">Kode:</span> 
                            <span class="text-primary-700">{{ $selectedRuangan->kode_ruangan }}</span>
                        </p>
                        <p class="text-sm">
                            <span class="font-semibold text-gray-700">Nama:</span> 
                            <span class="text-primary-700">{{ $selectedRuangan->nama_ruangan }}</span>
                        </p>
                        <p class="text-sm">
                            <span class="font-semibold text-gray-700">Kapasitas:</span> 
                            <span class="text-primary-700">{{ $selectedRuangan->kapasitas }} orang</span>
                        </p>
                        <p class="text-sm">
                            <span class="font-semibold text-gray-700">Status:</span> 
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $selectedRuangan->status == 'tersedia' ? 'bg-green-100 text-green-800' : ($selectedRuangan->status == 'dipinjam' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($selectedRuangan->status) }}
                            </span>
                        </p>
                        @if($selectedRuangan->fasilitas)
                        <p class="text-sm">
                            <span class="font-semibold text-gray-700">Fasilitas:</span> 
                            <span class="text-primary-700">{{ Str::limit($selectedRuangan->fasilitas, 60) }}</span>
                        </p>
                        @endif
                    </div>
                </div>
                @endif
                
                <div class="space-y-4">
                    @forelse($ruangan as $room)
                    <div class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors {{ $selectedRuanganId == $room->id ? 'border-primary-300 bg-blue-50' : '' }}">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-semibold text-gray-800">{{ $room->kode_ruangan }} - {{ $room->nama_ruangan }}</h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-users mr-1"></i> Kapasitas: {{ $room->kapasitas }} orang
                                </p>
                                @if($room->fasilitas)
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-tools mr-1"></i> Fasilitas: {{ Str::limit($room->fasilitas, 40) }}
                                </p>
                                @endif
                            </div>
                            <span class="px-2 py-1 rounded-full text-xs {{ $room->status == 'tersedia' ? 'bg-green-100 text-green-800' : ($room->status == 'dipinjam' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($room->status) }}
                            </span>
                        </div>
                        @if($selectedRuanganId != $room->id && $room->status == 'tersedia')
                        <div class="mt-3 text-right">
                            <button onclick="selectRuangan({{ $room->id }})" 
                                    class="text-primary-600 hover:text-primary-800 text-sm font-medium flex items-center ml-auto">
                                <i class="fas fa-check-circle mr-1"></i> Pilih Ruangan Ini
                            </button>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <i class="fas fa-door-closed text-gray-400 text-4xl mb-4"></i>
                        <p class="text-gray-500">Tidak ada ruangan tersedia</p>
                    </div>
                    @endforelse
                </div>
                
                <!-- ✅ INFORMASI TENTANG JEDA 1 JAM DAN BATAS JAM 07:00-17:00 -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>Informasi Penting
                    </h4>
                    <ul class="text-sm text-blue-700 space-y-1">
                        <li><strong>• JAM OPERASIONAL:</strong> Peminjaman hanya dari jam 07:00 - 17:00</li>
                        <li><strong>• DURASI MINIMAL:</strong> 15 menit</li>
                        <li><strong>• TOLERANSI KETERLAMBATAN:</strong> Maksimal 15 menit dari waktu mulai, jika lebih dari 15 menit peminjaman akan otomatis dibatalkan/dianggap batal</li>
                        <li><strong>• KONFIRMASI:</strong> Akan diberikan maksimal 2x24 jam setelah pengajuan</li>
                        <li><strong>• PEMBATALAN:</strong> Batalkan peminjaman minimal 1 jam sebelum waktu mulai jika tidak jadi digunakan</li>
                        <li><strong>• KEHADIRAN:</strong> Pengguna wajib hadir 10 menit sebelum waktu mulai untuk konfirmasi ke petugas</li>
                    </ul>
                </div>

                <!-- ✅ CONTOH KASUS JEDA 1 JAM DAN BATAS JAM -->
                <div class="mt-4 p-4 bg-green-50 rounded-lg">
                    <h4 class="font-semibold text-green-800 mb-2">
                        <i class="fas fa-lightbulb mr-2"></i>Contoh Penjadwalan
                    </h4>
                    <div class="text-sm text-green-700 space-y-2">
                        <div class="border-l-4 border-green-500 pl-3">
                            <p class="font-medium">Jika ada peminjaman:</p>
                            <p class="text-green-600">09:20 - 10:00</p>
                        </div>
                        <div class="border-l-4 border-green-500 pl-3">
                            <p class="font-medium">Maka peminjaman berikutnya:</p>
                            <p class="text-green-600"><strong>✅ BISA:</strong> 11:00 - 12:00 (jeda 1 jam)</p>
                            <p class="text-red-600"><strong>❌ TIDAK BISA:</strong> 10:30 - 11:30 (kurang dari 1 jam)</p>
                            <p class="text-red-600"><strong>❌ TIDAK BISA:</strong> 09:35 - 11:00 (overlap)</p>
                        </div>
                        <div class="border-l-4 border-green-500 pl-3 mt-2">
                            <p class="font-medium">Batas jam operasional (07:00 - 17:00):</p>
                            <p class="text-green-600"><strong>✅ BISA:</strong> 07:30 - 09:00 (dalam jam operasional)</p>
                            <p class="text-green-600"><strong>✅ BISA:</strong> 15:00 - 16:30 (dalam jam operasional)</p>
                            <p class="text-red-600"><strong>❌ TIDAK BISA:</strong> 06:30 - 08:00 (kurang dari 07:00)</p>
                            <p class="text-red-600"><strong>❌ TIDAK BISA:</strong> 16:30 - 17:30 (melebihi jam 17:00)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cek jika data akademik belum lengkap
    const isDataLengkap = {{ $isDataLengkap ? 'true' : 'false' }};
    
    if (!isDataLengkap) {
        const submitButton = document.querySelector('button[type="submit"]');
        submitButton.addEventListener('click', function(e) {
            if (this.disabled) {
                e.preventDefault();
                alert('Harap lengkapi data profil akademik terlebih dahulu sebelum mengajukan peminjaman.');
                window.location.href = "{{ route('profil.akademik') }}";
            }
        });
    }
    
    // ✅ AMBIL PARAMETER DARI URL
    const urlParams = new URLSearchParams(window.location.search);
    const tanggalParam = urlParams.get('tanggal');
    const jamMulaiParam = urlParams.get('jam_mulai');
    const jamSelesaiParam = urlParams.get('jam_selesai');
    
    // Format waktu menjadi HH:MM
    function formatTime(hours) {
        return hours.toString().padStart(2, '0') + ':00';
    }
    
    // Fungsi untuk mendapatkan waktu saat ini dalam format HH:MM
    function getCurrentTime() {
        const now = new Date();
        const hours = now.getHours().toString().padStart(2, '0');
        const minutes = now.getMinutes().toString().padStart(2, '0');
        return hours + ':' + minutes;
    }
    
    // Fungsi untuk mendapatkan waktu minimal berdasarkan tanggal (minimal 07:00)
    function getMinTimeForDate(selectedDate) {
        const today = new Date().toISOString().split('T')[0];
        const MIN_JAM = "07:00";
        const MAX_JAM = "17:00";
        
        // Jika tanggal yang dipilih adalah hari ini
        if (selectedDate === today) {
            // Tambah 30 menit dari waktu sekarang sebagai buffer
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);
            
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            
            // Round up to nearest 5 minutes
            const roundedMinutes = Math.ceil(minutes / 5) * 5;
            let minTime;
            if (roundedMinutes >= 60) {
                minTime = (parseInt(hours) + 1).toString().padStart(2, '0') + ':00';
            } else {
                minTime = hours + ':' + roundedMinutes.toString().padStart(2, '0');
            }
            
            // Pastikan minTime tidak kurang dari jam 07:00
            if (minTime < MIN_JAM) {
                minTime = MIN_JAM;
            }
            
            // Pastikan minTime tidak melebihi jam 17:00
            if (minTime > MAX_JAM) {
                minTime = MAX_JAM;
            }
            
            return minTime;
        }
        
        // Untuk tanggal besok atau setelahnya, mulai dari 07:00
        return MIN_JAM;
    }
    
    // Fungsi untuk mendapatkan nama hari dalam bahasa Indonesia
    function getHariIndonesia(dateString) {
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const date = new Date(dateString);
        return days[date.getDay()];
    }
    
    // Fungsi untuk cek apakah tanggal adalah 31 Januari 2026
    function is31Jan2026(dateString) {
        return dateString === '2026-01-31';
    }
    
    // Fungsi untuk validasi kapasitas ruangan
    function validateKapasitas() {
        const ruanganSelect = document.getElementById('ruanganSelect');
        const jumlahPesertaInput = document.getElementById('jumlahPeserta');
        const kapasitasError = document.getElementById('kapasitasError');
        
        if (ruanganSelect.value && jumlahPesertaInput.value) {
            const selectedOption = ruanganSelect.options[ruanganSelect.selectedIndex];
            const kapasitas = parseInt(selectedOption.getAttribute('data-kapasitas'));
            const jumlahPeserta = parseInt(jumlahPesertaInput.value);
            
            if (jumlahPeserta > kapasitas) {
                kapasitasError.classList.remove('hidden');
                return false;
            } else {
                kapasitasError.classList.add('hidden');
                return true;
            }
        }
        return true;
    }
    
    // Real-time availability check dengan jeda 1 jam
    async function checkRoomAvailability() {
        const ruanganId = document.getElementById('ruanganSelect').value;
        const tanggalMulai = document.getElementById('tanggal_mulai').value;
        const tanggalSelesai = document.getElementById('tanggal_selesai').value;
        const jamMulai = document.getElementById('jam_mulai').value;
        const jamSelesai = document.getElementById('jam_selesai').value;
        const submitButton = document.getElementById('submitButton');
        const availabilityStatus = document.getElementById('availabilityStatus');
        
        // Reset status
        availabilityStatus.innerHTML = '';
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        
        // Validasi minimal input
        if (!ruanganId || !tanggalMulai || !tanggalSelesai || !jamMulai || !jamSelesai) {
            return;
        }
        
        // Validasi jam selesai harus setelah jam mulai
        if (jamSelesai <= jamMulai) {
            availabilityStatus.innerHTML = `
                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                    <i class="fas fa-times-circle text-red-500 text-lg mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-red-800">Jam Tidak Valid</h4>
                        <p class="text-sm text-red-700 mt-1">Jam selesai harus setelah jam mulai</p>
                    </div>
                </div>
            `;
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        
        // Validasi jam tidak boleh kurang dari 07:00
        if (jamMulai < "07:00" || jamSelesai < "07:00") {
            availabilityStatus.innerHTML = `
                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                    <i class="fas fa-times-circle text-red-500 text-lg mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-red-800">Jam Tidak Valid</h4>
                        <p class="text-sm text-red-700 mt-1">Jam peminjaman tidak boleh kurang dari jam 07:00</p>
                    </div>
                </div>
            `;
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        
        // Validasi jam tidak boleh melebihi jam 17:00
        if (jamMulai > "17:00" || jamSelesai > "17:00") {
            availabilityStatus.innerHTML = `
                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                    <i class="fas fa-times-circle text-red-500 text-lg mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-red-800">Jam Tidak Valid</h4>
                        <p class="text-sm text-red-700 mt-1">Jam peminjaman tidak boleh melebihi jam 17:00</p>
                    </div>
                </div>
            `;
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        
        // Validasi durasi minimal 15 menit
        const startTime = new Date(`2000-01-01T${jamMulai}`);
        const endTime = new Date(`2000-01-01T${jamSelesai}`);
        const durationMinutes = (endTime - startTime) / (1000 * 60);
        
        if (durationMinutes < 15) {
            availabilityStatus.innerHTML = `
                <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                    <i class="fas fa-times-circle text-red-500 text-lg mr-3"></i>
                    <div>
                        <h4 class="font-semibold text-red-800">Durasi Terlalu Pendek</h4>
                        <p class="text-sm text-red-700 mt-1">Durasi peminjaman minimal 15 menit</p>
                    </div>
                </div>
            `;
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }
        
        try {
            // Tampilkan loading
            availabilityStatus.innerHTML = `
                <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
                    <span class="text-blue-700 font-medium">Memeriksa ketersediaan ruangan dengan jeda 1 jam...</span>
                </div>
            `;
            
            // Gunakan route yang benar
            const response = await fetch("{{ route('peminjaman-ruangan.check-availability') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    ruangan_id: ruanganId,
                    tanggal_mulai: tanggalMulai,
                    tanggal_selesai: tanggalSelesai,
                    jam_mulai: jamMulai,
                    jam_selesai: jamSelesai
                })
            });
            
            const data = await response.json();
            
            if (data.available) {
                availabilityStatus.innerHTML = `
                    <div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
                        <i class="fas fa-check-circle text-green-500 text-lg mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-green-800">✅ Ruangan Tersedia</h4>
                            <p class="text-sm text-green-700 mt-1">${data.message}</p>
                            <p class="text-xs text-green-600 mt-2">
                                <i class="fas fa-clock mr-1"></i> Memenuhi aturan jeda 1 jam
                            </p>
                        </div>
                    </div>
                `;
                submitButton.disabled = false;
                submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                availabilityStatus.innerHTML = `
                    <div class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
                        <i class="fas fa-times-circle text-red-500 text-lg mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-red-800">❌ Ruangan Tidak Tersedia</h4>
                            <p class="text-sm text-red-700 mt-1">${data.message}</p>
                            <p class="text-xs text-red-600 mt-2">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Melanggar aturan jeda 1 jam antara peminjaman
                            </p>
                        </div>
                    </div>
                `;
                submitButton.disabled = true;
                submitButton.classList.add('opacity-50', 'cursor-not-allowed');
            }
            
        } catch (error) {
            console.error('Error checking availability:', error);
            availabilityStatus.innerHTML = `
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-yellow-700">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Tidak dapat memeriksa ketersediaan. Harap coba lagi.
                    </p>
                </div>
            `;
        }
    }
    
    // Fungsi untuk memilih ruangan dari panel kanan
    window.selectRuangan = function(ruanganId) {
        const ruanganSelect = document.getElementById('ruanganSelect');
        ruanganSelect.value = ruanganId;
        
        // Trigger change event
        const event = new Event('change');
        ruanganSelect.dispatchEvent(event);
        
        // Scroll ke form
        ruanganSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    // Element references
    const tanggalMulaiInput = document.getElementById('tanggal_mulai');
    const tanggalSelesaiInput = document.getElementById('tanggal_selesai');
    const jamMulaiInput = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const hariInput = document.getElementById('hari');
    const ruanganSelect = document.getElementById('ruanganSelect');
    const jumlahPesertaInput = document.getElementById('jumlahPeserta');
    const form = document.getElementById('peminjamanForm');
    
    // Error elements
    const tanggalMulaiError = document.getElementById('tanggal_mulai_error');
    const tanggalSelesaiError = document.getElementById('tanggal_selesai_error');
    const jamMulaiError = document.getElementById('jam_mulai_error');
    const jamSelesaiError = document.getElementById('jam_selesai_error');
    
    const today = new Date().toISOString().split('T')[0];
    const currentTime = getCurrentTime();
    
    // Set min date untuk tanggal mulai (hari ini)
    tanggalMulaiInput.min = today;
    
    // Set min dan max untuk jam (07:00 - 17:00)
    jamMulaiInput.min = "07:00";
    jamMulaiInput.max = "17:00";
    jamSelesaiInput.min = "07:00";
    jamSelesaiInput.max = "17:00";
    
    // ✅ JIKA ADA PARAMETER TANGGAL, GUNAKAN NILAI TERSEBUT
    if (tanggalParam) {
        tanggalMulaiInput.value = tanggalParam;
        tanggalSelesaiInput.value = tanggalParam;
    }
    
    // ✅ JIKA ADA PARAMETER JAM, GUNAKAN NILAI TERSEBUT
    if (jamMulaiParam) {
        jamMulaiInput.value = jamMulaiParam;
    }
    
    if (jamSelesaiParam) {
        jamSelesaiInput.value = jamSelesaiParam;
    }
    
    // Set default tanggal mulai (hari ini) jika belum ada nilai
    if (!tanggalMulaiInput.value) {
        tanggalMulaiInput.value = today;
    }
    
    // Set default tanggal selesai (sama dengan tanggal mulai)
    if (!tanggalSelesaiInput.value) {
        tanggalSelesaiInput.value = tanggalMulaiInput.value;
    }
    
    // Fungsi untuk validasi tanggal 31 Januari 2026
    function validate31Jan2026() {
        const tanggalMulai = tanggalMulaiInput.value;
        const tanggalSelesai = tanggalSelesaiInput.value;
        
        // Reset error messages
        tanggalMulaiError.classList.add('hidden');
        tanggalSelesaiError.classList.add('hidden');
        
        let hasError = false;
        
        // Cek tanggal mulai
        if (is31Jan2026(tanggalMulai)) {
            tanggalMulaiError.classList.remove('hidden');
            hasError = true;
        }
        
        // Cek tanggal selesai
        if (is31Jan2026(tanggalSelesai)) {
            tanggalSelesaiError.classList.remove('hidden');
            hasError = true;
        }
        
        return !hasError;
    }
    
    // Fungsi untuk validasi jam tidak bisa memilih waktu yang sudah lewat, minimal 07:00, maksimal 17:00
    function validateJamInput() {
        const tanggalMulai = tanggalMulaiInput.value;
        const jamMulai = jamMulaiInput.value;
        const jamSelesai = jamSelesaiInput.value;
        
        // Reset error messages
        jamMulaiError.classList.add('hidden');
        jamSelesaiError.classList.add('hidden');
        
        let hasError = false;
        
        // Validasi jam mulai tidak boleh kurang dari 07:00
        if (jamMulai && jamMulai < "07:00") {
            jamMulaiError.classList.remove('hidden');
            jamMulaiError.textContent = 'Jam mulai tidak boleh kurang dari jam 07:00';
            hasError = true;
        }
        
        // Validasi jam selesai tidak boleh kurang dari 07:00
        if (jamSelesai && jamSelesai < "07:00") {
            jamSelesaiError.classList.remove('hidden');
            jamSelesaiError.textContent = 'Jam selesai tidak boleh kurang dari jam 07:00';
            hasError = true;
        }
        
        // Validasi jam mulai tidak boleh melebihi jam 17:00
        if (jamMulai && jamMulai > "17:00") {
            jamMulaiError.classList.remove('hidden');
            jamMulaiError.textContent = 'Jam mulai tidak boleh melebihi jam 17:00';
            hasError = true;
        }
        
        // Validasi jam selesai tidak boleh melebihi jam 17:00
        if (jamSelesai && jamSelesai > "17:00") {
            jamSelesaiError.classList.remove('hidden');
            jamSelesaiError.textContent = 'Jam selesai tidak boleh melebihi jam 17:00';
            hasError = true;
        }
        
        // Validasi untuk tanggal mulai hari ini (jam tidak boleh kurang dari waktu sekarang)
        if (tanggalMulai === today) {
            const minTime = getMinTimeForDate(tanggalMulai);
            
            // Validasi jam mulai tidak boleh kurang dari waktu minimal
            if (jamMulai && jamMulai < minTime) {
                jamMulaiError.classList.remove('hidden');
                jamMulaiError.textContent = `Waktu minimal untuk hari ini adalah ${minTime}`;
                hasError = true;
            }
        }
        
        // Validasi jam selesai harus setelah jam mulai
        if (jamMulai && jamSelesai && jamSelesai <= jamMulai) {
            jamSelesaiError.classList.remove('hidden');
            jamSelesaiError.textContent = 'Jam selesai harus setelah jam mulai';
            hasError = true;
        }
        
        return !hasError;
    }
    
    // Fungsi untuk update field hari
    function updateHariField() {
        const tanggalMulai = tanggalMulaiInput.value;
        const tanggalSelesai = tanggalSelesaiInput.value;
        
        if (tanggalMulai === tanggalSelesai) {
            // Jika tanggal sama, tampilkan satu hari
            hariInput.value = getHariIndonesia(tanggalMulai);
        } else {
            // Jika tanggal berbeda, tampilkan rentang hari
            const hariMulai = getHariIndonesia(tanggalMulai);
            const hariSelesai = getHariIndonesia(tanggalSelesai);
            hariInput.value = `${hariMulai} - ${hariSelesai}`;
        }
    }
    
    // Fungsi untuk update waktu minimal berdasarkan tanggal
    function updateMinTime() {
        const selectedDate = tanggalMulaiInput.value;
        let minTime = getMinTimeForDate(selectedDate);
        
        // Set min time untuk jam mulai
        jamMulaiInput.min = minTime;
        
        // Jika jam mulai sudah diisi tapi kurang dari min time, reset
        if (jamMulaiInput.value && jamMulaiInput.value < minTime) {
            jamMulaiInput.value = minTime;
        }
        
        // Jika jam mulai melebihi 17:00, reset
        if (jamMulaiInput.value && jamMulaiInput.value > "17:00") {
            jamMulaiInput.value = "17:00";
        }
        
        // Jika jam mulai kurang dari 07:00, reset
        if (jamMulaiInput.value && jamMulaiInput.value < "07:00") {
            jamMulaiInput.value = "07:00";
        }
        
        // Reset jam selesai jika jam mulai berubah
        if (jamSelesaiInput.value && jamSelesaiInput.value <= jamMulaiInput.value) {
            const jamMulai = jamMulaiInput.value;
            const [hours, minutes] = jamMulai.split(':').map(Number);
            let nextHour = hours + 1;
            if (nextHour >= 24 || nextHour > 17) nextHour = 17;
            if (nextHour < 7) nextHour = 7;
            
            jamSelesaiInput.value = nextHour.toString().padStart(2, '0') + ':00';
        }
        
        // Validasi jam
        validateJamInput();
    }
    
    // Event listeners untuk tanggal mulai
    tanggalMulaiInput.addEventListener('change', function() {
        // Validasi tanggal selesai tidak boleh kurang dari tanggal mulai
        if (tanggalSelesaiInput.value < this.value) {
            tanggalSelesaiInput.value = this.value;
        }
        
        // Update min date untuk tanggal selesai
        tanggalSelesaiInput.min = this.value;
        
        // Validasi tanggal 31 Jan 2026
        validate31Jan2026();
        
        // Update waktu minimal
        updateMinTime();
        
        // Update field hari
        updateHariField();
        
        // Check availability
        checkRoomAvailability();
    });
    
    // Event listeners untuk tanggal selesai
    tanggalSelesaiInput.addEventListener('change', function() {
        // Validasi tanggal selesai tidak boleh kurang dari tanggal mulai
        if (this.value < tanggalMulaiInput.value) {
            this.value = tanggalMulaiInput.value;
        }
        
        // Validasi tanggal 31 Jan 2026
        validate31Jan2026();
        
        // Update field hari
        updateHariField();
        
        // Check availability
        checkRoomAvailability();
    });
    
    // Event listeners untuk jam mulai
    jamMulaiInput.addEventListener('change', function() {
        // Validasi jam tidak bisa memilih yang sudah lewat, minimal 07:00, maksimal 17:00
        validateJamInput();
        
        // Set default jam selesai jika belum diisi
        if (!jamSelesaiInput.value) {
            const [hours, minutes] = this.value.split(':').map(Number);
            let nextHour = hours + 1;
            if (nextHour >= 24) nextHour = 23;
            if (nextHour > 17) nextHour = 17; // Batasi maksimal jam 17:00
            if (nextHour < 7) nextHour = 7; // Batasi minimal jam 07:00
            jamSelesaiInput.value = nextHour.toString().padStart(2, '0') + ':00';
        }
        
        // Validasi jam selesai harus setelah jam mulai
        if (jamSelesaiInput.value && jamSelesaiInput.value <= this.value) {
            const [hours, minutes] = this.value.split(':').map(Number);
            let nextHour = hours + 1;
            if (nextHour >= 24) nextHour = 23;
            if (nextHour > 17) nextHour = 17; // Batasi maksimal jam 17:00
            if (nextHour < 7) nextHour = 7; // Batasi minimal jam 07:00
            jamSelesaiInput.value = nextHour.toString().padStart(2, '0') + ':00';
        }
        
        // Check availability
        checkRoomAvailability();
    });
    
    // Event listeners untuk jam selesai
    jamSelesaiInput.addEventListener('change', function() {
        validateJamInput();
        checkRoomAvailability();
    });
    
    // Event listener untuk ruangan select
    ruanganSelect.addEventListener('change', function() {
        validateKapasitas();
        checkRoomAvailability();
    });
    
    // Event listener untuk jumlah peserta
    jumlahPesertaInput.addEventListener('input', function() {
        validateKapasitas();
    });
    
    // Inisialisasi awal
    updateMinTime();
    updateHariField();
    validate31Jan2026();
    validateKapasitas();
    
    // ✅ JIKA ADA PARAMETER, CEK AVAILABILITY OTOMATIS
    if (tanggalParam && jamMulaiParam && jamSelesaiParam) {
        setTimeout(() => {
            checkRoomAvailability();
        }, 1000);
    }
    
    // Set default jam mulai jika belum diisi
    if (!jamMulaiInput.value) {
        const selectedDate = tanggalMulaiInput.value;
        let minTime = getMinTimeForDate(selectedDate);
        
        jamMulaiInput.value = minTime;
        
        // Set default jam selesai
        const [hours, minutes] = minTime.split(':').map(Number);
        let nextHour = hours + 1;
        if (nextHour >= 24) nextHour = 23;
        if (nextHour > 17) nextHour = 17; // Batasi maksimal jam 17:00
        if (nextHour < 7) nextHour = 7; // Batasi minimal jam 07:00
        jamSelesaiInput.value = nextHour.toString().padStart(2, '0') + ':00';
    }
    
    // Auto-scroll jika ada ruangan terpilih
    @if($selectedRuanganId)
    setTimeout(function() {
        ruanganSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 300);
    @endif
    
    // Validasi form submit
    form.addEventListener('submit', function(e) {
        // Validasi akhir sebelum submit
        const isTanggalValid = validate31Jan2026();
        const isJamValid = validateJamInput();
        const isKapasitasValid = validateKapasitas();
        
        // Validasi jam tidak boleh kurang dari 07:00
        const jamMulai = jamMulaiInput.value;
        const jamSelesai = jamSelesaiInput.value;
        
        if (jamMulai < "07:00" || jamSelesai < "07:00") {
            e.preventDefault();
            alert('Jam peminjaman tidak boleh kurang dari jam 07:00');
            return;
        }
        
        // Validasi jam tidak boleh melebihi jam 17:00
        if (jamMulai > "17:00" || jamSelesai > "17:00") {
            e.preventDefault();
            alert('Jam peminjaman tidak boleh melebihi jam 17:00');
            return;
        }
        
        // Validasi jenis_pengaju (wajib diisi di profil)
        const userJenisPengaju = {{ $user->jenis_pengaju ? 'true' : 'false' }};
        
        if (!userJenisPengaju) {
            e.preventDefault();
            alert('Harap lengkapi jenis pengaju di profil akademik terlebih dahulu.');
            window.location.href = "{{ route('profil.akademik') }}";
            return;
        }
        
        // Cek jika ada error
        if (!isTanggalValid || !isJamValid || !isKapasitasValid) {
            e.preventDefault();
            
            // Tampilkan semua error messages
            const errors = [];
            
            if (!isTanggalValid) {
                if (!tanggalMulaiError.classList.contains('hidden')) {
                    errors.push('Tanggal 31 Januari 2026 tidak tersedia untuk tanggal mulai');
                }
                if (!tanggalSelesaiError.classList.contains('hidden')) {
                    errors.push('Tanggal 31 Januari 2026 tidak tersedia untuk tanggal selesai');
                }
            }
            
            if (!isJamValid) {
                if (!jamMulaiError.classList.contains('hidden')) {
                    errors.push(jamMulaiError.textContent);
                }
                if (!jamSelesaiError.classList.contains('hidden')) {
                    errors.push(jamSelesaiError.textContent);
                }
            }
            
            if (!isKapasitasValid) {
                errors.push('Jumlah peserta melebihi kapasitas ruangan yang dipilih');
            }
            
            alert('Terdapat kesalahan:\n\n' + errors.join('\n'));
        }
        
        // Validasi tambahan: tanggal selesai >= tanggal mulai
        if (tanggalSelesaiInput.value < tanggalMulaiInput.value) {
            e.preventDefault();
            alert('Tanggal selesai tidak boleh kurang dari tanggal mulai');
        }
        
        // Validasi ruangan harus dipilih
        if (!ruanganSelect.value) {
            e.preventDefault();
            alert('Harap pilih ruangan terlebih dahulu');
        }
        
        // Validasi durasi minimal 15 menit
        const jamMulaiVal = jamMulaiInput.value;
        const jamSelesaiVal = jamSelesaiInput.value;
        const startTime = new Date(`2000-01-01T${jamMulaiVal}`);
        const endTime = new Date(`2000-01-01T${jamSelesaiVal}`);
        const durationMinutes = (endTime - startTime) / (1000 * 60);
        
        if (durationMinutes < 15) {
            e.preventDefault();
            alert('Durasi peminjaman minimal 15 menit');
        }
    });
    
    // Check availability setelah 2 detik
    setTimeout(() => {
        checkRoomAvailability();
    }, 2000);
    
    // Real-time validation
    setInterval(function() {
        // Update min time untuk hari ini (real-time)
        if (tanggalMulaiInput.value === today) {
            updateMinTime();
        }
    }, 60000); // Update setiap 1 menit
    
    // Debounce untuk mencegah terlalu banyak request
    let debounceTimer;
    function debounceCheckAvailability() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(checkRoomAvailability, 500);
    }
    
    // Tambahkan debounce pada input events
    tanggalMulaiInput.addEventListener('input', debounceCheckAvailability);
    tanggalSelesaiInput.addEventListener('input', debounceCheckAvailability);
    jamMulaiInput.addEventListener('input', debounceCheckAvailability);
    jamSelesaiInput.addEventListener('input', debounceCheckAvailability);
    ruanganSelect.addEventListener('input', debounceCheckAvailability);
});
</script>
@endsection