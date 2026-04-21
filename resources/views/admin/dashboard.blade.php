@extends('layouts.admin')

@section('header', 'Dashboard Utama')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Total Pegawai</h6>
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
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Sudah Absen Hari Ini</h6>
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
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1" style="font-size: 0.8rem; opacity: 0.8;">Waktu Server</h6>
                        <h2 class="fw-bold mb-0" id="clock">00:00:00</h2>
                    </div>
                    <div class="icon">
                        <i class="bi bi-clock-fill" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary">Selamat Datang, Admin BKK</h5>
            </div>
            <div class="card-body">
                <p>Melalui panel ini, Anda dapat mengelola data pegawai dan memantau kehadiran pegawai secara real-time dengan bukti foto kamera belakang.</p>
                <div class="alert alert-light border">
                    <strong>Tips:</strong> Pastikan Anda rutin mengecek menu <strong>Laporan Absensi</strong> untuk memvalidasi foto yang dikirimkan pegawai.
                </div>
                <div class="mt-4">
                    <a href="/admin/laporan" class="btn btn-primary me-2">Lihat Laporan Hari Ini</a>
                    <a href="/admin/pegawai" class="btn btn-outline-secondary">Kelola Data Pegawai</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fungsi untuk membuat jam digital real-time
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
    }

    setInterval(updateClock, 1000);
    updateClock(); // Jalankan langsung tanpa menunggu interval
</script>
@endpush
