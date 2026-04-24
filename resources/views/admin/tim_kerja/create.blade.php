@extends('layouts.admin')

@section('header', 'Tambah Tim Kerja Baru')

@section('content')
    <div class="row">
        <div class="col-md-8 col-lg-6">
            {{-- Pesan Error --}}
            @if ($errors->any())
                <div class="alert alert-danger shadow-sm border-0 mb-4">
                    <div class="d-flex">
                        <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3" style="border-top: 5px solid #40BF89;">
                    <h6 class="mb-0 fw-bold" style="color: #2c3e50;"><i class="bi bi-people me-2"></i>Form Input Tim Kerja</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.tim-kerja.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Nama Tim Kerja</label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                placeholder="Contoh: Tim Kerja Pengawasan" value="{{ old('nama') }}" required autofocus>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Pilih Ketua Tim</label>
                            <select name="ketua_id" class="form-select @error('ketua_id') is-invalid @enderror">
                                <option value="">-- Pilih Pegawai sebagai Ketua --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('ketua_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} (NIP: {{ $user->nip }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text mt-2" style="font-size: 0.85rem;">
                                <i class="bi bi-info-circle me-1 text-primary"></i>
                                Pegawai yang dipilih akan menjadi penanggung jawab tim ini.
                            </div>
                            @error('ketua_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="text-muted opacity-25 my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.tim-kerja.index') }}" class="btn btn-light border px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #40BF89; border: none;">
                                <i class="bi bi-save me-1"></i> Simpan Data Tim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
