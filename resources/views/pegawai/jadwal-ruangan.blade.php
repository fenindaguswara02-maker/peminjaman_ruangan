@extends('layouts.pegawai')

@section('title', 'Jadwal Ruangan - Pegawai')
@section('page-title', 'Jadwal Ruangan')

@section('content')
@php
    // Buat alias untuk kompatibilitas
    $peminjaman = $peminjamanForDate;
    
    // Filter peminjaman hanya yang status disetujui untuk ditampilkan di jadwal
    $peminjamanDisetujui = $peminjaman->filter(function($booking) {
        return in_array($booking->status, ['disetujui', 'approved']);
    });
@endphp

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-primary-900 elegant-font">Jadwal Ruangan</h1>
                <p class="text-gray-600">Lihat dan kelola jadwal peminjaman ruangan</p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                    <input type="date" id="filter-date" value="{{ $selectedDate }}" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ruangan</label>
                    <select id="filter-ruangan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        <option value="">Semua Ruangan</option>
                        @foreach($ruangan as $room)
                            <option value="{{ $room->id }}" {{ $selectedRuangan == $room->id ? 'selected' : '' }}>
                                {{ $room->kode_ruangan }} - {{ $room->nama_ruangan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Ruangan</label>
                    <select id="filter-status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        <option value="">Semua Status</option>
                        <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="dibooking" {{ request('status') == 'dibooking' ? 'selected' : '' }}>Dibooking</option>
                        <option value="dipakai" {{ request('status') == 'dipakai' ? 'selected' : '' }}>Dipakai</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="applyFilters()" class="w-full bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center justify-center space-x-2 transition-colors">
                        <i class="fas fa-filter"></i>
                        <span>Filter Jadwal</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Kalender/Grid Jadwal -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="p-4 border-b">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Jadwal untuk {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM YYYY') }}</h2>
                <div class="flex space-x-2">
                    <button onclick="previousDate()" class="p-2 border rounded-lg hover:bg-gray-50"><i class="fas fa-chevron-left"></i></button>
                    <button onclick="todayDate()" class="px-4 py-2 border rounded-lg bg-primary-50 text-primary-600 hover:bg-primary-100 text-sm font-medium">Hari Ini</button>
                    <button onclick="nextDate()" class="p-2 border rounded-lg hover:bg-gray-50"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kapasitas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Ruangan</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        @for($i = 7; $i <= 17; $i++)
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ sprintf('%02d:00', $i) }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($ruangan as $room)
                    @php
                        // HANYA ambil peminjaman dengan status disetujui untuk ditampilkan di jadwal
                        $peminjamanRuangan = $peminjamanForDate->where('ruangan_id', $room->id)->filter(function($booking) {
                            return in_array($booking->status, ['disetujui', 'approved']);
                        });
                        $currentTime = now();
                    @endphp
                    <tr class="hover:bg-gray-50" id="room-row-{{ $room->id }}">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $room->kode_ruangan }}</div>
                            <div class="text-sm text-gray-500">{{ $room->nama_ruangan }}</div>
                         </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $room->kapasitas }} orang</span>
                         </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($room->status == 'tersedia')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" id="status-badge-{{ $room->id }}"><i class="fas fa-check-circle mr-1"></i>Tersedia</span>
                            @elseif($room->status == 'dibooking')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800" id="status-badge-{{ $room->id }}"><i class="fas fa-calendar-alt mr-1"></i>Dibooking</span>
                            @elseif($room->status == 'dipakai')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 animate-pulse" id="status-badge-{{ $room->id }}"><i class="fas fa-users mr-1"></i>Dipakai</span>
                            @elseif($room->status == 'maintenance')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" id="status-badge-{{ $room->id }}"><i class="fas fa-tools mr-1"></i>Maintenance</span>
                            @endif
                         </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <button onclick="showEditStatusModal({{ $room->id }}, '{{ $room->status }}', '{{ addslashes($room->nama_ruangan) }}')" 
                                    class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-50" title="Edit Status Ruangan">
                                <i class="fas fa-edit"></i>
                            </button>
                         </td>
                        @for($hour = 7; $hour <= 17; $hour++)
                            @php
                                $hourStr = sprintf('%02d:00', $hour);
                                $isBooked = false;
                                $bookingInfo = null;
                                $isCurrentHour = $hour == date('H');
                                foreach($peminjamanRuangan as $booking) {
                                    if (!in_array($booking->status, ['disetujui', 'approved'])) continue;
                                    $startHour = (int) substr($booking->jam_mulai, 0, 2);
                                    $endHour = (int) substr($booking->jam_selesai, 0, 2);
                                    if ($hour >= $startHour && $hour < $endHour) {
                                        $isBooked = true;
                                        $bookingInfo = $booking;
                                        break;
                                    }
                                }
                            @endphp
                            <td class="px-4 py-3 text-center border-l">
                                @if($room->status == 'maintenance')
                                    <div class="w-full h-8 bg-red-50 border border-red-200 rounded flex items-center justify-center" title="Maintenance"><i class="fas fa-tools text-red-400 text-xs"></i></div>
                                @elseif($isBooked && $bookingInfo)
                                    @php
                                        $cellColor = 'bg-yellow-50 border-yellow-200';
                                        $iconColor = 'text-yellow-500';
                                        $icon = 'fa-calendar-alt';
                                        if ($bookingInfo->status_real_time == 'akan_datang') { $cellColor = 'bg-blue-50 border-blue-200'; $iconColor = 'text-blue-500'; $icon = 'fa-clock'; }
                                        elseif ($bookingInfo->status_real_time == 'berlangsung') { $cellColor = 'bg-purple-50 border-purple-200 animate-pulse'; $iconColor = 'text-purple-500'; $icon = 'fa-users'; }
                                        elseif ($bookingInfo->status_real_time == 'selesai') { $cellColor = 'bg-green-50 border-green-200'; $iconColor = 'text-green-500'; $icon = 'fa-check-circle'; }
                                    @endphp
                                    <div class="w-full h-8 {{ $cellColor }} rounded flex items-center justify-center relative group cursor-pointer" 
                                         title="{{ $bookingInfo->acara }}"
                                         onclick="showDetailModalPegawai({{ $bookingInfo->id }})">
                                        <i class="fas {{ $icon }} {{ $iconColor }} text-xs"></i>
                                        @if($isCurrentHour && $selectedDate == date('Y-m-d'))<div class="absolute top-0 right-0 w-2 h-2 bg-blue-500 rounded-full"></div>@endif
                                        <div class="absolute z-10 invisible group-hover:visible bg-gray-900 text-white text-xs rounded py-1 px-2 bottom-full mb-2 left-1/2 transform -translate-x-1/2 whitespace-nowrap min-w-[200px]">
                                            <div class="font-semibold text-sm mb-1">{{ $bookingInfo->acara }}</div>
                                            <div class="text-gray-300"><i class="fas fa-user mr-1"></i>{{ $bookingInfo->nama_pengaju }}</div>
                                            <div class="text-gray-400"><i class="fas fa-clock mr-1"></i>{{ $bookingInfo->jam_mulai }} - {{ $bookingInfo->jam_selesai }}</div>
                                        </div>
                                    </div>
                                @else
                                    @php $cellClass = ($isCurrentHour && $selectedDate == date('Y-m-d')) ? 'bg-primary-50 border-2 border-primary-300' : 'bg-gray-50 border border-gray-200'; @endphp
                                    <div class="w-full h-8 {{ $cellClass }} rounded flex items-center justify-center" title="Tersedia {{ $hourStr }}"></div>
                                @endif
                             </td>
                        @endfor
                     </tr>
                    @endforeach
                </tbody>
             </table>
        </div>
        @if($ruangan->isEmpty())
            <div class="text-center py-12"><i class="fas fa-door-closed text-4xl text-gray-300 mb-3"></i><p class="text-gray-500">Tidak ada data ruangan</p></div>
        @endif
    </div>

    <!-- Daftar Peminjaman Hari Ini (HANYA STATUS DISETUJUI) -->
    <div class="bg-white rounded-lg shadow-lg mb-6">
        <div class="p-4 border-b">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Peminjaman untuk {{ \Carbon\Carbon::parse($selectedDate)->isoFormat('dddd, D MMMM YYYY') }}</h2>
                <button onclick="updateAllStatus()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg text-sm font-medium flex items-center"><i class="fas fa-sync-alt mr-2"></i> Update Status</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acara</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengaju</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peserta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Real-time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Riwayat Catatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="peminjamanTable">
                    @php $no = 1; @endphp
                    @foreach($peminjaman as $booking)
                        @if(!in_array($booking->status, ['disetujui', 'approved'])) @continue @endif
                        @php
                            $currentTime = now();
                            $bookingStart = \Carbon\Carbon::parse($selectedDate . ' ' . $booking->jam_mulai);
                            $bookingEnd = \Carbon\Carbon::parse($selectedDate . ' ' . $booking->jam_selesai);
                            $isActiveNow = $selectedDate == $currentTime->format('Y-m-d') && $currentTime->between($bookingStart, $bookingEnd) && $booking->status == 'disetujui';
                            
                            $bookingData = json_encode([
                                'id' => $booking->id,
                                'username' => $booking->user->username ?? '-',
                                'pengaju' => $booking->nama_pengaju,
                                'jenis' => $booking->jenis_pengaju,
                                'nim' => $booking->nim_nip,
                                'fakultas' => $booking->fakultas,
                                'prodi' => $booking->prodi ?? '-',
                                'email' => $booking->email,
                                'telepon' => $booking->no_telepon,
                                'ruangan' => $booking->ruangan->nama_ruangan,
                                'kode_ruangan' => $booking->ruangan->kode_ruangan,
                                'kapasitas' => $booking->ruangan->kapasitas,
                                'lokasi' => $booking->ruangan->lokasi ?? '-',
                                'acara' => $booking->acara,
                                'hari' => $booking->hari,
                                'tanggal' => \Carbon\Carbon::parse($booking->tanggal)->translatedFormat('d F Y'),
                                'tanggal_mulai' => \Carbon\Carbon::parse($booking->tanggal_mulai)->translatedFormat('d F Y'),
                                'tanggal_selesai' => \Carbon\Carbon::parse($booking->tanggal_selesai)->translatedFormat('d F Y'),
                                'jam_mulai' => $booking->jam_mulai,
                                'jam_selesai' => $booking->jam_selesai,
                                'peserta' => $booking->jumlah_peserta,
                                'status' => $booking->status,
                                'status_real_time' => $booking->status_real_time ?? 'akan_datang',
                                'alasan_penolakan' => $booking->alasan_penolakan,
                                'keterangan' => $booking->keterangan,
                                'lampiran_surat' => $booking->lampiran_surat,
                                'catatan' => $booking->catatan ?? '',
                                'created_at' => \Carbon\Carbon::parse($booking->created_at)->translatedFormat('l, d F Y H:i'),
                            ]);
                        @endphp
                        <tr class="hover:bg-gray-50" id="booking-row-{{ $booking->id }}" data-detail="{{ $bookingData }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $no++ }}</td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="font-medium text-gray-900">{{ $booking->ruangan->kode_ruangan }}</div><div class="text-sm text-gray-500">{{ $booking->ruangan->nama_ruangan }}</div></td>
                            <td class="px-6 py-4"><div class="text-sm font-medium text-gray-900">{{ $booking->acara }}</div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3"><span class="text-white text-sm font-medium">{{ strtoupper(substr($booking->nama_pengaju, 0, 1)) }}</span></div>
                                    <div><div class="text-sm text-gray-900">{{ $booking->nama_pengaju }}</div><div class="text-xs text-cyan-600"><i class="fas fa-at mr-1"></i>{{ $booking->user->username ?? '-' }}</div></div>
                                </div>
                             </td>
                            <td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-900">{{ $booking->jam_mulai }} - {{ $booking->jam_selesai }}</div></td>
                            <td class="px-6 py-4 whitespace-nowrap"><span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $booking->jumlah_peserta }} orang</span></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Disetujui</span>
                             </td>
                            <td class="px-6 py-4 whitespace-nowrap" id="real-time-status-{{ $booking->id }}">
                                @switch($booking->status_real_time)
                                    @case('akan_datang')<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Akan Datang</span>@break
                                    @case('berlangsung')<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 animate-pulse"><i class="fas fa-play-circle mr-1"></i>Berlangsung</span>@break
                                    @case('selesai')<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Selesai</span>@break
                                    @default<span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><i class="fas fa-clock mr-1"></i>Akan Datang</span>
                                @endswitch
                             </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($booking->catatan && $booking->catatan != '')
                                    <button onclick="showRiwayatCatatan({{ $booking->id }})" 
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800 hover:bg-teal-200 transition-colors cursor-pointer"
                                            title="Klik untuk lihat riwayat catatan">
                                        <i class="fas fa-history mr-1"></i>
                                        Lihat Riwayat
                                    </button>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                             </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" id="action-cell-{{ $booking->id }}">
                                <div class="flex space-x-2">
                                    <button onclick="showDetailModalPegawai({{ $booking->id }})" class="text-blue-600 hover:text-blue-900" title="Detail"><i class="fas fa-eye"></i></button>
                                    @if($booking->status_real_time != 'selesai')
                                        <button onclick="showEditStatusModalPeminjaman({{ $booking->id }})" class="text-purple-600 hover:text-purple-900" title="Edit Status"><i class="fas fa-edit"></i></button>
                                    @endif
                                    <!-- Aksi Batalkan TETAP ADA -->
                                    <form action="{{ route('pegawai.peminjaman-ruangan.cancel', $booking->id) }}" method="POST" class="inline" onsubmit="return confirm('Batalkan peminjaman ini?')">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="text-gray-600 hover:text-gray-900" title="Batalkan">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                </div>
                             </td>
                         </tr>
                    @endforeach
                </tbody>
             </table>
            @if($peminjamanDisetujui->isEmpty())
                <div class="text-center py-8"><i class="fas fa-calendar-times text-3xl text-gray-300 mb-3"></i><p class="text-gray-500">Tidak ada peminjaman yang disetujui untuk tanggal ini</p></div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL DETAIL PEMINJAMAN -->
