@extends('layouts.admin')

@section('header', 'Edit Tim Kerja')

@section('content')
<div class="row">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3" style="border-top: 5px solid #40BF89;">
                <h6 class="mb-0 fw-bold" style="color: #2c3e50;"><i class="bi bi-pencil-square me-2"></i>Form Perubahan Tim Kerja</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.tim-kerja.update', $tim->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Nama Tim Kerja</label>
                        <input type="text" name="nama"
                               class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $tim->nama) }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary">Ketua Tim Saat Ini</label>
                        <select name="ketua_id" class="form-select @error('ketua_id') is-invalid @enderror">
                            <option value="">-- Pilih Ketua Tim --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('ketua_id', $tim->ketua_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} (NIP: {{ $user->nip }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text mt-2" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle me-1 text-primary"></i>
                            Mengubah ketua akan memperbarui tanggung jawab laporan tim ini.
                        </div>
                        @error('ketua_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="text-muted opacity-25 my-4">

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.tim-kerja.index') }}" class="btn btn-light border px-4">
                            <i class="bi bi-arrow-left"></i> Batal
                        </a>
                        <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #40BF89; border: none;">
                            <i class="bi bi-check-circle me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info Card Tambahan --}}
        <div class="mt-4 p-3 rounded-3 border-0 shadow-sm" style="background-color: rgba(64, 191, 137, 0.05); border-left: 4px solid #40BF89 !important;">
            <div class="d-flex">
                <i class="bi bi-lightbulb text-warning me-3 fs-4"></i>
                <p class="small text-muted mb-0">
                    <strong>Tips:</strong> Pastikan nama tim sesuai dengan nomenklatur terbaru di <strong>BKK Kelas I Pontianak</strong> untuk memudahkan pencarian pada laporan bulanan.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
