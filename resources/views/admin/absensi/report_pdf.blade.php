<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Pegawai BKK Pontianak</title>
    <style>
        /* Konfigurasi Halaman PDF (Landscape A4) */
        @page {
            size: A4 landscape;
            margin: 1.2cm 1cm 1.5cm 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8.5pt;
            color: #222;
            line-height: 1.3;
        }

        /* Header Kop Surat */
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            position: relative;
        }

        .header h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header h2 {
            margin: 2px 0;
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 3px 0 0;
            font-size: 7.5pt;
            font-style: italic;
            color: #444;
        }

        /* Tabel Informasi Filter */
        .info-laporan {
            width: 100%;
            margin-bottom: 12px;
            font-size: 8.5pt;
            border-collapse: collapse;
        }

        .info-laporan td {
            border: none !important;
            padding: 2px 4px;
            vertical-align: top;
        }

        /* Tabel Utama Data Absensi */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        table.data-table,
        table.data-table th,
        table.data-table td {
            border: 1px solid #444;
        }

        table.data-table th {
            background-color: #e9ecef;
            padding: 6px 3px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            color: #111;
        }

        table.data-table td {
            padding: 5px 4px;
            vertical-align: middle;
            font-size: 8pt;
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-muted {
            color: #6c757d;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 3px;
        }

        .status-terlambat {
            color: #dc3545;
            font-weight: bold;
        }

        .status-hadir {
            color: #198754;
            font-weight: bold;
        }

        /* Blok Tanda Tangan */
        .signature-section {
            margin-top: 25px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-box {
            float: right;
            width: 250px;
            text-align: center;
            font-size: 8.5pt;
        }

        .signature-space {
            height: 55px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    {{-- Header Kop Surat Kedinasan --}}
    <div class="header">
        <h3>KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h3>
        <h2>BALAI KEKARANTINAAN KESEHATAN KELAS I PONTIANAK</h2>
        <p>Jl. Jenderal Ahmad Yani, Arang Limbung, Kec. Sungai Raya, Kabupaten Kubu Raya, Kalimantan Barat 78391</p>
    </div>

    {{-- Tabel Informasi Filter Laporan --}}
    <table class="info-laporan">
        <tr>
            <td style="width: 12%;"><strong>Jenis Laporan</strong></td>
            <td style="width: 38%;">: Rekapitulasi Absensi Pegawai</td>
            <td style="width: 12%;"><strong>Periode</strong></td>
            <td style="width: 38%;">:
                @if (!empty($start_date) && !empty($end_date))
                    {{ \Carbon\Carbon::parse($start_date)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->isoFormat('D MMMM Y') }}
                @else
                    Semua Periode
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Nama Pegawai</strong></td>
            <td>: {{ $user->name ?? 'Semua Pegawai' }}</td>
            <td><strong>Tipe Absen</strong></td>
            <td>: {{ strtoupper($tipe_filter ?? 'Semua') }}</td>
        </tr>
        <tr>
            <td><strong>Tim Kerja</strong></td>
            <td>: {{ $tim_filter ?? 'Semua Tim Kerja' }}</td>
            <td><strong>Lokasi Kantor</strong></td>
            <td>: {{ $lokasi_filter ?? 'Semua Lokasi' }}</td>
        </tr>
        <tr>
            <td><strong>Tanggal Cetak</strong></td>
            <td>: {{ \Carbon\Carbon::parse($tanggal_cetak ?? now())->isoFormat('D MMMM Y') }}</td>
            <td></td>
            <td></td>
        </tr>
    </table>

    {{-- Tabel Data Absensi --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th style="width: 55px;">Hari</th>
                <th style="width: 65px;">Tanggal</th>
                <th>Nama Pegawai</th>
                <th style="width: 85px;">NIP</th>
                <th style="width: 60px;">Tipe</th>
                <th style="width: 50px;">Masuk</th>
                <th style="width: 50px;">Pulang</th>
                <th style="width: 65px;">Jam Kerja</th>
                <th style="width: 140px;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $a)
                @php
                    \Carbon\Carbon::setLocale('id');

                    $checkIn = $a->check_in_time ? \Carbon\Carbon::parse($a->check_in_time) : null;
                    $checkOut = $a->check_out_time ? \Carbon\Carbon::parse($a->check_out_time) : null;

                    // Kalkulasi Durasi Jam Kerja
                    $durasiKerja = '-';
                    if ($checkIn && $checkOut) {
                        $diffInMinutes = $checkIn->diffInMinutes($checkOut);
                        $jam = floor($diffInMinutes / 60);
                        $menit = $diffInMinutes % 60;

                        if ($jam > 0 && $menit > 0) {
                            $durasiKerja = "{$jam}j {$menit}m";
                        } elseif ($jam > 0) {
                            $durasiKerja = "{$jam} jam";
                        } else {
                            $durasiKerja = "{$menit} mnt";
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>

                    {{-- Hari --}}
                    <td class="text-center">
                        {{ $checkIn ? $checkIn->isoFormat('dddd') : '-' }}
                    </td>

                    {{-- Tanggal --}}
                    <td class="text-center">
                        {{ $checkIn ? $checkIn->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Nama Pegawai --}}
                    <td>
                        <span class="fw-bold">{{ $a->user->name ?? 'User Terhapus' }}</span>
                        @if ($a->user && $a->user->tim_kerja)
                            <br><small class="text-muted" style="font-size: 7pt;">({{ $a->user->tim_kerja->nama }})</small>
                        @endif
                    </td>

                    {{-- NIP --}}
                    <td class="text-center">{{ $a->user->nip ?? '-' }}</td>

                    {{-- Logika Tipe Absen --}}
                    <td class="text-center">
                        @if (!empty($a->reason_change_status))
                            <span style="text-decoration: line-through; color: #888;">
                                {{ $a->tipe_absen == 'WFA' ? 'WFO' : 'WFA' }}
                            </span>
                            <strong>&rarr; {{ $a->tipe_absen }}</strong>
                        @else
                            {{ $a->tipe_absen ?? '-' }}
                        @endif
                    </td>

                    {{-- Jam Masuk & Pulang --}}
                    <td class="text-center">{{ $checkIn ? $checkIn->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $checkOut ? $checkOut->format('H:i') : '-' }}</td>

                    {{-- Durasi --}}
                    <td class="text-center">{{ $durasiKerja }}</td>

                    {{-- Keterangan / Status --}}
                    <td style="font-size: 7.5pt;">
                        @if ($a->status == 'terlambat')
                            <span class="status-terlambat">[Terlambat]</span>
                        @else
                            <span class="status-hadir">[Hadir]</span>
                        @endif

                        @if (!empty($a->reason_change_status))
                            <br><small class="text-muted">Revisi: {{ $a->reason_change_status }}</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #666;">
                        Tidak ada data absensi yang ditemukan untuk kriteria filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Blok Tanda Tangan Pengesahan Laporan --}}
    <div class="signature-section">
        <div class="signature-box">
            <p>Pontianak, {{ \Carbon\Carbon::parse($tanggal_cetak ?? now())->isoFormat('D MMMM Y') }}</p>
            <p style="margin-top: -5px;"><strong>Pengelola Kepegawaian / Penanggung Jawab</strong></p>
            <div class="signature-space"></div>
            <p style="text-decoration: underline; font-weight: bold; margin-bottom: 2px;">( .................................................... )</p>
            <p style="margin-top: 0; font-size: 7.5pt;">NIP. ...............................................</p>
        </div>
        <div class="clear"></div>
    </div>
</body>

</html>
