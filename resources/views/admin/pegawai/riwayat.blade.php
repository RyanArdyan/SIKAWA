@extends('layouts.admin') {{-- Sesuaikan dengan nama layout admin kamu --}}

@section('header', 'Riwayat Absensi Pegawai')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-primary">Cek Riwayat Absensi Mandiri</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('absen.riwayat') }}" method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Nomor Induk Pegawai (NIP)</label>
                            <input type="text" name="nip" class="form-control"
                                   placeholder="Masukkan NIP (Contoh: 123456789)"
                                   value="{{ request('nip') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Periode Waktu</label>
                            <select name="filter" class="form-select">
                                <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                                <option value="weekly" {{ request('filter') == 'weekly' ? 'selected' : '' }}>Minggu Ini</option>
                                <option value="monthly" {{ request('filter') == 'monthly' ? 'selected' : '' }}>Bulan Ini</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Cari
                            </button>

                            @if(isset($attendances) && $attendances->count() > 0)
                                <a href="{{ route('absen.exportPdf', ['nip' => request('nip'), 'filter' => request('filter')]) }}"
                                   class="btn btn-danger w-100">
                                    <i class="bi bi-file-pdf"></i> Export
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if(request('nip'))
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Tanggal</th>
                                    <th class="py-3">Jam Masuk</th>
                                    <th class="py-3">Jam Pulang</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $item)
                                <tr>
                                    <td class="fw-medium">
                                        {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $item->check_in_time ? \Carbon\Carbon::parse($item->check_in_time)->format('H:i') : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $item->check_out_time ? \Carbon\Carbon::parse($item->check_out_time)->format('H:i') : 'Belum Pulang' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->status == 'terlambat')
                                            <span class="badge bg-danger">Terlambat</span>
                                        @else
                                            <span class="badge bg-success">Hadir</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{-- Tambahkan tombol detail jika diperlukan di masa depan --}}
                                        <button class="btn btn-sm btn-outline-secondary" title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-info-circle fs-2 d-block mb-2"></i>
                                            Tidak ada data absensi ditemukan untuk NIP <strong>{{ request('nip') }}</strong> pada periode ini.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="alert alert-info border-0 shadow-sm">
                <i class="bi bi-info-circle me-2"></i> Silakan masukkan NIP pegawai untuk melihat riwayat absensi.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
