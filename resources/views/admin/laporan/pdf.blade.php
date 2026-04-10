<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul ?? 'Laporan Peminjaman Ruangan' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 15px;
            background: white;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #3b82f6;
        }
        
        .header h1 {
            font-size: 20px;
            color: #1e3a8a;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14px;
            color: #3b82f6;
            margin-bottom: 8px;
        }
        
        .header p {
            color: #666;
            font-size: 9px;
        }
        
        /* Info Box */
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 10px 12px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        
        .info-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
            font-size: 11px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        
        .info-item {
            font-size: 9px;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            display: block;
        }
        
        .info-value {
            color: #333;
        }
        
        /* Section */
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            padding-bottom: 6px;
            border-bottom: 2px solid #3b82f6;
            margin-bottom: 12px;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            font-size: 8px;
        }
        
        th {
            background: #1e3a8a;
            color: white;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #3b82f6;
        }
        
        td {
            padding: 6px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .status-menunggu { background: #fef3c7; color: #92400e; }
        .status-disetujui { background: #dcfce7; color: #166534; }
        .status-ditolak { background: #fee2e2; color: #991b1b; }
        .status-selesai { background: #dbeafe; color: #1e40af; }
        .status-dibatalkan { background: #f1f5f9; color: #475569; }
        
        /* Status Real Time */
        .rt-akan_datang { background: #e0e7ff; color: #3730a3; }
        .rt-sedang_berlangsung { background: #fed7aa; color: #9a3412; }
        .rt-selesai { background: #d1fae5; color: #065f46; }
        .rt-dibatalkan { background: #fee2e2; color: #991b1b; }
        
        /* Two Columns */
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        /* Stats Card */
        .stats-card {
            background: #f8fafc;
            border-radius: 6px;
            padding: 10px;
            border: 1px solid #e2e8f0;
        }
        
        .stats-card h4 {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #1e293b;
        }
        
        /* Bar Chart */
        .bar-container { margin-bottom: 8px; }
        
        .bar-label {
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            margin-bottom: 2px;
        }
        
        .bar {
            background: #e2e8f0;
            border-radius: 3px;
            height: 10px;
            overflow: hidden;
        }
        
        .bar-fill {
            background: #3b82f6;
            height: 100%;
            border-radius: 3px;
        }
        
        .bar-fill.green { background: #22c55e; }
        .bar-fill.yellow { background: #eab308; }
        .bar-fill.red { background: #ef4444; }
        .bar-fill.purple { background: #8b5cf6; }
        
        /* Text Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        
        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
        
        /* Total Data di bawah tabel */
        .table-footer {
            margin-top: 0;
            padding: 8px;
            background: #f1f5f9;
            border-radius: 0 0 6px 6px;
            font-size: 9px;
            border: 1px solid #cbd5e1;
            border-top: none;
        }
        
        /* Summary di bawah tabel */
        .summary {
            margin-top: 15px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 6px;
            font-size: 9px;
        }
        
        .summary h4 {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1e3a8a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
        }
        
        .summary-grid-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 10px;
        }
        
        .summary-item {
            line-height: 1.6;
        }
        
        .summary-label {
            font-weight: 600;
            color: #475569;
        }
        
        .summary-value {
            font-weight: bold;
            color: #1e293b;
        }
        
        /* Warna baris bergantian */
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Catatan column style */
        .catatan-cell {
            max-width: 150px;
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.3;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <h1>LAPORAN PEMINJAMAN RUANGAN</h1>
        <h2>Sistem Informasi Peminjaman Ruangan</h2>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }} WIB</p>
        <p>Oleh: {{ $generatedBy ?? auth()->user()->name ?? 'Administrator' }}</p>
    </div>

    <!-- INFO FILTER -->
    <div class="info-box">
        <div class="info-title">Informasi Filter Laporan</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Periode:</span>
                <span class="info-value">{{ $periode ?? Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Jenis Pengaju:</span>
                <span class="info-value">{{ $jenisPengajuLabel ?? 'Semua' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span>
                <span class="info-value">{{ $statusLabel ?? 'Semua' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Ruangan:</span>
                <span class="info-value">{{ $ruanganNama ?? 'Semua Ruangan' }}</span>
            </div>
        </div>
    </div>

    <!-- DATA PEMINJAMAN DETAIL (TABEL LENGKAP) -->
    <div class="section">
        <div class="section-title">Data Peminjaman Ruangan</div>
        
        <table>
            <thead>
                <tr>
                    <th width="10">No</th>
                    <th width="25">Username</th>
                    <th width="30">Tanggal</th>
                    <th width="35">Ruangan</th>
                    <th width="50">Nama Peminjam</th>
                    <th width="25">Jenis</th>
                    <th width="30">Waktu</th>
                    <th width="45">Kegiatan</th>
                    <th width="15">Peserta</th>
                    <th width="20">Hari</th>
                    <th width="30">Tgl Mulai</th>
                    <th width="30">Tgl Selesai</th>
                    <th width="30">Status</th>
                    <th width="30">Status RT</th>
                    <th width="20">Lampiran</th>
                    <th width="35">Alasan</th>
                    <th width="35">Dibuat</th>
                    <th width="50">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjaman ?? [] as $index => $item)
                @php
                    $catatan = $item->catatan ?? '';
                    $catatan = preg_replace('/[^\x20-\x7E]/u', '', $catatan);
                    $catatan = trim($catatan);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->user->username ?? $item->username ?? '-' }}</td>
                    <td class="text-center">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $item->ruangan->kode_ruangan ?? '-' }}</td>
                    <td>{{ $item->nama_pengaju ?? $item->user->name ?? '-' }}</td>
                    <td class="text-center">
                        @php
                            $jenisLabel = [
                                'mahasiswa' => 'Mahasiswa',
                                'dosen' => 'Dosen',
                                'staff' => 'Staff',
                            ][$item->jenis_pengaju ?? ''] ?? ucfirst($item->jenis_pengaju ?? '-');
                        @endphp
                        {{ $jenisLabel }}
                    </td>
                    <td class="text-center">
                        {{ $item->jam_mulai ?? '-' }} - {{ $item->jam_selesai ?? '-' }}
                        @if($item->jam_mulai && $item->jam_selesai)
                        <br><small>
                            @php
                                $mulai = \Carbon\Carbon::parse($item->jam_mulai);
                                $selesai = \Carbon\Carbon::parse($item->jam_selesai);
                                $durasi = $selesai->diffInMinutes($mulai);
                            @endphp
                            {{ floor($durasi/60) }} jam {{ $durasi%60 }} menit
                        </small>
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($item->acara ?? '-', 40) }}</td>
                    <td class="text-center">{{ $item->jumlah_peserta ?? 0 }}</td>
                    <td class="text-center">{{ $item->hari ?? '-' }}</td>
                    <td class="text-center">{{ $item->tanggal_mulai ? \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $item->tanggal_selesai ? \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $item->status }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td class="text-center">
                        @php
                            $statusRt = $item->status_real_time ?? 'akan_datang';
                            $statusRtLabel = [
                                'akan_datang' => 'Akan Datang',
                                'sedang_berlangsung' => 'Berlangsung',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan'
                            ][$statusRt] ?? ucfirst($statusRt);
                        @endphp
                        <span class="status-badge rt-{{ $statusRt }}">
                            {{ $statusRtLabel }}
                        </span>
                    </td>
                    <td class="text-center">{{ $item->lampiran_surat ? 'Ada' : '-' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($item->alasan_penolakan ?? '-', 25) }}</td>
                    <td class="text-center">{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') : '-' }}</td>
                    <td class="catatan-cell">
                        @if($catatan && $catatan != '?' && strlen($catatan) > 2)
                            {{ \Illuminate\Support\Str::limit($catatan, 80) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="18" class="text-center">Tidak ada data peminjaman</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Total Data di bawah tabel -->
        <div class="table-footer">
            <strong>Total Data:</strong> {{ count($peminjaman ?? []) }} peminjaman
        </div>
    </div>

    <!-- SUMMARY RINGKASAN DI BAWAH TABEL (SEPERTI CONTOH) -->
    <div class="summary">
        <h4>RINGKASAN DATA</h4>
        
        <div class="summary-grid-summary">
            <div class="summary-item">
                <div><span class="summary-label">Total Peminjaman:</span> <span class="summary-value">{{ $totalPeminjaman ?? 0 }}</span></div>
                <div><span class="summary-label">Disetujui:</span> {{ $totalDisetujui ?? 0 }}</div>
                <div><span class="summary-label">Menunggu:</span> {{ $totalMenunggu ?? 0 }}</div>
            </div>
            <div class="summary-item">
                <div><span class="summary-label">Ditolak:</span> {{ $totalDitolak ?? 0 }}</div>
                <div><span class="summary-label">Selesai:</span> {{ $totalSelesai ?? 0 }}</div>
                <div><span class="summary-label">Dibatalkan:</span> {{ $totalDibatalkan ?? 0 }}</div>
            </div>
            <div class="summary-item">
                @php
                    $approved = $totalDisetujui ?? 0;
                    $total = $totalPeminjaman ?? 0;
                    $rate = $total > 0 ? round(($approved/$total)*100) : 0;
                @endphp
                <div><span class="summary-label">Tingkat Persetujuan:</span> <strong>{{ $rate }}%</strong></div>
                @if(isset($ruanganTerbanyak))
                <div><span class="summary-label">Ruangan Terbanyak:</span> <strong>{{ $ruanganTerbanyak }}</strong></div>
                @endif
            </div>
        </div>
    </div>

    <!-- STATISTIK PER JENIS PENGAJU & RUANGAN -->
    <div class="section">
        <div class="section-title">Statistik Berdasarkan Jenis Pengaju</div>
        @php
            $jenisStats = [
                'mahasiswa' => isset($peminjaman) ? $peminjaman->where('jenis_pengaju', 'mahasiswa')->count() : 0,
                'dosen' => isset($peminjaman) ? $peminjaman->where('jenis_pengaju', 'dosen')->count() : 0,
                'staff' => isset($peminjaman) ? $peminjaman->where('jenis_pengaju', 'staff')->count() : 0,
            ];
        @endphp
        <div class="two-columns">
            <div class="stats-card">
                @foreach($jenisStats as $jenis => $count)
                <div class="bar-container">
                    <div class="bar-label">
                        <span>{{ ucfirst($jenis) }}</span>
                        <span>{{ $count }} ({{ $totalPeminjaman > 0 ? round(($count/$totalPeminjaman)*100) : 0 }}%)</span>
                    </div>
                    <div class="bar">
                        <div class="bar-fill {{ $jenis == 'mahasiswa' ? 'blue' : ($jenis == 'dosen' ? 'green' : ($jenis == 'staff' ? 'purple' : 'yellow')) }}" 
                             style="width: {{ $totalPeminjaman > 0 ? ($count/$totalPeminjaman)*100 : 0 }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="stats-card">
                <h4>5 Ruangan Terpopuler</h4>
                @php
                    $ruanganStats = [];
                    if(isset($peminjaman)) {
                        foreach($peminjaman ?? [] as $item) {
                            $kode = $item->ruangan->kode_ruangan ?? 'Unknown';
                            $ruanganStats[$kode] = ($ruanganStats[$kode] ?? 0) + 1;
                        }
                    }
                    arsort($ruanganStats);
                    $ruanganStats = array_slice($ruanganStats, 0, 5);
                    $maxCount = !empty($ruanganStats) ? max($ruanganStats) : 1;
                @endphp
                @forelse($ruanganStats as $kode => $count)
                <div class="bar-container">
                    <div class="bar-label">
                        <span>{{ $kode }}</span>
                        <span>{{ $count }}x</span>
                    </div>
                    <div class="bar">
                        <div class="bar-fill green" style="width: {{ ($count / $maxCount) * 100 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-center">Tidak ada data ruangan</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Informasi Peminjaman Ruangan</p>
        <p>© {{ date('Y') }} - Sistem Informasi Peminjaman Ruangan | Dicetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>