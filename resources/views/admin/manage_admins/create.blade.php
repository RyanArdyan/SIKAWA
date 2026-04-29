@extends('layouts.admin')

@section('header', 'Tambah Admin Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6"> {{-- Lebar dikecilkan agar lebih proporsional untuk form ringkas --}}
        <div class="card border-0 shadow-sm bg-body-tertiary">
            <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                <h5 class="mb-0 fw-bold text-body">Pilih Pegawai Jadi Admin</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.manage-admins.store') }}" method="POST">
                    @csrf

                    {{-- DROPDOWN PILIH PEGAWAI --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium text-body-secondary">Nama Pegawai</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">-- Cari & Pilih Pegawai --</option>
                            @foreach($pegawai as $p)
                                <option value="{{ $p->id }}" {{ old('user_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} (NIP: {{ $p->nip }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">
                            <i class="bi bi-info-circle me-1"></i>Hanya menampilkan user dengan hak akses 'pegawai'.
                        </small>
                        @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    {{-- PILIH ROLE --}}
                    <div class="mb-4">
                        <label class="form-label fw-medium text-body-secondary">Tentukan Role Akses</label>
                        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin (Standar)</option>
                            <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin (Full Akses)</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex gap-2 border-top pt-4">
                        <button type="submit" class="btn text-white px-4 fw-bold" style="background-color: #40BF89; border: none;">
                            <i class="bi bi-person-check-fill me-2"></i> Jadikan Admin
                        </button>
                        <a href="{{ route('admin.manage-admins.index') }}" class="btn btn-outline-secondary px-4">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Pesan Tambahan --}}
        <div class="alert alert-info border-0 shadow-sm mt-3" style="background-color: rgba(64, 191, 137, 0.1); color: #2d8660;">
            <small>
                <strong>Catatan:</strong> Pegawai yang diangkat menjadi Admin akan tetap menggunakan <strong>NIP</strong> dan <strong>Password</strong> lama mereka untuk masuk ke Dashboard.
            </small>
        </div>
    </div>
</div>
@endsection
