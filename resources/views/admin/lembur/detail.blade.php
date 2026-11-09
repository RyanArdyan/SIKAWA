@extends('layouts.admin')

@section('header', 'Detail Laporan Lembur Pegawai')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('admin.lembur.report') }}" class="btn btn-outline-secondary btn-sm shadow-sm" style="border-radius: 8px;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Laporan Lembur
        </a>
    </div>

    <div class="row">
        {{-- Sisi Kiri: Profil Pegawai & Keterangan --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase small mb-4">Profil Pegawai Lembur</h6>
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 55px; height: 55px;">
                            <i class="bi bi-person-fill fs-3"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="mb-1 fw-bold text-dark">{{ $lembur->user->name }}</h5>
                            <span class="text-secondary small">NIP: {{ $lembur->user->nip }}</span><br>
                            <span class="badge bg-light text-secondary border mt-1">
                                <i class="bi bi-people-fill me-1"></i> {{ $lembur->user->tim_kerja->nama ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 mb-4">

                    <div class="mb-3">
                        <label class="small text-muted fw-bold d-block mb-1">Tanggal Lembur</label>
                        <span class="text-dark fw-medium">
                            <i class="bi bi-calendar-check me-1 text-primary"></i>
                            {{ \Carbon\Carbon::parse($lembur->tanggal)->translatedFormat('d F Y') }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted fw-bold d-block mb-2">Status Approval</label>
                        @if($lembur->status == 'disetujui')
                            <span class="badge px-3 py-2 bg-success bg-opacity-10 text-success border border-success">
                                <i class="bi bi-check-circle me-1"></i> DISETUJUI
                            </span>
                        @elseif($lembur->status == 'ditolak')
                            <span class="badge px-3 py-2 bg-danger bg-opacity-10 text-danger border border-danger">
                                <i class="bi bi-x-circle me-1"></i> DITOLAK
                            </span>
                        @else
                            <span class="badge px-3 py-2 bg-warning bg-opacity-10 text-warning border border-warning">
                                <i class="bi bi-hourglass-split me-1"></i> PENDING
                            </span>
                        @endif
                    </div>

                    {{-- Keterangan Pekerjaan Lembur (Jika ada) --}}
                    @if($lembur->keterangan_lembur)
                    <div class="mb-2 p-3 bg-light rounded border">
                        <label class="small text-muted fw-bold d-block mb-1"><i class="bi bi-card-text me-1"></i> Keterangan Pekerjaan</label>
                        <p class="mb-0 text-dark small">{{ $lembur->keterangan_lembur }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Foto Bukti Lembur --}}
        <div class="col-md-8">
            <div class="row g-3">
                {{-- Foto Masuk Lembur --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-box-arrow-in-right me-2"></i>Bukti Masuk Lembur</h6>
                        </div>
                        <div class="card-body p-3 bg-light text-center d-flex align-items-center justify-content-center">
                            @if($lembur->photo_path_in)
                                <img src="{{ asset('storage/lemburs/' . $lembur->photo_path_in) }}"
                                     class="img-fluid rounded shadow-sm border"
                                     style="max-height: 400px; width: 100%; object-fit: cover;" alt="Foto Masuk Lembur">
                            @else
                                <div class="py-5 text-muted">
                                    <i class="bi bi-image display-4 opacity-25"></i>
                                    <p class="mt-2 small">Tidak ada foto masuk</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white border-0 text-center py-3">
                            @if($lembur->check_in_lembur)
                                <span class="text-dark fw-bold small"><i class="bi bi-clock me-1 text-primary"></i> {{ \Carbon\Carbon::parse($lembur->check_in_lembur)->format('H:i:s') }} WIB</span>
                            @else
                                <span class="badge bg-light text-muted border">KOSONG</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Foto Pulang Lembur --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-box-arrow-right me-2"></i>Bukti Pulang Lembur</h6>
                        </div>
                        <div class="card-body p-3 bg-light text-center d-flex align-items-center justify-content-center">
                            @if($lembur->photo_path_out)
                                <img src="{{ asset('storage/lemburs/' . $lembur->photo_path_out) }}"
                                     class="img-fluid rounded shadow-sm border"
                                     style="max-height: 400px; width: 100%; object-fit: cover;" alt="Foto Pulang Lembur">
                            @else
                                <div class="py-5 text-muted">
                                    <i class="bi bi-camera-video-off display-4 opacity-25"></i>
                                    <p class="mt-2 small">Belum melakukan presensi pulang</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white border-0 text-center py-3">
                            @if($lembur->check_out_lembur)
                                <span class="text-dark fw-bold small"><i class="bi bi-clock me-1 text-warning"></i> {{ \Carbon\Carbon::parse($lembur->check_out_lembur)->format('H:i:s') }} WIB</span>
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
