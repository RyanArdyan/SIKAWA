@extends('layouts.admin')

@section('header', 'Hapus Data Absensi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        {{-- Card Utama dengan Tema Full Hijau SIKAWA --}}
        <div class="card border-0 shadow-sm bg-body-tertiary">
            <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
                <div class="d-flex align-items-center">
                    {{-- Icon Box Hijau --}}
                    <div class="bg-opacity-10 p-2 rounded-3 me-3" style="background-color: #40BF89;">
                        <i class="bi bi-trash3-fill fs-5" style="color: #40BF89;"></i>
                    </div>
                    <h5 class="mb-0 fw-bold text-body">Bersihkan Riwayat Absensi</h5>
                </div>
            </div>
            <div class="card-body p-4">

                {{-- Alert Sukses (Tema Hijau) --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="background-color: #e8f7f0; color: #1a4d36;">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Informasi Peringatan (Soft Hijau) --}}
                <div class="alert border-0 shadow-sm mb-4" role="alert" style="background-color: #f0fdf4; color: #166534; border-left: 4px solid #40BF89 !important;">
                    <small class="fw-bold d-block mb-1">
                        <i class="bi bi-info-circle-fill me-1"></i> Informasi Pembersihan:
                    </small>
                    <small>Menghapus data akan mengosongkan ruang penyimpanan server. Pastikan Anda telah melakukan ekspor PDF jika data masih dibutuhkan.</small>
                </div>

                {{-- Form Pembersihan --}}
                <form id="form-hapus-absensi" action="{{ route('admin.absensi.processDelete') }}" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="mb-3">
                        <label class="form-label fw-bold text-body-secondary">Mulai Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                <i class="bi bi-calendar2-range" style="color: #40BF89;"></i>
                            </span>
                            <input type="date" name="start_date" id="start_date"
                                class="form-control bg-body border-secondary-subtle border-start-0 text-body" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-body-secondary">Hingga Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                                <i class="bi bi-calendar2-check" style="color: #40BF89;"></i>
                            </span>
                            <input type="date" name="end_date" id="end_date"
                                class="form-control bg-body border-secondary-subtle border-start-0 text-body" required>
                        </div>
                    </div>

                    {{-- Tombol Utama Hijau --}}
                    <button type="submit" class="btn text-white w-100 fw-bold shadow-sm py-2 mt-2"
                        style="background-color: #40BF89; border: none;"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus permanen data pada rentang tanggal tersebut?')">
                        <i class="bi bi-shield-lock-fill me-2"></i> Konfirmasi Penghapusan
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

{{-- Script untuk SweetAlert --}}
@push('scripts')
<!-- Pastikan CDN Sweetalert ini dipanggil jika belum ada di layouts.admin Anda -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-hapus-absensi');

        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah form langsung tersubmit

            // Ambil value tanggal
            let startDate = document.getElementById('start_date').value;
            let endDate = document.getElementById('end_date').value;

            // Pastikan tidak kosong (meski sudah ada atribut 'required' di HTML)
            if(!startDate || !endDate) {
                Swal.fire('Oops!', 'Harap pilih rentang tanggal terlebih dahulu.', 'warning');
                return;
            }

            // Tampilkan SweetAlert
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                html: `Data absensi dari tanggal <b>${startDate}</b> hingga <b>${endDate}</b> <br><br> <span class="text-danger">Semua data dan file foto pada rentang tersebut akan dihapus PERMANEN dan tidak dapat dikembalikan!</span>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Permanen!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user klik "Ya", submit form-nya
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
