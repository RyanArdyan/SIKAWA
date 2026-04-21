@extends('layouts.admin')

@section('header', 'Pengaturan Sistem')

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary">Konfigurasi Jam Kerja</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="jam_masuk" class="form-label fw-bold">Jam Masuk Kerja</label>
                            <input type="time" name="jam_masuk" id="jam_masuk"
                                   class="form-control form-control-lg @error('jam_masuk') is-invalid @enderror"
                                   value="{{ $jamMasuk }}" required>
                            @error('jam_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="jam_pulang" class="form-label fw-bold">Jam Pulang Kerja</label>
                            <input type="time" name="jam_pulang" id="jam_pulang"
                                   class="form-control form-control-lg @error('jam_pulang') is-invalid @enderror"
                                   value="{{ $jamPulang }}" required>
                            @error('jam_pulang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info border-0 bg-light">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Pegawai yang absen melewati <strong>Jam Masuk</strong> akan otomatis ditandai sebagai
                            <span class="text-danger fw-bold">Terlambat</span> pada laporan.
                        </small>
                    </div>

                    <div class="d-grid mt-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body p-4">
                <h5 class="fw-bold"><i class="bi bi-info-square me-2"></i>Panduan</h5>
                <p class="text-muted">Pengaturan ini berlaku untuk seluruh pegawai <strong>BKK Kelas I Pontianak</strong>. Perubahan akan langsung berdampak pada:</p>
                <ul class="text-muted">
                    <li>Label status di Laporan Absensi.</li>
                    <li>Perhitungan keterlambatan sistem.</li>
                </ul>
                <hr>
                <ul class="text-muted small ps-3">
                    <li>Gunakan format 24 jam (Contoh: 08:00).</li>
                    <li>Pastikan jam sudah sesuai dengan zona waktu <strong>WIB</strong>.</li>
                    <li>Sistem akan mencatat riwayat perubahan untuk audit internal.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
