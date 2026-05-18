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

                        {{-- Input NIP --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">NIP (Nomor Induk Pegawai)</label>
                            <div class="input-group">
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

                        {{-- Input Nama Lengkap --}}
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

                        {{-- Input Penempatan Tim Kerja --}}
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

                        {{-- INPUT BARU: Pangkat Golongan --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Pangkat Golongan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                    <i class="bi bi-award text-body-secondary"></i>
                                </span>
                                <input type="text" name="pangkat_golongan"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('pangkat_golongan') is-invalid @enderror"
                                    placeholder="Contoh: IV/b" value="{{ old('pangkat_golongan') }}">
                            </div>
                            @error('pangkat_golongan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- INPUT BARU: Jabatan --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Jabatan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                    <i class="bi bi-briefcase text-body-secondary"></i>
                                </span>
                                <input type="text" name="jabatan"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('jabatan') is-invalid @enderror"
                                    placeholder="Contoh: Sanitarian Ahli Madya (JFT)" value="{{ old('jabatan') }}">
                            </div>
                            @error('jabatan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- INPUT BARU: Kelas Jabatan --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Kelas Jabatan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                    <i class="bi bi-layers text-body-secondary"></i>
                                </span>
                                <input type="text" name="kelas_jabatan"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('kelas_jabatan') is-invalid @enderror"
                                    placeholder="Contoh: 11" value="{{ old('kelas_jabatan') }}">
                            </div>
                            @error('kelas_jabatan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- INPUT BARU: Pendidikan --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Pendidikan Terakhir</label>
                            <div class="input-group">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                    <i class="bi bi-mortarboard text-body-secondary"></i>
                                </span>
                                <input type="text" name="pendidikan"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('pendidikan') is-invalid @enderror"
                                    placeholder="Contoh: S.1 Kedokteran Umum" value="{{ old('pendidikan') }}">
                            </div>
                            @error('pendidikan')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        {{-- Informasi Akun Otomatis --}}
                        <div class="alert border-0 bg-info bg-opacity-10 py-3 mb-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-25 rounded-circle p-2 me-3">
                                    <i class="bi bi-shield-lock-fill text-info fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-body" style="font-size: 0.9rem;">Informasi Akun Otomatis</h6>
                                    <p class="mb-0 text-body-secondary" style="font-size: 0.85rem;">
                                        Email: <span class="badge bg-body text-body-secondary border border-secondary-subtle fw-medium">NIP@bkk.go.id</span>
                                        <br>
                                        Password: <span class="badge bg-body text-body-secondary border border-secondary-subtle fw-medium">password123</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        {{-- Tombol Aksi --}}
                        <div class="d-flex justify-content-between align-items-center">
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
