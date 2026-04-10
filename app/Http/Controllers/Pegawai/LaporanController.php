<?php

namespace App\Http\Controllers\Pegawai;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman laporan peminjaman ruangan
     */
    public function index(Request $request)
    {
        try {
            // Set default tanggal (bulan berjalan)
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $ruanganId = $request->input('ruangan_id', 'all');
            
            // Validasi tanggal
            if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
                return redirect()->back()->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai!');
            }
            
            // Get semua ruangan untuk dropdown
            $ruangan = Ruangan::orderBy('nama_ruangan')->get();
            
            // Query data peminjaman dengan relasi
            $query = PeminjamanRuangan::with(['ruangan', 'user']);
            
            // Filter berdasarkan tanggal
            if ($startDate && $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                      ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                      ->orWhere(function($query) use ($startDate, $endDate) {
                          $query->where('tanggal_mulai', '<=', $startDate)
                                ->where('tanggal_selesai', '>=', $endDate);
                      });
                });
            }
            
            // Filter berdasarkan ruangan
            if ($ruanganId !== 'all') {
                $query->where('ruangan_id', $ruanganId);
            }
            
            // Ambil data peminjaman
            $peminjaman = $query->orderBy('tanggal_mulai', 'desc')
                               ->orderBy('jam_mulai', 'desc')
                               ->get();
            
            // Hitung statistik
            $totalPeminjaman = $peminjaman->count();
            
            // Hitung status counts dengan mapping yang benar
            $statusCounts = [
                'menunggu' => $peminjaman->whereIn('status', ['menunggu', 'pending'])->count(),
                'disetujui' => $peminjaman->whereIn('status', ['disetujui', 'approved'])->count(),
                'ditolak' => $peminjaman->whereIn('status', ['ditolak', 'rejected'])->count(),
                'selesai' => $peminjaman->whereIn('status_real_time', ['selesai', 'completed'])->count(),
                'dibatalkan' => $peminjaman->whereIn('status', ['dibatalkan', 'cancelled'])->count(),
            ];
            
            // Ruangan paling sering dipinjam
            $ruanganTerbanyak = $this->getRuanganTerbanyak($peminjaman);
            
            // Format tanggal untuk display
            $formattedStartDate = Carbon::parse($startDate)->format('d M Y');
            $formattedEndDate = Carbon::parse($endDate)->format('d M Y');
            
            // Catat aktivitas melihat laporan
            $this->logActivity(
                'VIEW_LAPORAN',
                'laporan',
                null,
                'Mengakses halaman laporan peminjaman ruangan',
                null,
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'ruangan_id' => $ruanganId,
                    'total_data' => $totalPeminjaman
                ],
                $request
            );
            
            return view('pegawai.laporan.index', compact(
                'startDate',
                'endDate',
                'formattedStartDate',
                'formattedEndDate',
                'ruanganId',
                'ruangan',
                'peminjaman',
                'totalPeminjaman',
                'statusCounts',
                'ruanganTerbanyak'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error in LaporanController@index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    /**
     * Mendapatkan nama ruangan yang paling sering dipinjam
     */
    private function getRuanganTerbanyak($peminjaman)
    {
        if ($peminjaman->isEmpty()) {
            return '-';
        }
        
        $ruanganCounts = $peminjaman->groupBy('ruangan_id')->map->count();
        $topRuanganId = $ruanganCounts->sortDesc()->keys()->first();
        
        $topRuangan = $peminjaman->firstWhere('ruangan_id', $topRuanganId);
        
        if ($topRuangan && $topRuangan->ruangan) {
            return $topRuangan->ruangan->nama_ruangan;
        }
        
        return '-';
    }
    
    /**
     * Export laporan ke PDF
     */
    public function exportPDF(Request $request)
    {
        try {
            // Validasi request
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            
            // Ambil parameter
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $ruanganId = $request->input('ruangan_id', 'all');
            
            // Ambil data laporan
            $data = $this->getLaporanData($startDate, $endDate, $ruanganId);
            
            // Catat aktivitas export PDF
            $this->logActivity(
                'EXPORT_PDF',
                'laporan',
                null,
                'Mengekspor laporan peminjaman ruangan ke PDF',
                null,
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'ruangan_id' => $ruanganId,
                    'total_data' => $data['totalPeminjaman'],
                    'format' => 'PDF'
                ],
                $request
            );
            
            // Load view untuk PDF
            $pdf = Pdf::loadView('pegawai.laporan.pdf', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'margin_top' => 20,
                    'margin_bottom' => 20,
                    'margin_left' => 15,
                    'margin_right' => 15
                ]);
            
            // Nama file
            $fileName = 'laporan_peminjaman_' . Carbon::now()->format('Ymd_His') . '.pdf';
            
            // Download PDF
            return $pdf->download($fileName);
            
        } catch (\Exception $e) {
            \Log::error('Error in LaporanController@exportPDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Export laporan ke Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Validasi request
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);
            
            // Ambil parameter
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $ruanganId = $request->input('ruangan_id', 'all');
            
            // Ambil data laporan
            $data = $this->getLaporanData($startDate, $endDate, $ruanganId);
            
            // Catat aktivitas export Excel
            $this->logActivity(
                'EXPORT_EXCEL',
                'laporan',
                null,
                'Mengekspor laporan peminjaman ruangan ke Excel',
                null,
                [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'ruangan_id' => $ruanganId,
                    'total_data' => $data['totalPeminjaman'],
                    'format' => 'Excel'
                ],
                $request
            );
            
            // Buat class anonymous untuk export Excel
            $export = new class($data) implements \Maatwebsite\Excel\Concerns\FromView, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithEvents {
                private $data;
                
                public function __construct($data)
                {
                    $this->data = $data;
                }
                
                public function view(): \Illuminate\Contracts\View\View
                {
                    return view('pegawai.laporan.excel', $this->data);
                }
                
                public function title(): string
                {
                    return 'Laporan Peminjaman Ruangan';
                }
                
                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function(\Maatwebsite\Excel\Events\AfterSheet $event) {
                            $sheet = $event->sheet->getDelegate();
                            
                            // Style header
                            $sheet->getStyle('A1:H1')->applyFromArray([
                                'font' => ['bold' => true, 'size' => 12],
                                'fill' => [
                                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => '4F81BD']
                                ],
                                'font' => ['color' => ['rgb' => 'FFFFFF']]
                            ]);
                            
                            // Auto size columns
                            foreach (range('A', 'H') as $column) {
                                $sheet->getColumnDimension($column)->setAutoSize(true);
                            }
                        }
                    ];
                }
            };
            
            // Download Excel
            return Excel::download(
                $export,
                'laporan_peminjaman_' . Carbon::now()->format('Ymd_His') . '.xlsx'
            );
            
        } catch (\Exception $e) {
            \Log::error('Error in LaporanController@exportExcel: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal export Excel: ' . $e->getMessage());
        }
    }
    
    /**
     * Mengambil data untuk dashboard (AJAX)
     */
    public function dashboardData(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            
            $data = $this->getStatistikData($startDate, $endDate);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Mendapatkan statistik ruangan
     */
    public function getRuanganStats(Request $request)
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
            
            $stats = Ruangan::withCount(['peminjaman' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_mulai', [$startDate, $endDate]);
            }])->get();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Method helper untuk mengambil data laporan
     */
    private function getLaporanData($startDate, $endDate, $ruanganId = 'all')
    {
        // Query data peminjaman
        $query = PeminjamanRuangan::with(['ruangan', 'user'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('tanggal_mulai', [$startDate, $endDate])
                  ->orWhereBetween('tanggal_selesai', [$startDate, $endDate])
                  ->orWhere(function($query) use ($startDate, $endDate) {
                      $query->where('tanggal_mulai', '<=', $startDate)
                            ->where('tanggal_selesai', '>=', $endDate);
                  });
            });
        
        if ($ruanganId !== 'all') {
            $query->where('ruangan_id', $ruanganId);
        }
        
        $peminjaman = $query->orderBy('tanggal_mulai', 'desc')
                           ->orderBy('jam_mulai', 'desc')
                           ->get();
        
        // Hitung statistik
        $totalPeminjaman = $peminjaman->count();
        $statusCounts = [
            'menunggu' => $peminjaman->whereIn('status', ['menunggu', 'pending'])->count(),
            'disetujui' => $peminjaman->whereIn('status', ['disetujui', 'approved'])->count(),
            'ditolak' => $peminjaman->whereIn('status', ['ditolak', 'rejected'])->count(),
            'selesai' => $peminjaman->whereIn('status_real_time', ['selesai', 'completed'])->count(),
            'dibatalkan' => $peminjaman->whereIn('status', ['dibatalkan', 'cancelled'])->count(),
        ];
        
        // Ambil informasi ruangan yang dipilih
        $selectedRuangan = null;
        if ($ruanganId !== 'all') {
            $selectedRuangan = Ruangan::find($ruanganId);
        }
        
        // Ruangan paling sering dipinjam
        $ruanganTerbanyak = $this->getRuanganTerbanyak($peminjaman);
        
        // Format tanggal
        $formattedStartDate = Carbon::parse($startDate)->format('d M Y');
        $formattedEndDate = Carbon::parse($endDate)->format('d M Y');
        
        return [
            'peminjaman' => $peminjaman,
            'totalPeminjaman' => $totalPeminjaman,
            'statusCounts' => $statusCounts,
            'ruanganTerbanyak' => $ruanganTerbanyak,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'ruanganId' => $ruanganId,
            'selectedRuangan' => $selectedRuangan,
            'formattedStartDate' => $formattedStartDate,
            'formattedEndDate' => $formattedEndDate,
            'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
            'generatedBy' => auth()->user()->name ?? 'Pegawai',
        ];
    }
    
    /**
     * Method helper untuk mengambil data statistik
     */
    private function getStatistikData($startDate, $endDate)
    {
        // Statistik per hari
        $dailyStats = PeminjamanRuangan::select(
                \DB::raw('DATE(tanggal_mulai) as date'),
                \DB::raw('COUNT(*) as total'),
                \DB::raw('SUM(CASE WHEN status IN ("disetujui", "approved") THEN 1 ELSE 0 END) as approved'),
                \DB::raw('SUM(CASE WHEN status IN ("menunggu", "pending") THEN 1 ELSE 0 END) as pending')
            )
            ->whereBetween('tanggal_mulai', [$startDate, $endDate])
            ->groupBy(\DB::raw('DATE(tanggal_mulai)'))
            ->orderBy('date')
            ->get();
        
        // Statistik per ruangan
        $ruanganStats = Ruangan::withCount(['peminjaman' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_mulai', [$startDate, $endDate]);
            }])
            ->having('peminjaman_count', '>', 0)
            ->orderBy('peminjaman_count', 'desc')
            ->get();
        
        return [
            'daily' => $dailyStats,
            'ruangan' => $ruanganStats,
            'total' => $dailyStats->sum('total'),
        ];
    }
    
    // ==================== ACTIVITY LOG HELPER FUNCTIONS ====================

    /**
     * Helper function untuk mencatat aktivitas ke LogActivity
     * SESUAI DENGAN STRUKTUR TABEL: id, user_id, tipe, aktivitas, deskripsi, ip_address, user_agent, created_at, updated_at
     */
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
            
            // Map action ke tipe (enum: login, logout, create, update, delete, approve, reject)
            $tipe = $this->mapActionToTipe($action);
            
            // Format aktivitas
            $aktivitas = $action . ' - ' . $model;
            if ($modelId) {
                $aktivitas .= ' (ID: ' . $modelId . ')';
            }
            
            // Format deskripsi lengkap dengan detail perubahan
            $fullDescription = $description;
            if ($oldData || $newData) {
                $fullDescription .= "\n\n📋 DETAIL:";
                if ($oldData) {
                    $fullDescription .= "\n• Data Lama: " . json_encode($oldData, JSON_PRETTY_PRINT);
                }
                if ($newData) {
                    $fullDescription .= "\n• Data Baru: " . json_encode($newData, JSON_PRETTY_PRINT);
                }
            }
            
            // Simpan ke LogActivity (HANYA menggunakan kolom yang ada di tabel)
            LogActivity::create([
                'user_id' => $userId,
                'tipe' => $tipe,
                'aktivitas' => $aktivitas,
                'deskripsi' => $fullDescription,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]);
            
            \Log::info('Activity logged: ' . $action . ' - ' . $description);
            
        } catch (\Exception $e) {
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }
    
    /**
     * Map action ke tipe yang sesuai dengan enum di tabel log_activities
     * Enum values: login, logout, create, update, delete, approve, reject
     */
    private function mapActionToTipe($action)
    {
        $mapping = [
            'VIEW_LAPORAN' => 'login',
            'EXPORT_PDF' => 'create',
            'EXPORT_EXCEL' => 'create',
            'CREATE' => 'create',
            'UPDATE' => 'update',
            'UPDATE_STATUS' => 'approve',
            'DELETE' => 'delete',
        ];
        
        return $mapping[$action] ?? 'create';
    }
}