<div id="detailModalPegawai" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Detail Peminjaman Ruangan</h3>
            <button onclick="closeDetailModalPegawai()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]" id="detailContentPegawai">
            <div class="text-center py-8"><div class="loading-spinner mx-auto"></div><p class="mt-4">Memuat data...</p></div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button onclick="closeDetailModalPegawai()" class="px-4 py-2 bg-gray-500 text-white rounded-lg">Tutup</button>
        </div>
    </div>
</div>

<!-- MODAL EDIT STATUS PEMINJAMAN -->
<div id="editStatusModalPeminjaman" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-indigo-50">
            <h3 class="text-lg font-semibold text-gray-800">Edit Status Real-time</h3>
            <p class="text-sm text-gray-600">Perubahan status akan tercatat di riwayat</p>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Status Baru</label>
                <select id="newStatusPeminjamanSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                    <option value="akan_datang">Akan Datang</option>
                    <option value="berlangsung">Berlangsung</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
            <div class="mb-4 hidden" id="statusPeminjamanWarning">
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                        <p class="text-sm text-yellow-700" id="warningPeminjamanText"></p>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note mr-1 text-teal-600"></i> Catatan Perubahan Status
                </label>
                <textarea id="statusPeminjamanNote" rows="4" 
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors" 
                          placeholder="Masukkan catatan untuk perubahan status ini..."></textarea>
                <p class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-info-circle mr-1"></i> Catatan ini akan tersimpan dan dapat dilihat di riwayat catatan
                </p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
            <button onclick="closeEditStatusModalPeminjaman()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">Batal</button>
            <button onclick="saveStatusPeminjamanChange()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center space-x-2">
                <i class="fas fa-save"></i><span>Simpan Perubahan</span>
            </button>
        </div>
    </div>
