<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Lembur Pegawai BKK Pontianak</title>
    <style>
        @page {
            size: A4 portrait; /* Orientasi Portrait */
            margin: 1.2cm 1cm 1.5cm 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 8pt; /* Sedikit dikecilkan agar muat di portrait */
            color: #222;
            line-height: 1.3;
        }

        /* Header Kop Surat */
        .header-container {
            width: 100%;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header-kemenkes {
            font-size: 11pt;
            font-weight: bold;
            color: #00A8B5;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .header-bkk {
            font-size: 13pt;
            font-weight: bold;
            color: #E28427;
            letter-spacing: 0.5px;
            margin: 3px 0 6px 0;
            text-transform: uppercase;
        }

        .header-address {
            font-size: 7.5pt;
            color: #333;
            margin: 0;
            line-height: 1.4;
        }

        .header-address .separator {
            margin: 0 6px;
            color: #aaa;
        }

        /* Tabel Informasi Filter */
        .info-laporan {
            width: 100%;
            margin-bottom: 10px;
            font-size: 8pt;
            border-collapse: collapse;
        }

        .info-laporan td {
            border: none !important;
            padding: 2px 2px;
            vertical-align: top;
        }

        /* Tabel Utama Data Lembur */
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
            padding: 5px 2px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            color: #111;
        }

        table.data-table td {
            padding: 4px 3px;
            vertical-align: top;
            font-size: 7.5pt;
        }

        /* Utility Classes */
        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-muted {
            color: #6c757d;
        }

        .status-badge {
            display: inline-block;
            font-size: 7pt;
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
            width: 220px;
            text-align: center;
            font-size: 8pt;
        }

        .signature-space {
            height: 50px;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    {{-- Header Kop Surat Teks Murni --}}
    <div class="header-container">
        <div class="header-kemenkes">Kementerian Kesehatan Republik Indonesia</div>
        <div class="header-bkk">Balai Kekarantinaan Kesehatan Kelas I Pontianak</div>
        <div class="header-address">
            Jl. Jenderal Ahmad Yani, Limbung, Kec. Sungai Raya, Kabupaten Kubu Raya, Kalimantan Barat 78391
            <br>
            Whatsapp: +62 811-5672-778 <span class="separator">|</span> Website: www.bkkpontianak.id
        </div>
    </div>

    {{-- Tabel Informasi Filter Laporan --}}
    <table class="info-laporan">
        <tr>
            <td style="width: 15%;"><strong>Jenis Laporan</strong></td>
            <td style="width: 40%;">: Rekapitulasi Presensi Lembur Pegawai</td>
            <td style="width: 12%;"><strong>Periode</strong></td>
            <td style="width: 33%;">:
                @if (!empty($start_date) && !empty($end_date))
                    {{ \Carbon\Carbon::parse($start_date)->isoFormat('D MMM Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->isoFormat('D MMM Y') }}
                @else
                    Semua Periode
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Pencarian</strong></td>
            <td>: {{ request()->get('nip') ?? 'Semua Pegawai' }}</td>
            <td><strong>Status Lembur</strong></td>
            <td>:
                @php
                    $statusFilter = request()->get('status');
                    if ($statusFilter == 'pending') echo 'Pending (Menunggu)';
                    elseif ($statusFilter == 'disetujui') echo 'Disetujui';
                    elseif ($statusFilter == 'ditolak') echo 'Ditolak';
                    else echo 'Semua Status';
                @endphp
            </td>
        </tr>
        <tr>
            <td><strong>Tim Kerja</strong></td>
            <td>:
                @if(!empty(request()->get('tim_kerja_ids')))
                    Filter Tim Kerja Aktif
                @else
                    Semua Tim Kerja
                @endif
            </td>
            <td><strong>Tanggal Cetak</strong></td>
            <td>: {{ \Carbon\Carbon::parse($print_date ?? now())->isoFormat('D MMMM Y') }}</td>
        </tr>
    </table>

    {{-- Tabel Data Lembur --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;">No</th>
                <th style="width: 75px;">Hari, Tanggal</th>
                <th style="width: 120px;">Nama Pegawai</th>
                <th style="width: 80px;">NIP</th>
                <th style="width: 40px;">Mulai</th>
                <th style="width: 40px;">Selesai</th>
                <th style="width: 45px;">Durasi</th>
                <th>Keterangan Tugas</th>
                <th style="width: 55px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lemburs as $index => $l)
                @php
                    \Carbon\Carbon::setLocale('id');

                    $checkIn = $l->check_in_lembur ? \Carbon\Carbon::parse($l->check_in_lembur) : null;
                    $checkOut = $l->check_out_lembur ? \Carbon\Carbon::parse($l->check_out_lembur) : null;

                    // Kalkulasi Durasi Jam Lembur
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

                    {{-- Hari & Tanggal (Digabung agar menghemat ruang horizontal) --}}
                    <td class="text-center">
                        {{ $checkIn ? $checkIn->isoFormat('dddd') : \Carbon\Carbon::parse($l->tanggal)->isoFormat('dddd') }},<br>
                        {{ \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y') }}
                    </td>

                    {{-- Nama & Tim Kerja --}}
                    <td>
                        <span class="fw-bold">{{ $l->user->name ?? 'User Terhapus' }}</span>
                        @if ($l->user && $l->user->tim_kerja)
                            <br><small class="text-muted" style="font-size: 6.5pt;">({{ $l->user->tim_kerja->nama }})</small>
                        @endif
                    </td>

                    {{-- NIP --}}
                    <td class="text-center">{{ $l->user->nip ?? '-' }}</td>

                    {{-- Jam Masuk & Pulang Lembur --}}
                    <td class="text-center">{{ $checkIn ? $checkIn->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $checkOut ? $checkOut->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $durasiKerja }}</td>

                    {{-- Keterangan Lembur --}}
                    <td>
                        {{ $l->keterangan_lembur ?? '-' }}
                        @if($l->laporan_pdf)
                            <br><small style="font-style: italic; color: #555; font-size: 6.5pt;">(PDF Dilampirkan)</small>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="text-center">
                        @if($l->status == 'disetujui')
                            <span class="status-badge" style="color: #198754;">DISETUJUI</span>
                        @elseif($l->status == 'ditolak')
                            <span class="status-badge" style="color: #dc3545;">DITOLAK</span>
                        @else
                            <span class="status-badge" style="color: #fd7e14;">PENDING</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #666;">
                        Tidak ada data lembur yang ditemukan untuk kriteria filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Blok Tanda Tangan Pengesahan Laporan --}}
    <div class="signature-section">
        <div class="signature-box">
            <p>Pontianak, {{ \Carbon\Carbon::parse($print_date ?? now())->isoFormat('D MMMM Y') }}</p>
            <p style="margin-top: -5px;"><strong>Pengelola Kepegawaian</strong></p>
            <div class="signature-space"></div>
            <p style="text-decoration: underline; font-weight: bold; margin-bottom: 2px;">( .................................................... )</p>
            <p style="margin-top: 0; font-size: 7.5pt;">NIP. ...............................................</p>
        </div>
        <div class="clear"></div>
    </div>
</body>

</html>
