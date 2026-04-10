<?php

namespace App\Http\Controllers;

use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PeminjamanRuanganController extends Controller
{
    // User Functions
    public function create()
    {
        // Tampilkan semua ruangan yang tersedia
        $ruangan = Ruangan::all(); // Tampilkan semua, tidak perlu filter status
        $user = Auth::user();
        
        // Log: User mengakses halaman peminjaman
        $this->logActivity('access', 'Mengakses halaman peminjaman ruangan', 'User mengakses form peminjaman ruangan baru');
        
        return view('user.peminjaman-ruangan', compact('ruangan', 'user'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'jenis_pengaju' => 'required|in:mahasiswa,dosen,staff,tamu',
                'nama_pengaju' => 'required|string|max:255',
                'nim_nip' => 'required|string|max:50',
                'fakultas' => 'required|string|max:100',
                'prodi' => 'nullable|string|max:100',
                'email' => 'required|email|max:255',
                'no_telepon' => 'required|string|max:20',
                'ruangan_id' => 'required|exists:ruangan,id',
                'acara' => 'required|string|max:255',
                'tanggal_mulai' => 'required|date|after_or_equal:today',
                'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                'jam_mulai' => 'required|date_format:H:i',
                'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
                'jumlah_peserta' => 'required|integer|min:1',
                'keterangan' => 'nullable|string',
                'lampiran_surat' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048'
            ]);

            $ruangan = Ruangan::find($request->ruangan_id);
            
            // CEK 1: Kapasitas
            if ($request->jumlah_peserta > $ruangan->kapasitas) {
                // Log: Validasi gagal - kapasitas
                $this->logActivity('validation_failed', 'Validasi kapasitas gagal', 
                    "User mencoba meminjam ruangan {$ruangan->nama_ruangan} dengan {$request->jumlah_peserta} peserta, kapasitas maks: {$ruangan->kapasitas}");
                
                return back()->withErrors([
                    'jumlah_peserta' => 'Jumlah peserta melebihi kapasitas ruangan. Kapasitas maksimal: ' . $ruangan->kapasitas
                ])->withInput();
            }
            
            // CEK 2: Ketersediaan tanggal dan jam dengan jeda 1 jam
            $isAvailable = $this->checkRuanganAvailability(
                $request->ruangan_id,
                $request->tanggal_mulai,
                $request->tanggal_selesai,
                $request->jam_mulai,
                $request->jam_selesai
            );
            
            if (!$isAvailable['available']) {
                // Log: Validasi gagal - ruangan tidak tersedia
                $this->logActivity('validation_failed', 'Validasi ketersediaan gagal', 
                    "User mencoba meminjam ruangan {$ruangan->nama_ruangan} pada {$request->tanggal_mulai} {$request->jam_mulai}-{$request->jam_selesai}: {$isAvailable['message']}");
                
                return back()->withErrors([
                    'ruangan_id' => $isAvailable['message']
                ])->withInput();
            }

            // Generate nama hari
            $hari = $this->getHariIndonesia($request->tanggal_mulai);
            
            if ($request->tanggal_mulai != $request->tanggal_selesai) {
                $hariSelesai = $this->getHariIndonesia($request->tanggal_selesai);
                $hari = $hari . ' - ' . $hariSelesai;
            }

            // Buat data peminjaman
            $peminjamanData = [
                'user_id' => Auth::id(),
                'jenis_pengaju' => $request->jenis_pengaju,
                'nama_pengaju' => $request->nama_pengaju,
                'nim_nip' => $request->nim_nip,
                'fakultas' => $request->fakultas,
                'prodi' => $request->prodi ?? '',
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'ruangan_id' => $request->ruangan_id,
                'acara' => $request->acara,
                'hari' => $hari,
                'tanggal' => $request->tanggal_mulai,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'jam_mulai' => $request->jam_mulai . ':00',
                'jam_selesai' => $request->jam_selesai . ':00',
                'jumlah_peserta' => $request->jumlah_peserta,
                'keterangan' => $request->keterangan,
                'status' => 'menunggu',
                'status_real_time' => 'akan_datang'
            ];

            // Upload lampiran
            if ($request->hasFile('lampiran_surat')) {
                $filename = time() . '_' . $request->file('lampiran_surat')->getClientOriginalName();
                $path = $request->file('lampiran_surat')->storeAs('lampiran_surat', $filename, 'public');
                $peminjamanData['lampiran_surat'] = $path;
            }

            $peminjaman = PeminjamanRuangan::create($peminjamanData);
            
            // Log: User berhasil mengajukan peminjaman
            $this->logActivity('create', 'Mengajukan peminjaman ruangan baru',
                "User mengajukan peminjaman ruangan {$ruangan->nama_ruangan} untuk acara '{$request->acara}' " .
                "pada {$request->tanggal_mulai} {$request->jam_mulai}-{$request->jam_selesai}, " .
                "jumlah peserta: {$request->jumlah_peserta}");
            
            return redirect()->route('user.peminjaman-ruangan.riwayat')
                ->with('success', 'Peminjaman ruangan berhasil diajukan! Tunggu konfirmasi dari pegawai.');

        } catch (\Exception $e) {
            Log::error('Error saving peminjaman: ' . $e->getMessage());
            
            // Log: Error saat menyimpan peminjaman
            $this->logActivity('error', 'Gagal mengajukan peminjaman', 
                "Terjadi error saat user mengajukan peminjaman: " . $e->getMessage());
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Cek ketersediaan ruangan dengan JEDA 1 JAM antara peminjaman
     */
    private function checkRuanganAvailability($ruanganId, $tanggalMulai, $tanggalSelesai, $jamMulai, $jamSelesai, $excludePeminjamanId = null)
    {
        $startDate = Carbon::parse($tanggalMulai);
        $endDate = Carbon::parse($tanggalSelesai);
        
        // Konversi waktu request ke Carbon
        $requestStartTime = Carbon::createFromTimeString($jamMulai);
        $requestEndTime = Carbon::createFromTimeString($jamSelesai);
        
        Log::info('Checking availability with 1-hour buffer - Room: ' . $ruanganId . 
                 ', Request: ' . $startDate->format('Y-m-d') . ' ' . $jamMulai . '-' . $jamSelesai);
        
        // Validasi durasi minimal
        $duration = $requestEndTime->diffInMinutes($requestStartTime);
        if ($duration < 15) {
            return [
                'available' => false,
                'message' => 'Durasi peminjaman minimal 15 menit'
            ];
        }
        
        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $currentDate = $date->format('Y-m-d');
            
            // Cari semua peminjaman yang sudah disetujui untuk tanggal tersebut
            $existingPeminjaman = PeminjamanRuangan::where('ruangan_id', $ruanganId)
                ->where('status', 'disetujui')
                ->where(function($query) use ($date) {
                    $query->whereDate('tanggal_mulai', '<=', $date)
                          ->whereDate('tanggal_selesai', '>=', $date);
                });
            
            if ($excludePeminjamanId) {
                $existingPeminjaman->where('id', '!=', $excludePeminjamanId);
            }
            
            $bookings = $existingPeminjaman->get();
            
            foreach ($bookings as $booking) {
                $bookingStart = Carbon::parse($booking->jam_mulai);
                $bookingEnd = Carbon::parse($booking->jam_selesai);
                
                // LOGIKA UTAMA DENGAN JEDA 1 JAM:
                // ================================
                
                // 1. Cek OVERLAP langsung (tanpa jeda)
                // Peminjaman baru tidak boleh overlap dengan peminjaman lama
                $isOverlap = $requestStartTime->lessThan($bookingEnd) && 
                            $requestEndTime->greaterThan($bookingStart);
                
                if ($isOverlap) {
                    return $this->createConflictResponse($booking, 'Jadwal bertabrakan dengan peminjaman yang sudah ada');
                }
                
                // 2. Cek JEDA SEBELUM peminjaman lama
                // Peminjaman baru TIDAK BOLEH berakhir kurang dari 1 jam sebelum peminjaman lama dimulai
                // Contoh: Peminjaman lama 10:00-12:00
                //         Peminjaman baru 9:00-9:59 → ❌ TIDAK BOLEH (kurang dari 1 jam jeda)
                //         Peminjaman baru 9:00-9:00 → ✅ BOLEH (tepat 1 jam jeda)
                $bufferBefore = $bookingStart->copy()->subHour(); // 1 jam sebelum bookingStart
                if ($requestEndTime->greaterThan($bufferBefore) && $requestEndTime->lessThanOrEqualTo($bookingStart)) {
                    return $this->createConflictResponse($booking, 
                        "Harus ada jeda minimal 1 jam sebelum peminjaman berikutnya. " .
                        "Peminjaman berikutnya dimulai jam " . $bookingStart->format('H:i')
                    );
                }
                
                // 3. Cek JEDA SETELAH peminjaman lama
                // Peminjaman baru TIDAK BOLEH dimulai kurang dari 1 jam setelah peminjaman lama selesai
                // Contoh: Peminjaman lama 9:20-10:00
                //         Peminjaman baru 10:01-11:00 → ❌ TIDAK BOLEH (kurang dari 1 jam jeda)
                //         Peminjaman baru 11:00-12:00 → ✅ BOLEH (tepat 1 jam jeda)
                $bufferAfter = $bookingEnd->copy()->addHour(); // 1 jam setelah bookingEnd
                if ($requestStartTime->greaterThanOrEqualTo($bookingEnd) && $requestStartTime->lessThan($bufferAfter)) {
                    return $this->createConflictResponse($booking,
                        "Harus ada jeda minimal 1 jam setelah peminjaman sebelumnya. " .
                        "Peminjaman sebelumnya selesai jam " . $bookingEnd->format('H:i')
                    );
                }
            }
        }
        
        // Jika tidak ada konflik
        Log::info('Room ' . $ruanganId . ' is available for the requested time (with 1-hour buffer)');
        return ['available' => true, 'message' => 'Ruangan tersedia'];
    }

    /**
     * Helper method untuk membuat response konflik
     */
    private function createConflictResponse($booking, $additionalMessage = '')
    {
        $conflictStartDate = Carbon::parse($booking->tanggal_mulai)->format('d/m/Y');
        $conflictEndDate = Carbon::parse($booking->tanggal_selesai)->format('d/m/Y');
        
        // Format waktu
        $conflictJamMulai = date('H:i', strtotime($booking->jam_mulai));
        $conflictJamSelesai = date('H:i', strtotime($booking->jam_selesai));
        
        // Tentukan rentang tanggal untuk pesan
        $dateRange = $conflictStartDate;
        if ($conflictStartDate != $conflictEndDate) {
            $dateRange = $conflictStartDate . ' - ' . $conflictEndDate;
        }
        
        $message = "Ruangan sudah dipesan pada tanggal {$dateRange} " .
                  "jam {$conflictJamMulai} - {$conflictJamSelesai} " .
                  "untuk acara: '{$booking->acara}'";
        
        if ($additionalMessage) {
            $message .= ". " . $additionalMessage;
        }
        
        Log::warning('Conflict detected: ' . $message);
        
        return [
            'available' => false,
            'message' => $message
        ];
    }

    /**
     * API untuk real-time availability check dari frontend (PUBLIC)
     */
    public function checkAvailability(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'ruangan_id' => 'required|exists:ruangan,id',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date',
                'jam_mulai' => 'required|date_format:H:i',
                'jam_selesai' => 'required|date_format:H:i'
            ]);
            
            Log::info('Availability check request:', $request->all());
            
            // Validasi tambahan
            if ($request->tanggal_selesai < $request->tanggal_mulai) {
                return response()->json([
                    'available' => false,
                    'message' => 'Tanggal selesai tidak boleh kurang dari tanggal mulai'
                ]);
            }
            
            if ($request->jam_selesai <= $request->jam_mulai) {
                return response()->json([
                    'available' => false,
                    'message' => 'Jam selesai harus setelah jam mulai'
                ]);
            }
            
            // Validasi durasi minimal 15 menit
            $startTime = Carbon::createFromTimeString($request->jam_mulai);
            $endTime = Carbon::createFromTimeString($request->jam_selesai);
            $duration = $endTime->diffInMinutes($startTime);
            
            if ($duration < 15) {
                return response()->json([
                    'available' => false,
                    'message' => 'Durasi peminjaman minimal 15 menit'
                ]);
            }
            
            // Cek ketersediaan ruangan dengan jeda 1 jam
            $availability = $this->checkRuanganAvailability(
                $request->ruangan_id,
                $request->tanggal_mulai,
                $request->tanggal_selesai,
                $request->jam_mulai,
                $request->jam_selesai
            );
            
            Log::info('Availability check result:', $availability);
            
            return response()->json($availability);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in availability check: ' . $e->getMessage());
            return response()->json([
                'available' => false,
                'message' => 'Data yang dimasukkan tidak valid: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error checking availability: ' . $e->getMessage());
            return response()->json([
                'available' => false,
                'message' => 'Terjadi kesalahan saat memeriksa ketersediaan. Silakan coba lagi.'
            ], 500);
        }
    }

    private function getHariIndonesia($dateString)
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $date = Carbon::parse($dateString);
        return $days[$date->dayOfWeek];
    }

    /**
     * PERBAIKAN: Menampilkan riwayat peminjaman user dengan statistik
     * Status selesai dihitung dari status='selesai' ATAU (status='disetujui' DAN status_real_time='selesai')
     */
    public function riwayat(Request $request)
    {
        $userId = Auth::id();
        
        // Log untuk debugging
        Log::info('User ID: ' . $userId . ' mengakses halaman riwayat peminjaman');
        
        // Query dasar untuk peminjaman user
        $query = PeminjamanRuangan::where('user_id', $userId)
            ->with('ruangan')
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status jika ada
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan bulan jika ada
        if ($request->has('bulan') && $request->bulan != '') {
            $bulan = explode('-', $request->bulan);
            if (count($bulan) == 2) {
                $query->whereYear('created_at', $bulan[0])
                      ->whereMonth('created_at', $bulan[1]);
            }
        }

        // Ambil data dengan pagination
        $peminjamanRuangan = $query->paginate(10)->withQueryString();
        
        // Ambil semua data user untuk perhitungan statistik (tanpa filter)
        $allUserBookings = PeminjamanRuangan::where('user_id', $userId)->get();
        
        // HITUNG STATISTIK UNTUK USER DENGAN LOGIKA YANG BENAR UNTUK STATUS SELESAI
        $stats = [
            'total' => $allUserBookings->count(),
            'menunggu' => $allUserBookings->where('status', 'menunggu')->count(),
            'disetujui' => $allUserBookings->where('status', 'disetujui')->count(),
            'ditolak' => $allUserBookings->where('status', 'ditolak')->count(),
            'selesai' => $allUserBookings->filter(function($item) {
                // Status selesai jika:
                // 1. Status = 'selesai' (langsung)
                // 2. ATAU Status = 'disetujui' DAN status_real_time = 'selesai'
                return $item->status === 'selesai' || 
                       ($item->status === 'disetujui' && $item->status_real_time === 'selesai');
            })->count(),
            'dibatalkan' => $allUserBookings->where('status', 'dibatalkan')->count(),
        ];
        
        // Log statistik untuk debugging
        Log::info('Stats untuk user ' . $userId . ': ', $stats);
        
        // Log detail untuk status selesai
        $selesaiItems = $allUserBookings->filter(function($item) {
            return $item->status === 'selesai' || 
                   ($item->status === 'disetujui' && $item->status_real_time === 'selesai');
        });
        
        Log::info('Detail status selesai: ' . $selesaiItems->count() . ' item');
        foreach ($selesaiItems as $item) {
            Log::info('  - ID: ' . $item->id . ', Status: ' . $item->status . ', Real-time: ' . ($item->status_real_time ?? 'null') . ', Acara: ' . $item->acara);
        }
        
        // Log: User melihat riwayat peminjaman
        $this->logActivity('view', 'Melihat riwayat peminjaman', 'User mengakses halaman riwayat peminjaman ruangan');
        
        // Kembalikan view dengan data yang lengkap
        return view('user.riwayat', compact('peminjamanRuangan', 'stats'));
    }

    public function detailUser($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::with(['user', 'ruangan'])
                ->where('user_id', Auth::id())
                ->find($id);

            if (!$peminjaman) {
                // Log: Detail peminjaman tidak ditemukan
                $this->logActivity('error', 'Detail peminjaman tidak ditemukan', 
                    "User mencoba melihat detail peminjaman ID: {$id} tetapi data tidak ditemukan");
                
                return response()->json([
                    'success' => false,
                    'message' => 'Data peminjaman ruangan tidak ditemukan'
                ], 404);
            }

            $html = view('user.peminjaman-ruangan._detail-modal', compact('peminjaman'))->render();
            
            // Log: User melihat detail peminjaman
            $this->logActivity('view', 'Melihat detail peminjaman', 
                "User melihat detail peminjaman ID: {$id} untuk acara: {$peminjaman->acara}");
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error in detailUser: ' . $e->getMessage());
            
            // Log: Error saat melihat detail
            $this->logActivity('error', 'Error melihat detail peminjaman', 
                "Terjadi error saat user melihat detail peminjaman ID: {$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }
    
    public function cancelUser($id)
    {
        $peminjaman = PeminjamanRuangan::where('user_id', Auth::id())->findOrFail($id);
        
        if (!in_array($peminjaman->status, ['menunggu', 'disetujui'])) {
            // Log: Cancel gagal karena status tidak sesuai
            $this->logActivity('cancel_failed', 'Gagal membatalkan peminjaman', 
                "User mencoba membatalkan peminjaman ID: {$id} dengan status '{$peminjaman->status}' (status tidak diizinkan)");
            
            return back()->with('error', 'Tidak dapat membatalkan peminjaman dengan status ini.');
        }

        $peminjaman->status = 'dibatalkan';
        $peminjaman->save();

        // Log: User berhasil membatalkan peminjaman
        $this->logActivity('cancel', 'Membatalkan peminjaman', 
            "User membatalkan peminjaman ID: {$id} untuk acara: {$peminjaman->acara}, ruangan: {$peminjaman->ruangan->nama_ruangan}");
        
        return back()->with('success', 'Peminjaman ruangan berhasil dibatalkan.');
    }

    // ================ PEGAWAI METHODS ================
    
    public function indexPegawai(Request $request)
    {
        try {
            $query = PeminjamanRuangan::with(['user', 'ruangan'])
                ->orderBy('created_at', 'desc');

            // Filter berdasarkan status
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan ruangan
            if ($request->has('ruangan_id') && $request->ruangan_id != '') {
                $query->where('ruangan_id', $request->ruangan_id);
            }

            // Filter berdasarkan tanggal mulai
            if ($request->has('tanggal_mulai') && $request->tanggal_mulai != '') {
                $query->where('tanggal_mulai', '>=', $request->tanggal_mulai);
            }

            // Filter berdasarkan tanggal selesai
            if ($request->has('tanggal_selesai') && $request->tanggal_selesai != '') {
                $query->where('tanggal_selesai', '<=', $request->tanggal_selesai);
            }

            // Filter berdasarkan jenis pengaju
            if ($request->has('jenis_pengaju') && $request->jenis_pengaju != '') {
                $query->where('jenis_pengaju', $request->jenis_pengaju);
            }

            // Filter berdasarkan fakultas
            if ($request->has('fakultas') && $request->fakultas != '') {
                $query->where('fakultas', 'like', '%' . $request->fakultas . '%');
            }

            // Filter berdasarkan nama pengaju
            if ($request->has('nama_pengaju') && $request->nama_pengaju != '') {
                $query->where('nama_pengaju', 'like', '%' . $request->nama_pengaju . '%');
            }

            $peminjaman = $query->get();
            $ruangan = Ruangan::all();

            // PERBAIKAN: Hitung statistik dengan logika yang benar untuk status selesai
            $allPeminjaman = PeminjamanRuangan::all();
            
            $stats = [
                'total' => $allPeminjaman->count(),
                'menunggu' => $allPeminjaman->where('status', 'menunggu')->count(),
                'disetujui' => $allPeminjaman->where('status', 'disetujui')->count(),
                'ditolak' => $allPeminjaman->where('status', 'ditolak')->count(),
                'selesai' => $allPeminjaman->filter(function($item) {
                    return $item->status === 'selesai' || 
                           ($item->status === 'disetujui' && $item->status_real_time === 'selesai');
                })->count(),
                'dibatalkan' => $allPeminjaman->where('status', 'dibatalkan')->count(),
            ];

            // Log: Pegawai mengakses daftar peminjaman
            $this->logActivity('access', 'Pegawai mengakses daftar peminjaman', 
                "Pegawai mengakses halaman manajemen peminjaman ruangan dengan filter: " . json_encode($request->all()));
            
            return view('pegawai.peminjaman-ruangan', compact('peminjaman', 'ruangan', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error in indexPegawai: ' . $e->getMessage());
            
            // Log: Error saat mengakses daftar peminjaman
            $this->logActivity('error', 'Error mengakses daftar peminjaman', 
                "Terjadi error saat pegawai mengakses daftar peminjaman: " . $e->getMessage());
            
            return back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    public function detailPegawai($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::with(['user', 'ruangan'])->find($id);

            if (!$peminjaman) {
                // Log: Detail peminjaman tidak ditemukan oleh pegawai
                $this->logActivity('error', 'Detail peminjaman tidak ditemukan (pegawai)', 
                    "Pegawai mencoba melihat detail peminjaman ID: {$id} tetapi data tidak ditemukan");
                
                return response()->json([
                    'success' => false,
                    'message' => 'Data peminjaman ruangan tidak ditemukan'
                ], 404);
            }
            
            $html = view('pegawai.peminjaman-ruangan._detail-modal', compact('peminjaman'))->render();
            
            // Log: Pegawai melihat detail peminjaman
            $this->logActivity('view', 'Pegawai melihat detail peminjaman', 
                "Pegawai melihat detail peminjaman ID: {$id}, pengaju: {$peminjaman->nama_pengaju}, acara: {$peminjaman->acara}");
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error in detailPegawai: ' . $e->getMessage());
            
            // Log: Error saat pegawai melihat detail
            $this->logActivity('error', 'Error pegawai melihat detail', 
                "Terjadi error saat pegawai melihat detail peminjaman ID: {$id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    // ================ LOGIC UTAMA: APPROVE ================

    public function approvePegawai($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::with('ruangan')->findOrFail($id);
            $user = Auth::user();
            
            // Pastikan status masih menunggu
            if ($peminjaman->status != 'menunggu') {
                // Log: Approve gagal karena status tidak menunggu
                $this->logActivity('approve_failed', 'Gagal menyetujui peminjaman', 
                    "Pegawai {$user->name} mencoba menyetujui peminjaman ID: {$id} tetapi status sudah '{$peminjaman->status}'");
                
                return back()->with('error', 'Peminjaman ini sudah diproses sebelumnya.');
            }

            // Cek ulang ketersediaan ruangan dengan jeda 1 jam
            $availabilityCheck = $this->checkRuanganAvailability(
                $peminjaman->ruangan_id,
                $peminjaman->tanggal_mulai,
                $peminjaman->tanggal_selesai,
                $peminjaman->jam_mulai,
                $peminjaman->jam_selesai,
                $peminjaman->id
            );
            
            if (!$availabilityCheck['available']) {
                // Log: Approve gagal karena ruangan tidak tersedia
                $this->logActivity('approve_failed', 'Gagal menyetujui - konflik jadwal', 
                    "Pegawai {$user->name} gagal menyetujui peminjaman ID: {$id} karena konflik jadwal: {$availabilityCheck['message']}");
                
                return back()->with('error', 'Tidak dapat menyetujui: ' . $availabilityCheck['message']);
            }

            // Cek kapasitas
            $ruangan = Ruangan::find($peminjaman->ruangan_id);
            if ($peminjaman->jumlah_peserta > $ruangan->kapasitas) {
                // Log: Approve gagal karena kapasitas
                $this->logActivity('approve_failed', 'Gagal menyetujui - kapasitas', 
                    "Pegawai {$user->name} gagal menyetujui peminjaman ID: {$id} karena kapasitas ({$peminjaman->jumlah_peserta} > {$ruangan->kapasitas})");
                
                return back()->with('error', 'Jumlah peserta melebihi kapasitas ruangan. Kapasitas maksimal: ' . $ruangan->kapasitas);
            }

            // Update status peminjaman menjadi disetujui
            $peminjaman->status = 'disetujui';
            $peminjaman->status_real_time = 'akan_datang';
            $peminjaman->save();

            // Log: Pegawai berhasil menyetujui peminjaman
            $this->logActivity('approve', 'Menyetujui peminjaman ruangan',
                "Pegawai {$user->name} menyetujui peminjaman ID: {$id}, " .
                "Pengaju: {$peminjaman->nama_pengaju}, " .
                "Ruangan: {$ruangan->nama_ruangan}, " .
                "Acara: {$peminjaman->acara}, " .
                "Tanggal: {$peminjaman->tanggal_mulai} {$peminjaman->jam_mulai}-{$peminjaman->jam_selesai}");

            Log::info('Peminjaman ID: ' . $peminjaman->id . ' disetujui oleh pegawai ' . $user->name);

            return back()->with('success', 'Peminjaman ruangan berhasil disetujui.');

        } catch (\Exception $e) {
            Log::error('Error approving peminjaman: ' . $e->getMessage());
            
            // Log: Error saat approve
            $this->logActivity('error', 'Error saat menyetujui peminjaman', 
                "Terjadi error saat pegawai mencoba menyetujui peminjaman ID: {$id}: " . $e->getMessage());
            
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function rejectPegawai(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|min:5|max:1000'
        ]);

        $peminjaman = PeminjamanRuangan::findOrFail($id);
        $user = Auth::user();
        
        if ($peminjaman->status != 'menunggu') {
            // Log: Reject gagal karena status tidak menunggu
            $this->logActivity('reject_failed', 'Gagal menolak peminjaman', 
                "Pegawai {$user->name} mencoba menolak peminjaman ID: {$id} tetapi status sudah '{$peminjaman->status}'");
            
            return back()->with('error', 'Hanya peminjaman dengan status menunggu yang dapat ditolak.');
        }

        $peminjaman->status = 'ditolak';
        $peminjaman->alasan_penolakan = $request->alasan_penolakan;
        $peminjaman->save();

        // Log: Pegawai berhasil menolak peminjaman
        $this->logActivity('reject', 'Menolak peminjaman ruangan',
            "Pegawai {$user->name} menolak peminjaman ID: {$id}, " .
            "Pengaju: {$peminjaman->nama_pengaju}, " .
            "Acara: {$peminjaman->acara}, " .
            "Alasan: {$request->alasan_penolakan}");

        Log::info('Peminjaman ID: ' . $peminjaman->id . ' ditolak oleh pegawai ' . $user->name);

        return back()->with('success', 'Peminjaman ruangan ditolak.');
    }

    public function cancelPegawai($id)
    {
        $peminjaman = PeminjamanRuangan::with('ruangan')->findOrFail($id);
        $user = Auth::user();
        
        if (!in_array($peminjaman->status, ['menunggu', 'disetujui'])) {
            // Log: Cancel gagal karena status tidak sesuai
            $this->logActivity('cancel_failed', 'Gagal membatalkan peminjaman (pegawai)', 
                "Pegawai {$user->name} mencoba membatalkan peminjaman ID: {$id} tetapi status '{$peminjaman->status}' tidak diizinkan");
            
            return back()->with('error', 'Tidak dapat membatalkan peminjaman dengan status ini.');
        }

        $peminjaman->status = 'dibatalkan';
        $peminjaman->save();

        // Log: Pegawai berhasil membatalkan peminjaman
        $this->logActivity('cancel', 'Pegawai membatalkan peminjaman',
            "Pegawai {$user->name} membatalkan peminjaman ID: {$id}, " .
            "Pengaju: {$peminjaman->nama_pengaju}, " .
            "Ruangan: {$peminjaman->ruangan->nama_ruangan}, " .
            "Acara: {$peminjaman->acara}");

        Log::info('Peminjaman ID: ' . $peminjaman->id . ' dibatalkan oleh pegawai ' . $user->name);

        return back()->with('success', 'Peminjaman ruangan berhasil dibatalkan.');
    }

    // Method untuk update status real-time SEMUA (otomatis berdasarkan waktu)
    public function updateStatusRealTime()
    {
        try {
            $now = Carbon::now();
            $user = Auth::user();
            
            // Ambil semua peminjaman yang disetujui
            $peminjaman = PeminjamanRuangan::where('status', 'disetujui')
                ->with('ruangan')
                ->get();

            $updatedCount = 0;

            foreach ($peminjaman as $item) {
                // Parse tanggal dan jam dengan benar menggunakan helper
                $bookingStart = $this->parseBookingDateTime($item->tanggal_mulai, $item->jam_mulai);
                $bookingEnd = $this->parseBookingDateTime($item->tanggal_selesai, $item->jam_selesai);
                
                // Tentukan status real-time berdasarkan waktu
                $newStatus = 'akan_datang';
                
                if ($now->gte($bookingStart) && $now->lte($bookingEnd)) {
                    $newStatus = 'berlangsung';
                } elseif ($now->gt($bookingEnd)) {
                    $newStatus = 'selesai';
                }

                // Update jika status berubah
                if ($item->status_real_time != $newStatus) {
                    $oldStatus = $item->status_real_time;
                    $item->status_real_time = $newStatus;
                    $item->save();
                    $updatedCount++;
                    
                    // Log perubahan status real-time
                    $this->logActivity('update', 'Mengubah status real-time peminjaman',
                        "Sistem mengubah status real-time peminjaman ID: {$item->id} " .
                        "dari '{$oldStatus}' menjadi '{$newStatus}', " .
                        "Acara: {$item->acara}, Ruangan: {$item->ruangan->nama_ruangan}");
                }
            }

            // Log: Pegawai meng-update semua status real-time
            if ($updatedCount > 0) {
                $this->logActivity('update', 'Meng-update semua status real-time',
                    "Pegawai {$user->name} meng-update status real-time {$updatedCount} peminjaman");
            }

            Log::info('Status real-time updated by ' . $user->name . ': ' . $updatedCount . ' peminjaman diperbarui');

            return response()->json([
                'success' => true,
                'message' => 'Status real-time berhasil diperbarui',
                'updated' => $updatedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating real-time status: ' . $e->getMessage());
            
            // Log: Error saat update status real-time
            $this->logActivity('error', 'Error update status real-time', 
                "Terjadi error saat pegawai meng-update status real-time: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // ✅ HELPER METHOD untuk parse tanggal dan jam
    private function parseBookingDateTime($date, $time)
    {
        try {
            // Bersihkan waktu dari detik jika ada
            $cleanTime = $this->cleanTimeString($time);
            
            // Gabungkan tanggal dan waktu
            $dateTimeString = $date . ' ' . $cleanTime;
            
            // Parse dengan Carbon
            return Carbon::parse($dateTimeString);
            
        } catch (\Exception $e) {
            Log::error("Error parsing booking datetime - Date: {$date}, Time: {$time}, Error: " . $e->getMessage());
            
            // Fallback: parse tanggal saja
            $carbonDate = Carbon::parse($date);
            
            // Extract jam dan menit dari string waktu
            $timeParts = explode(':', $cleanTime);
            $hour = (int)$timeParts[0];
            $minute = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
            $second = isset($timeParts[2]) ? (int)$timeParts[2] : 0;
            
            return $carbonDate->setTime($hour, $minute, $second);
        }
    }
    
    // ✅ HELPER METHOD untuk membersihkan string waktu
    private function cleanTimeString($time)
    {
        // Jika waktu sudah dalam format HH:MM:SS, ambil hanya HH:MM
        $timeParts = explode(':', $time);
        
        if (count($timeParts) >= 2) {
            return $timeParts[0] . ':' . $timeParts[1];
        }
        
        return $time;
    }
    
    // ✅ Method untuk update status real-time SATU peminjaman (manual oleh pegawai)
    public function updateStatusRealTimeSingle(Request $request, $id)
    {
        try {
            $request->validate([
                'status_real_time' => 'required|in:akan_datang,berlangsung,selesai',
                'note' => 'nullable|string|max:500'
            ]);
            
            $peminjaman = PeminjamanRuangan::with('ruangan')->findOrFail($id);
            $user = Auth::user();
            
            // Cek apakah peminjaman sudah disetujui
            if ($peminjaman->status !== 'disetujui') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya peminjaman yang disetujui yang dapat diubah status real-time'
                ], 400);
            }
            
            $oldStatus = $peminjaman->status_real_time;
            $newStatus = $request->status_real_time;
            $currentTime = Carbon::now();
            
            // Parse tanggal dan jam dengan benar menggunakan helper
            $bookingStart = $this->parseBookingDateTime($peminjaman->tanggal_mulai, $peminjaman->jam_mulai);
            $bookingEnd = $this->parseBookingDateTime($peminjaman->tanggal_selesai, $peminjaman->jam_selesai);
            
            // Log untuk debugging
            Log::info("Update Status - ID: {$id}");
            Log::info("Current: " . $currentTime->format('Y-m-d H:i:s'));
            Log::info("Start: " . $bookingStart->format('Y-m-d H:i:s'));
            Log::info("End: " . $bookingEnd->format('Y-m-d H:i:s'));
            Log::info("Old: {$oldStatus}, New: {$newStatus}");
            
            // Validasi perubahan status berdasarkan logika bisnis
            switch ($newStatus) {
                case 'berlangsung':
                    // Validasi: Hanya bisa dari 'akan_datang' ke 'berlangsung'
                    if ($oldStatus !== 'akan_datang') {
                        return response()->json([
                            'success' => false,
                            'message' => "Hanya dapat memulai peminjaman dari status 'Akan Datang'"
                        ], 400);
                    }
                    
                    // Validasi: Tidak bisa set 'berlangsung' jika sudah lewat waktu selesai
                    if ($currentTime->gt($bookingEnd)) {
                        return response()->json([
                            'success' => false,
                            'message' => "Tidak dapat memulai peminjaman yang sudah berakhir"
                        ], 400);
                    }
                    break;
                    
                case 'selesai':
                    // Validasi: Hanya bisa dari 'berlangsung' ke 'selesai'
                    if ($oldStatus !== 'berlangsung') {
                        return response()->json([
                            'success' => false,
                            'message' => "Hanya dapat menyelesaikan peminjaman dari status 'Berlangsung'"
                        ], 400);
                    }
                    break;
                    
                case 'akan_datang':
                    // Validasi: Hanya bisa dari 'berlangsung' kembali ke 'akan_datang' (undo)
                    if ($oldStatus !== 'berlangsung') {
                        return response()->json([
                            'success' => false,
                            'message' => "Hanya dapat mengubah kembali ke 'Akan Datang' dari status 'Berlangsung'"
                        ], 400);
                    }
                    break;
            }
            
            // Jika semua validasi lolos, update status
            $peminjaman->status_real_time = $newStatus;
            $peminjaman->save();
            
            // Log aktivitas
            $this->logActivity('update', 'Mengubah status real-time peminjaman',
                "Pegawai {$user->name} mengubah status real-time peminjaman ID: {$id} " .
                "dari '{$oldStatus}' menjadi '{$newStatus}'" .
                ($request->note ? ", Catatan: {$request->note}" : ''));
            
            return response()->json([
                'success' => true,
                'message' => 'Status real-time berhasil diperbarui',
                'data' => [
                    'id' => $peminjaman->id,
                    'status_real_time' => $newStatus,
                    'ruangan_status' => $peminjaman->ruangan->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating real-time status single: ' . $e->getMessage());
            Log::error('Error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadSurat($id)
    {
        $peminjaman = PeminjamanRuangan::findOrFail($id);
        $user = Auth::user();
        
        if (!$peminjaman->lampiran_surat) {
            // Log: Download gagal karena file tidak ada
            $this->logActivity('download_failed', 'Gagal download lampiran', 
                "User {$user->name} mencoba download lampiran peminjaman ID: {$id} tetapi file tidak ditemukan");
            
            return back()->with('error', 'File lampiran tidak ditemukan.');
        }

        // Log: Berhasil download lampiran
        $this->logActivity('download', 'Download lampiran surat',
            "User {$user->name} mendownload lampiran peminjaman ID: {$id}, Acara: {$peminjaman->acara}");

        return Storage::disk('public')->download($peminjaman->lampiran_surat);
    }
    
    // ================ ADMIN METHODS (jika diperlukan) ================
    
    public function indexAdmin(Request $request)
    {
        try {
            $query = PeminjamanRuangan::with(['user', 'ruangan'])
                ->orderBy('created_at', 'desc');

            // Filtering sama seperti pegawai
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            if ($request->has('ruangan_id') && $request->ruangan_id != '') {
                $query->where('ruangan_id', $request->ruangan_id);
            }

            $peminjaman = $query->paginate(20);
            $ruangan = Ruangan::all();

            // PERBAIKAN: Hitung statistik dengan logika yang benar untuk status selesai
            $allPeminjaman = PeminjamanRuangan::all();
            
            $stats = [
                'total' => $allPeminjaman->count(),
                'menunggu' => $allPeminjaman->where('status', 'menunggu')->count(),
                'disetujui' => $allPeminjaman->where('status', 'disetujui')->count(),
                'ditolak' => $allPeminjaman->where('status', 'ditolak')->count(),
                'selesai' => $allPeminjaman->filter(function($item) {
                    return $item->status === 'selesai' || 
                           ($item->status === 'disetujui' && $item->status_real_time === 'selesai');
                })->count(),
                'dibatalkan' => $allPeminjaman->where('status', 'dibatalkan')->count(),
            ];

            return view('admin.peminjaman-ruangan.index', compact('peminjaman', 'ruangan', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error in indexAdmin: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat data.');
        }
    }

    public function detailAdmin($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::with(['user', 'ruangan'])->find($id);

            if (!$peminjaman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data peminjaman ruangan tidak ditemukan'
                ], 404);
            }
            
            $html = view('admin.peminjaman-ruangan._detail-modal', compact('peminjaman'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error in detailAdmin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server'
            ], 500);
        }
    }

    public function approveAdmin($id)
    {
        try {
            return $this->approvePegawai($id);
        } catch (\Exception $e) {
            Log::error('Error approveAdmin: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function rejectAdmin(Request $request, $id)
    {
        try {
            return $this->rejectPegawai($request, $id);
        } catch (\Exception $e) {
            Log::error('Error rejectAdmin: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cancelAdmin($id)
    {
        try {
            return $this->cancelPegawai($id);
        } catch (\Exception $e) {
            Log::error('Error cancelAdmin: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk log aktivitas
     */
    private function logActivity($tipe, $aktivitas, $deskripsi = null)
    {
        try {
            \App\Models\LogActivity::create([
                'user_id' => Auth::id(),
                'tipe' => $tipe,
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsi,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
}