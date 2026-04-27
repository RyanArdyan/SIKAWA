@extends('layouts.admin')

@section('header', 'Tim Kerja')

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
            <h5 class="mb-0 fw-bold text-body">Daftar Tim Kerja SIKAWA</h5>
            <a href="{{ route('admin.tim-kerja.create') }}" class="btn text-white shadow-sm fw-bold" style="background-color: #40BF89; border: none;">
                <i class="bi bi-plus-lg"></i> Tambah Tim Kerja
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
                            <th class="py-3 text-body-secondary">Ketua Tim</th>
                            <th class="py-3 text-body-secondary">Jumlah Anggota</th>
                            <th class="py-3 text-center text-body-secondary">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tim_kerja as $key => $tk)
                            <tr>
                                <td class="fw-bold text-body-secondary">{{ $key + 1 }}</td>
                                <td class="fw-bold text-body">{{ $tk->nama }}</td>
                                <td>
                                    @if ($tk->ketua)
                                        <span class="badge px-3 py-2" style="background-color: rgba(64, 191, 137, 0.1); color: #40BF89; border: 1px solid rgba(64, 191, 137, 0.3);">
                                            <i class="bi bi-person-badge me-1"></i>
                                            {{ $tk->ketua->name }}
                                        </span>
                                    @else
                                        {{-- Mengganti bg-light dengan bg-body-secondary agar tidak putih terang di mode gelap --}}
                                        <span class="badge bg-body-secondary text-body-secondary fw-normal border">
                                            <i class="bi bi-dash-circle me-1"></i> Belum ada ketua
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center text-body">
                                        <i class="bi bi-people text-body-secondary me-2"></i>
                                        <span class="fw-medium">{{ $tk->anggota->count() }} Orang</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('admin.tim-kerja.edit', $tk->id) }}"
                                            class="btn btn-sm btn-outline-warning px-3 fw-medium">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>

                                        <form action="{{ route('admin.tim-kerja.destroy', $tk->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus tim ini? Semua pegawai di dalam tim ini juga akan terhapus!')">
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
                                    Belum ada data tim kerja.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
