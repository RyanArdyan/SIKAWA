@extends('layouts.admin')

@section('header', 'Tambah Pegawai Baru')

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm bg-body-tertiary">
                <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                    <h6 class="mb-0 fw-bold text-body">
                        <i class="bi bi-person-plus me-2 text-success"></i>Formulir Data Pegawai
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.pegawai.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">NIP (Nomor Induk Pegawai)</label>
                            <div class="input-group">
                                {{-- Mengganti bg-light menjadi bg-body agar sinkron dengan input --}}
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                    <i class="bi bi-card-heading text-body-secondary"></i>
                                </span>
                                <input type="text" name="nip"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('nip') is-invalid @enderror"
                                    placeholder="Masukkan NIP" value="{{ old('nip') }}" required>
                            </div>
                            @error('nip')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                    <i class="bi bi-person text-body-secondary"></i>
                                </span>
                                <input type="text" name="name"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('name') is-invalid @enderror"
                                    placeholder="Nama lengkap beserta gelar (jika ada)" value="{{ old('name') }}"
                                    required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Penempatan Tim Kerja</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                    <i class="bi bi-diagram-3 text-body-secondary"></i>
                                </span>
                                <select name="tim_kerja_id"
                                    class="form-select bg-body border-secondary-subtle text-body border-start-0 @error('tim_kerja_id') is-invalid @enderror"
                                    required>
                                    <option value="" class="text-body">-- Pilih Tim Kerja --</option>
                                    @foreach ($tim_kerja as $tim)
                                        <option value="{{ $tim->id }}" class="text-body"
                                            {{ old('tim_kerja_id') == $tim->id ? 'selected' : '' }}>
                                            {{ $tim->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-text mt-2 text-body-secondary" style="font-size: 0.85rem;">
                                <i class="bi bi-info-circle me-1 text-info"></i> Penempatan ini akan menentukan ketua tim
                                yang bertanggung jawab.
                            </div>
                            @error('tim_kerja_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tambahkan Informasi Akun Otomatis tepat sebelum tombol aksi --}}
                        <div class="alert border-0 bg-info bg-opacity-10 py-3 mb-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-25 rounded-circle p-2 me-3">
                                    <i class="bi bi-shield-lock-fill text-info fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-body" style="font-size: 0.9rem;">Informasi Akun Otomatis
                                    </h6>
                                    <p class="mb-0 text-body-secondary" style="font-size: 0.85rem;">
                                        Email: <span
                                            class="badge bg-body text-body-secondary border border-secondary-subtle fw-medium">NIP@bkk.go.id</span>
                                        <br>
                                        Password: <span
                                            class="badge bg-body text-body-secondary border border-secondary-subtle fw-medium">password123</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <hr class="my-4 text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center">
                            {{-- Menggunakan btn-secondary transparan untuk tombol kembali --}}
                            <a href="{{ route('admin.pegawai.index') }}"
                                class="btn btn-secondary bg-opacity-10 text-body border-0 px-4 fw-medium">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn text-white px-4 shadow-sm fw-bold"
                                style="background-color: #40BF89; border: none;">
                                <i class="bi bi-save me-1"></i> Simpan Data Pegawai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
