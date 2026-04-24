@extends('layouts.app') {{-- Sesuaikan dengan layout utama kamu --}}

@section('content')
<div class="container py-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Cek Riwayat Absensi</h5>
            <form action="{{ route('absen.riwayat') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="nip" class="form-control" placeholder="Masukkan NIP" value="{{ $nip }}" required>
                </div>
                <div class="col-md-4">
                    <select name="filter" class="form-select">
                        <option value="today" {{ $filter == 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="weekly" {{ $filter == 'weekly' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="monthly" {{ $filter == 'monthly' ? 'selected' : '' }}>Bulan Ini</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>

                    {{-- Tombol muncul hanya jika user ditemukan --}}
                    @if($user && $attendances->count() > 0)
                        <a href="{{ route('absen.exportPdf', ['nip' => $nip, 'filter' => $filter]) }}" class="btn btn-danger w-100">
                            PDF
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($user)
    <div class="alert alert-info">
        Menampilkan data untuk: <strong>{{ $user->name }}</strong>
    </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal DFSA</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
                        <td>{{ $item->check_in_time ? \Carbon\Carbon::parse($item->check_in_time)->format('H:i') : '-' }}</td>
                        <td>{{ $item->check_out_time ? \Carbon\Carbon::parse($item->check_out_time)->format('H:i') : '-' }}</td>
                        <td>
                            <span class="badge {{ $item->status == 'terlambat' ? 'bg-danger' : 'bg-success' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Data absensi belum tersedia.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
