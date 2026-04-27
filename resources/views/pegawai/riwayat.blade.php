@extends('layouts.app')

@section('content')
    <div class="container py-4">
        {{-- Card Filter --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div style="width: 4px; height: 20px; background-color: #40BF89; border-radius: 10px;" class="me-2"></div>
                    <h5 class="fw-bold mb-0">Cek Riwayat Absensi</h5>
                </div>

                <form action="{{ route('absen.riwayat') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Nomor Induk Pegawai (NIP)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                            <input type="text" name="nip" class="form-control border-start-0" placeholder="Masukkan NIP"
                                value="{{ $nip }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-secondary">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn text-white w-100 fw-bold shadow-sm"
                            style="background-color: #40BF89; border: none; border-radius: 8px;">
                            <i class="bi bi-search me-1"></i> Cari
                        </button>

                        @if ($user && $attendances->count() > 0)
                            <a href="{{ route('absen.exportPdf', ['nip' => $nip, 'start_date' => $start_date, 'end_date' => $end_date]) }}"
                                class="btn btn-danger w-100 fw-bold shadow-sm" style="border-radius: 8px; background-color: #e74c3c; border: none;">
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
                            <th class="ps-4 py-3 text-secondary small text-uppercase">Tanggal DFSA</th>
                            <th class="py-3 text-secondary small text-uppercase">Jam Masuk</th>
                            <th class="py-3 text-secondary small text-uppercase">Jam Pulang</th>
                            <th class="py-3 text-secondary small text-uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $item)
                            <tr>
                                <td class="ps-4 fw-medium">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
                                <td class="text-secondary">{{ $item->check_in_time ? \Carbon\Carbon::parse($item->check_in_time)->format('H:i') : '-' }}</td>
                                <td class="text-secondary">{{ $item->check_out_time ? \Carbon\Carbon::parse($item->check_out_time)->format('H:i') : '-' }}</td>
                                <td>
                                    @if($item->status == 'terlambat')
                                        <span class="badge px-3 py-2" style="background-color: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.2);">
                                            <i class="bi bi-x-circle me-1"></i> Terlambat
                                        </span>
                                    @else
                                        <span class="badge px-3 py-2" style="background-color: rgba(64, 191, 137, 0.1); color: #40BF89; border: 1px solid rgba(64, 191, 137, 0.2);">
                                            <i class="bi bi-check-circle me-1"></i> Tepat Waktu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-clipboard-x fs-1 d-block mb-3 opacity-25"></i>
                                    Data absensi belum tersedia untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
