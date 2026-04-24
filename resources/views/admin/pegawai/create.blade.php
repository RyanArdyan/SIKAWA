@extends('layouts.admin')

@section('header', 'Tambah Pegawai Baru')

@section('content')
<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3" style="border-top: 5px solid #40BF89;">
                <h6 class="mb-0 fw-bold" style="color: #2c3e50;"><i class="bi bi-person-plus me-2"></i>Formulir Data Pegawai</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.pegawai.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">NIP (Nomor Induk Pegawai)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-card-heading text-muted"></i></span>
                            <input type="text" name="nip" class="form-control border-start-0 @error('nip') is-invalid @enderror"
                                   placeholder="Masukkan NIP" value="{{ old('nip') }}" required>
                        </div>
                        @error('nip') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="name" class="form-control border-start-0 @error('name') is-invalid @enderror"
                                   placeholder="Nama lengkap beserta gelar (jika ada)" value="{{ old('name') }}" required>
                        </div>
                        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Penempatan Tim Kerja</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-diagram-3 text-muted"></i></span>
                            <select name="tim_kerja_id" class="form-select border-start-0 @error('tim_kerja_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Tim Kerja --</option>
                                @foreach($tim_kerja as $tim)
                                    <option value="{{ $tim->id }}" {{ old('tim_kerja_id') == $tim->id ? 'selected' : '' }}>
                                        {{ $tim->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text mt-2" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle me-1 text-primary"></i> Penempatan ini akan menentukan ketua tim yang bertanggung jawab.
                        </div>
                        @error('tim_kerja_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <hr class="my-4 opacity-25">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.pegawai.index') }}" class="btn btn-light border px-4 text-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #40BF89; border: none;">
                            <i class="bi bi-save me-1"></i> Simpan Data Pegawai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
