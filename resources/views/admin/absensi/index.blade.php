@extends('layouts.admin')

@section('header', 'Laporan Absensi Pegawai')

@section('content')
    {{-- Form Filter --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.absensi.report') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary">Cari Pegawai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="nip" class="form-control border-start-0" placeholder="NIP atau Nama..." value="{{ $nip ?? '' }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary">Tim Kerja</label>
                    <select name="tim_kerja_id" class="form-select">
                        <option value="">-- Semua Tim --</option>
                        @foreach($timKerjas as $tim)
                            <option value="{{ $tim->id }}" {{ ($timKerjaId ?? '') == $tim->id ? 'selected' : '' }}>
                                {{ $tim->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-secondary">Periode</label>
                    <select name="periode" class="form-select">
                        <option value="today" {{ ($periode ?? 'today') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="weekly" {{ ($periode ?? '') == 'weekly' ? 'selected' : '' }}>Minggu Ini</option>
                        <option value="monthly" {{ ($periode ?? '') == 'monthly' ? 'selected' : '' }}>Bulan Ini</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn text-white w-100 shadow-sm" style="background-color: #40BF89; border: none;">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    {{-- Tombol Export PDF tetap merah agar terlihat sebagai aksi krusial --}}
                    @if($attendances->count() > 0)
                        <a href="{{ route('admin.absensi.exportPdf', request()->all()) }}" class="btn btn-danger w-100 shadow-sm">
                            <i class="bi bi-file-pdf"></i> PDF
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Riwayat Kehadiran --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3" style="border-top: 5px solid #40BF89;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="color: #2c3e50;">Riwayat Kehadiran</h5>
                <span class="badge px-3 py-2" style="background-color: #40BF89;">
                    <i class="bi bi-clock me-1"></i> Jam Masuk: {{ $jamMasuk }} WIB
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">No</th>
                            <th class="py-3">Aksi</th>
                            <th class="py-3">Nama Pegawai</th>
                            <th class="py-3">Tim Kerja</th>
                            <th class="py-3">NIP</th>
                            <th class="py-3">Waktu Absen</th>
                            <th class="py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $key => $a)
                            <tr>
                                <td class="text-muted">{{ $key + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.laporan.detail', $a->id) }}" class="btn btn-sm text-white px-3" style="background-color: #40BF89;">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                                <td class="fw-bold" style="color: #2c3e50;">{{ $a->user->name ?? 'User Terhapus' }}</td>
                                <td>
                                    <span class="badge px-2 py-1 fw-normal" style="background-color: rgba(64, 191, 137, 0.1); color: #40BF89; border: 1px solid rgba(64, 191, 137, 0.2);">
                                        {{ $a->user->tim_kerja->nama ?? 'Tanpa Tim' }}
                                    </span>
                                </td>
                                <td><span class="badge bg-light text-dark border fw-normal">{{ $a->user->nip ?? '-' }}</span></td>
                                <td class="text-secondary small">{{ \Carbon\Carbon::parse($a->check_in_time)->format('d M Y, H:i') }} WIB</td>
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
                                <td colspan="7" class="text-center text-muted py-5">
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
