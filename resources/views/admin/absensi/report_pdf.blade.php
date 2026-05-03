<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Pegawai BKK Pontianak</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }

        .header h2,
        .header h3 {
            margin: 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 8pt;
            font-style: italic;
        }

        .info-laporan {
            margin-bottom: 15px;
            width: 100%;
            font-size: 9pt;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
        }

        td {
            padding: 6px 4px;
            vertical-align: middle;
            font-size: 9pt;
        }

        .text-center {
            text-align: center;
        }

        .status-hadir {
            color: #198754;
            font-weight: bold;
        }

        .status-terlambat {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h3>
        <h2>BALAI KEKARANTINAAN KESEHATAN KELAS I PONTIANAK</h2>
        <p>Jl. Jenderal Ahmad Yani, Arang Limbung, Kec. Sungai Raya, Kabupaten Kubu Raya, Kalimantan Barat 78391</p>
    </div>

    {{-- Tabel Informasi Filter Laporan --}}
    <table class="info-laporan" style="border: none !important;">
        <tr style="border: none !important;">
            <td style="border: none; width: 15%;"><strong>Jenis Laporan</strong></td>
            <td style="border: none; width: 35%;">: Rekapitulasi Absensi Pegawai</td>

            <td style="border: none; width: 15%;"><strong>Periode</strong></td>
            <td style="border: none; width: 35%;">:
                {{-- Perbaikan: Variabel $start_date dan $end_date sudah berupa string dari Controller --}}
                @if (!empty($start_date) && !empty($end_date))
                    {{ $start_date }} s/d {{ $end_date }}
                @else
                    Semua Periode
                @endif
            </td>
        </tr>
        <tr style="border: none !important;">
            <td style="border: none;"><strong>Nama Pegawai</strong></td>
            <td style="border: none;">: {{ $user->name ?? 'Semua Pegawai' }}</td>

            <td style="border: none;"><strong>Dicetak</strong></td>
            <td style="border: none;">: {{ $tanggal_cetak }}</td>
        </tr>
        <tr style="border: none !important;">
            <td style="border: none;"><strong>Tim Kerja</strong></td>
            <td style="border: none;">: {{ $tim_filter }}</td>

            <td style="border: none;"><strong>Tipe Absen</strong></td>
            <td style="border: none;">: {{ strtoupper($tipe_filter ?? 'Semua') }}</td>
        </tr>
    </table>

    {{-- Tabel Data Absensi --}}
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Tanggal</th>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Tipe</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $a)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        {{ $a->check_in_time ? \Carbon\Carbon::parse($a->check_in_time)->translatedFormat('d/m/Y') : '-' }}
                    </td>
                    <td>{{ $a->user->name ?? 'User Terhapus' }}</td>
                    <td class="text-center">{{ $a->user->nip ?? '-' }}</td>
                    <td class="text-center">{{ $a->tipe_absen ?? '-' }}</td>
                    <td class="text-center">
                        {{ $a->check_in_time ? \Carbon\Carbon::parse($a->check_in_time)->format('H:i') : '-' }}
                    </td>
                    <td class="text-center">
                        {{ $a->check_out_time ? \Carbon\Carbon::parse($a->check_out_time)->format('H:i') : '-' }}
                    </td>
                    <td class="text-center">
                        <span class="{{ $a->status == 'terlambat' ? 'status-terlambat' : 'status-hadir' }}">
                            {{ strtoupper($a->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">
                        Tidak ada data absensi untuk periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
