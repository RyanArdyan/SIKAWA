@extends('layouts.admin')

@section('header', 'Lokasi Kantor')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 text-white shadow-sm" role="alert" style="background-color: #40BF89;">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm bg-body-tertiary">
        {{-- Mengubah bg-white menjadi bg-transparent agar mengikuti tema kartu --}}
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center py-3" style="border-top: 5px solid #40BF89;">
            <h5 class="mb-0 fw-bold text-body">Daftar Lokasi Kantor SIKAWA</h5>
            <a href="{{ route('admin.locations.create') }}" class="btn text-white shadow-sm fw-bold" style="background-color: #40BF89; border: none;">
                <i class="bi bi-plus-lg"></i> Tambah Lokasi
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    {{-- Menghapus table-light agar header tabel adaptif --}}
                    <thead>
                        <tr>
                            <th class="py-3 text-body-secondary">No</th>
                            <th class="py-3 text-body-secondary">Nama Tim</th>
                            <th class="py-3 text-center text-body-secondary">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $key => $lc)
                            <tr>
                                <td class="fw-bold text-body-secondary">{{ $key + 1 }}</td>
                                <td class="fw-bold text-body">{{ $lc->name }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.locations.edit', $lc->id) }}"
                                            class="btn btn-sm btn-outline-warning px-3 fw-medium">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>

                                        <form action="{{ route('admin.locations.destroy', $lc->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus lokasi ini?')">
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
                                <td colspan="5" class="text-center py-5 text-body-secondary">
                                    <i class="bi bi-briefcase text-body-tertiary d-block mb-2" style="font-size: 3rem;"></i>
                                    Belum ada data lokasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
