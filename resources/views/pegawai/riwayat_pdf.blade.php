<!DOCTYPE html>
<html>

<head>
    <title>Riwayat Absensi - {{ $user->name }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #444;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-hadir {
            color: green;
            font-weight: bold;
        }

        .status-terlambat {
            color: red;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: right;
            margin-right: 50px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>BALAI KEKARANTINAAN KESEHATAN PONTIANAK</h2>
        <p style="margin: 5px 0;">Laporan Riwayat Absensi Mandiri Pegawai</p>
    </div>

    <div class="info">
        <table>
            <tr style="border: none;">
                <td style="border: none; width: 100px; padding: 2px;"><strong>Nama</strong></td>
                <td style="border: none; padding: 2px;">: {{ $user->name }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px;"><strong>NIP</strong></td>
                <td style="border: none; padding: 2px;">: {{ $user->nip }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px;"><strong>Periode</strong></td>
                <td style="border: none; padding: 2px;">: {{ ucfirst($filter) }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Pulang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
                    <td>{{ $item->check_in_time ? \Carbon\Carbon::parse($item->check_in_time)->format('H:i') : '-' }}
                    </td>
                    <td>{{ $item->check_out_time ? \Carbon\Carbon::parse($item->check_out_time)->format('H:i') : '-' }}
                    </td>
                    <td>
                        <span class="{{ $item->status == 'terlambat' ? 'status-terlambat' : 'status-hadir' }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Pontianak, {{ $tanggal }}</p>
        <br><br><br>
        <p><strong>( {{ $user->name }} )</strong></p>
    </div>
</body>

</html>
