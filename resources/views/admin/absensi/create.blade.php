@extends('layouts.admin')

@section('header', 'Buat Presensi Manual')

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
            <h5 class="mb-0 fw-bold text-body">Form Presensi Baru</h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.laporan.storeManual') }}" method="POST">
                @csrf
                <div class="row g-4">
                    {{-- Pilih Pegawai --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body-secondary">Pegawai</label>
                        <select name="user_id" class="form-select bg-body border-secondary-subtle" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($allPegawai as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->nip }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Lokasi --}}
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-body-secondary">Lokasi Kantor (Tidak Perlu Jika WFA)</label>
                        <select name="location_id" class="form-select bg-body border-secondary-subtle">
                            <option value="">-- Pilih Lokasi (Tidak Perlu Jika WFA) --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Waktu Masuk --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-body-secondary">Waktu Check-In</label>
                        <input type="datetime-local" name="check_in_time" class="form-control bg-body border-secondary-subtle" required>
                    </div>

                    {{-- Waktu Pulang --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-body-secondary">Waktu Check-Out (Opsional)</label>
                        <input type="datetime-local" name="check_out_time" class="form-control bg-body border-secondary-subtle">
                    </div>

                    {{-- Tipe Absen --}}
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-body-secondary">Tipe Absen</label>
                        <select name="tipe_absen" class="form-select bg-body border-secondary-subtle" required>
                            <option value="WFO">WFO (Work From Office)</option>
                            <option value="WFA">WFA (Work From Anywhere)</option>
                        </select>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn text-white px-4 fw-bold" style="background-color: #40BF89;">
                            <i class="bi bi-save me-1"></i> Simpan Presensi
                        </button>
                        <a href="{{ route('admin.absensi.report') }}" class="btn btn-outline-secondary px-4 fw-bold">
                            Batal
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
