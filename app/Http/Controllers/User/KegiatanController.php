<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PeminjamanRuangan;
use App\Models\User;
use App\Models\Ruangan;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        // Hitung total peminjaman saja
        $peminjamanCount = PeminjamanRuangan::count();
        $totalKegiatan = $peminjamanCount;

        // Jika tidak ada tabel ruangan, ambil data tanpa join
        try {
            // Ambil data peminjaman ruangan dengan join jika tabel ruangan ada
            $kegiatan = PeminjamanRuangan::query()
                ->select('peminjaman_ruangan.*', 'users.name as nama_pengguna')
                ->join('users', 'peminjaman_ruangan.user_id', '=', 'users.id')
                ->when($request->status && $request->status !== 'semua', function($query) use ($request) {
                    return $query->where('peminjaman_ruangan.status', $request->status);
                })
                ->when($request->dari_tanggal, function($query) use ($request) {
                    return $query->where('peminjaman_ruangan.tanggal', '>=', $request->dari_tanggal);
                })
                ->when($request->sampai_tanggal, function($query) use ($request) {
                    return $query->where('peminjaman_ruangan.tanggal', '<=', $request->sampai_tanggal);
                })
                ->orderBy('peminjaman_ruangan.tanggal', 'desc')
                ->orderBy('peminjaman_ruangan.jam_mulai', 'desc')
                ->paginate(10);
                
            // Tambahkan informasi ruangan jika ada
            $kegiatan->getCollection()->transform(function($item) {
                $item->jenis = 'peminjaman_ruangan';
                $item->nama_ruangan = 'Ruangan ID: ' . $item->ruangan_id; // Default jika tidak ada tabel ruangan
                $item->jenis_ruangan = '-';
                return $item;
            });
                
        } catch (\Exception $e) {
            // Jika error, ambil tanpa join
            $kegiatan = PeminjamanRuangan::query()
                ->when($request->status && $request->status !== 'semua', function($query) use ($request) {
                    return $query->where('status', $request->status);
                })
                ->when($request->dari_tanggal, function($query) use ($request) {
                    return $query->where('tanggal', '>=', $request->dari_tanggal);
                })
                ->when($request->sampai_tanggal, function($query) use ($request) {
                    return $query->where('tanggal', '<=', $request->sampai_tanggal);
                })
                ->orderBy('tanggal', 'desc')
                ->orderBy('jam_mulai', 'desc')
                ->paginate(10);
                
            // Tambahkan informasi pengguna dan ruangan
            $kegiatan->getCollection()->transform(function($item) {
                $item->jenis = 'peminjaman_ruangan';
                $item->nama_pengguna = 'User ID: ' . $item->user_id;
                $item->nama_ruangan = 'Ruangan ID: ' . $item->ruangan_id;
                $item->jenis_ruangan = '-';
                return $item;
            });
        }

        return view('user.daftar-kegiatan', compact('kegiatan', 'peminjamanCount', 'totalKegiatan'));
    }
}