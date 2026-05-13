@extends('layouts.admin')

@section('header', 'Penyesuaian Lupa Absen')

@section('content')
<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm bg-body-tertiary">
            {{-- Header dengan aksen hijau khas SIKAWA --}}
            <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                <h6 class="mb-0 fw-bold text-body">
                    <i class="bi bi-clock-history me-2 text-success"></i>Form Lupa Absensi
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

                <form action="{{ route('admin.laporan.updateLupaAbsen', $attendance->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Input Jam Masuk --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-body-secondary">Jam Masuk (Check In)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                <i class="bi bi-box-arrow-in-right text-success"></i>
                            </span>
                            <input type="time" name="check_in_time"
                                class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('check_in_time') is-invalid @enderror"
                                value="{{ old('check_in_time', \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i')) }}" required>
                        </div>
                        @error('check_in_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    {{-- Input Jam Pulang --}}
                    <div class="mb-4">
                        <label class="form-label fw-bold text-body-secondary">Jam Pulang (Check Out)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                <i class="bi bi-box-arrow-left text-danger"></i>
                            </span>
                            <input type="time" name="check_out_time"
                                class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('check_out_time') is-invalid @enderror"
                                value="{{ old('check_out_time', $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i') : '') }}">
                        </div>
                        <div class="form-text mt-2 text-body-secondary" style="font-size: 0.8rem;">
                            <i class="bi bi-info-circle me-1 text-info"></i> Kosongkan jika pegawai belum melapor jam pulang.
                        </div>
                        @error('check_out_time') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
