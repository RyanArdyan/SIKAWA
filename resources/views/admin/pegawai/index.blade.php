@extends('layouts.admin')

@section('header', 'Data Pegawai')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" style="border-top: 5px solid #40BF89;">
        <h5 class="mb-0 fw-bold" style="color: #2c3e50;">Daftar Pegawai SIKAWA</h5>
        <a href="{{ route('admin.pegawai.create') }}" class="btn text-white shadow-sm" style="background-color: #40BF89; border: none;">
            <i class="bi bi-plus-lg"></i> Tambah Pegawai
        </a>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success border-0 text-white" style="background-color: #40BF89;">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3" style="color: #2c3e50;">No</th>
                        <th class="py-3" style="color: #2c3e50;">NIP</th>
                        <th class="py-3" style="color: #2c3e50;">Nama Pegawai</th>
                        <th class="py-3 text-center" style="color: #2c3e50;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pegawai as $key => $p)
                    <tr>
                        <td class="fw-bold text-muted">{{ $key + 1 }}</td>
                        <td>
                            <span class="badge px-3 py-2" style="background-color: rgba(64, 191, 137, 0.1); color: #40BF89; border: 1px solid rgba(64, 191, 137, 0.3);">
                                {{ $p->nip }}
                            </span>
                        </td>
                        <td class="fw-medium">{{ $p->name }}</td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.pegawai.edit', $p->id) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>

                                <form action="{{ route('admin.pegawai.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pegawai ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-person-exclamation d-block mb-2" style="font-size: 2rem;"></i>
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
