@extends('layouts.admin')

@section('header', 'Dashboard Utama')

@section('content')
<div class="row">
    {{-- Card Statistik tetap menggunakan gradient karena sudah memiliki kontras yang baik --}}
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #40BF89, #5ed3a1);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.9;">Total Pegawai</h6>
                        <h2 class="fw-bold mb-0">{{ $totalPegawai }}</h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-people-fill" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #2c3e50, #4a6076);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.9;">Sudah Absen Hari Ini</h6>
                        <h2 class="fw-bold mb-0">{{ $absenHariIni }}</h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-camera-fill" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(45deg, #ffc107, #ffdb70);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.9; color: #212529;">Waktu Server</h6>
                        <h2 class="fw-bold mb-0" id="clock" style="color: #212529;">00:00:00</h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-clock-fill" style="font-size: 2.5rem; opacity: 0.3; color: #212529;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        {{-- Card di bawah ini sekarang adaptif terhadap Dark Mode --}}
        <div class="card border-0 shadow-sm bg-body-tertiary">
            <div class="card-header bg-transparent py-3" style="border-left: 5px solid #40BF89;">
                <h5 class="mb-0 fw-bold" style="color: #40BF89;">Selamat Datang, Admin SIKAWA</h5>
            </div>
            <div class="card-body">
                <p class="text-body">Melalui panel ini, Anda dapat mengelola data pegawai dan memantau kehadiran pegawai secara real-time dengan bukti foto kamera depan.</p>

                {{-- Mengganti alert-light dengan latar belakang transparan yang adaptif --}}
                <div class="alert border-start border-4" style="border-color: #40BF89 !important; background-color: rgba(64, 191, 137, 0.1);">
                    <strong class="text-body">Tips:</strong> <span class="text-body">Pastikan Anda rutin mengecek menu <strong>Laporan Absensi</strong> untuk memvalidasi foto dan laporan PDF yang dikirimkan pegawai.</span>
                </div>

                <div class="mt-4">
                    <a href="/admin/laporan" class="btn text-white me-2 px-4 shadow-sm fw-bold" style="background-color: #40BF89; border: none;">
                        <i class="bi bi-file-earmark-text me-1"></i> Lihat Laporan Hari Ini
                    </a>
                    <a href="/admin/pegawai" class="btn btn-outline-secondary px-4 fw-bold">
                        <i class="bi bi-person-gear me-1"></i> Kelola Data Pegawai
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();

        // Menggunakan Intl.DateTimeFormat untuk memastikan format 24 jam dan timezone Asia/Jakarta (WIB)
        const options = {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };

        const formatter = new Intl.DateTimeFormat('en-GB', options);
        const timeString = formatter.format(now);

        document.getElementById('clock').textContent = timeString + " WIB";
    }

    // Jalankan fungsi setiap 1 detik
    setInterval(updateClock, 1000);

    // Panggil sekali di awal agar tidak menunggu 1 detik pertama
    updateClock();
</script>
@endpush
