<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Peminjaman</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 4px;
            vertical-align: top;
        }

        th {
            background: #4F81BD;
            color: white;
        }

        .status {
            font-size: 7px;
            font-weight: bold;
        }

        .catatan {
            font-size: 7px;
            font-style: italic;
        }

        .summary {
            margin-top: 10px;
            padding: 6px;
            border: 1px solid #ccc;
            background: #f9f9f9;
            font-size: 8px;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 7px;
            color: #777;
        }
    </style>
</head>

<body>

<h3 style="text-align:center;">
    LAPORAN PEMINJAMAN RUANGAN
</h3>

<p>
    Periode: {{ $formattedStartDate }} - {{ $formattedEndDate }} <br>
    Dicetak: {{ $generatedAt }} <br>
    Oleh: {{ $generatedBy }}
</p>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Ruangan</th>
            <th>Peminjam</th>
            <th>Kontak</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Status</th>
            <th>Status RT</th>
            <th>Acara</th>
            <th>Keterangan</th>
            <th>Catatan</th>
            <th>Info</th>
        </tr>
    </thead>

    <tbody>
        @forelse($peminjaman as $i => $item)

        @php
            $catatan = $item->catatan ?? '';
            $catatan = preg_replace('/[^\x20-\x7E]/u', '', $catatan);
            $catatan = trim($catatan);

            $mulai = \Carbon\Carbon::parse($item->jam_mulai);
            $selesai = \Carbon\Carbon::parse($item->jam_selesai);
            $durasi = $selesai->diffInMinutes($mulai);
        @endphp

        <tr>
            <td>{{ $i+1 }}</td>

            <td>
                {{ $item->ruangan->nama_ruangan ?? '-' }} <br>
                <small>{{ $item->ruangan->kode_ruangan ?? '' }}</small>
            </td>

            <td>
                {{ $item->nama_pengaju ?? '-' }} <br>
                <small>{{ $item->jenis_pengaju ?? '-' }}</small>
            </td>

            <td>
                {{ $item->email ?? '-' }} <br>
                {{ $item->no_telepon ?? '-' }}
            </td>

            <td>
                {{ date('d/m/Y', strtotime($item->tanggal_mulai)) }}
            </td>

            <td>
                {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }}
                -
                {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}
                <br>
                <small>{{ floor($durasi/60) }} jam {{ $durasi%60 }} menit</small>
            </td>

            <td class="status">
                {{ ucfirst($item->status) }}
            </td>

            <!-- STATUS REALTIME (KOLOM BARU) -->
            <td class="status">
                {{ str_replace('_',' ', $item->status_real_time ?? '-') }}
            </td>

            <td>
                {{ $item->acara ?? '-' }}
            </td>

            <td>
                {{ $item->keterangan ?? '-' }}
            </td>

            <td class="catatan">
                @if($catatan && $catatan != '?' && strlen($catatan) > 2)
                    {{ \Illuminate\Support\Str::limit($catatan, 80) }}
                @else
                    -
                @endif
            </td>

            <td>
                {{ date('d/m/Y', strtotime($item->created_at)) }}
            </td>
        </tr>

        @empty
        <tr>
            <td colspan="12" style="text-align:center;">
                Tidak ada data
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ================= TOTAL / SUMMARY ================= --}}
<div class="summary">

    <strong>RINGKASAN DATA</strong><br><br>

    Total Peminjaman: <strong>{{ $totalPeminjaman }}</strong><br>

    Disetujui: {{ $statusCounts['disetujui'] ?? 0 }}<br>
    Menunggu: {{ $statusCounts['menunggu'] ?? 0 }}<br>
    Ditolak: {{ $statusCounts['ditolak'] ?? 0 }}<br>
    Selesai: {{ $statusCounts['selesai'] ?? 0 }}<br>
    Dibatalkan: {{ $statusCounts['dibatalkan'] ?? 0 }}<br>

    <br>

    @php
        $approved = $statusCounts['disetujui'] ?? 0;
        $total = $totalPeminjaman ?? 0;
        $rate = $total > 0 ? round(($approved/$total)*100) : 0;
    @endphp

    Tingkat Persetujuan: <strong>{{ $rate }}%</strong><br>

    @if(isset($ruanganTerbanyak))
        Ruangan Terbanyak: <strong>{{ $ruanganTerbanyak }}</strong>
    @endif

</div>

<div class="footer">
    Dicetak pada {{ $generatedAt }}
</div>

</body>
</html>