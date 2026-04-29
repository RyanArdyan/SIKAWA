@extends('layouts.admin')

@section('header', 'Edit Akses Admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-body-tertiary">
                <div class="card-header bg-transparent py-3" style="border-top: 5px solid #ffc107;">
                    <h5 class="mb-0 fw-bold text-body">Ubah Hak Akses</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.manage-admins.update', $admin->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Informasi Profil (Statis) --}}
                        <div class="mb-4">
                            <label class="form-label fw-medium text-body-secondary">Profil Administrator</label>
                            <div class="p-3 rounded border bg-light-subtle">
                                <div class="fw-bold text-body">{{ $admin->name }}</div>
                                <div class="text-muted small">NIP: {{ $admin->nip }}</div>
                                <div class="text-muted small">{{ $admin->email }}</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium text-body-secondary">Tingkat Hak Akses</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="super_admin" {{ $admin->role == 'super_admin' ? 'selected' : '' }}>
                                    Super Admin (Akses Penuh Sistem)
                                </option>
                                <option value="admin" {{ $admin->role == 'admin' ? 'selected' : '' }}>
                                    Admin (Manajemen Data & Absensi)
                                </option>
                                <option value="pegawai" {{ $admin->role == 'pegawai' ? 'selected' : '' }}>
                                    Pegawai (Hanya Akses User/Absensi)
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="alert alert-warning border-0 mt-3 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Jika Anda memilih <strong>Pegawai</strong>, user ini akan langsung dikeluarkan dari daftar
                                admin dan tidak bisa lagi masuk ke Dashboard Backend.
                            </div>
                        </div>

                        <div class="d-flex gap-2 border-top pt-4">
                            <button type="submit" class="btn btn-warning text-dark px-4 fw-bold border-0 shadow-sm">
                                <i class="bi bi-shield-check me-2"></i> Perbarui Akses
                            </button>
                            <a href="{{ route('admin.manage-admins.index') }}" class="btn btn-outline-secondary px-4">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
