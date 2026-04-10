<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use App\Models\LogActivity; // TAMBAHKAN INI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // TAMBAHKAN INI

class RuanganController extends Controller
{
    /**
     * Menampilkan halaman utama dengan tabel ruangan
     */
    public function index(Request $request)
    {
        try {
            // Ambil parameter pencarian
            $search = $request->input('search');
            $status = $request->input('status');
            
            // Query dasar
            $query = Ruangan::query();
            
            // Filter pencarian
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('kode_ruangan', 'LIKE', "%{$search}%")
                      ->orWhere('nama_ruangan', 'LIKE', "%{$search}%")
                      ->orWhere('fasilitas', 'LIKE', "%{$search}%");
                });
            }
            
            // Filter status - SESUAIKAN DENGAN DATABASE
            $validStatuses = ['tersedia', 'dipakai', 'maintenance', 'dibooking'];
            if ($status && in_array($status, $validStatuses)) {
                $query->where('status', $status);
            }
            
            // Pagination
            $ruangan = $query->orderBy('created_at', 'desc')->paginate(10);
            
            // Hitung statistik
            $statistics = [
                'total' => Ruangan::count(),
                'tersedia' => Ruangan::where('status', 'tersedia')->count(),
                'dibooking' => Ruangan::where('status', 'dibooking')->count(),
                'dipakai' => Ruangan::where('status', 'dipakai')->count(),
                'maintenance' => Ruangan::where('status', 'maintenance')->count(),
            ];
            
            return view('admin.ruangan', compact('ruangan', 'statistics'));
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * API: Get detail ruangan untuk modal
     */
    public function show($id)
    {
        try {
            $ruangan = Ruangan::with('peminjaman')->findOrFail($id);
            
            // Format tanggal
            $ruangan->tanggal_dibuat = $ruangan->created_at ? $ruangan->created_at->format('d-m-Y H:i') : '-';
            $ruangan->tanggal_diupdate = $ruangan->updated_at ? $ruangan->updated_at->format('d-m-Y H:i') : '-';
            
            // Tambahkan data peminjaman aktif jika ada
            $peminjamanAktif = $ruangan->peminjaman()
                ->where('status', 'disetujui')
                ->where('status_real_time', '!=', 'selesai')
                ->orderBy('tanggal', 'asc')
                ->orderBy('jam_mulai', 'asc')
                ->get(['acara', 'tanggal', 'jam_mulai', 'jam_selesai', 'nama_pengaju']);
            
            $ruangan->peminjaman_aktif = $peminjamanAktif;
            
            return response()->json([
                'success' => true,
                'data' => $ruangan
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error show ruangan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ], 404);
        }
    }
    
    /**
     * API: Get statistics
     */
    public function getStatistics()
    {
        try {
            $statistics = [
                'total' => Ruangan::count(),
                'tersedia' => Ruangan::where('status', 'tersedia')->count(),
                'dibooking' => Ruangan::where('status', 'dibooking')->count(),
                'dipakai' => Ruangan::where('status', 'dipakai')->count(),
                'maintenance' => Ruangan::where('status', 'maintenance')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil statistik'
            ], 500);
        }
    }
    
    /**
     * API: Check ketersediaan kode ruangan
     */
    public function checkKode(Request $request)
    {
        try {
            $kode = $request->input('kode');
            $id = $request->input('id'); // Untuk edit mode
            
            $query = Ruangan::where('kode_ruangan', $kode);
            
            // Jika dalam mode edit, exclude ruangan dengan ID ini
            if ($id) {
                $query->where('id', '!=', $id);
            }
            
            $exists = $query->exists();
            
            return response()->json([
                'success' => true,
                'available' => !$exists
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa kode ruangan'
            ], 500);
        }
    }
    
    /**
     * API: Check status ruangan sebelum dihapus
     */
    public function checkStatus($id)
    {
        try {
            $ruangan = Ruangan::with('peminjaman')->findOrFail($id);
            
            // Log untuk debugging
            Log::info('Check status for ruangan ID: ' . $id, [
                'status' => $ruangan->status,
                'total_peminjaman' => $ruangan->peminjaman->count()
            ]);
            
            // Cek apakah ruangan memiliki peminjaman aktif
            $hasActivePeminjaman = $ruangan->peminjaman()
                ->whereIn('status', ['disetujui', 'menunggu'])
                ->where(function($q) {
                    $q->where('status_real_time', '!=', 'selesai')
                      ->orWhereNull('status_real_time');
                })
                ->exists();
            
            // Status yang tidak boleh dihapus
            $statusTidakBolehHapus = ['dibooking', 'dipakai', 'maintenance'];
            $statusBolehHapus = !in_array($ruangan->status, $statusTidakBolehHapus) && !$hasActivePeminjaman;
            
            $statusLabel = [
                'tersedia' => 'Tersedia',
                'dibooking' => 'Dibooking',
                'dipakai' => 'Dipakai',
                'maintenance' => 'Maintenance'
            ];
            
            $message = '';
            if (!$statusBolehHapus) {
                if ($hasActivePeminjaman) {
                    $message = 'Ruangan tidak dapat dihapus karena masih memiliki peminjaman aktif';
                } else {
                    $message = 'Ruangan tidak dapat dihapus karena statusnya ' . ($statusLabel[$ruangan->status] ?? $ruangan->status);
                }
            }
            
            return response()->json([
                'success' => true,
                'status' => $ruangan->status,
                'status_label' => $statusLabel[$ruangan->status] ?? $ruangan->status,
                'boleh_hapus' => $statusBolehHapus,
                'has_active_peminjaman' => $hasActivePeminjaman,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checkStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status ruangan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Store new ruangan - DENGAN LOG ACTIVITY
     */
    public function store(Request $request)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'kode_ruangan' => 'required|unique:ruangan,kode_ruangan|max:20',
            'nama_ruangan' => 'required|max:100',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string',
            'lokasi' => 'nullable|string|max:255',
            'status' => 'required|in:tersedia,dipakai,maintenance,dibooking',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $data = $request->all();
            
            // Handle image upload
            if ($request->hasFile('gambar')) {
                $imagePath = $request->file('gambar')->store('ruangan', 'public');
                $data['gambar'] = $imagePath;
            } else {
                $data['gambar'] = null;
            }
            
            $ruangan = Ruangan::create($data);
            
            Log::info('Ruangan created: ' . $ruangan->kode_ruangan);
            
            // 🔥 LOG ACTIVITY: Tambah Ruangan
            try {
                LogActivity::create([
                    'user_id' => Auth::id(),
                    'tipe' => 'create',
                    'aktivitas' => 'Tambah Ruangan Baru',
                    'deskripsi' => 'Admin menambahkan ruangan: ' . $ruangan->nama_ruangan . ' (' . $ruangan->kode_ruangan . ')',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $logError) {
                Log::error('Gagal membuat log ruangan: ' . $logError->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil ditambahkan!',
                'data' => $ruangan
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error store ruangan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan ruangan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Update ruangan - DENGAN LOG ACTIVITY
     */
    public function update(Request $request, $id)
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'kode_ruangan' => 'required|unique:ruangan,kode_ruangan,' . $id . '|max:20',
            'nama_ruangan' => 'required|max:100',
            'kapasitas' => 'required|integer|min:1',
            'fasilitas' => 'nullable|string',
            'lokasi' => 'nullable|string|max:255',
            'status' => 'required|in:tersedia,dipakai,maintenance,dibooking',
            'keterangan' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $ruangan = Ruangan::findOrFail($id);
            
            // Simpan data lama untuk log
            $oldData = [
                'nama_ruangan' => $ruangan->nama_ruangan,
                'kode_ruangan' => $ruangan->kode_ruangan,
                'status' => $ruangan->status,
                'kapasitas' => $ruangan->kapasitas,
                'lokasi' => $ruangan->lokasi,
            ];
            
            $data = $request->except(['_method', '_token']);
            
            // Handle image upload
            if ($request->hasFile('gambar')) {
                // Delete old image if exists
                if ($ruangan->gambar && Storage::disk('public')->exists($ruangan->gambar)) {
                    Storage::disk('public')->delete($ruangan->gambar);
                }
                
                $imagePath = $request->file('gambar')->store('ruangan', 'public');
                $data['gambar'] = $imagePath;
            }
            
            $ruangan->update($data);
            
            Log::info('Ruangan updated: ' . $ruangan->kode_ruangan);
            
            // 🔥 LOG ACTIVITY: Update Ruangan
            try {
                $changes = [];
                if ($oldData['nama_ruangan'] != $ruangan->nama_ruangan) $changes[] = 'nama';
                if ($oldData['kode_ruangan'] != $ruangan->kode_ruangan) $changes[] = 'kode';
                if ($oldData['status'] != $ruangan->status) $changes[] = 'status';
                if ($oldData['kapasitas'] != $ruangan->kapasitas) $changes[] = 'kapasitas';
                if ($oldData['lokasi'] != $ruangan->lokasi) $changes[] = 'lokasi';
                
                $changesText = !empty($changes) ? ' (mengubah ' . implode(', ', $changes) . ')' : '';
                
                LogActivity::create([
                    'user_id' => Auth::id(),
                    'tipe' => 'update',
                    'aktivitas' => 'Update Data Ruangan',
                    'deskripsi' => 'Admin mengupdate ruangan: ' . $ruangan->nama_ruangan . $changesText,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $logError) {
                Log::error('Gagal membuat log ruangan: ' . $logError->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Ruangan berhasil diperbarui!',
                'data' => $ruangan
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error update ruangan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui ruangan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Delete ruangan - DENGAN LOG ACTIVITY
     */
    public function destroy($id)
    {
        try {
            $ruangan = Ruangan::with('peminjaman')->findOrFail($id);
            
            // Log untuk debugging
            Log::info('Attempting to delete ruangan ID: ' . $id, [
                'status' => $ruangan->status,
                'total_peminjaman' => $ruangan->peminjaman->count()
            ]);
            
            // Cek apakah ruangan memiliki peminjaman dengan status tertentu
            $activePeminjaman = $ruangan->peminjaman()
                ->whereIn('status', ['disetujui', 'menunggu'])
                ->where(function($q) {
                    $q->where('status_real_time', '!=', 'selesai')
                      ->orWhereNull('status_real_time');
                })
                ->exists();
            
            // Status yang tidak boleh dihapus
            $statusTidakBolehHapus = ['dibooking', 'dipakai', 'maintenance'];
            
            if (in_array($ruangan->status, $statusTidakBolehHapus)) {
                $statusLabel = [
                    'dibooking' => 'Dibooking',
                    'dipakai' => 'Dipakai',
                    'maintenance' => 'Maintenance'
                ];
                
                return response()->json([
                    'success' => false,
                    'message' => "Ruangan tidak dapat dihapus karena statusnya {$statusLabel[$ruangan->status]}"
                ], 400);
            }
            
            if ($activePeminjaman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus ruangan yang masih memiliki peminjaman aktif!'
                ], 400);
            }
            
            // Cek apakah ada peminjaman yang sudah selesai (boleh dihapus, tapi beri warning)
            $hasSelesaiPeminjaman = $ruangan->peminjaman()
                ->where('status', 'selesai')
                ->orWhere('status_real_time', 'selesai')
                ->exists();
            
            // Simpan data untuk log sebelum dihapus
            $namaRuangan = $ruangan->nama_ruangan;
            $kodeRuangan = $ruangan->kode_ruangan;
            
            // Delete image if exists
            if ($ruangan->gambar && Storage::disk('public')->exists($ruangan->gambar)) {
                Storage::disk('public')->delete($ruangan->gambar);
            }
            
            // Hapus semua peminjaman terkait (jika ada)
            if ($ruangan->peminjaman()->count() > 0) {
                $ruangan->peminjaman()->delete();
            }
            
            // Hapus ruangan
            $ruangan->delete();
            
            // 🔥 LOG ACTIVITY: Hapus Ruangan
            try {
                LogActivity::create([
                    'user_id' => Auth::id(),
                    'tipe' => 'delete',
                    'aktivitas' => 'Hapus Ruangan',
                    'deskripsi' => 'Admin menghapus ruangan: ' . $namaRuangan . ' (' . $kodeRuangan . ')',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);
            } catch (\Exception $logError) {
                Log::error('Gagal membuat log ruangan: ' . $logError->getMessage());
            }
            
            $message = 'Ruangan berhasil dihapus!';
            if ($hasSelesaiPeminjaman) {
                $message = 'Ruangan dan riwayat peminjaman berhasil dihapus!';
            }
            
            Log::info('Ruangan deleted: ' . $id);
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Ruangan not found: ' . $id);
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error delete ruangan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus ruangan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API: Update status ruangan secara manual - DENGAN LOG ACTIVITY
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:tersedia,dipakai,maintenance,dibooking'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $ruangan = Ruangan::findOrFail($id);
            
            $oldStatus = $ruangan->status;
            
            // Jika mengubah status dari dibooking ke tersedia, pastikan tidak ada peminjaman aktif
            if ($ruangan->status == 'dibooking' && $request->status == 'tersedia') {
                $hasActivePeminjaman = $ruangan->peminjaman()
                    ->where('status', 'disetujui')
                    ->where('status_real_time', '!=', 'selesai')
                    ->exists();
                    
                if ($hasActivePeminjaman) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak dapat mengubah status menjadi tersedia karena masih ada peminjaman aktif!'
                    ], 400);
                }
            }
            
            $ruangan->status = $request->status;
            $ruangan->save();
            
            Log::info('Status ruangan updated: ' . $ruangan->kode_ruangan . ' to ' . $request->status);
            
            // 🔥 LOG ACTIVITY: Update Status Ruangan
            try {
                $statusLabel = [
                    'tersedia' => 'Tersedia',
                    'dipakai' => 'Dipakai',
                    'maintenance' => 'Maintenance',
                    'dibooking' => 'Dibooking'
                ];
                
                $oldLabel = $statusLabel[$oldStatus] ?? $oldStatus;
                $newLabel = $statusLabel[$request->status] ?? $request->status;
                
                LogActivity::create([
                    'user_id' => Auth::id(),
                    'tipe' => 'update',
                    'aktivitas' => 'Update Status Ruangan',
                    'deskripsi' => 'Admin mengubah status ruangan ' . $ruangan->nama_ruangan . ' dari ' . $oldLabel . ' menjadi ' . $newLabel,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            } catch (\Exception $logError) {
                Log::error('Gagal membuat log status ruangan: ' . $logError->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Status ruangan berhasil diperbarui!',
                'data' => $ruangan
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error update status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }
}