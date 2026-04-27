@extends('layouts.admin')

@section('header', 'Data Pegawai')

@section('content')
<div class="card border-0 shadow-sm bg-body-tertiary">
    {{-- Header kartu dibuat transparan agar mengikuti bg-body-tertiary --}}
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3" style="border-top: 5px solid #40BF89;">
        <h5 class="mb-0 fw-bold text-body">Daftar Pegawai SIKAWA</h5>
        <a href="{{ route('admin.pegawai.create') }}" class="btn text-white shadow-sm fw-bold" style="background-color: #40BF89; border: none;">
            <i class="bi bi-plus-lg"></i> Tambah Pegawai
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success border-0 text-white shadow-sm" style="background-color: #40BF89;">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                {{-- Menghapus table-light agar header tabel adaptif --}}
                <thead>
                    <tr>
                        <th class="py-3 text-body-secondary">No</th>
                        <th class="py-3 text-body-secondary">NIP</th>
                        <th class="py-3 text-body-secondary">Nama Pegawai</th>
                        <th class="py-3 text-center text-body-secondary">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawai as $key => $p)
                    <tr>
                        <td class="fw-bold text-body-secondary">{{ $key + 1 }}</td>
                        <td>
                            <span class="badge px-3 py-2" style="background-color: rgba(64, 191, 137, 0.1); color: #40BF89; border: 1px solid rgba(64, 191, 137, 0.3);">
                                {{ $p->nip }}
                            </span>
                        </td>
                        <td class="fw-medium text-body">{{ $p->name }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.pegawai.edit', $p->id) }}" class="btn btn-sm btn-outline-warning px-3 fw-medium">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>

                                <form action="{{ route('admin.pegawai.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pegawai ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3 fw-medium">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-body-secondary">
                            <i class="bi bi-person-exclamation d-block mb-2 text-body-tertiary" style="font-size: 3rem;"></i>
                            Belum ada data pegawai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
