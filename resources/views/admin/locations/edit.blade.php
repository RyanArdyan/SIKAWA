@extends('layouts.admin')

@section('header', 'Edit Lokasi Kantor')

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

            <div class="card border-0 shadow-sm bg-body-tertiary">
                <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                    <h6 class="mb-0 fw-bold text-body">
                        <i class="bi bi-pencil-square me-2 text-success"></i>Edit Data Lokasi: {{ $location->name }}
                    </h6>
                </div>
                <div class="card-body p-4">
                    {{-- Action diarahkan ke update dengan method PUT --}}
                    <form action="{{ route('admin.locations.update', $location->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Nama Lokasi</label>
                            <input type="text" name="name" class="form-control bg-body border-secondary-subtle text-body @error('name') is-invalid @enderror"
                                placeholder="Contoh: Kantor Induk" value="{{ old('name', $location->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Latitude</label>
                            <input type="number" step="any" name="latitude" class="form-control bg-body border-secondary-subtle text-body @error('latitude') is-invalid @enderror"
                                placeholder="Contoh: -6.2088" value="{{ old('latitude', $location->latitude) }}" required>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Longitude</label>
                            <input type="number" step="any" name="longitude" class="form-control bg-body border-secondary-subtle text-body @error('longitude') is-invalid @enderror"
                                placeholder="Contoh: 106.8456" value="{{ old('longitude', $location->longitude) }}" required>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-body-secondary">Radius (meter)</label>
                            <input type="number" name="radius" class="form-control bg-body border-secondary-subtle text-body @error('radius') is-invalid @enderror"
                                placeholder="Contoh: 500" value="{{ old('radius', $location->radius) }}" required>
                            @error('radius')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="text-muted opacity-25 my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.locations.index') }}" class="btn btn-secondary bg-opacity-10 text-body border-0 px-4">
                                Batal
                            </a>
                            <button type="submit" class="btn text-white px-4 shadow-sm fw-bold" style="background-color: #40BF89; border: none;">
                                <i class="bi bi-arrow-repeat me-1"></i> Perbarui Data Lokasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
