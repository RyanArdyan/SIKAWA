@extends('layouts.app')

@section('title', 'Absensi Berhasil')

@section('content')
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 text-center">
            <div class="card shadow border-0 py-5 animate__animated animate__fadeInUp">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 6rem; color: #40BF89;"></i>
                    </div>

                    <h2 class="fw-bold text-dark">Absen Berhasil!</h2>
                    <p class="text-muted fs-5">
                        Data kehadiran dan foto Anda telah tersimpan di sistem SIKAWA BKK.
                    </p>

                    <div class="alert alert-light border-start border-4 mt-4 mb-4 shadow-sm" style="border-color: #40BF89 !important;">
                        <small class="text-secondary">
                            Jam Absen: <strong>{{ date('H:i') }} WIB</strong><br>
                            Tanggal: <strong>{{ date('d F Y') }}</strong>
                        </small>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('absen.home') }}" class="btn btn-lg px-5 shadow-sm text-white"
                           style="background-color: #40BF89; border: none;">
                            <i class="bi bi-arrow-left me-2"></i> Kembali ke Beranda
                        </a>
                    </div>

                    <p class="text-muted small mt-5">
                        Halaman ini akan kembali ke Beranda otomatis dalam 30 detik.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Animasi kustom untuk icon agar lebih smooth */
        .bi-check-circle-fill {
            display: inline-block;
            animation: bounceIn 0.8s;
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.05); opacity: 1; }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }

        /* Memberikan sedikit aksen pada card */
        .card {
            border-top: 8px solid #40BF89 !important;
        }
    </style>
@endpush

@push('scripts')
<script>
    // Otomatis kembali ke halaman absen setelah 30 detik
    setTimeout(function() {
        window.location.href = "{{ route('absen.home') }}";
    }, 30000);
</script>
@endpush
