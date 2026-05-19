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

                        {{-- Input NIP --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">NIP (Nomor Induk Pegawai)</label>
                            <div class="input-group @error('nip') has-validation @enderror">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0 @error('nip') border-danger @enderror">
                                    <i class="bi bi-card-heading text-body-secondary"></i>
                                </span>
                                <input type="text" name="nip"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('nip') is-invalid @enderror"
                                    placeholder="Masukkan NIP" value="{{ old('nip', $pegawai->nip) }}" required>
                                @error('nip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Input Nama Lengkap --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Nama Lengkap</label>
                            <div class="input-group @error('name') has-validation @enderror">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0 @error('name') border-danger @enderror">
                                    <i class="bi bi-person text-body-secondary"></i>
                                </span>
                                <input type="text" name="name"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('name') is-invalid @enderror"
                                    placeholder="Nama lengkap beserta gelar (jika ada)" value="{{ old('name', $pegawai->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Input Penempatan Tim Kerja --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Penempatan Tim Kerja</label>
                            <div class="input-group @error('tim_kerja_id') has-validation @enderror">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0 @error('tim_kerja_id') border-danger @enderror">
                                    <i class="bi bi-diagram-3 text-body-secondary"></i>
                                </span>
                                <select name="tim_kerja_id"
                                    class="form-select bg-body border-secondary-subtle text-body border-start-0 @error('tim_kerja_id') is-invalid @enderror"
                                    required>
                                    <option value="" class="text-body">-- Pilih Tim Kerja --</option>
                                    @foreach ($tim_kerja as $tim)
                                        <option value="{{ $tim->id }}" class="text-body"
                                            {{ old('tim_kerja_id', $pegawai->tim_kerja_id) == $tim->id ? 'selected' : '' }}>
                                            {{ $tim->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tim_kerja_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text mt-2 text-body-secondary" style="font-size: 0.85rem;">
                                <i class="bi bi-info-circle me-1 text-info"></i> Pilih tim kerja baru jika pegawai pindah posisi laporan.
                            </div>
                        </div>

                        {{-- TAMBAHAN BARU: Dropdown Wilayah / Lokasi Kerja --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Wilayah / Lokasi Kerja</label>
                            <div class="input-group @error('location_id') has-validation @enderror">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0 @error('location_id') border-danger @enderror">
                                    <i class="bi bi-geo-alt text-body-secondary"></i>
                                </span>
                                <select name="location_id"
                                    class="form-select bg-body border-secondary-subtle text-body border-start-0 @error('location_id') is-invalid @enderror"
                                    required>
                                    <option value="" class="text-body">-- Pilih Wilayah / Lokasi Kerja --</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}" class="text-body"
                                            {{ old('location_id', $pegawai->location_id) == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- DROPDOWN: Pangkat Golongan (Termasuk Golongan V - IX) --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Pangkat Golongan</label>
                            <div class="input-group @error('pangkat_golongan') has-validation @enderror">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0 @error('pangkat_golongan') border-danger @enderror">
                                    <i class="bi bi-award text-body-secondary"></i>
                                </span>
                                <select name="pangkat_golongan"
                                    class="form-select bg-body border-secondary-subtle text-body border-start-0 @error('pangkat_golongan') is-invalid @enderror">
                                    <option value="" class="text-body">-- Pilih Pangkat / Golongan --</option>

                                    {{-- Golongan V - IX (PPPK) --}}
                                    <optgroup label="Golongan V - IX (PPPK)" class="text-body">
                                        <option value="IX" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IX' ? 'selected' : '' }}>Golongan IX</option>
                                        <option value="VIII" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'VIII' ? 'selected' : '' }}>Golongan VIII</option>
                                        <option value="VII" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'VII' ? 'selected' : '' }}>Golongan VII</option>
                                        <option value="VI" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'VI' ? 'selected' : '' }}>Golongan VI</option>
                                        <option value="V" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'V' ? 'selected' : '' }}>Golongan V</option>
                                    </optgroup>

                                    {{-- Golongan IV (Pembina) --}}
                                    <optgroup label="Golongan IV (Pembina)" class="text-body">
                                        <option value="IV/e" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/e' ? 'selected' : '' }}>Pembina Utama (IV/e)</option>
                                        <option value="IV/d" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/d' ? 'selected' : '' }}>Pembina Utama Madya (IV/d)</option>
                                        <option value="IV/c" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/c' ? 'selected' : '' }}>Pembina Utama Muda (IV/c)</option>
                                        <option value="IV/b" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/b' ? 'selected' : '' }}>Pembina Tingkat I (IV/b)</option>
                                        <option value="IV/a" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'IV/a' ? 'selected' : '' }}>Pembina (IV/a)</option>
                                    </optgroup>

                                    {{-- Golongan III (Penata) --}}
                                    <optgroup label="Golongan III (Penata)" class="text-body">
                                        <option value="III/d" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'III/d' ? 'selected' : '' }}>Penata Tingkat I (III/d)</option>
                                        <option value="III/c" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'III/c' ? 'selected' : '' }}>Penata (III/c)</option>
                                        <option value="III/b" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'III/b' ? 'selected' : '' }}>Penata Muda Tingkat I (III/b)</option>
                                        <option value="III/a" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'III/a' ? 'selected' : '' }}>Penata Muda (III/a)</option>
                                    </optgroup>

                                    {{-- Golongan II (Pengatur) --}}
                                    <optgroup label="Golongan II (Pengatur)" class="text-body">
                                        <option value="II/d" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'II/d' ? 'selected' : '' }}>Pengatur Tingkat I (II/d)</option>
                                        <option value="II/c" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'II/c' ? 'selected' : '' }}>Pengatur (II/c)</option>
                                        <option value="II/b" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'II/b' ? 'selected' : '' }}>Pengatur Muda Tingkat I (II/b)</option>
                                        <option value="II/a" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'II/a' ? 'selected' : '' }}>Pengatur Muda (II/a)</option>
                                    </optgroup>

                                    {{-- Golongan I (Juru) --}}
                                    <optgroup label="Golongan I (Juru)" class="text-body">
                                        <option value="I/d" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'I/d' ? 'selected' : '' }}>Juru Tingkat I (I/d)</option>
                                        <option value="I/c" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'I/c' ? 'selected' : '' }}>Juru (I/c)</option>
                                        <option value="I/b" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'I/b' ? 'selected' : '' }}>Juru Muda Tingkat I (I/b)</option>
                                        <option value="I/a" {{ old('pangkat_golongan', $pegawai->pangkat_golongan) == 'I/a' ? 'selected' : '' }}>Juru Muda (I/a)</option>
                                    </optgroup>
                                </select>
                                @error('pangkat_golongan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- INPUT EDIT: Jabatan --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Jabatan</label>
                            <div class="input-group @error('jabatan') has-validation @enderror">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0 @error('jabatan') border-danger @enderror">
                                    <i class="bi bi-briefcase text-body-secondary"></i>
                                </span>
                                <input type="text" name="jabatan"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('jabatan') is-invalid @enderror"
                                    placeholder="Contoh: Sanitarian Ahli Madya (JFT)" value="{{ old('jabatan', $pegawai->jabatan) }}">
                                @error('jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- INPUT EDIT: Kelas Jabatan --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Kelas Jabatan</label>
                            <div class="input-group @error('kelas_jabatan') has-validation @enderror">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0 @error('kelas_jabatan') border-danger @enderror">
                                    <i class="bi bi-layers text-body-secondary"></i>
                                </span>
                                <input type="text" name="kelas_jabatan"
                                    class="form-control bg-body border-secondary-subtle text-body border-start-0 @error('kelas_jabatan') is-invalid @enderror"
                                    placeholder="Contoh: 11" value="{{ old('kelas_jabatan', $pegawai->kelas_jabatan) }}">
                                @error('kelas_jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- DROPDOWN: Pendidikan Terakhir --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Pendidikan Terakhir</label>
                            <div class="input-group @error('pendidikan') has-validation @enderror">
                                <span class="input-group-text bg-body border-secondary-subtle border-end-0 @error('pendidikan') border-danger @enderror">
                                    <i class="bi bi-mortarboard text-body-secondary"></i>
                                </span>
                                <select name="pendidikan"
                                    class="form-select bg-body border-secondary-subtle text-body border-start-0 @error('pendidikan') is-invalid @enderror">
                                    <option value="" class="text-body">-- Pilih Pendidikan Terakhir --</option>

                                    {{-- Pendidikan Menengah --}}
                                    <optgroup label="Pendidikan Menengah / Sederajat" class="text-body">
                                        <option value="SLTA" {{ old('pendidikan', $pegawai->pendidikan) == 'SLTA' ? 'selected' : '' }}>SLTA / SMA / SMK / MA</option>
                                    </optgroup>

                                    {{-- Program Diploma --}}
                                    <optgroup label="Program Diploma" class="text-body">
                                        <option value="D-I" {{ old('pendidikan', $pegawai->pendidikan) == 'D-I' ? 'selected' : '' }}>Diploma I (D1)</option>
                                        <option value="D-II" {{ old('pendidikan', $pegawai->pendidikan) == 'D-II' ? 'selected' : '' }}>Diploma II (D2)</option>
                                        <option value="D-III" {{ old('pendidikan', $pegawai->pendidikan) == 'D-III' ? 'selected' : '' }}>Diploma III (D3)</option>
                                        <option value="D-IV" {{ old('pendidikan', $pegawai->pendidikan) == 'D-IV' ? 'selected' : '' }}>Diploma IV (D4)</option>
                                    </optgroup>

                                    {{-- Program Sarjana & Pascasarjana --}}
                                    <optgroup label="Program Sarjana & Pascasarjana" class="text-body">
                                        <option value="S1" {{ old('pendidikan', $pegawai->pendidikan) == 'S1' ? 'selected' : '' }}>Sarjana (S1)</option>
                                        <option value="S2" {{ old('pendidikan', $pegawai->pendidikan) == 'S2' ? 'selected' : '' }}>Magister (S2)</option>
                                        <option value="S3" {{ old('pendidikan', $pegawai->pendidikan) == 'S3' ? 'selected' : '' }}>Doktor (S3)</option>
                                    </optgroup>

                                    {{-- Profesi --}}
                                    <optgroup label="Program Profesi / Spesialis" class="text-body">
                                        <option value="Profesi" {{ old('pendidikan', $pegawai->pendidikan) == 'Profesi' ? 'selected' : '' }}>Profesi (Apoteker, Ners, Dokter, dll.)</option>
                                        <option value="Spesialis" {{ old('pendidikan', $pegawai->pendidikan) == 'Spesialis' ? 'selected' : '' }}>Spesialis (Sp.A, Sp.PD, dll.)</option>
                                    </optgroup>
                                </select>
                                @error('pendidikan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Informasi Akun Otomatis --}}
                        <div class="alert border-0 bg-info bg-opacity-10 py-3 mb-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-info bg-opacity-25 rounded-circle p-2 me-3">
                                    <i class="bi bi-shield-lock-fill text-info fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-bold text-body" style="font-size: 0.9rem;">Informasi Akun Otomatis</h6>
                                    <p class="mb-0 text-body-secondary" style="font-size: 0.85rem;">
                                        Email: <span class="badge bg-body text-body-secondary border border-secondary-subtle fw-medium">{{ $pegawai->nip }}@bkk.go.id</span>
                                        <br>
                                        Password: <span class="badge bg-body text-body-secondary border border-secondary-subtle fw-medium">password123</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.pegawai.index') }}"
                                class="btn btn-secondary bg-opacity-10 text-body border-0 px-4 fw-medium">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn text-white px-4 shadow-sm fw-bold"
                                style="background-color: #40BF89; border: none;">
                                <i class="bi bi-check-circle me-1"></i> Update Data Pegawai
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
