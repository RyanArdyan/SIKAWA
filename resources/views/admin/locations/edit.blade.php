@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Lokasi: {{ $location->name }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.locations.update', $location->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label>Nama Lokasi</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $location->name }}" required>
                </div>

                <div class="form-group mb-3">
                    <label>IP Address Publik</label>
                    <input type="text" name="ip_address" class="form-control @error('ip_address') is-invalid @enderror" value="{{ $location->ip_address }}" required>
                </div>

                <div class="form-group mb-3">
                    <label>Status Lokasi</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ $location->is_active ? 'selected' : '' }}>Aktif (Bisa Absen WFO)</option>
                        <option value="0" {{ !$location->is_active ? 'selected' : '' }}>Nonaktif (Hanya WFA)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Perbarui Lokasi</button>
                <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
