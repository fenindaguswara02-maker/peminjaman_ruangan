<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    public function index(Request $request)
    {
        $query = Ruangan::query();
        
        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Filter berdasarkan kapasitas minimum
        if ($request->has('kapasitas_min') && $request->kapasitas_min != '') {
            $query->where('kapasitas', '>=', $request->kapasitas_min);
        }
        
        // Filter berdasarkan pencarian
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('kode_ruangan', 'like', "%{$search}%")
                  ->orWhere('nama_ruangan', 'like', "%{$search}%")
                  ->orWhere('fasilitas', 'like', "%{$search}%");
            });
        }
        
        // Sorting
        $sort = $request->get('sort', 'nama_ruangan');
        $order = $request->get('order', 'asc');
        $query->orderBy($sort, $order);
        
        $ruangan = $query->paginate(12)->withQueryString();
        
        $stats = [
            'total' => Ruangan::count(),
            'tersedia' => Ruangan::where('status', 'tersedia')->count(),
            'dipinjam' => Ruangan::where('status', 'dipinjam')->count(),
            'maintenance' => Ruangan::where('status', 'maintenance')->count(),
        ];
        
        return view('user.ruangan.index', compact('ruangan', 'stats'));
    }
    
    public function show($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        
        // Ambil jadwal peminjaman aktif untuk ruangan ini
        $jadwal = \App\Models\PeminjamanRuangan::where('ruangan_id', $id)
            ->where('status', 'disetujui')
            ->whereDate('tanggal', '>=', now()->format('Y-m-d'))
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->limit(10)
            ->get();
        
        return view('user.ruangan.show', compact('ruangan', 'jadwal'));
    }
    
    public function checkAvailability(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);
        
        $ruangan = Ruangan::findOrFail($id);
        
        $tersedia = $ruangan->isAvailable($request->tanggal, $request->jam_mulai, $request->jam_selesai);
        
        if ($tersedia) {
            return response()->json([
                'success' => true,
                'message' => 'Ruangan tersedia pada tanggal dan jam yang diminta.',
                'data' => [
                    'ruangan' => $ruangan->nama_ruangan,
                    'kode_ruangan' => $ruangan->kode_ruangan,
                    'tanggal' => $request->tanggal,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'kapasitas' => $ruangan->kapasitas
                ]
            ]);
        } else {
            // Cari jadwal yang bentrok
            $bentrok = \App\Models\PeminjamanRuangan::where('ruangan_id', $id)
                ->where('status', 'disetujui')
                ->whereDate('tanggal', $request->tanggal)
                ->where(function($query) use ($request) {
                    $query->where(function($q) use ($request) {
                        $q->whereTime('jam_mulai', '<', $request->jam_selesai)
                          ->whereTime('jam_selesai', '>', $request->jam_mulai);
                    });
                })
                ->first();
            
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak tersedia pada tanggal dan jam yang diminta.',
                'data' => $bentrok ? [
                    'acara' => $bentrok->acara,
                    'jam_mulai' => substr($bentrok->jam_mulai, 0, 5),
                    'jam_selesai' => substr($bentrok->jam_selesai, 0, 5),
                    'peminjam' => $bentrok->nama_pengaju
                ] : null
            ], 422);
        }
    }
}