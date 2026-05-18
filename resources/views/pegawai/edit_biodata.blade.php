@extends('layouts.admin')

@section('header', 'Ubah Biodata Mandiri')

@section('content')
    <div class="row">
        {{-- Mengubah grid sistem agar card menjadi lebih lebar dan pas di layar dashboard --}}
        <div class="col-12 col-xl-10">

            {{-- Alert Sukses --}}
            @if (session('success'))
                <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success fw-bold mb-4">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="card border-0 shadow-sm bg-body-tertiary">
                <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                    <h6 class="mb-0 fw-bold text-body">
                        <i class="bi bi-person-gear me-2 text-success"></i>Formulir Pembaruan Biodata Pegawai
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('pegawai.updateBiodata') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Kolom Kiri: Informasi Utama (Read-Only) --}}
                            <div class="col-md-5 border-end border-secondary border-opacity-10 mb-4 mb-md-0 pe-md-4">
                                <h6 class="fw-bold text-success mb-3" style="font-size: 0.9rem;">
                                    <i class="bi bi-lock-fill me-1"></i> Data Terkunci (Hubungi Admin)
                                </h6>

                                {{-- NIP (Read-Only) --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">NIP</label>
                                    <input type="text"
                                        class="form-control bg-body-secondary text-body-secondary border-secondary-subtle"
                                        value="{{ $pegawai->nip }}" readonly>
                                </div>

                                {{-- Tim Kerja (Read-Only) --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Penempatan Tim Kerja</label>
                                    <input type="text"
                                        class="form-control bg-body-secondary text-body-secondary border-secondary-subtle"
                                        value="{{ $pegawai->tim_kerja->nama ?? 'Tanpa Tim' }}" readonly>
                                </div>

                                {{-- Role Sistem (Read-Only) --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Hak Akses Sistem</label>
                                    <input type="text"
                                        class="form-control bg-body-secondary text-body-secondary border-secondary-subtle text-uppercase"
                                        value="{{ $pegawai->role }}" readonly>
                                </div>
                            </div>

                            {{-- Kolom Kanan: Data yang Boleh Diedit --}}
                            <div class="col-md-7 ps-md-4">
                                <h6 class="fw-bold text-primary mb-3" style="font-size: 0.9rem; color: #40BF89 !important;">
                                    <i class="bi bi-pencil-fill me-1"></i> Data yang Dapat Diubah
                                </h6>

                                {{-- Nama Lengkap --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Nama Lengkap & Gelar</label>
                                    <input type="text" name="name"
                                        class="form-control bg-body border-secondary-subtle text-body @error('name') is-invalid @enderror"
                                        value="{{ old('name', $pegawai->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Email</label>
                                    <input type="email" name="email"
                                        class="form-control bg-body border-secondary-subtle text-body @error('email') is-invalid @enderror"
                                        value="{{ old('email', $pegawai->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Pangkat / Golongan --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Pangkat / Golongan</label>
                                    <input type="text" name="pangkat_golongan"
                                        class="form-control bg-body border-secondary-subtle text-body"
                                        value="{{ old('pangkat_golongan', $pegawai->pangkat_golongan) }}"
                                        placeholder="Contoh: Penata / IIIc">
                                </div>

                                {{-- Jabatan --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Jabatan</label>
                                    <input type="text" name="jabatan"
                                        class="form-control bg-body border-secondary-subtle text-body"
                                        value="{{ old('jabatan', $pegawai->jabatan) }}"
                                        placeholder="Contoh: Epidemiolog Ahli Muda">
                                </div>

                                {{-- Kelas Jabatan --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Kelas Jabatan</label>
                                    <input type="text" name="kelas_jabatan"
                                        class="form-control bg-body border-secondary-subtle text-body"
                                        value="{{ old('kelas_jabatan', $pegawai->kelas_jabatan) }}"
                                        placeholder="Contoh: 9">
                                </div>

                                {{-- Pendidikan --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Pendidikan Terakhir</label>
                                    <input type="text" name="pendidikan"
                                        class="form-control bg-body border-secondary-subtle text-body"
                                        value="{{ old('pendidikan', $pegawai->pendidikan) }}"
                                        placeholder="Contoh: S1 Kesehatan Masyarakat">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        {{-- Pengaturan Keamanan Form Password Baru --}}
                        <div class="bg-body p-3 rounded border border-secondary border-opacity-10 mb-4">
                            <h6 class="fw-bold text-body" style="font-size: 0.85rem;"><i
                                    class="bi bi-shield-lock me-1 text-warning"></i> Ganti Password (Kosongkan jika tidak
                                ingin diubah)</h6>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label small text-body-secondary">Password Baru</label>
                                    <input type="password" name="password"
                                        class="form-control bg-body border-secondary-subtle text-body @error('password') is-invalid @enderror"
                                        placeholder="Minimal 6 karakter">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-body-secondary">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_confirmation"
                                        class="form-control bg-body border-secondary-subtle text-body"
                                        placeholder="Ulangi password baru">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end align-items-center">
                            <button type="submit" class="btn text-white px-4 shadow-sm fw-bold"
                                style="background-color: #40BF89; border: none;">
                                <i class="bi bi-check-circle me-1"></i> Simpan Perubahan Biodata
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
