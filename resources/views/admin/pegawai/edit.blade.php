@extends('layouts.admin')

@section('header', 'Edit Data Pegawai')

@section('content')
<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm bg-body-tertiary">
            {{-- Header kartu dengan ikon yang sama dengan formulir tambah --}}
            <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                <h6 class="mb-0 fw-bold text-body">
                    <i class="bi bi-pencil-square me-2 text-success"></i>Form Perubahan Data Pegawai
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.pegawai.update', $pegawai->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold text-body-secondary">NIP (Nomor Induk Pegawai)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                <i class="bi bi-card-heading text-body-secondary"></i>
                            </span>
                            <input type="text" name="nip" class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('nip') is-invalid @enderror"
                                   placeholder="Masukkan NIP" value="{{ old('nip', $pegawai->nip) }}" required>
                        </div>
                        @error('nip') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-body-secondary">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                <i class="bi bi-person text-body-secondary"></i>
                            </span>
                            <input type="text" name="name" class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('name') is-invalid @enderror"
                                   placeholder="Nama lengkap" value="{{ old('name', $pegawai->name) }}" required>
                        </div>
                        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-body-secondary">Penempatan Tim Kerja</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                <i class="bi bi-diagram-3 text-body-secondary"></i>
                            </span>
                            <select name="tim_kerja_id" class="form-select bg-body border-secondary-subtle text-body border-start-0 @error('tim_kerja_id') is-invalid @enderror" required>
                                <option value="" class="text-body">-- Pilih Tim Kerja --</option>
                                @foreach($tim_kerja as $tim)
                                    <option value="{{ $tim->id }}" class="text-body" {{ old('tim_kerja_id', $pegawai->tim_kerja_id) == $tim->id ? 'selected' : '' }}>
                                        {{ $tim->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text mt-2 text-body-secondary" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle me-1 text-info"></i> Pilih tim kerja baru jika pegawai pindah posisi laporan.
                        </div>
                        @error('tim_kerja_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4 text-muted opacity-25">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.pegawai.index') }}" class="btn btn-secondary bg-opacity-10 text-body border-0 px-4 fw-medium">
                            <i class="bi bi-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn text-white px-4 shadow-sm fw-bold" style="background-color: #40BF89; border: none;">
                            <i class="bi bi-check-circle me-1"></i> Update Data Pegawai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
