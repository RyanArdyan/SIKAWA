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

                                {{-- DROPDOWN: Pangkat / Golongan --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Pangkat Golongan</label>
                                    <select name="pangkat_golongan"
                                        class="form-select bg-body border-secondary-subtle text-body @error('pangkat_golongan') is-invalid @enderror">
                                        <option value="" class="text-body">-- Pilih Pangkat / Golongan --</option>

                                        {{-- Tambahan Baru: Golongan V - IX (PPPK) --}}
                                        <optgroup label="Golongan V - IX (PPPK)" class="text-body">
                                            <option value="IX"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IX' ? 'selected' : '' }}>
                                                Golongan IX</option>
                                            <option value="VIII"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'VIII' ? 'selected' : '' }}>
                                                Golongan VIII</option>
                                            <option value="VII"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'VII' ? 'selected' : '' }}>
                                                Golongan VII</option>
                                            <option value="VI"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'VI' ? 'selected' : '' }}>
                                                Golongan VI</option>
                                            <option value="V"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'V' ? 'selected' : '' }}>
                                                Golongan V</option>
                                        </optgroup>

                                        {{-- Golongan IV (Pembina) --}}
                                        <optgroup label="Golongan IV (Pembina)" class="text-body">
                                            <option value="IV/e"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/e' ? 'selected' : '' }}>
                                                Pembina Utama (IV/e)</option>
                                            <option value="IV/d"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/d' ? 'selected' : '' }}>
                                                Pembina Utama Madya (IV/d)</option>
                                            <option value="IV/c"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/c' ? 'selected' : '' }}>
                                                Pembina Utama Muda (IV/c)</option>
                                            <option value="IV/b"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/b' ? 'selected' : '' }}>
                                                Pembina Tingkat I (IV/b)</option>
                                            <option value="IV/a"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/a' ? 'selected' : '' }}>
                                                Pembina (IV/a)</option>
                                        </optgroup>

                                        {{-- Golongan III (Penata) --}}
                                        <optgroup label="Golongan III (Penata)" class="text-body">
                                            <option value="III/d"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'III/d' ? 'selected' : '' }}>
                                                Penata Tingkat I (III/d)</option>
                                            <option value="III/c"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'III/c' ? 'selected' : '' }}>
                                                Penata (III/c)</option>
                                            <option value="III/b"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'III/b' ? 'selected' : '' }}>
                                                Penata Muda Tingkat I (III/b)</option>
                                            <option value="III/a"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'III/a' ? 'selected' : '' }}>
                                                Penata Muda (III/a)</option>
                                        </optgroup>

                                        {{-- Golongan II (Pengatur) --}}
                                        <optgroup label="Golongan II (Pengatur)" class="text-body">
                                            <option value="II/d"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'II/d' ? 'selected' : '' }}>
                                                Pengatur Tingkat I (II/d)</option>
                                            <option value="II/c"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'II/c' ? 'selected' : '' }}>
                                                Pengatur (II/c)</option>
                                            <option value="II/b"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'II/b' ? 'selected' : '' }}>
                                                Pengatur Muda Tingkat I (II/b)</option>
                                            <option value="II/a"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'II/a' ? 'selected' : '' }}>
                                                Pengatur Muda (II/a)</option>
                                        </optgroup>

                                        {{-- Golongan I (Juru) --}}
                                        <optgroup label="Golongan I (Juru)" class="text-body">
                                            <option value="I/d"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'I/d' ? 'selected' : '' }}>
                                                Juru Tingkat I (I/d)</option>
                                            <option value="I/c"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'I/c' ? 'selected' : '' }}>
                                                Juru (I/c)</option>
                                            <option value="I/b"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'I/b' ? 'selected' : '' }}>
                                                Juru Muda Tingkat I (I/b)</option>
                                            <option value="I/a"
                                                {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'I/a' ? 'selected' : '' }}>
                                                Juru Muda (I/a)</option>
                                        </optgroup>
                                    </select>
                                    @error('pangkat_golongan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Jabatan --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Jabatan</label>
                                    <input type="text" name="jabatan"
                                        class="form-control bg-body border-secondary-subtle text-body @error('jabatan') is-invalid @enderror"
                                        value="{{ old('jabatan', $pegawai->jabatan) }}"
                                        placeholder="Contoh: Epidemiolog Ahli Muda">
                                    @error('jabatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Kelas Jabatan --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Kelas Jabatan</label>
                                    <input type="text" name="kelas_jabatan"
                                        class="form-control bg-body border-secondary-subtle text-body @error('kelas_jabatan') is-invalid @enderror"
                                        value="{{ old('kelas_jabatan', $pegawai->kelas_jabatan) }}"
                                        placeholder="Contoh: 9">
                                    @error('kelas_jabatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- DROPDOWN: Pendidikan Terakhir --}}
                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-body-secondary">Pendidikan Terakhir</label>
                                    <select name="pendidikan"
                                        class="form-select bg-body border-secondary-subtle text-body @error('pendidikan') is-invalid @enderror">
                                        <option value="" class="text-body">-- Pilih Pendidikan Terakhir --</option>

                                        {{-- Pendidikan Menengah --}}
                                        <optgroup label="Pendidikan Menengah / Sederajat" class="text-body">
                                            <option value="SLTA"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'SLTA' ? 'selected' : '' }}>
                                                SLTA / SMA / SMK / MA</option>
                                        </optgroup>

                                        {{-- Program Diploma --}}
                                        <optgroup label="Program Diploma" class="text-body">
                                            <option value="D-I"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'D-I' ? 'selected' : '' }}>
                                                Diploma I (D1)</option>
                                            <option value="D-II"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'D-II' ? 'selected' : '' }}>
                                                Diploma II (D2)</option>
                                            <option value="D-III"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'D-III' ? 'selected' : '' }}>
                                                Diploma III (D3)</option>
                                            <option value="D-IV"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'D-IV' ? 'selected' : '' }}>
                                                Diploma IV (D4)</option>
                                        </optgroup>

                                        {{-- Program Sarjana & Pascasarjana --}}
                                        <optgroup label="Program Sarjana & Pascasarjana" class="text-body">
                                            <option value="S1"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'S1' ? 'selected' : '' }}>
                                                Sarjana (S1)</option>
                                            <option value="S2"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'S2' ? 'selected' : '' }}>
                                                Magister (S2)</option>
                                            <option value="S3"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'S3' ? 'selected' : '' }}>
                                                Doktor (S3)</option>
                                        </optgroup>

                                        {{-- Profesi --}}
                                        <optgroup label="Program Profesi / Spesialis" class="text-body">
                                            <option value="Profesi"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'Profesi' ? 'selected' : '' }}>
                                                Profesi (Apoteker, Ners, Dokter, dll.)</option>
                                            <option value="Spesialis"
                                                {{ old('pendidikan', $pegawai->pendidikan) == 'Spesialis' ? 'selected' : '' }}>
                                                Spesialis (Sp.A, Sp.PD, dll.)</option>
                                        </optgroup>
                                    </select>
                                    @error('pendidikan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
