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
            border-bottom: 3px double #000; /* Garis ganda khas surat dinas */
            padding-bottom: 10px;
            position: relative;
        }

        /* Styling Logo di PDF (Opsional jika ingin pakai logo) */
        .logo-kiri {
            position: absolute;
            left: 0;
            top: 0;
            height: 60px;
        }

        .header h2, .header h3 {
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
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

        .text-center { text-align: center; }

        .status-hadir { color: #198754; font-weight: bold; }
        .status-terlambat { color: #dc3545; font-weight: bold; }

        .footer {
            margin-top: 30px;
            float: right;
            width: 250px;
            text-align: center;
        }

        .space-tanda-tangan { height: 60px; }
    </style>
</head>

<body>
    <div class="header">
        {{-- Jika ingin menambah logo, uncomment baris di bawah ini --}}
        {{-- <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/logo-kemenkes.png'))) }}" class="logo-kiri"> --}}

        <h3>KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h3>
        <h2>BALAI KEKARANTINAAN KESEHATAN KELAS I PONTIANAK</h2>
        <p>Jl. Rahadi Usman No.2, Kota Pontianak, Kalimantan Barat</p>
    </div>

    <table class="info-laporan" style="border: none;">
        <tr style="border: none;">
            <td style="border: none; width: 100px;"><strong>Jenis Laporan</strong></td>
            <td style="border: none;">: Rekapitulasi Absensi Pegawai</td>
            {{-- PERBAIKAN: Gunakan variabel 'filter' jika dari Controller mengirim 'filter' --}}
            <td style="border: none; text-align: right;"><strong>Periode:</strong> {{ ucfirst($filter ?? $periode) }}</td>
        </tr>
        <tr style="border: none;">
            <td style="border: none;"><strong>Nama Pegawai</strong></td>
            <td style="border: none;">: {{ $user->name ?? 'Semua Pegawai' }}</td>
            <td style="border: none; text-align: right;"><strong>Dicetak:</strong> {{ $tanggal_cetak }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Tanggal</th>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $a)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    {{-- PERBAIKAN: Gunakan created_at untuk tanggal record jika check_in_time null --}}
                    <td class="text-center">{{ \Carbon\Carbon::parse($a->created_at)->translatedFormat('d/m/Y') }}</td>
                    <td>{{ $a->user->name ?? 'User Terhapus' }}</td>
                    <td class="text-center">{{ $a->user->nip ?? '-' }}</td>
                    <td class="text-center">{{ $a->check_in_time ? \Carbon\Carbon::parse($a->check_in_time)->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $a->check_out_time ? \Carbon\Carbon::parse($a->check_out_time)->format('H:i') : '-' }}</td>
                    <td class="text-center">
                        <span class="{{ $a->status == 'terlambat' ? 'status-terlambat' : 'status-hadir' }}">
                            {{ strtoupper($a->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">Tidak ada data absensi untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Pontianak, {{ $tanggal_cetak }}</p>
        <p>Petugas Administrasi,</p>
        <div class="space-tanda-tangan"></div>
        <p><strong>{{ auth()->user()->name ?? '( __________________________ )' }}</strong></p>
        <p>BKK Kelas I Pontianak</p>
    </div>
</body>
</html>
