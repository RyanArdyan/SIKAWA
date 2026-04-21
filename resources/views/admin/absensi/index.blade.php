@extends('layouts.admin')

@section('header', 'Laporan Absensi Pegawai')

@section('content')
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Riwayat Kehadiran</h5>
                <span class="badge bg-primary">Jam Masuk: {{ $jamMasuk }} WIB</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Foto & Lokasi</th> <th>Nama Pegawai</th>
                            <th>NIP</th>
                            <th>Waktu Absen</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $key => $a)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    {{-- Link menuju halaman detail --}}
                                    <a href="{{ route('admin.laporan.detail', $a->id) }}" class="badge bg-success text-decoration-none">
                                        <i class="bi bi-eye me-1"></i> Lihat Detail
                                    </a>
                                </td>
                                <td class="fw-bold">{{ $a->user->name ?? 'User Terhapus' }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $a->user->nip ?? '-' }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($a->check_in_time)->format('d M Y, H:i') }} WIB</td>
                                <td>
                                    {{-- Gunakan kolom status dari database agar lebih konsisten --}}
                                    @if ($a->status == 'terlambat')
                                        <span class="badge bg-danger">Terlambat</span>
                                    @else
                                        <span class="badge bg-success">Tepat Waktu</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada data absensi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

{{-- Bagian Modal dan Script showModal bisa dihapus jika kamu sudah mantap pindah halaman --}}
