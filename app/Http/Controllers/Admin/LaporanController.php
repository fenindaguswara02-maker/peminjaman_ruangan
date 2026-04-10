<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeminjamanRuangan;
use App\Models\Ruangan;
use App\Models\User;
use App\Models\LogActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    /**
     * Display halaman laporan dengan filter
     */
    public function index(Request $request)
    {
        // ============ AMBIL SEMUA PARAMETER FILTER ============
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $jenisPengaju = $request->get('jenis_pengaju', '');
        $status = $request->get('status', '');
        $ruanganId = $request->get('ruangan_id', '');
        $periodeAnalisis = $request->get('periode_analisis', 'harian');
        $tipeGrafik = $request->get('tipe_grafik', 'bar');
        $activeTab = $request->get('tab', 'grafik');
        
        // Filter Bulanan Spesifik
        $filterBulanan = $request->get('filter_bulanan', '0');
        $bulan = $request->get('bulan', Carbon::now()->format('m'));
        $tahun = $request->get('tahun', Carbon::now()->format('Y'));

        // ============ BUILD QUERY DENGAN FILTER ============
        $query = PeminjamanRuangan::with(['ruangan', 'user']);

        // PRIORITAS 1: Filter Bulanan Spesifik
        if ($filterBulanan == '1') {
            $query->whereYear('tanggal', $tahun)
                  ->whereMonth('tanggal', $bulan);
            
            // Update tanggal untuk tampilan
            $startDate = Carbon::createFromDate($tahun, $bulan, 1)->format('Y-m-d');
            $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
        } 
        // PRIORITAS 2: Filter Range Tanggal
        else {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        }

        // FILTER TAMBAHAN
        if (!empty($jenisPengaju)) {
            $query->where('jenis_pengaju', $jenisPengaju);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($ruanganId)) {
            $query->where('ruangan_id', $ruanganId);
        }

        // ============ GET DATA ============
        $peminjaman = $query->orderBy('tanggal', 'desc')
                           ->orderBy('jam_mulai', 'asc')
                           ->get();

        $totalPeminjaman = $peminjaman->count();

        // ============ STATISTIK ============
        $totalDisetujui = $peminjaman->where('status', 'disetujui')->count();
        $totalDitolak = $peminjaman->where('status', 'ditolak')->count();
        $totalMenunggu = $peminjaman->where('status', 'menunggu')->count();
        $totalDibatalkan = $peminjaman->where('status', 'dibatalkan')->count();
        
        // PERBAIKAN: Ambil status selesai dari status_real_time
        $totalSelesai = $peminjaman->where('status_real_time', 'selesai')->count();

        // Statistik per jenis pengaju
        $statistikJenis = [
            'mahasiswa' => $peminjaman->where('jenis_pengaju', 'mahasiswa')->count(),
            'dosen' => $peminjaman->where('jenis_pengaju', 'dosen')->count(),
            'staff' => $peminjaman->where('jenis_pengaju', 'staff')->count(),
            'tamu' => $peminjaman->where('jenis_pengaju', 'tamu')->count(),
        ];

        // Statistik ruangan
        $statistikRuangan = Ruangan::withCount(['peminjaman' => function($q) use ($filterBulanan, $bulan, $tahun, $startDate, $endDate, $jenisPengaju, $status, $ruanganId) {
            if ($filterBulanan == '1') {
                $q->whereYear('tanggal', $tahun)
                  ->whereMonth('tanggal', $bulan);
            } else {
                $q->whereBetween('tanggal', [$startDate, $endDate]);
            }
            
            if ($jenisPengaju) $q->where('jenis_pengaju', $jenisPengaju);
            if ($status) $q->where('status', $status);
            if ($ruanganId) $q->where('ruangan_id', $ruanganId);
        }])->orderBy('peminjaman_count', 'desc')->get();

        // Status distribution
        $statusDistribution = [];
        if ($totalDisetujui > 0) $statusDistribution['disetujui'] = ['label' => 'Disetujui', 'count' => $totalDisetujui, 'percentage' => $totalPeminjaman > 0 ? round(($totalDisetujui / $totalPeminjaman) * 100, 1) : 0, 'color' => '#10B981'];
        if ($totalDitolak > 0) $statusDistribution['ditolak'] = ['label' => 'Ditolak', 'count' => $totalDitolak, 'percentage' => $totalPeminjaman > 0 ? round(($totalDitolak / $totalPeminjaman) * 100, 1) : 0, 'color' => '#EF4444'];
        if ($totalMenunggu > 0) $statusDistribution['menunggu'] = ['label' => 'Menunggu', 'count' => $totalMenunggu, 'percentage' => $totalPeminjaman > 0 ? round(($totalMenunggu / $totalPeminjaman) * 100, 1) : 0, 'color' => '#F59E0B'];
        if ($totalDibatalkan > 0) $statusDistribution['dibatalkan'] = ['label' => 'Dibatalkan', 'count' => $totalDibatalkan, 'percentage' => $totalPeminjaman > 0 ? round(($totalDibatalkan / $totalPeminjaman) * 100, 1) : 0, 'color' => '#6B7280'];
        if ($totalSelesai > 0) $statusDistribution['selesai'] = ['label' => 'Selesai', 'count' => $totalSelesai, 'percentage' => $totalPeminjaman > 0 ? round(($totalSelesai / $totalPeminjaman) * 100, 1) : 0, 'color' => '#3B82F6'];

        // ============ ANALISIS ============
        $analisisHarian = $this->getAnalisisHarian($peminjaman, $startDate, $endDate);
        $hariTerpopuler = $this->getHariTerpopuler($peminjaman);
        $jamPuncak = $this->getJamPuncak($peminjaman);
        $jumlahHari = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $rataRataHarian = $totalPeminjaman > 0 ? round($totalPeminjaman / $jumlahHari, 1) : 0;

        $analisisMingguan = $this->getAnalisisMingguan($peminjaman, $startDate, $endDate);
        $mingguTerbaik = $this->getMingguTerbaik($analisisMingguan);
        $rataRataMingguan = count($analisisMingguan) > 0 ? round($totalPeminjaman / count($analisisMingguan), 1) : 0;
        $totalMinggu = count($analisisMingguan);
        $pertumbuhanMingguan = $this->getPertumbuhanMingguan($analisisMingguan);

        $analisisBulanan = $this->getAnalisisBulanan($peminjaman);
        $bulanTerbaik = $this->getBulanTerbaik($analisisBulanan);
        $rataRataBulanan = count($analisisBulanan) > 0 ? round(array_sum(array_column($analisisBulanan, 'total')) / count($analisisBulanan), 1) : 0;
        $totalBulan = count($analisisBulanan);
        $pertumbuhanBulanan = $this->getPertumbuhanBulanan($analisisBulanan);

        $analisisTahunan = $this->getAnalisisTahunan($peminjaman);
        $tahunSaatIni = date('Y');
        $totalTahunIni = $peminjaman->filter(function($item) use ($tahunSaatIni) {
            return Carbon::parse($item->tanggal)->year == $tahunSaatIni;
        })->count();
        $tahunTerbaik = $this->getTahunTerbaik($analisisTahunan);
        $pertumbuhanTahunan = $this->getPertumbuhanTahunan($analisisTahunan);
        $rataRataTahunan = count($analisisTahunan) > 0 ? round(array_sum(array_column($analisisTahunan, 'total')) / count($analisisTahunan), 1) : 0;

        // ============ DATA CHART ============
        $trendData = $this->getTrendData($peminjaman, $periodeAnalisis, $startDate, $endDate);
        $weeklyData = $this->getWeeklyChartData($analisisMingguan);
        $monthlyData = $this->getMonthlyChartData($analisisBulanan);
        $yearlyData = $this->getYearlyChartData($analisisTahunan);
        $hourlyData = $this->getHourlyData($peminjaman);
        $facultyData = $this->getFacultyData($peminjaman);

        // ============ DATA UNTUK DROPDOWN ============
        $ruangan = Ruangan::orderBy('kode_ruangan')->get();
        $jenisOptions = [
            'mahasiswa' => 'Mahasiswa',
            'dosen' => 'Dosen',
            'staff' => 'Staff',
            'tamu' => 'Tamu'
        ];
        $statusOptions = [
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
        ];

        // Rating (default)
        $ratingRataRata = 4.5;
        
        // TAMBAHKAN DATA USER UNTUK FILTER
        $users = User::orderBy('name')->get();

        return view('admin.laporan.index', compact(
            'startDate',
            'endDate',
            'jenisPengaju',
            'status',
            'ruanganId',
            'periodeAnalisis',
            'tipeGrafik',
            'activeTab',
            'filterBulanan',
            'bulan',
            'tahun',
            'peminjaman',
            'totalPeminjaman',
            'totalDisetujui',
            'totalDitolak',
            'totalMenunggu',
            'totalDibatalkan',
            'totalSelesai',
            'statistikJenis',
            'statistikRuangan',
            'statusDistribution',
            'analisisHarian',
            'hariTerpopuler',
            'jamPuncak',
            'rataRataHarian',
            'analisisMingguan',
            'mingguTerbaik',
            'rataRataMingguan',
            'totalMinggu',
            'pertumbuhanMingguan',
            'analisisBulanan',
            'bulanTerbaik',
            'rataRataBulanan',
            'totalBulan',
            'pertumbuhanBulanan',
            'analisisTahunan',
            'tahunSaatIni',
            'totalTahunIni',
            'tahunTerbaik',
            'pertumbuhanTahunan',
            'rataRataTahunan',
            'trendData',
            'weeklyData',
            'monthlyData',
            'yearlyData',
            'hourlyData',
            'facultyData',
            'ruangan',
            'jenisOptions',
            'statusOptions',
            'ratingRataRata',
            'users'
        ));
    }

    /**
     * CETAK PDF - DENGAN FILTER LENGKAP DAN LOG ACTIVITY
     */
    public function cetakPdf(Request $request)
    {
        try {
            // ============ AMBIL SEMUA PARAMETER FILTER ============
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $jenisPengaju = $request->get('jenis_pengaju', '');
            $status = $request->get('status', '');
            $ruanganId = $request->get('ruangan_id', '');
            $filterBulanan = $request->get('filter_bulanan', '0');
            $bulan = $request->get('bulan', Carbon::now()->format('m'));
            $tahun = $request->get('tahun', Carbon::now()->format('Y'));

            // ============ VALIDASI TANGGAL ============
            if (!$startDate && $filterBulanan != '1') {
                $startDate = Carbon::now()->subMonth()->format('Y-m-d');
            }
            if (!$endDate && $filterBulanan != '1') {
                $endDate = Carbon::now()->format('Y-m-d');
            }

            // ============ BUILD QUERY ============
            $query = PeminjamanRuangan::with(['ruangan', 'user']);

            // PRIORITAS 1: FILTER BULANAN SPESIFIK
            if ($filterBulanan == '1') {
                $query->whereYear('tanggal', $tahun)
                      ->whereMonth('tanggal', $bulan);
                $startDate = Carbon::createFromDate($tahun, $bulan, 1)->format('Y-m-d');
                $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            } 
            // PRIORITAS 2: FILTER RANGE TANGGAL
            else {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            }

            // FILTER TAMBAHAN
            if (!empty($jenisPengaju)) {
                $query->where('jenis_pengaju', $jenisPengaju);
            }
            if (!empty($status)) {
                $query->where('status', $status);
            }
            if (!empty($ruanganId)) {
                $query->where('ruangan_id', $ruanganId);
            }

            // ============ GET DATA ============
            $peminjaman = $query->orderBy('tanggal', 'desc')
                               ->orderBy('jam_mulai', 'asc')
                               ->get();

            // ============ HITUNG STATISTIK UNTUK PDF ============
            $totalPeminjaman = $peminjaman->count();
            $totalDisetujui = $peminjaman->where('status', 'disetujui')->count();
            $totalDitolak = $peminjaman->where('status', 'ditolak')->count();
            $totalMenunggu = $peminjaman->where('status', 'menunggu')->count();
            $totalDibatalkan = $peminjaman->where('status', 'dibatalkan')->count();
            $totalSelesai = $peminjaman->where('status_real_time', 'selesai')->count();

            // Hitung rata-rata per hari
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $days = $start->diffInDays($end) + 1;
            $avgPerDay = $totalPeminjaman > 0 ? round($totalPeminjaman / $days, 1) : 0;
            
            // Persentase disetujui
            $percentageDisetujui = $totalPeminjaman > 0 ? round(($totalDisetujui / $totalPeminjaman) * 100, 1) : 0;

            // Nama ruangan
            $ruanganNama = 'Semua Ruangan';
            if ($ruanganId) {
                $ruangan = Ruangan::find($ruanganId);
                $ruanganNama = $ruangan ? $ruangan->kode_ruangan . ' - ' . $ruangan->nama_ruangan : 'Semua Ruangan';
            }

            // Label untuk filter
            $jenisOptions = [
                'mahasiswa' => 'Mahasiswa',
                'dosen' => 'Dosen',
                'staff' => 'Staff',
                'tamu' => 'Tamu'
            ];
            $statusOptions = [
                'menunggu' => 'Menunggu',
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
                'dibatalkan' => 'Dibatalkan',
            ];
            
            $jenisPengajuLabel = $jenisPengaju ? ($jenisOptions[$jenisPengaju] ?? ucfirst($jenisPengaju)) : 'Semua';
            $statusLabel = $status ? ($statusOptions[$status] ?? ucfirst($status)) : 'Semua';

            // ============ DATA PDF ============
            $data = [
                'judul' => 'LAPORAN PEMINJAMAN RUANGAN',
                'subjudul' => 'Sistem Peminjaman Ruangan Digital',
                'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y H:i:s'),
                'periode' => $filterBulanan == '1' 
                    ? Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y')
                    : Carbon::parse($startDate)->translatedFormat('d F Y') . ' - ' . Carbon::parse($endDate)->translatedFormat('d F Y'),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'filterBulanan' => $filterBulanan,
                'bulan' => $bulan,
                'tahun' => $tahun,
                'jenisPengaju' => $jenisPengaju,
                'jenisPengajuLabel' => $jenisPengajuLabel,
                'status' => $status,
                'statusLabel' => $statusLabel,
                'ruanganId' => $ruanganId,
                'ruanganNama' => $ruanganNama,
                'peminjaman' => $peminjaman,
                'totalPeminjaman' => $totalPeminjaman,
                'totalDisetujui' => $totalDisetujui,
                'totalDitolak' => $totalDitolak,
                'totalMenunggu' => $totalMenunggu,
                'totalDibatalkan' => $totalDibatalkan,
                'totalSelesai' => $totalSelesai,
                'avgPerDay' => $avgPerDay,
                'days' => $days,
                'percentageDisetujui' => $percentageDisetujui,
                'filterDescription' => $this->getFilterDescription($filterBulanan, $startDate, $endDate, $bulan, $tahun, $jenisPengajuLabel, $statusLabel, $ruanganNama)
            ];

            // ============ SIMPAN KE LOG ACTIVITY (DISESUAIKAN DENGAN STRUKTUR TABEL) ============
            $this->logActivity(
                'cetak_pdf',
                'Mencetak laporan PDF',
                [
                    'periode' => $data['periode'],
                    'jenis_pengaju' => $jenisPengajuLabel,
                    'status' => $statusLabel,
                    'ruangan' => $ruanganNama,
                    'total_data' => $totalPeminjaman,
                    'filter_bulanan' => $filterBulanan == '1' ? 'Ya' : 'Tidak',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            );

            // ============ GENERATE PDF ============
            $pdf = Pdf::loadView('admin.laporan.pdf', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'chroot' => public_path(),
                ]);

            $filename = 'laporan-peminjaman-' . Carbon::now()->format('Y-m-d-H-i-s') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            // Log error activity
            $this->logActivity(
                'cetak_pdf_error',
                'Gagal mencetak laporan PDF',
                ['error' => $e->getMessage()]
            );
            
            return redirect()->route('admin.laporan.index')
                ->with('error', 'Gagal membuat PDF: ' . $e->getMessage());
        }
    }

    /**
     * CETAK EXCEL - DENGAN FILTER LENGKAP DAN LOG ACTIVITY
     */
    public function cetakExcel(Request $request)
    {
        try {
            // ============ AMBIL SEMUA PARAMETER FILTER ============
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $jenisPengaju = $request->get('jenis_pengaju', '');
            $status = $request->get('status', '');
            $ruanganId = $request->get('ruangan_id', '');
            $filterBulanan = $request->get('filter_bulanan', '0');
            $bulan = $request->get('bulan', Carbon::now()->format('m'));
            $tahun = $request->get('tahun', Carbon::now()->format('Y'));

            // ============ VALIDASI TANGGAL ============
            if (!$startDate && $filterBulanan != '1') {
                $startDate = Carbon::now()->subMonth()->format('Y-m-d');
            }
            if (!$endDate && $filterBulanan != '1') {
                $endDate = Carbon::now()->format('Y-m-d');
            }

            // ============ BUILD QUERY ============
            $query = PeminjamanRuangan::with(['ruangan', 'user']);

            if ($filterBulanan == '1') {
                $query->whereYear('tanggal', $tahun)
                      ->whereMonth('tanggal', $bulan);
                $startDate = Carbon::createFromDate($tahun, $bulan, 1)->format('Y-m-d');
                $endDate = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->format('Y-m-d');
            } else {
                $query->whereBetween('tanggal', [$startDate, $endDate]);
            }

            if (!empty($jenisPengaju)) $query->where('jenis_pengaju', $jenisPengaju);
            if (!empty($status)) $query->where('status', $status);
            if (!empty($ruanganId)) $query->where('ruangan_id', $ruanganId);

            $peminjaman = $query->orderBy('tanggal', 'desc')->get();
            $totalData = $peminjaman->count();

            // Label untuk filter
            $jenisOptions = [
                'mahasiswa' => 'Mahasiswa',
                'dosen' => 'Dosen',
                'staff' => 'Staff',
                'tamu' => 'Tamu'
            ];
            $statusOptions = [
                'menunggu' => 'Menunggu',
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
                'dibatalkan' => 'Dibatalkan',
            ];
            
            $jenisPengajuLabel = $jenisPengaju ? ($jenisOptions[$jenisPengaju] ?? ucfirst($jenisPengaju)) : 'Semua';
            $statusLabel = $status ? ($statusOptions[$status] ?? ucfirst($status)) : 'Semua';
            
            $ruanganNama = 'Semua Ruangan';
            if ($ruanganId) {
                $ruangan = Ruangan::find($ruanganId);
                $ruanganNama = $ruangan ? $ruangan->kode_ruangan . ' - ' . $ruangan->nama_ruangan : 'Semua Ruangan';
            }
            
            $periodeText = $filterBulanan == '1' 
                ? Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y')
                : Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');

            // ============ SIMPAN KE LOG ACTIVITY (DISESUAIKAN DENGAN STRUKTUR TABEL) ============
            $this->logActivity(
                'cetak_excel',
                'Mencetak laporan Excel/CSV',
                [
                    'periode' => $periodeText,
                    'jenis_pengaju' => $jenisPengajuLabel,
                    'status' => $statusLabel,
                    'ruangan' => $ruanganNama,
                    'total_data' => $totalData,
                    'filter_bulanan' => $filterBulanan == '1' ? 'Ya' : 'Tidak',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            );

            // ============ GENERATE CSV ============
            $filename = 'laporan-peminjaman-' . Carbon::now()->format('Y-m-d-H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0'
            ];

            $callback = function() use ($peminjaman, $periodeText, $jenisPengajuLabel, $statusLabel, $ruanganNama) {
                $file = fopen('php://output', 'w');
                
                // UTF-8 BOM untuk Excel
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // HEADER
                fputcsv($file, ['LAPORAN PEMINJAMAN RUANGAN']);
                fputcsv($file, ['']);
                
                // PERIODE
                fputcsv($file, ['Periode:', $periodeText]);
                
                // FILTER
                fputcsv($file, ['Jenis Pengaju:', $jenisPengajuLabel]);
                fputcsv($file, ['Status:', $statusLabel]);
                fputcsv($file, ['Ruangan:', $ruanganNama]);
                fputcsv($file, ['Tanggal Cetak:', Carbon::now()->format('d/m/Y H:i:s')]);
                fputcsv($file, ['']);
                
                // STATISTIK
                $total = $peminjaman->count();
                $disetujui = $peminjaman->where('status', 'disetujui')->count();
                $ditolak = $peminjaman->where('status', 'ditolak')->count();
                $menunggu = $peminjaman->where('status', 'menunggu')->count();
                $dibatalkan = $peminjaman->where('status', 'dibatalkan')->count();
                $selesai = $peminjaman->where('status_real_time', 'selesai')->count();
                
                fputcsv($file, ['STATISTIK']);
                fputcsv($file, ['Total Peminjaman:', $total]);
                fputcsv($file, ['Disetujui:', $disetujui, '(' . ($total > 0 ? round(($disetujui/$total)*100, 1) : 0) . '%)']);
                fputcsv($file, ['Ditolak:', $ditolak, '(' . ($total > 0 ? round(($ditolak/$total)*100, 1) : 0) . '%)']);
                fputcsv($file, ['Menunggu:', $menunggu, '(' . ($total > 0 ? round(($menunggu/$total)*100, 1) : 0) . '%)']);
                fputcsv($file, ['Dibatalkan:', $dibatalkan, '(' . ($total > 0 ? round(($dibatalkan/$total)*100, 1) : 0) . '%)']);
                fputcsv($file, ['Selesai (Real Time):', $selesai, '(' . ($total > 0 ? round(($selesai/$total)*100, 1) : 0) . '%)']);
                fputcsv($file, ['']);
                
                // DATA
                fputcsv($file, ['No', 'Username', 'Nama Peminjam', 'Email', 'Jenis', 'Fakultas', 'Tanggal', 'Ruangan', 'Acara', 'Jam Mulai', 'Jam Selesai', 'Jumlah Peserta', 'Status', 'Status Real Time', 'Alasan', 'Lampiran', 'Dibuat', 'Diupdate']);
                
                $no = 1;
                foreach ($peminjaman as $item) {
                    fputcsv($file, [
                        $no++,
                        $item->user->username ?? $item->username ?? '-',
                        $item->nama_pengaju ?? $item->user->name ?? '-',
                        $item->email ?? $item->user->email ?? '-',
                        $item->jenis_pengaju ?? '-',
                        $item->fakultas ?: '-',
                        Carbon::parse($item->tanggal)->format('d/m/Y'),
                        ($item->ruangan->kode_ruangan ?? '-') . ' - ' . ($item->ruangan->nama_ruangan ?? '-'),
                        $item->acara,
                        $item->jam_mulai,
                        $item->jam_selesai,
                        $item->jumlah_peserta ?? '-',
                        ucfirst($item->status),
                        ucfirst(str_replace('_', ' ', $item->status_real_time ?? 'akan_datang')),
                        $item->alasan_penolakan ?: '-',
                        $item->lampiran_surat ? 'Ada' : '-',
                        $item->created_at ? Carbon::parse($item->created_at)->format('d/m/Y H:i') : '-',
                        $item->updated_at ? Carbon::parse($item->updated_at)->format('d/m/Y H:i') : '-'
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            // Log error activity
            $this->logActivity(
                'cetak_excel_error',
                'Gagal mencetak laporan Excel',
                ['error' => $e->getMessage()]
            );
            
            return redirect()->route('admin.laporan.index')
                ->with('error', 'Gagal membuat Excel: ' . $e->getMessage());
        }
    }

    /**
     * LOG ACTIVITY - Fungsi untuk mencatat aktivitas ke database
     * DISESUAIKAN DENGAN STRUKTUR TABEL log_activities
     */
    private function logActivity($action, $description, $details = [])
    {
        try {
            // Cek apakah tabel log_activities ada
            if (!\Illuminate\Support\Facades\Schema::hasTable('log_activities')) {
                \Log::warning('Tabel log_activities belum dibuat');
                return;
            }
            
            // Format deskripsi yang lebih rapi
            $deskripsiFormatted = $description . "\n";
            $deskripsiFormatted .= str_repeat('─', 50) . "\n";
            
            foreach ($details as $key => $value) {
                $keyLabel = ucfirst(str_replace('_', ' ', $key));
                $deskripsiFormatted .= "📌 {$keyLabel}: " . ($value ?: '-') . "\n";
            }
            
            $deskripsiFormatted .= str_repeat('─', 50) . "\n";
            $deskripsiFormatted .= "🕐 Waktu: " . Carbon::now()->translatedFormat('d F Y H:i:s');
            
            // Tentukan tipe berdasarkan action
            $tipe = 'cetak';
            if (str_contains($action, 'pdf')) {
                $tipe = 'cetak_pdf';
            } elseif (str_contains($action, 'excel')) {
                $tipe = 'cetak_excel';
            } elseif (str_contains($action, 'error')) {
                $tipe = 'error';
            }
            
            // Nama aktivitas yang akan ditampilkan
            $aktivitas = '';
            if ($action == 'cetak_pdf') {
                $aktivitas = '📄 CETAK LAPORAN PDF';
            } elseif ($action == 'cetak_excel') {
                $aktivitas = '📊 CETAK LAPORAN EXCEL/CSV';
            } elseif ($action == 'cetak_pdf_error') {
                $aktivitas = '❌ GAGAL CETAK PDF';
            } elseif ($action == 'cetak_excel_error') {
                $aktivitas = '❌ GAGAL CETAK EXCEL';
            } else {
                $aktivitas = '📋 ' . strtoupper(str_replace('_', ' ', $action));
            }
            
            LogActivity::create([
                'user_id' => auth()->id(),
                'tipe' => $tipe,
                'aktivitas' => $aktivitas,
                'deskripsi' => $deskripsiFormatted,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan log activity: ' . $e->getMessage());
        }
    }

    /**
     * Get filter description for PDF
     */
    private function getFilterDescription($filterBulanan, $startDate, $endDate, $bulan, $tahun, $jenisPengajuLabel, $statusLabel, $ruanganNama)
    {
        $desc = [];
        
        if ($filterBulanan == '1') {
            $desc[] = 'Bulan: ' . Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y');
        } else {
            $desc[] = 'Periode: ' . Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y');
        }
        
        if ($jenisPengajuLabel != 'Semua') $desc[] = 'Jenis Pengaju: ' . $jenisPengajuLabel;
        if ($statusLabel != 'Semua') $desc[] = 'Status: ' . $statusLabel;
        if ($ruanganNama != 'Semua Ruangan') $desc[] = 'Ruangan: ' . $ruanganNama;
        
        return implode(' • ', $desc);
    }

    // ============ HELPER METHODS (SEMUA SAMA SEPERTI SEBELUMNYA) ============
    
    private function getAnalisisHarian($peminjaman, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $results = [];
        
        $current = $start->copy();
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $dayPeminjaman = $peminjaman->filter(function($item) use ($dateStr) {
                return Carbon::parse($item->tanggal)->format('Y-m-d') == $dateStr;
            });
            
            $results[] = [
                'tanggal' => $dateStr,
                'hari' => $current->translatedFormat('l'),
                'total' => $dayPeminjaman->count(),
                'disetujui' => $dayPeminjaman->where('status', 'disetujui')->count(),
                'ditolak' => $dayPeminjaman->where('status', 'ditolak')->count(),
                'menunggu' => $dayPeminjaman->where('status', 'menunggu')->count(),
                'selesai' => $dayPeminjaman->where('status_real_time', 'selesai')->count(),
            ];
            
            $current->addDay();
        }
        
        return $results;
    }

    private function getHariTerpopuler($peminjaman)
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $dayCounts = array_fill_keys($days, 0);
        
        foreach ($peminjaman as $item) {
            $hari = Carbon::parse($item->tanggal)->translatedFormat('l');
            if (isset($dayCounts[$hari])) {
                $dayCounts[$hari]++;
            }
        }
        
        arsort($dayCounts);
        $mostPopular = array_key_first($dayCounts);
        
        return [
            'nama' => $mostPopular,
            'count' => $dayCounts[$mostPopular] ?? 0
        ];
    }

    private function getJamPuncak($peminjaman)
    {
        $hourCounts = array_fill_keys(range(7, 21), 0);
        
        foreach ($peminjaman as $item) {
            $hour = (int) substr($item->jam_mulai, 0, 2);
            if (isset($hourCounts[$hour])) {
                $hourCounts[$hour]++;
            }
        }
        
        arsort($hourCounts);
        $peakHour = array_key_first($hourCounts);
        
        return [
            'jam' => $peakHour ? sprintf('%02d:00 - %02d:00', $peakHour, $peakHour + 1) : 'Tidak ada data',
            'count' => $hourCounts[$peakHour] ?? 0
        ];
    }

    private function getAnalisisMingguan($peminjaman, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfWeek();
        $end = Carbon::parse($endDate);
        $results = [];
        $weekNumber = 1;
        
        $current = $start->copy();
        while ($current <= $end) {
            $weekStart = $current->copy();
            $weekEnd = $current->copy()->endOfWeek();
            
            if ($weekEnd > $end) $weekEnd = $end->copy();
            
            $weekPeminjaman = $peminjaman->filter(function($item) use ($weekStart, $weekEnd) {
                $date = Carbon::parse($item->tanggal);
                return $date >= $weekStart && $date <= $weekEnd;
            });
            
            if ($weekPeminjaman->count() > 0 || $current <= $end) {
                $results[] = [
                    'minggu_ke' => $weekNumber,
                    'periode' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                    'total' => $weekPeminjaman->count(),
                    'disetujui' => $weekPeminjaman->where('status', 'disetujui')->count(),
                    'selesai' => $weekPeminjaman->where('status_real_time', 'selesai')->count(),
                    'trend' => 0
                ];
                $weekNumber++;
            }
            
            $current->addWeek();
        }
        
        // Calculate trends
        for ($i = 1; $i < count($results); $i++) {
            if ($results[$i-1]['total'] > 0) {
                $results[$i]['trend'] = round((($results[$i]['total'] - $results[$i-1]['total']) / $results[$i-1]['total']) * 100, 1);
            }
        }
        
        return $results;
    }

    private function getMingguTerbaik($analisisMingguan)
    {
        if (empty($analisisMingguan)) {
            return ['minggu' => 'Tidak ada data', 'total' => 0];
        }
        
        $best = collect($analisisMingguan)->sortByDesc('total')->first();
        return [
            'minggu' => 'Minggu ' . $best['minggu_ke'],
            'total' => $best['total']
        ];
    }

    private function getPertumbuhanMingguan($analisisMingguan)
    {
        if (count($analisisMingguan) < 2) return 0;
        $first = $analisisMingguan[0]['total'];
        $last = end($analisisMingguan)['total'];
        return $first > 0 ? round((($last - $first) / $first) * 100, 1) : 0;
    }

    private function getAnalisisBulanan($peminjaman)
    {
        $grouped = $peminjaman->groupBy(function($item) {
            return Carbon::parse($item->tanggal)->format('Y-m');
        })->sortKeys();
        
        $results = [];
        $prevTotal = null;
        
        foreach ($grouped as $key => $items) {
            $date = Carbon::parse($key . '-01');
            $total = $items->count();
            $trend = 0;
            
            if ($prevTotal !== null && $prevTotal > 0) {
                $trend = round((($total - $prevTotal) / $prevTotal) * 100, 1);
            }
            
            $results[] = [
                'bulan' => $date->translatedFormat('F Y'),
                'total' => $total,
                'disetujui' => $items->where('status', 'disetujui')->count(),
                'ditolak' => $items->where('status', 'ditolak')->count(),
                'selesai' => $items->where('status_real_time', 'selesai')->count(),
                'trend' => $trend
            ];
            
            $prevTotal = $total;
        }
        
        return $results;
    }

    private function getBulanTerbaik($analisisBulanan)
    {
        if (empty($analisisBulanan)) {
            return ['bulan' => 'Tidak ada data', 'total' => 0];
        }
        
        $best = collect($analisisBulanan)->sortByDesc('total')->first();
        return [
            'bulan' => $best['bulan'],
            'total' => $best['total']
        ];
    }

    private function getPertumbuhanBulanan($analisisBulanan)
    {
        if (count($analisisBulanan) < 2) return 0;
        $first = $analisisBulanan[0]['total'];
        $last = end($analisisBulanan)['total'];
        return $first > 0 ? round((($last - $first) / $first) * 100, 1) : 0;
    }

    private function getAnalisisTahunan($peminjaman)
    {
        $grouped = $peminjaman->groupBy(function($item) {
            return Carbon::parse($item->tanggal)->year;
        })->sortKeysDesc();
        
        $results = [];
        $prevTotal = null;
        
        foreach ($grouped as $year => $items) {
            $total = $items->count();
            $pertumbuhan = 0;
            
            if ($prevTotal !== null && $prevTotal > 0) {
                $pertumbuhan = round((($total - $prevTotal) / $prevTotal) * 100, 1);
            }
            
            $results[] = [
                'tahun' => $year,
                'total' => $total,
                'disetujui' => $items->where('status', 'disetujui')->count(),
                'ditolak' => $items->where('status', 'ditolak')->count(),
                'selesai' => $items->where('status_real_time', 'selesai')->count(),
                'pertumbuhan' => $pertumbuhan
            ];
            
            $prevTotal = $total;
        }
        
        return $results;
    }

    private function getTahunTerbaik($analisisTahunan)
    {
        if (empty($analisisTahunan)) {
            return ['tahun' => 'Tidak ada data', 'total' => 0];
        }
        
        $best = collect($analisisTahunan)->sortByDesc('total')->first();
        return [
            'tahun' => $best['tahun'],
            'total' => $best['total']
        ];
    }

    private function getPertumbuhanTahunan($analisisTahunan)
    {
        return $analisisTahunan[0]['pertumbuhan'] ?? 0;
    }

    private function getTrendData($peminjaman, $periode, $startDate, $endDate)
    {
        $labels = [];
        $data = [];
        
        if ($periode == 'harian') {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
            $current = $start->copy();
            
            while ($current <= $end) {
                $labels[] = $current->format('d/m');
                $data[] = $peminjaman->filter(function($item) use ($current) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d') == $current->format('Y-m-d');
                })->count();
                $current->addDay();
            }
        } elseif ($periode == 'mingguan') {
            $analisis = $this->getAnalisisMingguan($peminjaman, $startDate, $endDate);
            foreach ($analisis as $item) {
                $labels[] = 'M' . $item['minggu_ke'];
                $data[] = $item['total'];
            }
        } elseif ($periode == 'bulanan') {
            $analisis = $this->getAnalisisBulanan($peminjaman);
            foreach ($analisis as $item) {
                $labels[] = Carbon::parse($item['bulan'])->format('M Y');
                $data[] = $item['total'];
            }
        } else {
            $analisis = $this->getAnalisisTahunan($peminjaman);
            foreach ($analisis as $item) {
                $labels[] = $item['tahun'];
                $data[] = $item['total'];
            }
        }
        
        return ['labels' => $labels, 'data' => $data];
    }

    private function getWeeklyChartData($analisisMingguan)
    {
        return [
            'labels' => array_map(function($item) {
                return 'Minggu ' . $item['minggu_ke'];
            }, $analisisMingguan),
            'data' => array_column($analisisMingguan, 'total')
        ];
    }

    private function getMonthlyChartData($analisisBulanan)
    {
        return [
            'labels' => array_column($analisisBulanan, 'bulan'),
            'data' => array_column($analisisBulanan, 'total')
        ];
    }

    private function getYearlyChartData($analisisTahunan)
    {
        return [
            'labels' => array_column($analisisTahunan, 'tahun'),
            'data' => array_column($analisisTahunan, 'total')
        ];
    }

    private function getHourlyData($peminjaman)
    {
        $hourly = array_fill_keys(array_map(function($h) {
            return sprintf('%02d:00', $h);
        }, range(7, 21)), 0);
        
        foreach ($peminjaman as $item) {
            $hour = (int) substr($item->jam_mulai, 0, 2);
            $key = sprintf('%02d:00', $hour);
            if (isset($hourly[$key])) {
                $hourly[$key]++;
            }
        }
        
        return $hourly;
    }

    private function getFacultyData($peminjaman)
    {
        $faculty = [];
        foreach ($peminjaman as $item) {
            $fakultas = $item->fakultas ?: 'Lainnya';
            $faculty[$fakultas] = ($faculty[$fakultas] ?? 0) + 1;
        }
        arsort($faculty);
        return array_slice($faculty, 0, 10);
    }
}