<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RuanganController extends Controller
{
    /**
     * Update status ruangan
     */
    public function updateStatus(Request $request, $id)
    {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:tersedia,dibooking,dipakai,maintenance',
            'keterangan' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cari ruangan
        $ruangan = Ruangan::find($id);
        
        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ], 404);
        }

        // Simpan status lama
        $oldStatus = $ruangan->status;

        // Update status
        $ruangan->status = $request->status;
        
        // Update status_message jika kolomnya ada
        if ($request->has('keterangan') && !empty($request->keterangan)) {
            // Jika kolom status_message tidak ada di database, bisa simpan ke kolom keterangan
            // atau abaikan saja
            if (isset($ruangan->status_message)) {
                $ruangan->status_message = $request->keterangan;
            }
        }
        
        $ruangan->save();

        return response()->json([
            'success' => true,
            'message' => 'Status ruangan berhasil diperbarui',
            'data' => [
                'id' => $ruangan->id,
                'status' => $ruangan->status,
                'status_message' => $ruangan->status_message ?? '',
                'updated_at' => $ruangan->updated_at->format('Y-m-d H:i:s')
            ]
        ]);
    }

    /**
     * Get status history
     */
    public function getStatusHistory($id)
    {
        $ruangan = Ruangan::find($id);
        
        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ], 404);
        }

        // Data dummy untuk demo
        // TODO: nanti bisa diganti dengan query dari tabel riwayat jika ada
        $history = [
            [
                'status' => $ruangan->status,
                'status_display' => $this->getStatusDisplay($ruangan->status),
                'keterangan' => $ruangan->status_message ?? 'Status saat ini',
                'waktu' => $ruangan->updated_at->format('d M Y H:i'),
                'user' => auth()->user()->name
            ],
            [
                'status' => 'tersedia',
                'status_display' => 'Tersedia',
                'keterangan' => 'Ruangan dibuka kembali',
                'waktu' => now()->subDays(1)->format('d M Y H:i'),
                'user' => 'Sistem'
            ],
            [
                'status' => 'dipakai',
                'status_display' => 'Dipakai',
                'keterangan' => 'Digunakan untuk rapat prodi',
                'waktu' => now()->subDays(2)->format('d M Y H:i'),
                'user' => 'Sistem'
            ],
            [
                'status' => 'dibooking',
                'status_display' => 'Dibooking',
                'keterangan' => 'Dibooking untuk acara wisuda',
                'waktu' => now()->subDays(3)->format('d M Y H:i'),
                'user' => 'Sistem'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Show detail ruangan
     */
    public function show($id)
    {
        $ruangan = Ruangan::find($id);
        
        if (!$ruangan) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ruangan->id,
                'kode_ruangan' => $ruangan->kode_ruangan,
                'nama_ruangan' => $ruangan->nama_ruangan,
                'kapasitas' => $ruangan->kapasitas,
                'lokasi' => $ruangan->lokasi ?? '-',
                'status' => $ruangan->status,
                'status_message' => $ruangan->status_message ?? '-',
                'keterangan' => $ruangan->keterangan ?? '-',
                'created_at' => $ruangan->created_at->format('d M Y H:i'),
                'updated_at' => $ruangan->updated_at->format('d M Y H:i'),
                'status_badge' => $this->getStatusBadge($ruangan->status)
            ]
        ]);
    }

    /**
     * Helper function untuk mendapatkan display status
     */
    private function getStatusDisplay($status)
    {
        $statusMap = [
            'tersedia' => 'Tersedia',
            'dibooking' => 'Dibooking',
            'dipakai' => 'Dipakai',
            'maintenance' => 'Maintenance'
        ];

        return $statusMap[$status] ?? ucfirst($status);
    }

    /**
     * Helper function untuk mendapatkan badge status
     */
    private function getStatusBadge($status)
    {
        $badgeMap = [
            'tersedia' => [
                'class' => 'bg-green-100 text-green-800',
                'icon' => 'fa-check-circle'
            ],
            'dibooking' => [
                'class' => 'bg-yellow-100 text-yellow-800',
                'icon' => 'fa-calendar-alt'
            ],
            'dipakai' => [
                'class' => 'bg-purple-100 text-purple-800',
                'icon' => 'fa-users'
            ],
            'maintenance' => [
                'class' => 'bg-red-100 text-red-800',
                'icon' => 'fa-tools'
            ]
        ];

        return $badgeMap[$status] ?? [
            'class' => 'bg-gray-100 text-gray-800',
            'icon' => 'fa-circle'
        ];
    }
}