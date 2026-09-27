@extends('layouts.admin')

@section('header', 'Penyesuaian Lupa Absen & Ubah Koordinat')

@section('content')
<div class="row">
    <div class="col-md-10 col-lg-8">
        <div class="card border-0 shadow-sm bg-body-tertiary">
            {{-- Header dengan aksen hijau khas SIKAWA --}}
            <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                <h6 class="mb-0 fw-bold text-body">
                    <i class="bi bi-clock-history me-2 text-success"></i>Form Lupa Absensi & Ubah Koordinat
                </h6>
            </div>

            <div class="card-body p-4">
                {{-- Informasi Pegawai (Read Only) --}}
                <div class="d-flex align-items-center mb-4 p-3 bg-body rounded border border-secondary-subtle">
                    <div class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-person-badge text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-body">{{ $attendance->user->name }}</h6>
                        <small class="text-body-secondary">NIP: {{ $attendance->user->nip }}</small>
                    </div>
                </div>

                <form action="{{ route('admin.laporan.updateLupaAbsen', $attendance->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- SECTION 1: PRESENSI MASUK --}}
                    <div class="card border border-secondary-subtle mb-4 bg-body">
                        <div class="card-header bg-success bg-opacity-10 border-0 fw-bold text-success">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Data Presensi Masuk (Check In)
                        </div>
                        <div class="card-body">
                            {{-- Input Jam Masuk --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold text-body-secondary">Jam Masuk</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                        <i class="bi bi-clock text-success"></i>
                                    </span>
                                    <input type="time" name="check_in_time"
                                        class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('check_in_time') is-invalid @enderror"
                                        value="{{ old('check_in_time', \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i')) }}" required>
                                </div>
                                @error('check_in_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Input Foto Masuk --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold text-body-secondary">Foto Presensi Masuk</label>
                                @if($attendance->photo_path)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $attendance->photo_path) }}" alt="Foto Masuk" class="img-thumbnail rounded" style="max-height: 100px; object-fit: cover;">
                                        <small class="d-block text-muted">Foto saat ini</small>
                                    </div>
                                @endif
                                <div class="input-group">
                                    <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                        <i class="bi bi-camera text-success"></i>
                                    </span>
                                    <input type="file" name="photo_path" accept="image/*"
                                        class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('photo_path') is-invalid @enderror">
                                </div>
                                <div class="form-text mt-1 text-body-secondary" style="font-size: 0.8rem;">
                                    Kosongkan jika tidak ingin mengubah/menambahkan foto masuk.
                                </div>
                                @error('photo_path') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Input Koordinat Masuk --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold text-body-secondary">
                                    <i class="bi bi-geo-alt-fill text-success me-1"></i> Koordinat Lokasi Masuk
                                </label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-body border-secondary-subtle text-body-secondary">Lat</span>
                                            <input type="text" name="latitude_in" id="latitude_in"
                                                class="form-control bg-body border-secondary-subtle text-body @error('latitude_in') is-invalid @enderror"
                                                placeholder="-0.0263889"
                                                value="{{ old('latitude_in', $attendance->latitude_in) }}">
                                        </div>
                                        @error('latitude_in') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-body border-secondary-subtle text-body-secondary">Long</span>
                                            <input type="text" name="longitude_in" id="longitude_in"
                                                class="form-control bg-body border-secondary-subtle text-body @error('longitude_in') is-invalid @enderror"
                                                placeholder="109.3425000"
                                                value="{{ old('longitude_in', $attendance->longitude_in) }}">
                                        </div>
                                        @error('longitude_in') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: PRESENSI KELUAR --}}
                    <div class="card border border-secondary-subtle mb-4 bg-body">
                        <div class="card-header bg-danger bg-opacity-10 border-0 fw-bold text-danger">
                            <i class="bi bi-box-arrow-left me-1"></i> Data Presensi Keluar (Check Out)
                        </div>
                        <div class="card-body">
                            {{-- Input Jam Pulang --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold text-body-secondary">Jam Pulang</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                        <i class="bi bi-clock text-danger"></i>
                                    </span>
                                    <input type="time" name="check_out_time"
                                        class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('check_out_time') is-invalid @enderror"
                                        value="{{ old('check_out_time', $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '') }}">
                                </div>
                                <div class="form-text mt-1 text-body-secondary" style="font-size: 0.8rem;">
                                    <i class="bi bi-info-circle me-1 text-info"></i> Kosongkan jika pegawai belum melapor jam pulang.
                                </div>
                                @error('check_out_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Input Foto Keluar --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold text-body-secondary">Foto Presensi Keluar</label>
                                @if($attendance->photo_path_out)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $attendance->photo_path_out) }}" alt="Foto Keluar" class="img-thumbnail rounded" style="max-height: 100px; object-fit: cover;">
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
                                @error('photo_path_out') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            {{-- Input Koordinat Keluar --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold text-body-secondary">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Koordinat Lokasi Keluar
                                </label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-body border-secondary-subtle text-body-secondary">Lat</span>
                                            <input type="text" name="latitude_out" id="latitude_out"
                                                class="form-control bg-body border-secondary-subtle text-body @error('latitude_out') is-invalid @enderror"
                                                placeholder="-0.0263889"
                                                value="{{ old('latitude_out', $attendance->latitude_out) }}">
                                        </div>
                                        @error('latitude_out') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-body border-secondary-subtle text-body-secondary">Long</span>
                                            <input type="text" name="longitude_out" id="longitude_out"
                                                class="form-control bg-body border-secondary-subtle text-body @error('longitude_out') is-invalid @enderror"
                                                placeholder="109.3425000"
                                                value="{{ old('longitude_out', $attendance->longitude_out) }}">
                                        </div>
                                        @error('longitude_out') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Alert Info Sinkronisasi Status --}}
                    <div class="alert border-0 bg-info bg-opacity-10 py-3 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-square-fill text-info fs-5 me-3"></i>
                            <div>
                                <p class="mb-0 text-body-secondary" style="font-size: 0.85rem;">
                                    Status kehadiran akan otomatis diperbarui menjadi <strong>Hadir</strong> setelah data disimpan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.absensi.report') }}"
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
