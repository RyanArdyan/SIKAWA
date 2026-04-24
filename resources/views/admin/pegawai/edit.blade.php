@extends('layouts.admin')

@section('header', 'Edit Data Pegawai')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.pegawai.update', $pegawai->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">NIP (Nomor Induk Pegawai)</label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $pegawai->nip) }}" required>
                        @error('nip') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $pegawai->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tim Kerja</label>
                        <select name="tim_kerja_id" class="form-select @error('tim_kerja_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Tim Kerja --</option>
                            @foreach($tim_kerja as $tim)
                                <option value="{{ $tim->id }}" {{ old('tim_kerja_id', $pegawai->tim_kerja_id) == $tim->id ? 'selected' : '' }}>
                                    {{ $tim->nama }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Pilih tim kerja baru jika pegawai pindah posisi.</div>
                        @error('tim_kerja_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.pegawai.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-warning px-4 text-white">Update Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
