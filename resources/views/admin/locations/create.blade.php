@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tambah Lokasi Baru</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.locations.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Nama Lokasi (Contoh: Kantor Pusat Pontianak)</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            required>
                    </div>
                    <div class="form-group mb-3">
                        <label>IP Address Publik</label>
                        <input type="text" name="ip_address"
                            class="form-control @error('ip_address') is-invalid @enderror"
                            placeholder="Contoh: 114.125.xx.xx" required>
                        <small class="text-muted">Gunakan 127.0.0.1 jika sedang testing di localhost.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Lokasi</button>
                    <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
@endsection
