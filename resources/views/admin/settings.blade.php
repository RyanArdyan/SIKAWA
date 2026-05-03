@extends('layouts.admin')

@section('header', 'Pengaturan Sistem')

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3" style="border-top: 5px solid #40BF89;">
                    <h5 class="mb-0 fw-bold" style="color: #2c3e50;">Konfigurasi Jam Kerja SIKAWA</h5>
                </div>
                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 text-white shadow-sm mb-4"
                            role="alert" style="background-color: #40BF89;">
                            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="jam_masuk" class="form-label fw-bold text-secondary">Jam Masuk Kerja</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i
                                            class="bi bi-box-arrow-in-right text-success"></i></span>
                                    <input type="time" name="jam_masuk" id="jam_masuk"
                                        class="form-control form-control-lg @error('jam_masuk') is-invalid @enderror"
                                        value="{{ $jamMasuk }}" required>
                                </div>
                                @error('jam_masuk')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="jam_pulang" class="form-label fw-bold text-secondary">Jam Pulang Kerja</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i
                                            class="bi bi-box-arrow-left text-danger"></i></span>
                                    <input type="time" name="jam_pulang" id="jam_pulang"
                                        class="form-control form-control-lg @error('jam_pulang') is-invalid @enderror"
                                        value="{{ $jamPulang }}" required>
                                </div>
                                @error('jam_pulang')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Ganti bagian alert di dalam form dengan kode ini --}}
                        <div class="alert border-start border-4 mb-4"
                            style="border-color: #40BF89 !important; background-color: rgba(64, 191, 137, 0.1);">
                            <small class="text-body">
                                <i class="bi bi-info-circle-fill me-1" style="color: #40BF89;"></i>
                                Pegawai yang absen melewati <strong>Jam Masuk</strong> akan otomatis ditandai sebagai
                                <span class="text-danger fw-bold">Terlambat</span> pada laporan.
                            </small>
                        </div>

                        <div class="d-grid mt-2">
                            <button type="submit" class="btn text-white btn-lg shadow-sm"
                                style="background-color: #40BF89; border: none;">
                                <i class="bi bi-save me-2"></i> Simpan Konfigurasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #2c3e50, #4a6076);">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-square-fill me-2"></i>Panduan Admin</h5>
                    <p class="small opacity-75">Pengaturan ini berlaku secara global. Perubahan akan langsung berdampak
                        pada:</p>

                    <div class="d-flex mb-3">
                        <div class="me-3 fs-3"><i class="bi bi-graph-up-arrow"></i></div>
                        <div>
                            <h6 class="mb-0">Akurasi Laporan</h6>
                            <small class="opacity-75">Status kehadiran akan dihitung ulang secara real-time.</small>
                        </div>
                    </div>

                    <div class="d-flex mb-3">
                        <div class="me-3 fs-3"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <h6 class="mb-0">Audit Internal</h6>
                            <small class="opacity-75">Sistem mencatat riwayat perubahan untuk keperluan audit.</small>
                        </div>
                    </div>

                    <hr class="opacity-25">

                    <ul class="small ps-3 opacity-75">
                        <li>Gunakan format 24 jam (Contoh: 08:00).</li>
                        <li>Pastikan sesuai dengan zona waktu <strong>WIB</strong>.</li>
                        <li>Perubahan ini tidak mengubah data absensi yang sudah masuk di hari-hari sebelumnya.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
