<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\PeminjamanRuangan;
use App\Models\Jadwal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        // Query dengan eager loading
        $query = Ruangan::withCount(['peminjaman' => function($query) {
            $query->where('status', 'disetujui')
                  ->whereDate('tanggal', '>=', Carbon::today());
        }]);
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan kapasitas minimum
        if ($request->has('kapasitas_min') && $request->kapasitas_min != '') {
            $query->where('kapasitas', '>=', $request->kapasitas_min);
        }
        
        // Filter berdasarkan lokasi
        if ($request->has('lokasi') && $request->lokasi != '') {
            $query->where('lokasi', 'like', "%{$request->lokasi}%");
        }
        
        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_ruangan', 'like', "%{$search}%")
                  ->orWhere('nama_ruangan', 'like', "%{$search}%")
                  ->orWhere('fasilitas', 'like', "%{$search}%")
                  ->orWhere('lokasi', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sort = $request->get('sort', 'nama_ruangan');
        $order = $request->get('order', 'asc');
        $query->orderBy($sort, $order);
        
        // Pagination
        $ruangan = $query->paginate(12)->withQueryString();
        
        // Statistics
        $stats = [
            'total' => Ruangan::count(),
            'tersedia' => Ruangan::where('status', 'tersedia')->count(),
            'dipinjam' => Ruangan::where('status', 'dipinjam')->count(),
            'maintenance' => Ruangan::where('status', 'maintenance')->count(),
            'total_kapasitas' => Ruangan::sum('kapasitas'),
            'ruangan_terbesar' => Ruangan::orderBy('kapasitas', 'desc')->first(),
            'ruangan_terkecil' => Ruangan::orderBy('kapasitas', 'asc')->first(),
        ];
        
        // Get unique locations for filter
        $locations = Ruangan::select('lokasi')->whereNotNull('lokasi')->distinct()->get();
        
        return view('user.ruangan.index', compact('ruangan', 'stats', 'locations'));
    }
    
    public function show($id)
    {
        $ruangan = Ruangan::with(['peminjaman' => function($query) {
            $query->where('status', 'disetujui')
                  ->whereDate('tanggal', '>=', Carbon::today())
                  ->orderBy('tanggal', 'asc')
                  ->orderBy('jam_mulai', 'asc')
                  ->limit(10);
        }])->findOrFail($id);
        
        // Ambil statistik penggunaan bulan ini
        $usageStats = $ruangan->getUsageStats();
        
        // Ambil jadwal kantor untuk ruangan ini
        $jadwalKantor = Jadwal::where('ruangan_id', $id)
            ->where('status', 'aktif')
            ->whereDate('tanggal', '>=', Carbon::today())
            ->orderBy('tanggal', 'asc')
            ->orderBy('waktu_mulai', 'asc')
            ->limit(5)
            ->get();
        
        // Ambil peminjaman aktif untuk ruangan ini
        $jadwalPeminjaman = $ruangan->peminjaman->take(10);
        
        // Gabungkan jadwal kantor dan peminjaman
        $jadwal = collect();
        
        // Tambahkan jadwal kantor
        foreach ($jadwalKantor as $item) {
            $jadwal->push((object)[
                'type' => 'kantor',
                'acara' => $item->acara,
                'tanggal' => $item->tanggal,
                'jam_mulai' => $item->waktu_mulai,
                'jam_selesai' => $item->waktu_selesai,
                'keterangan' => $item->keterangan,
                'jenis' => $item->jenis_jadwal
            ]);
        }
        
        // Tambahkan peminjaman
        foreach ($jadwalPeminjaman as $item) {
            $jadwal->push((object)[
                'type' => 'peminjaman',
                'acara' => $item->acara,
                'nama_pengaju' => $item->nama_pengaju,
                'tanggal' => $item->tanggal,
                'jam_mulai' => $item->jam_mulai,
                'jam_selesai' => $item->jam_selesai,
                'jumlah_peserta' => $item->jumlah_peserta
            ]);
        }
        
        // Urutkan berdasarkan tanggal dan waktu
        $jadwal = $jadwal->sortBy([
            ['tanggal', 'asc'],
            ['jam_mulai', 'asc']
        ])->take(10);
        
        // Ambil ruangan terkait (ruangan lain dengan status tersedia)
        $relatedRooms = Ruangan::where('status', 'tersedia')
            ->where('id', '!=', $id)
            ->inRandomOrder()
            ->limit(3)
            ->get();
        
        return view('user.ruangan.show', compact(
            'ruangan', 
            'usageStats', 
            'jadwal', // Kirim $jadwal bukan $jadwalKantor
            'relatedRooms'
        ));
    }
    
    public function checkAvailability(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jumlah_peserta' => 'nullable|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $ruangan = Ruangan::findOrFail($id);
        
        // Cek kapasitas jika jumlah peserta diberikan
        if ($request->has('jumlah_peserta') && $request->jumlah_peserta > $ruangan->kapasitas) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah peserta melebihi kapasitas ruangan.',
                'data' => [
                    'kapasitas_maksimal' => $ruangan->kapasitas,
                    'jumlah_peserta' => $request->jumlah_peserta
                ]
            ], 422);
        }
        
        // Cek status ruangan
        if ($ruangan->status !== 'tersedia') {
            $statusMessage = [
                'dipinjam' => 'Ruangan sedang dipinjam',
                'maintenance' => 'Ruangan sedang dalam perbaikan'
            ];
            
            return response()->json([
                'success' => false,
                'message' => $statusMessage[$ruangan->status] ?? 'Ruangan tidak tersedia',
                'status' => $ruangan->status,
                'status_label' => $ruangan->status_label
            ], 422);
        }
        
        $tersedia = $ruangan->isAvailable(
            $request->tanggal, 
            $request->jam_mulai, 
            $request->jam_selesai
        );
        
        if ($tersedia) {
            $data = [
                'success' => true,
                'message' => 'Ruangan tersedia pada tanggal dan jam yang diminta.',
                'data' => [
                    'ruangan_id' => $ruangan->id,
                    'nama_ruangan' => $ruangan->nama_ruangan,
                    'kode_ruangan' => $ruangan->kode_ruangan,
                    'kapasitas' => $ruangan->kapasitas,
                    'lokasi' => $ruangan->lokasi,
                    'fasilitas' => $ruangan->fasilitas,
                    'tanggal' => $request->tanggal,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'tanggal_display' => Carbon::parse($request->tanggal)->translatedFormat('l, d F Y'),
                    'action_url' => route('user.peminjaman-ruangan.create', [
                        'ruangan' => $ruangan->id,
                        'tanggal' => $request->tanggal,
                        'jam_mulai' => $request->jam_mulai,
                        'jam_selesai' => $request->jam_selesai
                    ])
                ]
            ];
            
            // Tambahkan jumlah peserta jika ada
            if ($request->has('jumlah_peserta')) {
                $data['data']['jumlah_peserta'] = $request->jumlah_peserta;
            }
            
            return response()->json($data);
        } else {
            // Cari konflik peminjaman
            $conflictPeminjaman = PeminjamanRuangan::where('ruangan_id', $id)
                ->where('status', 'disetujui')
                ->whereDate('tanggal', $request->tanggal)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->where('jam_mulai', '<', $request->jam_selesai)
                          ->where('jam_selesai', '>', $request->jam_mulai);
                    });
                })
                ->first();
            
            // Cari konflik jadwal kantor
            $conflictJadwal = Jadwal::where('ruangan_id', $id)
                ->where('status', 'aktif')
                ->whereDate('tanggal', $request->tanggal)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->where('waktu_mulai', '<', $request->jam_selesai)
                          ->where('waktu_selesai', '>', $request->jam_mulai);
                    });
                })
                ->first();
            
            $conflictData = null;
            if ($conflictPeminjaman) {
                $conflictData = [
                    'type' => 'peminjaman',
                    'title' => 'Konflik dengan Peminjaman',
                    'acara' => $conflictPeminjaman->acara,
                    'peminjam' => $conflictPeminjaman->nama_pengaju,
                    'jam_mulai' => substr($conflictPeminjaman->jam_mulai, 0, 5),
                    'jam_selesai' => substr($conflictPeminjaman->jam_selesai, 0, 5),
                    'jumlah_peserta' => $conflictPeminjaman->jumlah_peserta
                ];
            } elseif ($conflictJadwal) {
                $conflictData = [
                    'type' => 'jadwal_kantor',
                    'title' => 'Konflik dengan Jadwal Kantor',
                    'acara' => $conflictJadwal->acara,
                    'jam_mulai' => substr($conflictJadwal->waktu_mulai, 0, 5),
                    'jam_selesai' => substr($conflictJadwal->waktu_selesai, 0, 5),
                    'keterangan' => $conflictJadwal->keterangan
                ];
            }
            
            $response = [
                'success' => false,
                'message' => 'Ruangan tidak tersedia pada tanggal dan jam yang diminta.',
                'conflict' => $conflictData,
                'status_ruangan' => $ruangan->status,
                'status_label' => $ruangan->status_label
            ];
            
            return response()->json($response, 422);
        }
    }
    
    private function findAlternativeTimes($ruangan, $tanggal, $jam_mulai, $jam_selesai)
    {
        $alternatives = [];
        $date = Carbon::parse($tanggal);
        $duration = Carbon::parse($jam_selesai)->diffInMinutes(Carbon::parse($jam_mulai));
        
        // Coba waktu setelahnya pada hari yang sama
        $endTime = Carbon::parse($jam_selesai);
        for ($i = 1; $i <= 3; $i++) {
            $newStart = $endTime->copy()->addMinutes(30 * $i);
            $newEnd = $newStart->copy()->addMinutes($duration);
            
            if ($newEnd->format('H:i') <= '21:00') { // Batas jam operasional
                $available = $ruangan->isAvailable($tanggal, $newStart->format('H:i'), $newEnd->format('H:i'));
                if ($available) {
                    $alternatives[] = [
                        'jam_mulai' => $newStart->format('H:i'),
                        'jam_selesai' => $newEnd->format('H:i'),
                        'label' => $newStart->format('H:i') . ' - ' . $newEnd->format('H:i')
                    ];
                    if (count($alternatives) >= 2) break;
                }
            }
        }
        
        // Jika tidak ada alternatif di hari yang sama, coba hari berikutnya
        if (empty($alternatives)) {
            for ($day = 1; $day <= 3; $day++) {
                $nextDate = $date->copy()->addDays($day);
                if ($ruangan->isAvailable($nextDate->format('Y-m-d'), $jam_mulai, $jam_selesai)) {
                    $alternatives[] = [
                        'tanggal' => $nextDate->format('Y-m-d'),
                        'tanggal_display' => $nextDate->translatedFormat('l, d F Y'),
                        'jam_mulai' => $jam_mulai,
                        'jam_selesai' => $jam_selesai,
                        'label' => $nextDate->translatedFormat('l, d F') . ' (' . $jam_mulai . ' - ' . $jam_selesai . ')'
                    ];
                    if (count($alternatives) >= 2) break;
                }
            }
        }
        
        return $alternatives;
    }
}