<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class JadwalPeminjamanController extends Controller
{
    /**
     * Menampilkan halaman jadwal peminjaman (dengan pagination)
     */
    public function index(Request $request)
    {
        // Ambil parameter filter
        $status = $request->input('status', 'all');
        $ruanganId = $request->input('ruangan_id', 'all');
        $jenisPengaju = $request->input('jenis_pengaju', 'all');
        $search = $request->input('search');
        $tanggal = $request->input('tanggal');
        $perPage = $request->input('per_page', 10);

        // Query untuk data peminjaman ruangan
        $query = PeminjamanRuangan::with(['user', 'ruangan'])
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Filter berdasarkan ruangan
        if ($ruanganId && $ruanganId !== 'all') {
            $query->where('ruangan_id', $ruanganId);
        }

        // Filter berdasarkan jenis pengaju
        if ($jenisPengaju && $jenisPengaju !== 'all') {
            $query->where('jenis_pengaju', $jenisPengaju);
        }

        // Filter berdasarkan tanggal
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        // Filter berdasarkan search
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('acara', 'like', "%{$search}%")
                  ->orWhere('keterangan', 'like', "%{$search}%")
                  ->orWhere('nama_pengaju', 'like', "%{$search}%")
                  ->orWhere('nim_nip', 'like', "%{$search}%")
                  ->orWhereHas('ruangan', function($q3) use ($search) {
                      $q3->where('nama_ruangan', 'like', "%{$search}%")
                         ->orWhere('kode_ruangan', 'like', "%{$search}%");
                  });
            });
        }

        // Ambil semua data untuk perhitungan statistik (tanpa filter pagination)
        $allData = PeminjamanRuangan::with(['user', 'ruangan'])->get();

        // Hitung statistik berdasarkan status
        $stats = [
            'total' => $allData->count(),
            'menunggu' => $allData->where('status', 'menunggu')->count(),
            'disetujui' => $allData->where('status', 'disetujui')->count(),
            'ditolak' => $allData->where('status', 'ditolak')->count(),
            'selesai' => $allData->where(function($item) {
                return $item->status === 'selesai' || 
                       ($item->status === 'disetujui' && $item->status_real_time === 'selesai');
            })->count(),
            'dibatalkan' => $allData->where('status', 'dibatalkan')->count(),
        ];

        $overallStats = [
            'total_ruangan' => $stats['total'],
            'disetujui_ruangan' => $stats['disetujui'],
            'menunggu_ruangan' => $stats['menunggu'],
            'ditolak_ruangan' => $stats['ditolak'],
            'selesai_ruangan' => $stats['selesai'],
            'dibatalkan_ruangan' => $stats['dibatalkan'],
        ];

        $filteredData = clone $query;
        $filteredStats = [
            'total' => (clone $filteredData)->count(),
            'menunggu' => (clone $filteredData)->where('status', 'menunggu')->count(),
            'disetujui' => (clone $filteredData)->where('status', 'disetujui')->count(),
            'ditolak' => (clone $filteredData)->where('status', 'ditolak')->count(),
            'selesai' => (clone $filteredData)->where(function($q) {
                $q->where('status', 'selesai')
                  ->orWhere(function($q2) {
                      $q2->where('status', 'disetujui')
                         ->where('status_real_time', 'selesai');
                  });
            })->count(),
            'dibatalkan' => (clone $filteredData)->where('status', 'dibatalkan')->count(),
        ];

        Log::info('Admin Stats:', $stats);

        $peminjamanRuangan = $query->paginate($perPage);
        $ruanganOptions = Ruangan::where('status', 'aktif')->get();
        $jenisPengajuOptions = PeminjamanRuangan::distinct()->pluck('jenis_pengaju')->filter();

        return view('admin.jadwal-peminjaman', compact(
            'peminjamanRuangan', 
            'overallStats',
            'filteredStats',
            'stats',
            'ruanganOptions',
            'jenisPengajuOptions',
            'status',
            'ruanganId',
            'jenisPengaju',
            'search',
            'tanggal',
            'perPage'
        ));
    }

    /**
     * Download file surat lampiran
     */
    public function downloadSurat($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::findOrFail($id);
            
            if (!$peminjaman->lampiran_surat) {
                return redirect()->back()->with('error', 'File surat tidak ditemukan');
            }
            
            $filePath = storage_path('app/public/' . $peminjaman->lampiran_surat);
            
            if (!file_exists($filePath)) {
                return redirect()->back()->with('error', 'File surat tidak ditemukan di server');
            }
            
            $this->logActivity(
                'DOWNLOAD_SURAT',
                'peminjaman_ruangan',
                $peminjaman->id,
                'Mendownload surat lampiran peminjaman: ' . $peminjaman->acara,
                null,
                ['lampiran_surat' => $peminjaman->lampiran_surat],
                request()
            );
            
            return response()->download($filePath, 'surat_' . str_replace(' ', '_', $peminjaman->acara) . '_' . date('Ymd') . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Error downloadSurat: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mendownload surat: ' . $e->getMessage());
        }
    }

    /**
     * API untuk mendapatkan data peminjaman untuk edit (untuk modal edit)
     */
    public function editData($id)
    {
        try {
            Log::info('editData called for ID: ' . $id);
            
            $peminjaman = PeminjamanRuangan::with(['user', 'ruangan'])
                ->findOrFail($id);

            $ruanganOptions = Ruangan::where('status', 'aktif')
                ->orWhere('status', 'tersedia')
                ->orderBy('kode_ruangan')
                ->get();

            $tanggalFormatted = $peminjaman->tanggal ? date('Y-m-d', strtotime($peminjaman->tanggal)) : '';
            $tanggalMulaiFormatted = $peminjaman->tanggal_mulai ? date('Y-m-d', strtotime($peminjaman->tanggal_mulai)) : '';
            $tanggalSelesaiFormatted = $peminjaman->tanggal_selesai ? date('Y-m-d', strtotime($peminjaman->tanggal_selesai)) : '';

            $jamMulaiFormatted = $peminjaman->jam_mulai;
            $jamSelesaiFormatted = $peminjaman->jam_selesai;

            // PERBAIKAN: Jika status ditolak atau dibatalkan, status_real_time dikirim null
            $statusRealTime = $peminjaman->status_real_time;
            if ($peminjaman->status == 'ditolak' || $peminjaman->status == 'dibatalkan') {
                $statusRealTime = null;
            }

            $data = [
                'id' => $peminjaman->id,
                'acara' => $peminjaman->acara ?? '',
                'hari' => $peminjaman->hari ?? '',
                'tanggal' => $tanggalFormatted,
                'tanggal_mulai' => $tanggalMulaiFormatted,
                'tanggal_selesai' => $tanggalSelesaiFormatted,
                'jam_mulai' => $jamMulaiFormatted,
                'jam_selesai' => $jamSelesaiFormatted,
                'jumlah_peserta' => $peminjaman->jumlah_peserta ?? 0,
                'keterangan' => $peminjaman->keterangan ?? '',
                'catatan' => $peminjaman->catatan ?? '',
                'status' => $peminjaman->status ?? 'menunggu',
                'status_real_time' => $statusRealTime,
                'alasan_penolakan' => $peminjaman->alasan_penolakan ?? '',
                'ruangan_id' => $peminjaman->ruangan_id,
                'ruanganOptions' => $ruanganOptions->map(function($item) {
                    return [
                        'id' => $item->id,
                        'kode_ruangan' => $item->kode_ruangan,
                        'nama_ruangan' => $item->nama_ruangan
                    ];
                })
            ];

            Log::info('editData success for ID: ' . $id, ['acara' => $data['acara']]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error editData: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * UPDATE PEMINJAMAN - Method untuk menyimpan perubahan data peminjaman
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Update called for ID: ' . $id);
            Log::info('Request data:', $request->all());

            // PERBAIKAN: status_real_time TIDAK WAJIB (nullable)
            $validated = $request->validate([
                'acara' => 'required|string|max:255',
                'ruangan_id' => 'required|exists:ruangan,id',
                'tanggal' => 'required|date',
                'hari' => 'required|string|max:50',
                'jam_mulai' => 'required',
                'jam_selesai' => 'required|after:jam_mulai',
                'jumlah_peserta' => 'nullable|integer|min:1',
                'status' => 'required|in:menunggu,disetujui,ditolak,dibatalkan,selesai',
                'status_real_time' => 'nullable|in:akan_datang,berlangsung,selesai,dibatalkan,menunggu,ditolak',
                'keterangan' => 'nullable|string',
                'catatan' => 'nullable|string',
                'alasan_penolakan' => 'nullable|string'
            ]);

            $peminjaman = PeminjamanRuangan::findOrFail($id);
            
            $oldData = [
                'acara' => $peminjaman->acara,
                'ruangan_id' => $peminjaman->ruangan_id,
                'tanggal' => $peminjaman->tanggal,
                'hari' => $peminjaman->hari,
                'jam_mulai' => $peminjaman->jam_mulai,
                'jam_selesai' => $peminjaman->jam_selesai,
                'jumlah_peserta' => $peminjaman->jumlah_peserta,
                'status' => $peminjaman->status,
                'status_real_time' => $peminjaman->status_real_time,
                'keterangan' => $peminjaman->keterangan,
                'catatan' => $peminjaman->catatan,
                'alasan_penolakan' => $peminjaman->alasan_penolakan
            ];
            
            $peminjaman->acara = $validated['acara'];
            $peminjaman->ruangan_id = $validated['ruangan_id'];
            $peminjaman->tanggal = $validated['tanggal'];
            $peminjaman->hari = $validated['hari'];
            $peminjaman->jam_mulai = $validated['jam_mulai'];
            $peminjaman->jam_selesai = $validated['jam_selesai'];
            $peminjaman->jumlah_peserta = $validated['jumlah_peserta'] ?? 0;
            $peminjaman->status = $validated['status'];
            $peminjaman->keterangan = $validated['keterangan'] ?? null;
            $peminjaman->catatan = $validated['catatan'] ?? null;
            
            // PERBAIKAN UTAMA: Update status real-time
            // Jika status ditolak atau dibatalkan, status_real_time di-set ke null
            if ($validated['status'] == 'ditolak' || $validated['status'] == 'dibatalkan') {
                $peminjaman->status_real_time = null;
            } else {
                // Jika status selain ditolak/dibatalkan, update status_real_time jika ada
                if (isset($validated['status_real_time'])) {
                    $peminjaman->status_real_time = $validated['status_real_time'];
                }
            }
            
            // Handle alasan penolakan
            if ($validated['status'] == 'ditolak' && isset($validated['alasan_penolakan'])) {
                $peminjaman->alasan_penolakan = $validated['alasan_penolakan'];
            } elseif ($validated['status'] != 'ditolak') {
                $peminjaman->alasan_penolakan = null;
            }
            
            $peminjaman->save();

            $newData = [
                'acara' => $peminjaman->acara,
                'ruangan_id' => $peminjaman->ruangan_id,
                'tanggal' => $peminjaman->tanggal,
                'hari' => $peminjaman->hari,
                'jam_mulai' => $peminjaman->jam_mulai,
                'jam_selesai' => $peminjaman->jam_selesai,
                'jumlah_peserta' => $peminjaman->jumlah_peserta,
                'status' => $peminjaman->status,
                'status_real_time' => $peminjaman->status_real_time,
                'keterangan' => $peminjaman->keterangan,
                'catatan' => $peminjaman->catatan,
                'alasan_penolakan' => $peminjaman->alasan_penolakan
            ];

            $this->logActivity(
                'UPDATE',
                'peminjaman_ruangan',
                $peminjaman->id,
                'Memperbarui data peminjaman ruangan',
                $oldData,
                $newData,
                $request
            );

            Log::info('Update success for ID: ' . $id);
            Log::info('Updated status_real_time: ' . ($peminjaman->status_real_time ?? 'null'));

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data peminjaman berhasil diperbarui',
                    'data' => $peminjaman
                ]);
            }

            return redirect()->route('admin.jadwal-peminjaman')
                ->with('success', 'Data peminjaman berhasil diperbarui');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error update: ' . json_encode($e->errors()));
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Error update peminjaman: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * API untuk mendapatkan detail peminjaman (untuk modal)
     */
    public function getDetail($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::with(['user', 'ruangan'])
                ->findOrFail($id);

            $statusLabel = $this->getStatusLabel($peminjaman->status ?? '');
            $statusColor = $this->getStatusColor($peminjaman->status ?? '');
            $statusColorClass = $this->getStatusColorClass($peminjaman->status ?? '');

            $tanggalFormatted = '-';
            if (!empty($peminjaman->tanggal)) {
                try {
                    $tanggalFormatted = date('d/m/Y', strtotime($peminjaman->tanggal));
                } catch (\Exception $e) {
                    $tanggalFormatted = (string) $peminjaman->tanggal;
                }
            }

            $tanggalMulaiFormatted = '-';
            if (!empty($peminjaman->tanggal_mulai)) {
                try {
                    $tanggalMulaiFormatted = date('d/m/Y', strtotime($peminjaman->tanggal_mulai));
                } catch (\Exception $e) {
                    $tanggalMulaiFormatted = (string) $peminjaman->tanggal_mulai;
                }
            }

            $tanggalSelesaiFormatted = '-';
            if (!empty($peminjaman->tanggal_selesai)) {
                try {
                    $tanggalSelesaiFormatted = date('d/m/Y', strtotime($peminjaman->tanggal_selesai));
                } catch (\Exception $e) {
                    $tanggalSelesaiFormatted = (string) $peminjaman->tanggal_selesai;
                }
            }

            $jamMulaiFormatted = '-';
            if (!empty($peminjaman->jam_mulai)) {
                if (is_string($peminjaman->jam_mulai)) {
                    $jamMulaiFormatted = substr($peminjaman->jam_mulai, 0, 5);
                } else {
                    try {
                        $jamMulaiFormatted = date('H:i', strtotime($peminjaman->jam_mulai));
                    } catch (\Exception $e) {
                        $jamMulaiFormatted = (string) $peminjaman->jam_mulai;
                    }
                }
            }

            $jamSelesaiFormatted = '-';
            if (!empty($peminjaman->jam_selesai)) {
                if (is_string($peminjaman->jam_selesai)) {
                    $jamSelesaiFormatted = substr($peminjaman->jam_selesai, 0, 5);
                } else {
                    try {
                        $jamSelesaiFormatted = date('H:i', strtotime($peminjaman->jam_selesai));
                    } catch (\Exception $e) {
                        $jamSelesaiFormatted = (string) $peminjaman->jam_selesai;
                    }
                }
            }

            $createdAtFormatted = '-';
            if (!empty($peminjaman->created_at)) {
                try {
                    $createdAtFormatted = date('d/m/Y H:i', strtotime($peminjaman->created_at));
                } catch (\Exception $e) {
                    $createdAtFormatted = (string) $peminjaman->created_at;
                }
            }

            $updatedAtFormatted = '-';
            if (!empty($peminjaman->updated_at)) {
                try {
                    $updatedAtFormatted = date('d/m/Y H:i', strtotime($peminjaman->updated_at));
                } catch (\Exception $e) {
                    $updatedAtFormatted = (string) $peminjaman->updated_at;
                }
            }

            // PERBAIKAN: Status real-time - jika ditolak/dibatalkan tampilkan "-"
            $statusRealTimeLabels = [
                'akan_datang' => 'Akan Datang',
                'berlangsung' => 'Berlangsung',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
                'menunggu' => 'Menunggu',
                'ditolak' => 'Ditolak'
            ];
            
            $statusRealTimeColors = [
                'akan_datang' => 'purple',
                'berlangsung' => 'orange',
                'selesai' => 'blue',
                'dibatalkan' => 'gray',
                'menunggu' => 'yellow',
                'ditolak' => 'red'
            ];
            
            $statusRealTimeIcons = [
                'akan_datang' => 'hourglass-start',
                'berlangsung' => 'play-circle',
                'selesai' => 'flag-checkered',
                'dibatalkan' => 'ban',
                'menunggu' => 'clock',
                'ditolak' => 'times-circle'
            ];
            
            $isRejectedOrCancelled = ($peminjaman->status == 'ditolak' || $peminjaman->status == 'dibatalkan');
            
            if ($isRejectedOrCancelled) {
                $statusRealTimeLabel = '-';
                $statusRealTimeColor = 'gray';
                $statusRealTimeIcon = 'minus-circle';
                $currentStatusRealTime = null;
            } else {
                $currentStatusRealTime = $peminjaman->status_real_time ?? 'menunggu';
                $statusRealTimeLabel = $statusRealTimeLabels[$currentStatusRealTime] ?? ucfirst($currentStatusRealTime);
                $statusRealTimeColor = $statusRealTimeColors[$currentStatusRealTime] ?? 'gray';
                $statusRealTimeIcon = $statusRealTimeIcons[$currentStatusRealTime] ?? 'info-circle';
            }

            $data = [
                'id' => $peminjaman->id,
                'acara' => $peminjaman->acara ?? '-',
                'hari' => $peminjaman->hari ?? '-',
                'tanggal' => $tanggalFormatted,
                'tanggal_mulai' => $tanggalMulaiFormatted,
                'tanggal_selesai' => $tanggalSelesaiFormatted,
                'jam_mulai' => $jamMulaiFormatted,
                'jam_selesai' => $jamSelesaiFormatted,
                'status' => $peminjaman->status ?? '-',
                'status_real_time' => $currentStatusRealTime,
                'status_real_time_label' => $statusRealTimeLabel,
                'status_real_time_color' => $statusRealTimeColor,
                'status_real_time_icon' => $statusRealTimeIcon,
                'status_label' => $statusLabel,
                'status_color' => $statusColor,
                'status_color_class' => $statusColorClass,
                'jumlah_peserta' => $peminjaman->jumlah_peserta ?? 0,
                'keterangan' => $peminjaman->keterangan ?? '-',
                'alasan_penolakan' => $peminjaman->alasan_penolakan ?? '-',
                'lampiran_surat' => $peminjaman->lampiran_surat ?? null,
                'created_at' => $createdAtFormatted,
                'updated_at' => $updatedAtFormatted,
                'user' => [
                    'name' => $peminjaman->user->name ?? $peminjaman->nama_pengaju ?? '-',
                    'nim_nip' => $peminjaman->user->nim_nip ?? $peminjaman->nim_nip ?? '-',
                    'jenis_pengaju' => $peminjaman->jenis_pengaju ?? $peminjaman->user->jenis_pengaju ?? '-',
                    'email' => $peminjaman->user->email ?? $peminjaman->email ?? '-',
                    'phone' => $peminjaman->user->phone ?? $peminjaman->no_telepon ?? '-',
                    'telepon' => $peminjaman->user->phone ?? $peminjaman->no_telepon ?? '-',
                    'fakultas' => $peminjaman->fakultas ?? '-',
                    'prodi' => $peminjaman->prodi ?? '-',
                    'foto' => $peminjaman->user->foto ?? null,
                ],
                'ruangan' => [
                    'kode_ruangan' => $peminjaman->ruangan->kode_ruangan ?? '-',
                    'nama_ruangan' => $peminjaman->ruangan->nama_ruangan ?? '-',
                    'kapasitas' => $peminjaman->ruangan->kapasitas ?? '-',
                    'lantai' => $peminjaman->ruangan->lantai ?? '-',
                    'fasilitas' => $peminjaman->ruangan->fasilitas ?? '-',
                ]
            ];

            Log::info('Detail Peminjaman ID: ' . $id, [
                'acara' => $data['acara'],
                'jam_mulai' => $jamMulaiFormatted,
                'jam_selesai' => $jamSelesaiFormatted,
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error getDetail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * API untuk update status peminjaman
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:disetujui,ditolak,selesai,dibatalkan',
                'alasan' => 'nullable|string|max:500'
            ]);

            $peminjaman = PeminjamanRuangan::findOrFail($id);
            
            $oldStatus = $peminjaman->status;
            $oldAlasan = $peminjaman->alasan_penolakan;

            $peminjaman->status = $request->status;
            
            // PERBAIKAN: Jika status ditolak atau dibatalkan, hapus status_real_time
            if ($request->status == 'ditolak' || $request->status == 'dibatalkan') {
                $peminjaman->status_real_time = null;
            }
            
            if ($request->status == 'ditolak' && $request->filled('alasan')) {
                $peminjaman->alasan_penolakan = $request->alasan;
            } else {
                $peminjaman->alasan_penolakan = null;
            }
            
            $peminjaman->save();

            $this->logActivity(
                'UPDATE_STATUS',
                'peminjaman_ruangan',
                $peminjaman->id,
                'Mengubah status peminjaman dari ' . ucfirst($oldStatus) . ' menjadi ' . ucfirst($request->status),
                ['status' => $oldStatus, 'alasan_penolakan' => $oldAlasan],
                ['status' => $peminjaman->status, 'alasan_penolakan' => $peminjaman->alasan_penolakan],
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diperbarui',
                'data' => [
                    'id' => $peminjaman->id,
                    'status' => $peminjaman->status,
                    'status_label' => $this->getStatusLabel($peminjaman->status)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updateStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk update status real-time (MANUAL)
     */
    public function updateStatusRealTime(Request $request, $id)
    {
        try {
            $request->validate([
                'status_real_time' => 'required|in:akan_datang,berlangsung,selesai,dibatalkan,menunggu,ditolak'
            ]);

            $peminjaman = PeminjamanRuangan::findOrFail($id);
            
            // PERBAIKAN: Cek apakah status peminjaman ditolak atau dibatalkan
            if ($peminjaman->status == 'ditolak' || $peminjaman->status == 'dibatalkan') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah status real-time karena peminjaman ' . $peminjaman->status
                ], 400);
            }
            
            $oldStatusRealTime = $peminjaman->status_real_time;
            $newStatusRealTime = $request->status_real_time;
            
            $peminjaman->status_real_time = $newStatusRealTime;
            $peminjaman->save();

            $this->logActivity(
                'UPDATE_STATUS_REAL_TIME',
                'peminjaman_ruangan',
                $peminjaman->id,
                'Mengubah status real-time dari "' . ($oldStatusRealTime ?? '-') . '" menjadi "' . $newStatusRealTime . '"',
                ['status_real_time' => $oldStatusRealTime],
                ['status_real_time' => $peminjaman->status_real_time],
                $request
            );

            $labels = [
                'akan_datang' => 'Akan Datang',
                'berlangsung' => 'Berlangsung',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
                'menunggu' => 'Menunggu',
                'ditolak' => 'Ditolak'
            ];
            
            $colors = [
                'akan_datang' => 'purple',
                'berlangsung' => 'orange',
                'selesai' => 'blue',
                'dibatalkan' => 'gray',
                'menunggu' => 'yellow',
                'ditolak' => 'red'
            ];
            
            $icons = [
                'akan_datang' => 'hourglass-start',
                'berlangsung' => 'play-circle',
                'selesai' => 'flag-checkered',
                'dibatalkan' => 'ban',
                'menunggu' => 'clock',
                'ditolak' => 'times-circle'
            ];

            return response()->json([
                'success' => true,
                'message' => 'Status real-time berhasil diperbarui',
                'data' => [
                    'id' => $peminjaman->id,
                    'status_real_time' => $peminjaman->status_real_time,
                    'status_real_time_label' => $labels[$newStatusRealTime] ?? $newStatusRealTime,
                    'status_real_time_color' => $colors[$newStatusRealTime] ?? 'gray',
                    'status_real_time_icon' => $icons[$newStatusRealTime] ?? 'info-circle'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updateStatusRealTime: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status real-time: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk delete peminjaman
     */
    public function delete($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::findOrFail($id);
            $acara = $peminjaman->acara;
            
            // Hapus file lampiran jika ada
            if ($peminjaman->lampiran_surat && Storage::disk('public')->exists($peminjaman->lampiran_surat)) {
                Storage::disk('public')->delete($peminjaman->lampiran_surat);
            }
            
            $deletedData = $peminjaman->toArray();
            
            $peminjaman->delete();

            $this->logActivity(
                'DELETE',
                'peminjaman_ruangan',
                $id,
                'Menghapus data peminjaman ruangan: ' . $acara,
                $deletedData,
                null,
                request()
            );

            return response()->json([
                'success' => true,
                'message' => 'Peminjaman "' . $acara . '" berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error delete: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus peminjaman: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk mendapatkan catatan internal peminjaman
     */
    public function getCatatan($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $peminjaman->id,
                    'catatan' => $peminjaman->catatan ?? ''
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getCatatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil catatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk update catatan internal peminjaman
     */
    public function updateCatatan(Request $request, $id)
    {
        try {
            $request->validate([
                'catatan' => 'nullable|string'
            ]);

            $peminjaman = PeminjamanRuangan::findOrFail($id);
            
            $oldCatatan = $peminjaman->catatan;
            
            $peminjaman->catatan = $request->catatan;
            $peminjaman->save();

            $this->logActivity(
                'UPDATE_CATATAN',
                'peminjaman_ruangan',
                $peminjaman->id,
                'Mengupdate catatan internal peminjaman',
                ['catatan' => $oldCatatan],
                ['catatan' => $peminjaman->catatan],
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil diperbarui',
                'data' => [
                    'id' => $peminjaman->id,
                    'catatan' => $peminjaman->catatan
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updateCatatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui catatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk delete catatan internal peminjaman
     */
    public function deleteCatatan($id)
    {
        try {
            $peminjaman = PeminjamanRuangan::findOrFail($id);
            
            $oldCatatan = $peminjaman->catatan;
            
            $peminjaman->catatan = null;
            $peminjaman->save();

            $this->logActivity(
                'DELETE_CATATAN',
                'peminjaman_ruangan',
                $peminjaman->id,
                'Menghapus catatan internal peminjaman',
                ['catatan' => $oldCatatan],
                ['catatan' => null],
                request()
            );

            return response()->json([
                'success' => true,
                'message' => 'Catatan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleteCatatan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus catatan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export data ke CSV
     */
    public function exportExcel(Request $request)
    {
        try {
            $this->logActivity(
                'EXPORT',
                'peminjaman_ruangan',
                null,
                'Mengekspor data peminjaman ke Excel',
                null,
                ['filter' => $request->all()],
                $request
            );

            $query = PeminjamanRuangan::with(['user', 'ruangan']);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('ruangan_id') && $request->ruangan_id !== 'all') {
                $query->where('ruangan_id', $request->ruangan_id);
            }

            if ($request->has('tanggal')) {
                $query->whereDate('tanggal', $request->tanggal);
            }

            $data = $query->orderBy('tanggal', 'desc')
                         ->orderBy('jam_mulai', 'desc')
                         ->get();

            $exportData = [];
            $exportData[] = ['LAPORAN JADWAL PEMINJAMAN RUANGAN'];
            $exportData[] = ['Dicetak pada: ' . date('d/m/Y H:i:s')];
            $exportData[] = [''];
            
            $headers = [
                'No', 'Tanggal', 'Jam', 'Ruangan', 'Acara',
                'Peminjam', 'NIM/NIP', 'Jenis', 'Status', 'Status Real-Time', 'Jumlah Peserta', 'Keterangan'
            ];
            $exportData[] = $headers;

            $statusRealTimeLabels = [
                'akan_datang' => 'Akan Datang',
                'berlangsung' => 'Berlangsung',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
                'menunggu' => 'Menunggu',
                'ditolak' => 'Ditolak'
            ];

            $no = 1;
            foreach ($data as $item) {
                // PERBAIKAN: Jika ditolak atau dibatalkan, status real-time = "-"
                $statusRealTimeDisplay = '-';
                if ($item->status != 'ditolak' && $item->status != 'dibatalkan') {
                    $statusRealTimeDisplay = $statusRealTimeLabels[$item->status_real_time] ?? ucfirst($item->status_real_time ?? 'menunggu');
                }
                
                $exportData[] = [
                    $no++,
                    $this->formatDateSimple($item->tanggal),
                    $this->formatTimeSimple($item->jam_mulai) . ' - ' . $this->formatTimeSimple($item->jam_selesai),
                    ($item->ruangan->kode_ruangan ?? '-') . ' - ' . ($item->ruangan->nama_ruangan ?? '-'),
                    $item->acara,
                    $item->nama_pengaju ?? $item->user->name ?? '-',
                    $item->nim_nip ?? $item->user->nim_nip ?? '-',
                    ucfirst($item->jenis_pengaju ?? '-'),
                    $this->getStatusLabel($item->status),
                    $statusRealTimeDisplay,
                    $item->jumlah_peserta ?? 0,
                    $item->keterangan ?? '-'
                ];
            }

            $filename = 'jadwal-peminjaman-' . date('Y-m-d-H-i-s') . '.csv';
            
            return response()->streamDownload(function() use ($exportData) {
                $output = fopen('php://output', 'w');
                foreach ($exportData as $row) {
                    fputcsv($output, $row);
                }
                fclose($output);
            }, $filename);

        } catch (\Exception $e) {
            Log::error('Error exportExcel: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat export: ' . $e->getMessage());
        }
    }

    /**
     * UPDATE ALL STATUS REAL-TIME - DINONAKTIFKAN
     */
    public function updateAllStatusRealTime()
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur update otomatis status real-time dinonaktifkan. Silakan update manual setiap peminjaman.'
        ], 403);
    }

    // ==================== HELPER FUNCTIONS ====================

    private function logActivity($action, $model, $modelId, $description, $oldData = null, $newData = null, $request = null)
    {
        try {
            $userId = null;
            $ipAddress = null;
            $userAgent = null;
            
            if ($request) {
                $ipAddress = $request->ip();
                $userAgent = $request->userAgent();
            } else {
                $ipAddress = request()->ip();
                $userAgent = request()->userAgent();
            }
            
            if (Auth::check()) {
                $userId = Auth::id();
            }
            
            $tipe = $this->mapActionToTipe($action);
            
            $aktivitas = $action . ' - ' . $model;
            if ($modelId) {
                $aktivitas .= ' (ID: ' . $modelId . ')';
            }
            
            $fullDescription = $description;
            if ($oldData || $newData) {
                $fullDescription .= "\n\n📋 DETAIL PERUBAHAN:";
                if ($oldData) {
                    $fullDescription .= "\n• Data Lama: " . json_encode($oldData, JSON_PRETTY_PRINT);
                }
                if ($newData) {
                    $fullDescription .= "\n• Data Baru: " . json_encode($newData, JSON_PRETTY_PRINT);
                }
            }
            
            LogActivity::create([
                'user_id' => $userId,
                'tipe' => $tipe,
                'aktivitas' => $aktivitas,
                'deskripsi' => $fullDescription,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]);
            
            Log::info('Activity logged: ' . $action . ' - ' . $description);
            
        } catch (\Exception $e) {
            Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }

    private function mapActionToTipe($action)
    {
        $mapping = [
            'CREATE' => 'create',
            'UPDATE' => 'update',
            'UPDATE_STATUS' => 'approve',
            'UPDATE_STATUS_REAL_TIME' => 'update',
            'DELETE' => 'delete',
            'DELETE_CATATAN' => 'delete',
            'UPDATE_CATATAN' => 'update',
            'VIEW' => 'login',
            'EXPORT' => 'create',
            'DOWNLOAD_SURAT' => 'create',
        ];
        
        return $mapping[$action] ?? 'update';
    }

    private function formatDateSimple($date)
    {
        if (empty($date)) {
            return '-';
        }
        
        try {
            return date('d/m/Y', strtotime($date));
        } catch (\Exception $e) {
            return (string) $date;
        }
    }

    private function formatTimeSimple($time)
    {
        if (empty($time)) {
            return '-';
        }
        
        if (is_string($time)) {
            return substr($time, 0, 5);
        }
        
        try {
            return date('H:i', strtotime($time));
        } catch (\Exception $e) {
            return (string) $time;
        }
    }

    private function getStatusColorClass($status)
    {
        switch ($status) {
            case 'disetujui':
                return 'green';
            case 'menunggu':
                return 'yellow';
            case 'ditolak':
                return 'red';
            case 'selesai':
                return 'blue';
            case 'dibatalkan':
                return 'gray';
            default:
                return 'gray';
        }
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
        ];
        
        return $labels[$status] ?? $status;
    }

    private function getStatusColor($status)
    {
        $colors = [
            'disetujui' => '#10B981',
            'menunggu' => '#F59E0B',
            'ditolak' => '#EF4444',
            'selesai' => '#3B82F6',
            'dibatalkan' => '#6B7280',
        ];
        
        return $colors[$status] ?? '#6B7280';
    }
}