</div>

<!-- MODAL EDIT STATUS RUANGAN (TANPA KETERANGAN - HANYA STATUS) -->
<div id="editStatusRuanganModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Edit Status Ruangan</h3>
            <button onclick="closeEditStatusRuanganModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-6">
            <input type="hidden" id="edit_ruangan_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Ruangan</label>
                <p class="text-gray-900 font-medium" id="edit_nama_ruangan"></p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status Ruangan</label>
                <select id="edit_status_ruangan" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="tersedia">Tersedia</option>
                    <option value="dibooking">Dibooking</option>
                    <option value="dipakai">Dipakai</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <!-- KETERANGAN DIHAPUS - TIDAK ADA LAGI TEXTAREA -->
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg hidden" id="editStatusWarning">
                <p class="text-sm text-yellow-700" id="editWarningText"></p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
            <button onclick="closeEditStatusRuanganModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg">Batal</button>
            <button onclick="saveStatusRuangan()" class="px-4 py-2 bg-primary-600 text-white rounded-lg">Simpan</button>
        </div>
    </div>
</div>

<!-- MODAL LIHAT RIWAYAT CATATAN -->
<div id="riwayatCatatanModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[85vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-teal-50 to-cyan-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-teal-600 rounded-full flex items-center justify-center"><i class="fas fa-history text-white"></i></div>
                    <div><h3 class="text-lg font-semibold text-gray-800">Riwayat Catatan Peminjaman</h3><p class="text-sm text-gray-600" id="riwayatCatatanAcara">-</p></div>
                </div>
                <button onclick="closeRiwayatCatatanModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(85vh-120px)]">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-start space-x-3 mb-4"><i class="fas fa-info-circle text-teal-600 mt-1"></i><p class="text-sm text-gray-500">Riwayat semua perubahan status dan catatan yang tersimpan</p></div>
                <div class="bg-white rounded-lg p-4 border border-gray-200"><pre id="riwayatCatatanContent" class="text-gray-700 whitespace-pre-wrap font-mono text-sm" style="white-space: pre-wrap; word-wrap: break-word;">-</pre></div>
                <div class="mt-4 text-xs text-gray-400 text-center"><i class="fas fa-lock mr-1"></i> Catatan ini hanya dapat dilihat, tidak dapat diedit</div>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
            <button onclick="closeRiwayatCatatanModal()" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">Tutup</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
