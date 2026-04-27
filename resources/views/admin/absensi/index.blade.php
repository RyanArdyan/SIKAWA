@extends('layouts.admin')

@section('header', 'Laporan Absensi Pegawai')

@section('content')
    {{-- Form Filter --}}
    <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
        <div class="card-body p-4">
            <form action="{{ route('admin.absensi.report') }}" method="GET" class="row g-3">
                {{-- Filter Nama/NIP --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">Cari Pegawai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                            <i class="bi bi-search text-body-secondary"></i>
                        </span>
                        <input type="text" name="nip" class="form-control bg-body border-secondary-subtle text-body border-start-0"
                            placeholder="NIP atau Nama..." value="{{ $nip ?? '' }}">
                    </div>
                </div>

                {{-- Filter Tim Kerja --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">Tim Kerja</label>
                    <select name="tim_kerja_id" class="form-select bg-body border-secondary-subtle text-body">
                        <option value="">-- Semua Tim --</option>
                        @foreach ($timKerjas as $tim)
                            <option value="{{ $tim->id }}" {{ ($timKerjaId ?? '') == $tim->id ? 'selected' : '' }}>
                                {{ $tim->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tanggal Mulai --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold text-body-secondary">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control bg-body border-secondary-subtle text-body" value="{{ $start_date }}">
                </div>

                {{-- Filter Tanggal Akhir --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold text-body-secondary">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control bg-body border-secondary-subtle text-body" value="{{ $end_date }}">
                </div>

                {{-- Tombol Aksi --}}
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn text-white w-100 shadow-sm fw-bold"
                        style="background-color: #40BF89; border: none;">
                        <i class="bi bi-filter"></i> Filter
                    </button>

                    @if ($attendances->count() > 0)
                        <a href="{{ route('admin.absensi.exportPdf', request()->all()) }}"
                            class="btn btn-danger w-100 shadow-sm fw-bold">
                            <i class="bi bi-file-pdf"></i> PDF
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Riwayat Kehadiran --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-body">Riwayat Kehadiran</h5>
                <span class="badge px-3 py-2" style="background-color: #40BF89;">
                    <i class="bi bi-clock me-1"></i> Jam Masuk: {{ $jamMasuk }} WIB
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="py-3 text-body-secondary">No</th>
                            <th class="py-3 text-body-secondary">Aksi</th>
                            <th class="py-3 text-body-secondary">Nama Pegawai</th>
                            <th class="py-3 text-body-secondary">Tim Kerja</th>
                            <th class="py-3 text-body-secondary">NIP</th>
                            <th class="py-3 text-body-secondary">Waktu Absen</th>
                            <th class="py-3 text-body-secondary">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $key => $a)
                            <tr>
                                <td class="text-body-secondary small">{{ $key + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.laporan.detail', $a->id) }}"
                                        class="btn btn-sm text-white px-3 fw-medium" style="background-color: #40BF89;">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                                <td class="fw-bold text-body">{{ $a->user->name ?? 'User Terhapus' }}</td>
                                <td>
                                    <span class="badge px-2 py-1 fw-normal"
                                        style="background-color: rgba(64, 191, 137, 0.1); color: #40BF89; border: 1px solid rgba(64, 191, 137, 0.2);">
                                        {{ $a->user->tim_kerja->nama ?? 'Tanpa Tim' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-body border border-secondary-subtle fw-normal">
                                        {{ $a->user->nip ?? '-' }}
                                    </span>
                                </td>
                                <td class="text-body-secondary small">
                                    {{ \Carbon\Carbon::parse($a->check_in_time)->format('d M Y, H:i') }} WIB
                                </td>
                                <td>
                                    @if ($a->status == 'terlambat')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3">
                                            <i class="bi bi-exclamation-circle me-1"></i> Terlambat
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                                            <i class="bi bi-check2-circle me-1"></i> Tepat Waktu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-body-secondary py-5">
                                    <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
                                    Tidak ada data absensi yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
