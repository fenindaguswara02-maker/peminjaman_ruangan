<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalRequest;
use App\Models\Jadwal;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    /**
     * Dashboard untuk pegawai
     */
    public function dashboardPegawai()
    {
        try {
            $user = Auth::user();
            $today = Carbon::today();
            $currentTime = Carbon::now();
            
            // Statistik cepat
            $todayJadwal = Jadwal::where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->count();
            
            $totalJadwal = Jadwal::where('user_id', $user->id)->count();
            
            $jadwalBerlangsung = Jadwal::where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->where('waktu_mulai', '<=', $currentTime->format('H:i:s'))
                ->where('waktu_selesai', '>=', $currentTime->format('H:i:s'))
                ->count();
            
            $jadwalAkanDatang = Jadwal::where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->where('waktu_mulai', '>', $currentTime->format('H:i:s'))
                ->count();
            
            // Jadwal hari ini
            $todayJadwalList = Jadwal::with('ruangan')
                ->where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->orderBy('waktu_mulai')
                ->get();
            
            // Jadwal mendatang (3 hari ke depan)
            $threeDaysLater = $today->copy()->addDays(3);
            $upcomingJadwal = Jadwal::with('ruangan')
                ->where('user_id', $user->id)
                ->whereBetween('tanggal', [$today->copy()->addDay(), $threeDaysLater])
                ->orderBy('tanggal')
                ->orderBy('waktu_mulai')
                ->limit(5)
                ->get();
            
            // Ringkasan minggu ini
            $startOfWeek = $today->copy()->startOfWeek();
            $endOfWeek = $today->copy()->endOfWeek();
            
            $weeklyStats = Jadwal::where('user_id', $user->id)
                ->whereBetween('tanggal', [$startOfWeek, $endOfWeek])
                ->selectRaw('DATE(tanggal) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy(function($item) {
                    return Carbon::parse($item->date)->format('Y-m-d');
                });
            
            // Buat array untuk 7 hari dalam seminggu
            $daysOfWeek = [];
            for ($i = 0; $i < 7; $i++) {
                $day = $startOfWeek->copy()->addDays($i);
                $daysOfWeek[] = [
                    'date' => $day->format('Y-m-d'),
                    'day_name' => $day->translatedFormat('D'),
                    'count' => isset($weeklyStats[$day->format('Y-m-d')]) ? $weeklyStats[$day->format('Y-m-d')]->count : 0,
                    'is_today' => $day->isToday()
                ];
            }
            
            // Ruangan terpopuler untuk user ini
            $popularRuangan = Ruangan::withCount(['jadwal' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                }])
                ->whereHas('jadwal', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderByDesc('jadwal_count')
                ->limit(5)
                ->get();
            
            return view('pegawai.dashboard', compact(
                'todayJadwal',
                'totalJadwal',
                'jadwalBerlangsung',
                'jadwalAkanDatang',
                'todayJadwalList',
                'upcomingJadwal',
                'daysOfWeek',
                'weeklyStats',
                'popularRuangan'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error in dashboardPegawai: ' . $e->getMessage());
            return view('pegawai.dashboard', [
                'todayJadwal' => 0,
                'totalJadwal' => 0,
                'jadwalBerlangsung' => 0,
                'jadwalAkanDatang' => 0,
                'todayJadwalList' => collect([]),
                'upcomingJadwal' => collect([]),
                'daysOfWeek' => [],
                'weeklyStats' => collect([]),
                'popularRuangan' => collect([]),
                'error' => 'Terjadi kesalahan saat memuat dashboard'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ruangan = Ruangan::where('status', 'tersedia')
            ->orderBy('kode_ruangan')
            ->get();
        
        return view('pegawai.buat-jadwal', compact('ruangan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJadwalRequest $request)
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            
            $ruangan = Ruangan::find($request->ruangan_id);
            if (!$ruangan) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Ruangan tidak ditemukan');
            }
            
            if (!$ruangan->isAvailable($request->tanggal, $request->waktu_mulai, $request->waktu_selesai)) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Ruangan tidak tersedia pada tanggal dan jam yang diminta');
            }
            
            Jadwal::create($data);
            
            return redirect()
                ->route('pegawai.jadwal.index')
                ->with('success', 'Jadwal berhasil dibuat!');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Check availability of room
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'ruangan_id' => 'required|exists:ruangan,id',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'exclude_id' => 'nullable|exists:jadwal,id'
        ]);

        try {
            $ruangan = Ruangan::find($request->ruangan_id);
            
            $isAvailable = $ruangan->isAvailable(
                $request->tanggal, 
                $request->waktu_mulai, 
                $request->waktu_selesai, 
                $request->exclude_id
            );

            return response()->json([
                'available' => $isAvailable,
                'message' => $isAvailable ? 'Ruangan tersedia' : 'Ruangan tidak tersedia'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'available' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Ambil semua jadwal (tidak hanya user yang login, tapi semua untuk kalender)
        $jadwalKantor = Jadwal::with(['user', 'ruangan'])
            ->whereMonth('tanggal', $currentMonth)
            ->whereYear('tanggal', $currentYear)
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();
        
        // Filter kegiatan hari ini (tampilkan semua, tidak hanya user login)
        $kegiatanHariIni = $jadwalKantor->filter(function($jadwal) use ($today) {
            return $jadwal->tanggal->format('Y-m-d') == $today;
        });
        
        // Format untuk tabel (ambil 10 terbaru)
        $kegiatanTerbaru = $jadwalKantor->take(10)->map(function($jadwal) {
            return [
                'id' => 'kantor_' . $jadwal->id,
                'type' => 'kantor',
                'nama_kegiatan' => $jadwal->nama_kegiatan,
                'deskripsi' => $jadwal->deskripsi ?? '-',
                'tanggal' => $jadwal->tanggal->format('Y-m-d'),
                'tanggal_mulai' => $jadwal->tanggal->format('Y-m-d'),
                'tanggal_selesai' => $jadwal->tanggal->format('Y-m-d'),
                'waktu_mulai' => $jadwal->waktu_mulai,
                'waktu_selesai' => $jadwal->waktu_selesai,
                'lokasi' => $jadwal->ruangan ? ($jadwal->ruangan->kode_ruangan . ' - ' . $jadwal->ruangan->nama_ruangan) : 'Tidak ditentukan',
                'ruangan_id' => $jadwal->ruangan_id,
                'ruangan_nama' => $jadwal->ruangan ? $jadwal->ruangan->nama_ruangan : null,
                'ruangan_kode' => $jadwal->ruangan ? $jadwal->ruangan->kode_ruangan : null,
                'kapasitas_peserta' => $jadwal->kapasitas_peserta,
                'created_at' => $jadwal->created_at,
                'creator' => $jadwal->user->name ?? 'Staff',
                'model_id' => $jadwal->id,
                'model_type' => 'kantor'
            ];
        });
        
        // Data untuk kalender
        $calendarEvents = [];
        foreach ($jadwalKantor as $jadwal) {
            $day = $jadwal->tanggal->day;
            if (!isset($calendarEvents[$day])) {
                $calendarEvents[$day] = [];
            }
            
            $calendarEvents[$day][] = [
                'id' => 'kantor_' . $jadwal->id,
                'type' => 'kantor',
                'nama_kegiatan' => $jadwal->nama_kegiatan,
                'lokasi' => $jadwal->ruangan ? ($jadwal->ruangan->kode_ruangan . ' - ' . $jadwal->ruangan->nama_ruangan) : 'Tidak ditentukan',
                'ruangan_nama' => $jadwal->ruangan ? $jadwal->ruangan->nama_ruangan : null
            ];
        }
        
        // Ambil daftar ruangan untuk filter
        $ruanganList = Ruangan::orderBy('kode_ruangan')->get();
        
        return view('pegawai.jadwal-staff', compact(
            'jadwalKantor',
            'kegiatanHariIni',
            'kegiatanTerbaru',
            'calendarEvents',
            'today',
            'currentMonth',
            'currentYear',
            'ruanganList'
        ));
    }

    /**
     * Menampilkan semua kegiatan dengan filter
     */
    public function semuaKegiatan(Request $request)
    {
        try {
            $currentMonth = $request->get('bulan') ? intval($request->get('bulan')) : null;
            $currentYear = $request->get('tahun') ? intval($request->get('tahun')) : Carbon::now()->year;
            
            // Query jadwal dengan ruangan
            $query = Jadwal::with(['user', 'ruangan'])
                ->whereYear('tanggal', $currentYear);
            
            if ($currentMonth) {
                $query->whereMonth('tanggal', $currentMonth);
            }
            
            // Filter ruangan
            $ruanganId = $request->get('ruangan_id');
            if ($ruanganId) {
                $query->where('ruangan_id', $ruanganId);
            }
            
            // Filter pencarian
            $search = $request->get('search');
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_kegiatan', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%")
                      ->orWhereHas('ruangan', function($q2) use ($search) {
                          $q2->where('nama_ruangan', 'like', "%{$search}%")
                             ->orWhere('kode_ruangan', 'like', "%{$search}%");
                      });
                });
            }
            
            // Urutkan
            $query->orderBy('tanggal', 'desc')
                  ->orderBy('waktu_mulai', 'asc');
            
            // Data untuk tabel
            $allKegiatan = $query->get();
            
            $kegiatanTerbaru = $allKegiatan->map(function($jadwal) {
                return [
                    'id' => 'kantor_' . $jadwal->id,
                    'type' => 'kantor',
                    'nama_kegiatan' => $jadwal->nama_kegiatan,
                    'deskripsi' => $jadwal->deskripsi ?? '-',
                    'tanggal' => $jadwal->tanggal->format('Y-m-d'),
                    'tanggal_mulai' => $jadwal->tanggal->format('Y-m-d'),
                    'tanggal_selesai' => $jadwal->tanggal->format('Y-m-d'),
                    'waktu_mulai' => $jadwal->waktu_mulai,
                    'waktu_selesai' => $jadwal->waktu_selesai,
                    'lokasi' => $jadwal->ruangan ? ($jadwal->ruangan->kode_ruangan . ' - ' . $jadwal->ruangan->nama_ruangan) : 'Tidak ditentukan',
                    'ruangan_id' => $jadwal->ruangan_id,
                    'ruangan_nama' => $jadwal->ruangan ? $jadwal->ruangan->nama_ruangan : null,
                    'ruangan_kode' => $jadwal->ruangan ? $jadwal->ruangan->kode_ruangan : null,
                    'kapasitas_peserta' => $jadwal->kapasitas_peserta,
                    'created_at' => $jadwal->created_at,
                    'creator' => $jadwal->user->name ?? 'Staff',
                    'model_id' => $jadwal->id,
                    'model_type' => 'kantor'
                ];
            });
            
            $totalData = $kegiatanTerbaru->count();
            
            // Data bulan untuk filter
            $months = [
                '' => 'Semua Bulan',
                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
            ];
            
            // Data tahun untuk filter
            $years = [];
            $currentYearValue = Carbon::now()->year;
            for ($i = $currentYearValue - 5; $i <= $currentYearValue + 5; $i++) {
                $years[$i] = $i;
            }
            
            // Data ruangan untuk filter
            $ruanganList = Ruangan::orderBy('nama_ruangan')->get();
            
            return view('pegawai.semua-kegiatan', compact(
                'kegiatanTerbaru', 
                'totalData', 
                'months',
                'years',
                'ruanganList'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error in semuaKegiatan: ' . $e->getMessage());
            return view('pegawai.semua-kegiatan')->with('error', 'Gagal memuat data: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Jadwal $jadwal)
    {
        if ($jadwal->user_id !== Auth::id()) {
            abort(403);
        }
        
        return view('pegawai.jadwal.show', compact('jadwal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    // Pada method edit() di controller, ubah return view:
public function edit($id)
{
    $jadwal = Jadwal::findOrFail($id);
    
    // Cek apakah user memiliki akses
    if ($jadwal->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
        abort(403, 'Anda tidak memiliki izin untuk mengedit jadwal ini.');
    }
    
    // Ambil daftar ruangan dari database
    $ruanganList = Ruangan::where('status', 'tersedia')
        ->orWhere('id', $jadwal->ruangan_id)
        ->orderBy('kode_ruangan')
        ->get();

    // GANTI INI: dari 'pegawai.jadwal.edit' menjadi 'pegawai.jadwal-edit'
    return view('pegawai.jadwal-edit', compact('jadwal', 'ruanganList'));
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);
        
        // Authorization check
        if ($jadwal->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengedit jadwal ini.'
                ], 403);
            }
            return back()->with('error', 'Anda tidak memiliki izin untuk mengedit jadwal ini.');
        }

        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'ruangan_id' => 'required|exists:ruangan,id',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'kapasitas_peserta' => 'nullable|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        // Validasi ruangan tersedia
        $ruangan = Ruangan::find($request->ruangan_id);
        if (!$ruangan) {
            return back()->withErrors(['ruangan_id' => 'Ruangan tidak ditemukan'])->withInput();
        }

        // Cek status ruangan (kecuali jika ruangan yang sama)
        if ($ruangan->status != 'tersedia' && $request->ruangan_id != $jadwal->ruangan_id) {
            return back()->withErrors(['ruangan_id' => 'Ruangan tidak tersedia'])->withInput();
        }

        // Cek kapasitas ruangan
        if ($request->kapasitas_peserta && $request->kapasitas_peserta > $ruangan->kapasitas) {
            return back()->withErrors(['kapasitas_peserta' => 'Kapasitas melebihi kapasitas ruangan (Maks: ' . $ruangan->kapasitas . ')'])->withInput();
        }

        // Validasi tanggal tidak boleh di masa lalu
        if (Carbon::parse($request->tanggal) < today()) {
            return back()->withErrors(['tanggal' => 'Tanggal tidak boleh di masa lalu'])->withInput();
        }

        // Validasi konflik jadwal
        $konflikJadwal = Jadwal::where('id', '!=', $jadwal->id)
            ->where('ruangan_id', $request->ruangan_id)
            ->where('tanggal', $request->tanggal)
            ->where(function($query) use ($request) {
                $query->whereBetween('waktu_mulai', [$request->waktu_mulai, $request->waktu_selesai])
                      ->orWhereBetween('waktu_selesai', [$request->waktu_mulai, $request->waktu_selesai])
                      ->orWhere(function($q) use ($request) {
                          $q->where('waktu_mulai', '<', $request->waktu_mulai)
                            ->where('waktu_selesai', '>', $request->waktu_selesai);
                      });
            })
            ->exists();

        if ($konflikJadwal) {
            return back()->withErrors(['waktu_mulai' => 'Jadwal bertabrakan dengan jadwal lain di ruangan yang sama'])->withInput();
        }

        $jadwal->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil diperbarui!'
            ]);
        }

        return redirect()->route('pegawai.jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);
        
        if ($jadwal->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus jadwal ini'
                ], 403);
            }
            abort(403);
        }
        
        try {
            $jadwal->delete();
            
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jadwal berhasil dihapus!'
                ]);
            }
            
            return redirect()->route('pegawai.jadwal.index')
                ->with('success', 'Jadwal berhasil dihapus!');
                
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus jadwal: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('pegawai.jadwal.index')
                ->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }
    
    /**
     * API: Get jadwal data untuk AJAX
     */
    public function getJadwalData($id)
    {
        $jadwal = Jadwal::with('ruangan')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'jadwal' => $jadwal
        ]);
    }
}