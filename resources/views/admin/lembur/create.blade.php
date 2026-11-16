@extends('layouts.admin')

@section('header', 'Buat Lembur Manual')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
            <h5 class="mb-0 fw-bold text-body">Form Lembur Baru</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.lembur.storeManual') }}" method="POST">
                @csrf
                <div class="row g-4">
                    {{-- Pilih Pegawai --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body-secondary">Pegawai</label>
                        <select name="user_id" class="form-select bg-body border-secondary-subtle @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($allPegawai as $p)
                                <option value="{{ $p->id }}" {{ old('user_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} ({{ $p->nip }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Keterangan Lembur (Opsional) --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body-secondary">Keterangan Lembur (Opsional)</label>
                        <input type="text" name="keterangan_lembur"
                            class="form-control bg-body border-secondary-subtle @error('keterangan_lembur') is-invalid @enderror"
                            placeholder="Contoh: Penyelesaian laporan bulanan"
                            value="{{ old('keterangan_lembur') }}">
                        @error('keterangan_lembur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Waktu Masuk Lembur --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body-secondary">Masuk Lembur</label>
                        <input type="datetime-local" name="check_in_lembur"
                            class="form-control bg-body border-secondary-subtle @error('check_in_lembur') is-invalid @enderror"
                            value="{{ old('check_in_lembur') }}" required>
                        @error('check_in_lembur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Waktu Keluar Lembur --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body-secondary">Keluar Lembur (Opsional)</label>
                        <input type="datetime-local" name="check_out_lembur"
                            class="form-control bg-body border-secondary-subtle @error('check_out_lembur') is-invalid @enderror"
                            value="{{ old('check_out_lembur') }}">
                        @error('check_out_lembur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn text-white px-4 fw-bold" style="background-color: #40BF89;">
                            <i class="bi bi-save me-1"></i> Simpan Lembur
                        </button>
                        <a href="{{ route('admin.lembur.report') }}" class="btn btn-outline-secondary px-4 fw-bold">
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
