@extends('layouts.admin')

@section('header', 'Detail Kehadiran Pegawai')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.absensi.report') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Laporan
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold text-muted text-uppercase small mb-3">Profil Pegawai</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                            <i class="bi bi-person-fill fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-0 fw-bold">{{ $attendance->user->name }}</h5>
                            <span class="text-muted small">NIP: {{ $attendance->user->nip }}</span>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25">

                    <div class="mb-3">
                        <label class="small text-muted d-block">Status Kehadiran</label>
                        @if($attendance->status == 'terlambat')
                            <span class="badge bg-danger">TERLAMBAT</span>
                        @else
                            <span class="badge bg-success">TEPAT WAKTU</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted d-block">Koordinat Lokasi Masuk</label>
                        <code class="text-primary">{{ $attendance->latitude_in ?? '-' }}, {{ $attendance->longitude_in ?? '-' }}</code>
                        @if($attendance->latitude_in)
                            <div class="mt-2">
                                <a href="https://www.google.com/maps?q={{ $attendance->latitude_in }},{{ $attendance->longitude_in }}"
                                   target="_blank" class="btn btn-sm btn-light border w-100">
                                    <i class="bi bi-geo-alt text-danger me-1"></i> Buka Google Maps
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($attendance->laporan_pdf)
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h6 class="fw-bold mb-2"><i class="bi bi-file-earmark-pdf me-1"></i> Laporan Kerja PDF</h6>
                    <p class="small opacity-75">Pegawai telah mengunggah laporan harian.</p>
                    <a href="{{ asset('storage/' . $attendance->laporan_pdf) }}" target="_blank" class="btn btn-light btn-sm w-100 fw-bold text-primary">
                        <i class="bi bi-download me-1"></i> Unduh / Lihat Laporan
                    </a>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-success"><i class="bi bi-box-arrow-in-right me-2"></i>Bukti Absen Masuk</h6>
                        </div>
                        <div class="card-body p-2 bg-light text-center">
                            <img src="{{ asset('storage/' . $attendance->photo_path) }}"
                                 class="img-fluid rounded shadow-sm border"
                                 style="max-height: 400px; width: 100%; object-fit: cover;" alt="Foto Masuk">
                        </div>
                        <div class="card-footer bg-white border-0 text-center py-3">
                            <span class="text-muted small"><i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') }} WIB</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-box-arrow-right me-2"></i>Bukti Absen Pulang</h6>
                        </div>
                        <div class="card-body p-2 bg-light text-center d-flex align-items-center justify-content-center">
                            @if($attendance->photo_path_out)
                                <img src="{{ asset('storage/' . $attendance->photo_path_out) }}"
                                     class="img-fluid rounded shadow-sm border"
                                     style="max-height: 400px; width: 100%; object-fit: cover;" alt="Foto Pulang">
                            @else
                                <div class="py-5 text-muted">
                                    <i class="bi bi-camera-video-off display-4 opacity-25"></i>
                                    <p class="mt-2 small">Belum melakukan absen pulang</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white border-0 text-center py-3">
                            @if($attendance->check_out_time)
                                <span class="text-muted small"><i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i:s') }} WIB</span>
                            @else
                                <span class="badge bg-light text-muted border">KOSONG</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