@keyframes modalFadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
.modal-animate { animation: modalFadeIn 0.3s ease-out; }
.loading-spinner { border: 3px solid rgba(0,0,0,0.1); border-radius: 50%; border-top: 3px solid #3b82f6; width: 40px; height: 40px; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.animate-pulse { animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite; }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
.group:hover .group-hover\:visible { visibility: visible; }
</style>
@endpush

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
let currentBookingId = null;
let currentRoomId = null;

function applyFilters() {
    let url = '{{ route("pegawai.jadwal-ruangan.index") }}?date=' + document.getElementById('filter-date').value;
    if (document.getElementById('filter-ruangan').value) url += '&ruangan=' + document.getElementById('filter-ruangan').value;
    if (document.getElementById('filter-status').value) url += '&status=' + document.getElementById('filter-status').value;
    window.location.href = url;
}
function previousDate() { let d = new Date('{{ $selectedDate }}'); d.setDate(d.getDate()-1); window.location.href = '{{ route("pegawai.jadwal-ruangan.index") }}?date=' + d.toISOString().split('T')[0]; }
function todayDate() { window.location.href = '{{ route("pegawai.jadwal-ruangan.index") }}?date=' + new Date().toISOString().split('T')[0]; }
function nextDate() { let d = new Date('{{ $selectedDate }}'); d.setDate(d.getDate()+1); window.location.href = '{{ route("pegawai.jadwal-ruangan.index") }}?date=' + d.toISOString().split('T')[0]; }

// Detail Peminjaman
function showDetailModalPegawai(id) {
    const row = document.querySelector(`tr[data-detail*='"id":${id}']`);
    if (!row) return alert('Data tidak ditemukan');
    try {
        const data = JSON.parse(row.getAttribute('data-detail'));
        document.getElementById('detailContentPegawai').innerHTML = generateDetailHtml(data);
        document.getElementById('detailModalPegawai').classList.remove('hidden');
        document.getElementById('detailModalPegawai').classList.add('modal-animate');
    } catch(e) { alert('Gagal memuat data'); }
}

function generateDetailHtml(data) {
    const realTimeColor = { akan_datang:'yellow', berlangsung:'purple', selesai:'green' }[data.status_real_time] || 'yellow';
    const realTimeText = { akan_datang:'Akan Datang', berlangsung:'Berlangsung', selesai:'Selesai' }[data.status_real_time] || data.status_real_time;
    const hasCatatan = data.catatan && data.catatan.trim() !== '';
    
    return `<div class="space-y-6">
        <div class="text-center border-b pb-4"><span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full">ID: #${data.id}</span><h4 class="text-xl font-bold mt-2">${escapeHtml(data.acara)}</h4><p>${escapeHtml(data.kode_ruangan)} - ${escapeHtml(data.ruangan)}</p></div>
        <div class="bg-gray-50 p-4 rounded-lg"><h5 class="font-semibold mb-3"><i class="fas fa-user-circle mr-2 text-primary-600"></i>Informasi Pengaju</h5>
            <div class="grid grid-cols-2 gap-4"><div class="bg-cyan-50 p-2 rounded col-span-2"><label class="text-xs text-gray-500">Username</label><p class="font-medium text-cyan-800">${escapeHtml(data.username)}</p></div>
            <div><label class="text-xs text-gray-500">Nama</label><p>${escapeHtml(data.pengaju)}</p></div><div><label class="text-xs text-gray-500">NIM/NIP</label><p>${escapeHtml(data.nim)}</p></div>
            <div><label class="text-xs text-gray-500">Email</label><p>${escapeHtml(data.email)}</p></div><div><label class="text-xs text-gray-500">Telepon</label><p>${escapeHtml(data.telepon)}</p></div></div></div>
        <div class="bg-gray-50 p-4 rounded-lg"><h5 class="font-semibold mb-3"><i class="fas fa-door-open mr-2 text-primary-600"></i>Informasi Ruangan</h5>
            <div class="grid grid-cols-2 gap-4"><div><label class="text-xs text-gray-500">Kode</label><p>${escapeHtml(data.kode_ruangan)}</p></div>
            <div><label class="text-xs text-gray-500">Nama</label><p>${escapeHtml(data.ruangan)}</p></div><div><label class="text-xs text-gray-500">Kapasitas</label><p>${escapeHtml(data.kapasitas)} orang</p></div>
            <div><label class="text-xs text-gray-500">Lokasi</label><p>${escapeHtml(data.lokasi)}</p></div></div></div>
        <div class="bg-gray-50 p-4 rounded-lg"><h5 class="font-semibold mb-3"><i class="fas fa-calendar-alt mr-2 text-primary-600"></i>Jadwal</h5>
            <div class="grid grid-cols-2 gap-4"><div><label class="text-xs text-gray-500">Tanggal</label><p>${escapeHtml(data.tanggal)}</p></div>
            <div><label class="text-xs text-gray-500">Waktu</label><p>${escapeHtml(data.jam_mulai)} - ${escapeHtml(data.jam_selesai)}</p></div>
            <div><label class="text-xs text-gray-500">Peserta</label><p>${escapeHtml(data.peserta)} orang</p></div>
            <div><label class="text-xs text-gray-500">Status Real-time</label><span class="px-2 py-1 bg-${realTimeColor}-100 text-${realTimeColor}-800 rounded-full text-xs">${realTimeText}</span></div></div></div>
        <div class="bg-teal-50 p-4 rounded-lg"><h5 class="font-semibold mb-3"><i class="fas fa-history mr-2 text-teal-600"></i>Riwayat Catatan</h5>
            ${hasCatatan ? `<div class="bg-white p-4 rounded border border-teal-200"><pre class="whitespace-pre-wrap font-mono text-sm text-gray-700">${escapeHtml(data.catatan)}</pre></div>` : 
            `<div class="bg-white p-4 rounded border text-center"><p class="text-gray-500">Belum ada catatan</p></div>`}</div></div>`;
}

function escapeHtml(t) { if(!t) return ''; const d=document.createElement('div'); d.textContent=t; return d.innerHTML; }
function closeDetailModalPegawai() { document.getElementById('detailModalPegawai').classList.add('hidden'); }

// Riwayat Catatan
function showRiwayatCatatan(id) {
    const row = document.querySelector(`tr[data-detail*='"id":${id}']`);
    if (!row) return alert('Data tidak ditemukan');
    const data = JSON.parse(row.getAttribute('data-detail'));
    document.getElementById('riwayatCatatanAcara').textContent = data.acara;
    document.getElementById('riwayatCatatanContent').textContent = data.catatan || 'Belum ada catatan';
    document.getElementById('riwayatCatatanModal').classList.remove('hidden');
    document.getElementById('riwayatCatatanModal').classList.add('flex', 'modal-animate');
}
function closeRiwayatCatatanModal() { document.getElementById('riwayatCatatanModal').classList.add('hidden'); }

// Edit Status Peminjaman
function showEditStatusModalPeminjaman(id) {
    currentBookingId = id;
    const row = document.querySelector(`tr[data-detail*='"id":${id}']`);
    if (!row) return alert('Data tidak ditemukan');
    const data = JSON.parse(row.getAttribute('data-detail'));
    document.getElementById('newStatusPeminjamanSelect').value = data.status_real_time || 'akan_datang';
    document.getElementById('statusPeminjamanNote').value = '';
    document.getElementById('editStatusModalPeminjaman').classList.remove('hidden');
    document.getElementById('editStatusModalPeminjaman').classList.add('modal-animate');
}
function closeEditStatusModalPeminjaman() { document.getElementById('editStatusModalPeminjaman').classList.add('hidden'); currentBookingId = null; }

async function saveStatusPeminjamanChange() {
    if (!currentBookingId) return;
    const newStatus = document.getElementById('newStatusPeminjamanSelect').value;
    const note = document.getElementById('statusPeminjamanNote').value;
    if (!confirm(`Ubah status menjadi "${newStatus}"?`)) return;
    
    const saveBtn = document.querySelector('#editStatusModalPeminjaman button[onclick="saveStatusPeminjamanChange()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
    saveBtn.disabled = true;
    
    try {
        const response = await fetch(`/pegawai/peminjaman-ruangan/${currentBookingId}/update-status-real-time`, {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ status_real_time: newStatus, note: note })
        });
        const data = await response.json();
        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Status diperbarui', showConfirmButton: false, timer: 1500 });
            closeEditStatusModalPeminjaman();
            setTimeout(() => window.location.reload(), 1500);
        } else { Swal.fire('Gagal!', data.message, 'error'); saveBtn.innerHTML = originalText; saveBtn.disabled = false; }
    } catch(e) { Swal.fire('Gagal!', e.message, 'error'); saveBtn.innerHTML = originalText; saveBtn.disabled = false; }
}

