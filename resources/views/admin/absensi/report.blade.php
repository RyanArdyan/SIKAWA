<!DOCTYPE html>
<html>

<head>
    <title>Laporan Absensi BKK</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>BALAI KEKARANTINAAN KESEHATAN PONTIANAK</h3>
        <h4>LAPORAN REKAPITULASI ABSENSI PEGAWAI</h4>
        <p>Periode: {{ ucfirst($periode) }} | Tim: {{ $tim_filter }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Tim Kerja</th>
                <th>Waktu Masuk</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $index => $a)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $a->user->name }}</td>
                    <td>{{ $a->user->nip }}</td>
                    <td>{{ $a->user->tim_kerja->nama ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($a->check_in_time)->format('d/m/Y H:i') }}</td>
                    <td>{{ ucfirst($a->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p style="text-align: right;">Dicetak pada: {{ $tanggal_cetak }}</p>
</body>

</html>
