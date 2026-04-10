<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JadwalRuanganController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter filter dari request
        $selectedDate = $request->get('date', Carbon::now()->format('Y-m-d'));
        $selectedRuangan = $request->get('ruangan', '');
        $selectedStatus = $request->get('status', '');
        
        // Ambil semua ruangan untuk filter
        $ruangan = Ruangan::orderBy('nama_ruangan')->get();
        
        // Filter ruangan berdasarkan status jika ada
        $ruanganQuery = Ruangan::query();
        if ($selectedStatus) {
            $ruanganQuery->where('status', $selectedStatus);
        }
        $filteredRuangan = $ruanganQuery->orderBy('nama_ruangan')->get();
        
        // Ambil peminjaman untuk tanggal yang dipilih (untuk tabel view)
        $peminjamanForDate = PeminjamanRuangan::whereDate('tanggal', $selectedDate)
            ->when($selectedRuangan, function($query, $selectedRuangan) {
                return $query->where('ruangan_id', $selectedRuangan);
            })
            ->with('ruangan', 'user')
            ->orderBy('jam_mulai')
            ->get();
            
        // Ambil semua peminjaman untuk tanggal ini (untuk tabel bawah)
        $peminjaman = PeminjamanRuangan::whereDate('tanggal', $selectedDate)
            ->when($selectedRuangan, function($query, $selectedRuangan) {
                return $query->where('ruangan_id', $selectedRuangan);
            })
            ->with('ruangan', 'user')
            ->orderBy('jam_mulai')
            ->get();
        
        // Cek ketersediaan ruangan untuk tanggal yang dipilih
        $ruanganTerpakai = [];
        $peminjamanHariIni = PeminjamanRuangan::whereDate('tanggal', $selectedDate)
            ->whereIn('status', ['disetujui', 'selesai'])
            ->get();
            
        foreach ($peminjamanHariIni as $p) {
            $jamMulai = (int) substr($p->jam_mulai, 0, 2);
            $jamSelesai = (int) substr($p->jam_selesai, 0, 2);
            
            for ($jam = $jamMulai; $jam < $jamSelesai; $jam++) {
                if (!isset($ruanganTerpakai[$p->ruangan_id][$jam])) {
                    $ruanganTerpakai[$p->ruangan_id][$jam] = [];
                }
                
                $ruanganTerpakai[$p->ruangan_id][$jam][] = [
                    'id' => $p->id,
                    'acara' => $p->acara,
                    'jam_mulai' => $p->jam_mulai,
                    'jam_selesai' => $p->jam_selesai,
                    'status_real_time' => $p->status_real_time ?? $this->calculateStatusRealTime($p)
                ];
            }
        }
        
        return view('pegawai.jadwal-ruangan', compact(
            'ruangan',
            'filteredRuangan',
            'ruanganTerpakai',
            'selectedDate',
            'selectedRuangan',
            'selectedStatus',
            'peminjamanForDate',
            'peminjaman'
        ));
    }
    
    /**
     * Calculate status real-time berdasarkan waktu sekarang
     */
    private function calculateStatusRealTime($peminjaman)
    {
        $currentTime = Carbon::now();
        $tanggal = $peminjaman->tanggal;
        $jamMulai = $peminjaman->jam_mulai;
        $jamSelesai = $peminjaman->jam_selesai;
        
        $startDateTime = Carbon::parse($tanggal . ' ' . $jamMulai);
        $endDateTime = Carbon::parse($tanggal . ' ' . $jamSelesai);
        
        if ($currentTime->lt($startDateTime)) {
            return 'akan_datang';
        } elseif ($currentTime->between($startDateTime, $endDateTime)) {
            return 'berlangsung';
        } else {
            return 'selesai';
        }
    }
    
    /**
     * Get booking detail for modal
     */
    public function detail($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::with('ruangan', 'user')->findOrFail($id);
            
            $statusText = [
                'menunggu' => 'Menunggu Persetujuan',
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan'
            ];
            
            $realTimeText = [
                'akan_datang' => 'Akan Datang',
                'berlangsung' => 'Berlangsung',
                'selesai' => 'Selesai'
            ];
            
            $statusColors = [
                'menunggu' => 'yellow',
                'disetujui' => 'green',
                'ditolak' => 'red',
                'selesai' => 'blue',
                'dibatalkan' => 'gray'
            ];
            
            $realTimeColors = [
                'akan_datang' => 'yellow',
                'berlangsung' => 'purple',
                'selesai' => 'green'
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $peminjaman->id,
                    'acara' => $peminjaman->acara,
                    'keterangan' => $peminjaman->keterangan,
                    'catatan' => $peminjaman->catatan,
                    'tanggal' => $peminjaman->tanggal,
                    'tanggal_formatted' => Carbon::parse($peminjaman->tanggal)->isoFormat('D MMMM YYYY'),
                    'tanggal_mulai' => $peminjaman->tanggal_mulai,
                    'tanggal_selesai' => $peminjaman->tanggal_selesai,
                    'jam_mulai' => $peminjaman->jam_mulai,
                    'jam_selesai' => $peminjaman->jam_selesai,
                    'jumlah_peserta' => $peminjaman->jumlah_peserta,
                    'nama_pengaju' => $peminjaman->nama_pengaju,
                    'no_telepon' => $peminjaman->no_telepon,
                    'email' => $peminjaman->email,
                    'nim_nip' => $peminjaman->nim_nip,
                    'fakultas' => $peminjaman->fakultas,
                    'prodi' => $peminjaman->prodi,
                    'jenis_pengaju' => $peminjaman->jenis_pengaju,
                    'status' => $peminjaman->status,
                    'status_text' => $statusText[$peminjaman->status] ?? $peminjaman->status,
                    'status_color' => $statusColors[$peminjaman->status] ?? 'gray',
                    'status_real_time' => $peminjaman->status_real_time ?? $this->calculateStatusRealTime($peminjaman),
                    'status_real_time_text' => $realTimeText[$peminjaman->status_real_time] ?? 'Akan Datang',
                    'status_real_time_color' => $realTimeColors[$peminjaman->status_real_time] ?? 'yellow',
                    'alasan_penolakan' => $peminjaman->alasan_penolakan,
                    'lampiran_surat' => $peminjaman->lampiran_surat,
                    'created_at_formatted' => $peminjaman->created_at->isoFormat('D MMMM YYYY HH:mm'),
                    'updated_at_formatted' => $peminjaman->updated_at->isoFormat('D MMMM YYYY HH:mm'),
                    'username' => $peminjaman->user->username ?? '-',
                    'ruangan' => $peminjaman->ruangan ? [
                        'id' => $peminjaman->ruangan->id,
                        'kode_ruangan' => $peminjaman->ruangan->kode_ruangan,
                        'nama_ruangan' => $peminjaman->ruangan->nama_ruangan,
                        'kapasitas' => $peminjaman->ruangan->kapasitas,
                        'lokasi' => $peminjaman->ruangan->lokasi,
                        'status' => $peminjaman->ruangan->status
                    ] : null
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching booking detail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail peminjaman'
            ], 404);
        }
    }
    
    /**
     * UPDATE STATUS REAL-TIME PEMINJAMAN DENGAN CATATAN
     * TIDAK MENGUBAH STATUS RUANGAN SECARA OTOMATIS
     */
    public function updateStatusRealTime(Request $request, $id)
    {
        try {
            // Cek apakah user login
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu'
                ], 401);
            }
            
            $peminjaman = PeminjamanRuangan::with('ruangan')->findOrFail($id);
            
            $validated = $request->validate([
                'status_real_time' => 'required|in:akan_datang,berlangsung,selesai',
                'note' => 'nullable|string|max:500'
            ]);
            
            if ($peminjaman->status != 'disetujui') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya peminjaman dengan status DISETUJUI yang dapat diubah status real-timenya'
                ], 400);
            }
            
            $oldStatus = $peminjaman->status_real_time;
            $newStatus = $validated['status_real_time'];
            $noteText = $validated['note'] ?? '';
            
            // ========== SIMPAN CATATAN (HANYA ISI CATATAN SAJA) ==========
            if (!empty($noteText)) {
                $oldCatatan = $peminjaman->catatan ?? '';
                
                // Jika sudah ada catatan sebelumnya, tambahkan dengan baris baru
                if (!empty($oldCatatan)) {
                    $peminjaman->catatan = $oldCatatan . "\n" . $noteText;
                } else {
                    $peminjaman->catatan = $noteText;
                }
            }
            
            // Update status real-time
            $peminjaman->status_real_time = $newStatus;
            $peminjaman->save();
            
            // ========== HAPUS PERUBAHAN STATUS RUANGAN OTOMATIS ==========
            // TIDAK ADA perubahan status ruangan di sini
            // Status ruangan hanya berubah ketika admin mengubahnya secara manual
            
            // Log activity
            try {
                LogActivity::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'update',
                    'aktivitas' => 'UPDATE STATUS REAL-TIME',
                    'deskripsi' => "========================================\n" .
                                   "📋 UPDATE STATUS REAL-TIME PEMINJAMAN\n" .
                                   "========================================\n" .
                                   "🆔 ID Peminjaman : #{$id}\n" .
                                   "📝 Acara         : {$peminjaman->acara}\n" .
                                   "🏠 Ruangan       : {$peminjaman->ruangan->nama_ruangan}\n" .
                                   "👤 Diubah Oleh   : " . (auth()->user()->name ?? auth()->user()->username) . "\n" .
                                   "🔄 Status Lama   : " . ($oldStatus ?? 'belum diatur') . "\n" .
                                   "🆕 Status Baru   : {$newStatus}\n" .
                                   ($noteText ? "📝 Catatan       : {$noteText}\n" : "") .
                                   "⏰ Waktu         : " . Carbon::now()->format('d-m-Y H:i:s') . "\n" .
                                   "========================================",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $logError) {
                \Log::error('Gagal membuat log: ' . $logError->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Status real-time berhasil diperbarui' . ($noteText ? ' dengan catatan' : ''),
                'data' => [
                    'id' => $peminjaman->id,
                    'status_real_time' => $peminjaman->status_real_time,
                    'catatan' => $peminjaman->catatan
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating real-time status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * UPDATE ALL STATUS REAL-TIME (BATCH UPDATE) - OTOMATIS OLEH SISTEM
     * TIDAK MENGUBAH STATUS RUANGAN SECARA OTOMATIS
     */
    public function updateAllStatusRealTime(Request $request)
    {
        try {
            $updated = 0;
            $logs = [];
            
            $peminjamanList = PeminjamanRuangan::with('ruangan')->where('status', 'disetujui')->get();
            
            foreach ($peminjamanList as $peminjaman) {
                $oldStatus = $peminjaman->status_real_time;
                $newStatus = $this->calculateStatusRealTime($peminjaman);
                
                if ($oldStatus != $newStatus) {
                    // Update status tanpa menyimpan catatan
                    $peminjaman->status_real_time = $newStatus;
                    $peminjaman->save();
                    $updated++;
                    
                    // ========== HAPUS PERUBAHAN STATUS RUANGAN OTOMATIS ==========
                    // TIDAK ADA perubahan status ruangan di sini
                    
                    $logs[] = "ID #{$peminjaman->id}: {$oldStatus} → {$newStatus}";
                }
            }
            
            // Catat batch update ke LogActivity jika ada perubahan
            if ($updated > 0) {
                try {
                    LogActivity::create([
                        'user_id' => auth()->id(),
                        'tipe' => 'update',
                        'aktivitas' => 'BATCH UPDATE STATUS REAL-TIME',
                        'deskripsi' => "========================================\n" .
                                       "🔄 BATCH UPDATE STATUS REAL-TIME\n" .
                                       "========================================\n" .
                                       "📊 Total Update : {$updated} peminjaman\n" .
                                       "👤 Dilakukan Oleh: " . (auth()->user()->name ?? auth()->user()->username ?? 'System') . "\n" .
                                       "⏰ Waktu        : " . Carbon::now()->format('d-m-Y H:i:s') . "\n" .
                                       "========================================\n" .
                                       "Detail Update:\n" . implode("\n", $logs) . "\n" .
                                       "========================================",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent()
                    ]);
                } catch (\Exception $logError) {
                    \Log::error('Gagal membuat log batch update: ' . $logError->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui {$updated} status real-time",
                'updated' => $updated
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error updating all real-time status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * UPDATE STATUS RUANGAN - DIPERBAIKI DENGAN LOG ACTIVITY YANG LEBIH DETAIL
     * INI ADALAH SATU-SATUNYA METHOD YANG MENGUBAH STATUS RUANGAN
     */
    public function updateRuanganStatus(Request $request, $id)
    {
        try {
            // Cek apakah user login
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu'
                ], 401);
            }
            
            $ruangan = Ruangan::findOrFail($id);
            
            $validated = $request->validate([
                'status' => 'required|in:tersedia,dibooking,dipakai,maintenance',
                'keterangan' => 'nullable|string|max:500'
            ]);
            
            $oldStatus = $ruangan->status;
            $newStatus = $validated['status'];
            $noteText = $validated['keterangan'] ?? '';
            
            // Mapping status ke teks yang lebih mudah dibaca
            $statusText = [
                'tersedia' => 'Tersedia',
                'dibooking' => 'Dibooking',
                'dipakai' => 'Sedang Dipakai',
                'maintenance' => 'Maintenance'
            ];
            
            $oldStatusText = $statusText[$oldStatus] ?? $oldStatus;
            $newStatusText = $statusText[$newStatus] ?? $newStatus;
            
            // Update status ruangan
            $ruangan->status = $newStatus;
            $ruangan->save();
            
            // Format deskripsi yang lebih detail untuk LogActivity
            $deskripsiLog = "========================================\n";
            $deskripsiLog .= "🏢 UPDATE STATUS RUANGAN\n";
            $deskripsiLog .= "========================================\n";
            $deskripsiLog .= "🆔 ID Ruangan    : " . $ruangan->id . "\n";
            $deskripsiLog .= "📝 Kode Ruangan  : " . $ruangan->kode_ruangan . "\n";
            $deskripsiLog .= "🏠 Nama Ruangan  : " . $ruangan->nama_ruangan . "\n";
            $deskripsiLog .= "📍 Lokasi        : " . ($ruangan->lokasi ?? '-') . "\n";
            $deskripsiLog .= "📊 Kapasitas     : " . ($ruangan->kapasitas ?? '-') . " orang\n";
            $deskripsiLog .= "🔄 Status Lama   : " . $oldStatusText . "\n";
            $deskripsiLog .= "🆕 Status Baru   : " . $newStatusText . "\n";
            $deskripsiLog .= "👤 Diubah Oleh   : " . (auth()->user()->name ?? auth()->user()->username ?? 'System') . "\n";
            $deskripsiLog .= "👔 Role          : " . (auth()->user()->role ?? 'pegawai') . "\n";
            $deskripsiLog .= "⏰ Waktu Update  : " . Carbon::now()->format('d-m-Y H:i:s') . "\n";
            $deskripsiLog .= "🌐 IP Address    : " . $request->ip() . "\n";
            if (!empty($noteText)) {
                $deskripsiLog .= "📝 Keterangan    : " . $noteText . "\n";
            }
            $deskripsiLog .= "========================================";
            
            // Simpan ke LogActivity
            try {
                $log = LogActivity::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'update',
                    'aktivitas' => 'UPDATE STATUS RUANGAN',
                    'deskripsi' => $deskripsiLog,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                \Log::info('Log aktivitas update status ruangan berhasil disimpan', [
                    'log_id' => $log->id,
                    'ruangan_id' => $ruangan->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'user_id' => auth()->id()
                ]);
                
            } catch (\Exception $logError) {
                \Log::error('Gagal membuat log aktivitas: ' . $logError->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Status ruangan berhasil diperbarui',
                'data' => [
                    'id' => $ruangan->id, 
                    'status' => $ruangan->status,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating ruangan status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status ruangan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * UPDATE STATUS PEMINJAMAN (APPROVE/REJECT)
     * TIDAK MENGUBAH STATUS RUANGAN SECARA OTOMATIS
     */
    public function updatePeminjamanStatus(Request $request, $id)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda harus login terlebih dahulu'
                ], 401);
            }
            
            $peminjaman = PeminjamanRuangan::with('ruangan')->findOrFail($id);
            
            $validated = $request->validate([
                'status' => 'required|in:disetujui,ditolak,dibatalkan',
                'alasan' => 'nullable|string|max:500'
            ]);
            
            $oldStatus = $peminjaman->status;
            $newStatus = $validated['status'];
            $alasan = $validated['alasan'] ?? '';
            
            $peminjaman->status = $newStatus;
            
            if ($newStatus == 'ditolak' && !empty($alasan)) {
                $peminjaman->alasan_penolakan = $alasan;
            }
            $peminjaman->save();
            
            // ========== HAPUS PERUBAHAN STATUS RUANGAN OTOMATIS ==========
            // TIDAK ADA perubahan status ruangan di sini
            // Status ruangan hanya diubah manual oleh admin melalui updateRuanganStatus
            
            // Catat ke LogActivity
            try {
                $statusText = [
                    'disetujui' => 'Disetujui',
                    'ditolak' => 'Ditolak',
                    'dibatalkan' => 'Dibatalkan'
                ];
                
                $deskripsiLog = "========================================\n";
                $deskripsiLog .= "📋 UPDATE STATUS PEMINJAMAN\n";
                $deskripsiLog .= "========================================\n";
                $deskripsiLog .= "🆔 ID Peminjaman : " . $peminjaman->id . "\n";
                $deskripsiLog .= "📝 Acara         : " . $peminjaman->acara . "\n";
                $deskripsiLog .= "🏠 Ruangan       : " . ($peminjaman->ruangan->nama_ruangan ?? '-') . "\n";
                $deskripsiLog .= "👤 Pengaju       : " . ($peminjaman->nama_pengaju ?? '-') . "\n";
                $deskripsiLog .= "📅 Tanggal       : " . ($peminjaman->tanggal ?? '-') . "\n";
                $deskripsiLog .= "⏰ Jam           : " . ($peminjaman->jam_mulai ?? '-') . " - " . ($peminjaman->jam_selesai ?? '-') . "\n";
                $deskripsiLog .= "🔄 Status Lama   : " . ($oldStatus ?? '-') . "\n";
                $deskripsiLog .= "🆕 Status Baru   : " . ($statusText[$newStatus] ?? $newStatus) . "\n";
                if (!empty($alasan)) {
                    $deskripsiLog .= "📝 Alasan        : " . $alasan . "\n";
                }
                $deskripsiLog .= "👤 Diubah Oleh   : " . (auth()->user()->name ?? auth()->user()->username ?? 'System') . "\n";
                $deskripsiLog .= "⏰ Waktu Update  : " . Carbon::now()->format('d-m-Y H:i:s') . "\n";
                $deskripsiLog .= "========================================";
                
                LogActivity::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'update',
                    'aktivitas' => 'UPDATE STATUS PEMINJAMAN',
                    'deskripsi' => $deskripsiLog,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $logError) {
                \Log::error('Gagal membuat log: ' . $logError->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Status peminjaman berhasil diperbarui',
                'data' => ['id' => $peminjaman->id, 'status' => $peminjaman->status]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error updating peminjaman status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * CANCEL PEMINJAMAN
     * TIDAK MENGUBAH STATUS RUANGAN SECARA OTOMATIS
     */
    public function cancelPeminjaman(Request $request, $id)
    {
        try {
            if (!Auth::check()) {
                return redirect()->back()->with('error', 'Anda harus login terlebih dahulu');
            }
            
            $peminjaman = PeminjamanRuangan::with('ruangan')->findOrFail($id);
            
            if (!in_array($peminjaman->status, ['menunggu', 'disetujui'])) {
                return redirect()->back()->with('error', 'Peminjaman tidak dapat dibatalkan');
            }
            
            $now = Carbon::now();
            $tanggalPeminjaman = Carbon::parse($peminjaman->tanggal);
            $jamMulai = Carbon::parse($peminjaman->jam_mulai);
            
            if ($tanggalPeminjaman->isToday() && $now->gt($jamMulai)) {
                return redirect()->back()->with('error', 'Peminjaman sudah melewati waktu mulai, tidak dapat dibatalkan');
            }
            
            $oldStatus = $peminjaman->status;
            
            $peminjaman->status = 'dibatalkan';
            $peminjaman->save();
            
            // ========== HAPUS PERUBAHAN STATUS RUANGAN OTOMATIS ==========
            // TIDAK ADA perubahan status ruangan di sini
            
            // Catat ke LogActivity
            try {
                $deskripsiLog = "========================================\n";
                $deskripsiLog .= "❌ PEMBATALAN PEMINJAMAN\n";
                $deskripsiLog .= "========================================\n";
                $deskripsiLog .= "🆔 ID Peminjaman : " . $peminjaman->id . "\n";
                $deskripsiLog .= "📝 Acara         : " . $peminjaman->acara . "\n";
                $deskripsiLog .= "🏠 Ruangan       : " . ($peminjaman->ruangan->nama_ruangan ?? '-') . "\n";
                $deskripsiLog .= "👤 Pengaju       : " . ($peminjaman->nama_pengaju ?? '-') . "\n";
                $deskripsiLog .= "📅 Tanggal       : " . ($peminjaman->tanggal ?? '-') . "\n";
                $deskripsiLog .= "⏰ Jam           : " . ($peminjaman->jam_mulai ?? '-') . " - " . ($peminjaman->jam_selesai ?? '-') . "\n";
                $deskripsiLog .= "🔄 Status Awal   : " . ($oldStatus ?? '-') . "\n";
                $deskripsiLog .= "🆕 Status Akhir  : Dibatalkan\n";
                $deskripsiLog .= "👤 Dibatalkan Oleh: " . (auth()->user()->name ?? auth()->user()->username ?? 'System') . "\n";
                $deskripsiLog .= "⏰ Waktu Batal   : " . Carbon::now()->format('d-m-Y H:i:s') . "\n";
                $deskripsiLog .= "========================================";
                
                LogActivity::create([
                    'user_id' => auth()->id(),
                    'tipe' => 'update',
                    'aktivitas' => 'BATALKAN PEMINJAMAN',
                    'deskripsi' => $deskripsiLog,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $logError) {
                \Log::error('Gagal membuat log: ' . $logError->getMessage());
            }
            
            return redirect()->back()->with('success', 'Peminjaman berhasil dibatalkan');
            
        } catch (\Exception $e) {
            \Log::error('Error cancelling peminjaman: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membatalkan peminjaman: ' . $e->getMessage());
        }
    }
    
    /**
     * GET CATATAN - Untuk melihat riwayat catatan (READ ONLY)
     */
    public function getCatatan($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $peminjaman->id,
                    'acara' => $peminjaman->acara,
                    'catatan' => $peminjaman->catatan ?? '',
                    'has_catatan' => !empty($peminjaman->catatan) && trim($peminjaman->catatan) !== ''
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting catatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil catatan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function getStatusText($status)
    {
        $statusText = [
            'menunggu' => 'Menunggu Persetujuan',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'selesai' => 'Selesai',
            'berlangsung' => 'Berlangsung',
            'akan_datang' => 'Akan Datang',
            'dibatalkan' => 'Dibatalkan'
        ];
        
        return $statusText[$status] ?? $status;
    }
}