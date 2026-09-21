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

        /* Header Kop Surat Teks Murni (Tanpa Gambar) */
        .header-container {
            width: 100%;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
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
            font-size: 14pt;
            font-weight: bold;
            color: #E28427;
            letter-spacing: 0.5px;
            margin: 3px 0 6px 0;
            text-transform: uppercase;
        }

        .header-address {
            font-size: 8pt;
            color: #333;
            margin: 0;
            line-height: 1.4;
        }

        .header-address .separator {
            margin: 0 6px;
            color: #aaa;
        }

        /* Tabel Informasi Filter & Pegawai (Di atas tabel) */
        .info-laporan {
            width: 100%;
            margin-bottom: 15px;
            font-size: 8.5pt;
            border-collapse: collapse;
        }

        .info-laporan td {
            border: none !important;
            padding: 3px 4px;
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
            padding: 7px 4px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            color: #111;
        }

        table.data-table td {
            padding: 6px 4px;
            vertical-align: middle;
            font-size: 8.5pt;
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
    {{-- Header Kop Surat Teks Murni --}}
    <div class="header-container">
        <div class="header-kemenkes">Kementerian Kesehatan Republik Indonesia</div>
        <div class="header-bkk">Balai Kekarantinaan Kesehatan Kelas I Pontianak</div>
        <div class="header-address">
            Jl. Arteri Supadio No.Km. 17, Limbung, Kec. Sungai Raya, Kabupaten Kubu Raya, Kalimantan Barat 78391
            <br>
            Whatsapp: 62 811-5672-778 <span class="separator">|</span> Website: www.bkkpontianak.id
        </div>
    </div>

    @php
        // Mengambil identitas pegawai dari $user atau dari baris data absensi pertama
        $firstAttendanceUser = $attendances->first()->user ?? null;
        $namaPegawai = $user->name ?? ($firstAttendanceUser->name ?? 'Semua Pegawai');
        $nipPegawai = $user->nip ?? ($firstAttendanceUser->nip ?? '-');
        $timKerja = $user->tim_kerja->nama ?? ($firstAttendanceUser->tim_kerja->nama ?? ($tim_filter ?? '-'));
    @endphp

    {{-- Informasi Pegawai & Filter Laporan (Di atas tabel) --}}
    <table class="info-laporan">
        <tr>
            <td style="width: 14%;"><strong>Jenis Laporan</strong></td>
            <td style="width: 36%;">: Rekapitulasi Absensi Pegawai</td>
            <td style="width: 14%;"><strong>Periode</strong></td>
            <td style="width: 36%;">:
                @if (!empty($start_date) && !empty($end_date))
                    {{ \Carbon\Carbon::parse($start_date)->isoFormat('D MMMM Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->isoFormat('D MMMM Y') }}
                @else
                    Semua Periode
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Nama Pegawai</strong></td>
            <td>: <strong style="font-size: 9pt;">{{ $namaPegawai }}</strong></td>
            <td><strong>Tim Kerja</strong></td>
            <td>: {{ $timKerja }}</td>
        </tr>
        <tr>
            <td><strong>NIP</strong></td>
            <td>: {{ $nipPegawai }}</td>
            <td><strong>Tanggal Cetak</strong></td>
            <td>: {{ \Carbon\Carbon::parse($tanggal_cetak ?? now())->isoFormat('D MMMM Y') }}</td>
        </tr>
    </table>

    {{-- Tabel Data Absensi --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40px;">NO</th>
                <th style="width: 120px;">HARI</th>
                <th style="width: 130px;">TANGGAL</th>
                <th style="width: 110px;">MASUK</th>
                <th style="width: 110px;">PULANG</th>
                <th>JAM KERJA</th>
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

                    {{-- Jam Masuk & Pulang --}}
                    <td class="text-center">{{ $checkIn ? $checkIn->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $checkOut ? $checkOut->format('H:i') : '-' }}</td>

                    {{-- Durasi Jam Kerja --}}
                    <td class="text-center">{{ $durasiKerja }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #666;">
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