async function updateAllStatus() {
    if (!confirm('Update semua status real-time?')) return;
    try {
        const response = await fetch('{{ route("pegawai.peminjaman-ruangan.update-status") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
        const data = await response.json();
        if (data.success) Swal.fire('Berhasil!', `Update ${data.updated || 0} status`, 'success').then(() => location.reload());
        else Swal.fire('Gagal!', data.message, 'error');
    } catch(e) { Swal.fire('Gagal!', 'Terjadi kesalahan', 'error'); }
}

// Edit Status Ruangan (TANPA KETERANGAN - HANYA STATUS)
function showEditStatusModal(id, currentStatus, namaRuangan) {
    currentRoomId = id;
    document.getElementById('edit_ruangan_id').value = id;
    document.getElementById('edit_nama_ruangan').textContent = namaRuangan;
    document.getElementById('edit_status_ruangan').value = currentStatus;
    
    const warningDiv = document.getElementById('editStatusWarning');
    const warningText = document.getElementById('editWarningText');
    if (currentStatus === 'maintenance') {
        warningText.textContent = 'Ruangan sedang dalam maintenance. Ubah status jika perbaikan selesai.';
        warningDiv.classList.remove('hidden');
    } else if (currentStatus === 'dipakai') {
        warningText.textContent = 'Ruangan sedang dipakai. Pastikan tidak ada acara berlangsung sebelum mengubah status.';
        warningDiv.classList.remove('hidden');
    } else { warningDiv.classList.add('hidden'); }
    
    document.getElementById('editStatusRuanganModal').classList.remove('hidden');
    document.getElementById('editStatusRuanganModal').classList.add('modal-animate');
}

function closeEditStatusRuanganModal() { document.getElementById('editStatusRuanganModal').classList.add('hidden'); currentRoomId = null; }

document.getElementById('edit_status_ruangan')?.addEventListener('change', function() {
    const warningDiv = document.getElementById('editStatusWarning');
    const warningText = document.getElementById('editWarningText');
    if (this.value === 'maintenance') {
        warningText.textContent = 'Status Maintenance akan menandai ruangan tidak tersedia untuk peminjaman.';
        warningDiv.classList.remove('hidden');
    } else if (this.value === 'dipakai') {
        warningText.textContent = 'Status Dipakai akan menandai ruangan sedang digunakan.';
        warningDiv.classList.remove('hidden');
    } else { warningDiv.classList.add('hidden'); }
});

async function saveStatusRuangan() {
    if (!currentRoomId) return;
    const status = document.getElementById('edit_status_ruangan').value;
    if (!confirm(`Ubah status ruangan menjadi "${status}"?`)) return;
    
    const saveBtn = document.querySelector('#editStatusRuanganModal button[onclick="saveStatusRuangan()"]');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
    saveBtn.disabled = true;
    
    try {
        // Kirim data ke server (TANPA KETERANGAN)
        const response = await fetch(`/pegawai/ruangan/${currentRoomId}/update-status`, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': csrfToken, 
                'Accept': 'application/json' 
            },
            body: JSON.stringify({ status: status })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update badge status di tabel
            const statusBadge = document.getElementById(`status-badge-${currentRoomId}`);
            if (statusBadge) {
                let badgeClass = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ';
                let badgeIcon = '', badgeText = '';
                switch(status) {
                    case 'tersedia': badgeClass += 'bg-green-100 text-green-800'; badgeIcon = 'fa-check-circle'; badgeText = 'Tersedia'; break;
                    case 'dibooking': badgeClass += 'bg-yellow-100 text-yellow-800'; badgeIcon = 'fa-calendar-alt'; badgeText = 'Dibooking'; break;
                    case 'dipakai': badgeClass += 'bg-purple-100 text-purple-800 animate-pulse'; badgeIcon = 'fa-users'; badgeText = 'Dipakai'; break;
                    case 'maintenance': badgeClass += 'bg-red-100 text-red-800'; badgeIcon = 'fa-tools'; badgeText = 'Maintenance'; break;
                }
                statusBadge.innerHTML = `<i class="fas ${badgeIcon} mr-1"></i>${badgeText}`;
                statusBadge.className = badgeClass;
            }
            
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Status ruangan diperbarui', showConfirmButton: false, timer: 1500 });
            closeEditStatusRuanganModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            Swal.fire('Gagal!', data.message || 'Gagal mengubah status', 'error');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    } catch(e) { 
        Swal.fire('Gagal!', 'Terjadi kesalahan: ' + e.message, 'error');
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    const modals = ['detailModalPegawai', 'editStatusModalPeminjaman', 'editStatusRuanganModal', 'riwayatCatatanModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if(modal) modal.addEventListener('click', function(e) { if(e.target === this) { 
            if(modalId==='detailModalPegawai') closeDetailModalPegawai(); 
            else if(modalId==='editStatusModalPeminjaman') closeEditStatusModalPeminjaman(); 
            else if(modalId==='editStatusRuanganModal') closeEditStatusRuanganModal(); 
            else closeRiwayatCatatanModal(); 
        } });
    });
    document.addEventListener('keydown', e => { if(e.key === 'Escape') { closeDetailModalPegawai(); closeEditStatusModalPeminjaman(); closeEditStatusRuanganModal(); closeRiwayatCatatanModal(); } });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush