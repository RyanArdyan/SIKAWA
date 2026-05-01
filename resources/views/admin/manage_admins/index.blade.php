@extends('layouts.admin')

@section('header', 'Kelola Admin')

@section('content')
    <div class="card border-0 shadow-sm bg-body-tertiary">
        {{-- Header dengan aksen hijau SIKAWA --}}
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3"
            style="border-top: 5px solid #40BF89;">
            <h5 class="mb-0 fw-bold text-body">Daftar Administrator Sistem</h5>
            <a href="{{ route('admin.manage-admins.create') }}" class="btn text-white shadow-sm fw-bold"
                style="background-color: #40BF89; border: none;">
                <i class="bi bi-shield-plus"></i> Tambah Admin
            </a>
        </div>

        <div class="card-body">
            {{-- Alert Success --}}
            @if (session('success'))
                <div class="alert alert-success border-0 text-white shadow-sm" style="background-color: #40BF89;">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif

            {{-- Alert Error (Penting untuk proteksi hapus diri sendiri) --}}
            @if (session('error'))
                <div class="alert alert-danger border-0 text-white shadow-sm">
                    <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="py-3 text-body-secondary">No</th>
                            <th class="py-3 text-body-secondary">NIP / Email</th>
                            <th class="py-3 text-body-secondary">Nama Admin</th>
                            <th class="py-3 text-body-secondary">Role</th>
                            <th class="py-3 text-center text-body-secondary">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $key => $a)
                            <tr>
                                <td class="fw-bold text-body-secondary">{{ $key + 1 }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-body">{{ $a->nip }}</span>
                                        <small class="text-body-secondary">{{ $a->email }}</small>
                                    </div>
                                </td>
                                <td class="fw-medium text-body">{{ $a->name }}</td>
                                <td>
                                    @if ($a->role === 'super_admin')
                                        <span class="badge bg-dark text-white px-3 py-2 border border-secondary">
                                            <i class="bi bi-star-fill text-warning me-1"></i> Super Admin
                                        </span>
                                    @else
                                        {{-- Menghapus text-dark dan menggantinya dengan inline style agar warna teks konsisten --}}
                                        <span class="badge px-3 py-2"
                                            style="background-color: rgba(64, 191, 137, 0.15);
                     color: #40BF89;
                     border: 1px solid rgba(64, 191, 137, 0.4);">
                                            Admin
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.manage-admins.edit', $a->id) }}"
                                            class="btn btn-sm btn-outline-warning px-3 fw-medium">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-shield-slash d-block mb-2 text-body-tertiary"
                                        style="font-size: 3rem;"></i>
                                    Belum ada data administrator.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
