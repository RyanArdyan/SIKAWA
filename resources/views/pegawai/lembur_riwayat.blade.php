@extends('layouts.app')

@section('title', 'Riwayat Lembur - SIKAWA')

@section('content')
    <div class="container py-4">
        {{-- Card Filter --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div style="width: 4px; height: 20px; background-color: #40BF89; border-radius: 10px;" class="me-2">
                    </div>
                    <h5 class="fw-bold mb-0">Cek Riwayat Lembur</h5>
                </div>

                <form action="{{ route('lembur.riwayat') }}" method="GET" class="row g-3">
                    {{-- Input NIP --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">NIP</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="bi bi-person text-muted"></i></span>
                            <input type="text" name="nip" class="form-control border-start-0"
                                placeholder="Masukkan NIP" value="{{ $nip }}" required>
                        </div>
                    </div>

                    {{-- Dari Tanggal --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                    </div>

                    {{-- Sampai Tanggal --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn text-white w-100 fw-bold shadow-sm"
                            style="background-color: #40BF89; border: none; border-radius: 8px;">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>

                        {{-- Tombol Export PDF (Tampil jika user dan data lembur ditemukan) --}}
                        @if ($user && $lemburs->count() > 0)
                            <a href="{{ route('lembur.exportPdf', ['nip' => $nip, 'start_date' => $start_date, 'end_date' => $end_date]) }}"
                                class="btn btn-danger w-100 fw-bold shadow-sm"
                                style="border-radius: 8px; background-color: #e74c3c; border: none;" target="_blank">
                                <i class="bi bi-file-pdf"></i> PDF
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Alert Info Pegawai --}}
        @if ($user)
            <div class="alert border-0 shadow-sm d-flex align-items-center"
                style="background-color: rgba(64, 191, 137, 0.1); color: #40BF89; border-radius: 12px;">
                <i class="bi bi-info-circle-fill me-2"></i>
                <span>Menampilkan data untuk: <strong>{{ $user->name }}</strong></span>
            </div>
        @endif

        {{-- Tabel Riwayat --}}
        <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #f8f9fa;">
                        <tr>
                            <th class="ps-4 py-3 text-secondary small text-uppercase" style="width: 50px;">No</th>
                            <th class="py-3 text-secondary small text-uppercase">Waktu Masuk Lembur</th>
                            <th class="py-3 text-secondary small text-uppercase">Waktu Pulang Lembur</th>
                            <th class="py-3 text-secondary small text-uppercase text-center">Status Laporan</th>
                            <th class="py-3 text-secondary small text-uppercase text-center">Status Approval</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lemburs as $item)
                            <tr>
                                {{-- Kolom Nomor Berkelanjutan --}}
                                <td class="ps-4 fw-medium">
                                    @if($lemburs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                        {{ ($lemburs->currentPage() - 1) * $lemburs->perPage() + $loop->iteration }}
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </td>

                                {{-- Kolom Waktu Check-In --}}
                                <td class="fw-medium">
                                    @if ($item->check_in_lembur)
                                        {{ \Carbon\Carbon::parse($item->check_in_lembur)->translatedFormat('d F Y') }} <br>
                                        <small
                                            class="text-secondary">{{ \Carbon\Carbon::parse($item->check_in_lembur)->format('H:i') }}
                                            WIB</small>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Kolom Waktu Check-Out --}}
                                <td class="fw-medium">
                                    @if ($item->check_out_lembur)
                                        {{ \Carbon\Carbon::parse($item->check_out_lembur)->translatedFormat('d F Y') }} <br>
                                        <small
                                            class="text-secondary">{{ \Carbon\Carbon::parse($item->check_out_lembur)->format('H:i') }}
                                            WIB</small>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Kolom Status Laporan --}}
                                <td class="text-center">
                                    @if ($item->keterangan_lembur || $item->laporan_pdf)
                                        <span class="badge px-3 py-2 bg-success bg-opacity-10 text-success border border-success">
                                            <i class="bi bi-file-earmark-check me-1"></i> Diserahkan
                                        </span>
                                    @else
                                        <span class="badge px-3 py-2 bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                            <i class="bi bi-file-earmark-x me-1"></i> Belum Ada
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom Status Approval (pending/disetujui/ditolak) --}}
                                <td class="text-center">
                                    @if ($item->status == 'disetujui')
                                        <span class="badge px-3 py-2"
                                            style="background-color: rgba(64, 191, 137, 0.1); color: #40BF89; border: 1px solid rgba(64, 191, 137, 0.2);">
                                            <i class="bi bi-check-circle me-1"></i> Disetujui
                                        </span>
                                    @elseif ($item->status == 'ditolak')
                                        <span class="badge px-3 py-2"
                                            style="background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.2);">
                                            <i class="bi bi-x-circle me-1"></i> Ditolak
                                        </span>
                                    @else
                                        <span class="badge px-3 py-2 bg-warning bg-opacity-10 text-warning border border-warning">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x fs-1 d-block mb-3 opacity-25"></i>
                                    Data riwayat lembur tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TOMBOL PAGINASI --}}
            @if($lemburs instanceof \Illuminate\Pagination\LengthAwarePaginator && $lemburs->hasPages())
                {{-- Hapus bg-white, gunakan var(--bs-card-bg) agar mengikuti warna card (light/dark mode) --}}
                <div class="card-footer border-0 pt-4 pb-2 d-flex justify-content-center" style="background-color: var(--bs-card-bg);">
                    <nav aria-label="Page navigation" class="mt-2">
                        {{-- Menggunakan bootstrap-5 dengan styling yang mendukung dark mode bawaan Bootstrap --}}
                        {{ $lemburs->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
            @endif
        </div>
    </div>
@endsection
