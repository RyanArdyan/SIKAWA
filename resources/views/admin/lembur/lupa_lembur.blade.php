@extends('layouts.admin')

@section('header', 'Penyesuaian Lupa Lembur')

@section('content')
    <div class="row">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm bg-body-tertiary">
                {{-- Header dengan aksen hijau khas SIKAWA --}}
                <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                    <h6 class="mb-0 fw-bold text-body">
                        <i class="bi bi-clock-history me-2 text-success"></i>Form Penyesuaian Lupa Lembur
                    </h6>
                </div>

                <div class="card-body p-4">
                    {{-- Informasi Pegawai (Read Only) --}}
                    <div class="d-flex align-items-center mb-4 p-3 bg-body rounded border border-secondary-subtle">
                        <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                            <i class="bi bi-person-badge text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-body">{{ $lembur->user->name ?? 'User Terhapus' }}</h6>
                            <small class="text-body-secondary">NIP: {{ $lembur->user->nip ?? '-' }}</small>
                        </div>
                    </div>

                    <form action="{{ route('admin.lembur.updateLupaLembur', $lembur->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- SECTION 1: LEMBUR MASUK --}}
                        <div class="card border border-secondary-subtle mb-4 bg-body">
                            <div class="card-header bg-success bg-opacity-10 border-0 fw-bold text-success">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Data Lembur Masuk (Check In)
                            </div>
                            <div class="card-body">
                                {{-- Input Waktu Masuk --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-body-secondary">Waktu Masuk</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                            <i class="bi bi-calendar-event text-success"></i>
                                        </span>
                                        <input type="datetime-local" name="check_in_lembur"
                                            class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('check_in_lembur') is-invalid @enderror"
                                            value="{{ old('check_in_lembur', $lembur->check_in_lembur ? \Carbon\Carbon::parse($lembur->check_in_lembur)->format('Y-m-d\TH:i') : '') }}"
                                            required>
                                    </div>
                                    @error('check_in_lembur')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Input Foto Presensi Masuk --}}
                                <div class="mb-1">
                                    <label class="form-label fw-bold text-body-secondary">Foto Presensi Masuk</label>
                                    @if ($lembur->photo_in_url)
                                        <div class="mb-2">
                                            <img src="{{ $lembur->photo_in_url }}"
                                                alt="Foto Masuk" class="img-thumbnail rounded"
                                                style="max-height: 100px; object-fit: cover;">
                                            <small class="d-block text-muted">Foto saat ini</small>
                                        </div>
                                    @endif
                                    <div class="input-group">
                                        <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                            <i class="bi bi-camera text-success"></i>
                                        </span>
                                        <input type="file" name="photo_path_in" accept="image/*"
                                            class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('photo_path_in') is-invalid @enderror">
                                    </div>
                                    <div class="form-text mt-1 text-body-secondary" style="font-size: 0.8rem;">
                                        Kosongkan jika tidak ingin mengubah/menambahkan foto masuk.
                                    </div>
                                    @error('photo_path_in')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 2: LEMBUR KELUAR --}}
                        <div class="card border border-secondary-subtle mb-4 bg-body">
                            <div class="card-header bg-danger bg-opacity-10 border-0 fw-bold text-danger">
                                <i class="bi bi-box-arrow-left me-1"></i> Data Lembur Keluar (Check Out)
                            </div>
                            <div class="card-body">
                                {{-- Input Waktu Pulang --}}
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-body-secondary">Waktu Pulang</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                            <i class="bi bi-calendar-event text-danger"></i>
                                        </span>
                                        <input type="datetime-local" name="check_out_lembur"
                                            class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('check_out_lembur') is-invalid @enderror"
                                            value="{{ old('check_out_lembur', $lembur->check_out_lembur ? \Carbon\Carbon::parse($lembur->check_out_lembur)->format('Y-m-d\TH:i') : '') }}">
                                    </div>
                                    <div class="form-text mt-1 text-body-secondary" style="font-size: 0.8rem;">
                                        <i class="bi bi-info-circle me-1 text-info"></i> Kosongkan jika pegawai belum
                                        melapor waktu pulang lembur.
                                    </div>
                                    @error('check_out_lembur')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Input Foto Presensi Keluar --}}
                                <div class="mb-1">
                                    <label class="form-label fw-bold text-body-secondary">Foto Presensi Keluar</label>
                                    @if ($lembur->photo_out_url)
                                        <div class="mb-2">
                                            <img src="{{ $lembur->photo_out_url }}"
                                                alt="Foto Keluar" class="img-thumbnail rounded"
                                                style="max-height: 100px; object-fit: cover;">
                                            <small class="d-block text-muted">Foto saat ini</small>
                                        </div>
                                    @endif
                                    <div class="input-group">
                                        <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                            <i class="bi bi-camera text-danger"></i>
                                        </span>
                                        <input type="file" name="photo_path_out" accept="image/*"
                                            class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('photo_path_out') is-invalid @enderror">
                                    </div>
                                    <div class="form-text mt-1 text-body-secondary" style="font-size: 0.8rem;">
                                        Kosongkan jika tidak ingin mengubah/menambahkan foto keluar.
                                    </div>
                                    @error('photo_path_out')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Alert Info Sinkronisasi Status --}}
                        <div class="alert border-0 bg-info bg-opacity-10 py-3 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-info-square-fill text-info fs-5 me-3"></i>
                                <div>
                                    <p class="mb-0 text-body-secondary" style="font-size: 0.85rem;">
                                        Status pengajuan lembur akan otomatis diperbarui menjadi <strong>Disetujui</strong> setelah
                                        data disimpan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.lembur.report') }}"
                                class="btn btn-secondary bg-opacity-10 text-body border-0 px-4 fw-medium">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn text-white px-4 shadow-sm fw-bold"
                                style="background-color: #40BF89; border: none;">
                                <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
