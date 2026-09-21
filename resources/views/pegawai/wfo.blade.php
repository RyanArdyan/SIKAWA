@extends('layouts.app')

@section('title', 'Presensi Kantor - SIKAWA')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 overflow-hidden" style="border-radius: 15px;">
                <div class="card-header text-white text-center py-4" style="background-color: #40BF89; border: none;">
                    <i class="bi bi-building-fill-check mb-2" style="font-size: 2rem;"></i>
                    <h4 class="mb-0 fw-bold">PRESENSI KANTOR (WFO)</h4>
                    <small id="subtitle-absen" class="opacity-75">Masukkan NIP untuk mencatat kehadiran</small>
                </div>

                <div class="card-body p-4 text-center">
                    {{-- Container Input NIP --}}
                    <div class="text-start mb-3">
                        <label for="nip" class="form-label fw-bold text-secondary mb-1">Nomor Induk Pegawai (NIP)</label>
                        <input type="text" id="nip" class="form-control form-control-lg border-2 shadow-none"
                            style="border-color: #e2e8f0;" placeholder="Contoh: 1992xxxx" autocomplete="off">

                        <div id="nama-pegawai" class="fw-bold mt-1" style="color: #40BF89;"></div>
                    </div>

                    {{-- Container Preview Kamera Depan --}}
                    <div id="camera-container" class="mb-3">
                        <label class="form-label fw-bold text-secondary d-block text-start mb-1">Presensi Swafoto</label>
                        <div class="position-relative overflow-hidden rounded-3 border"
                            style="background: #000; height: 260px;">
                            <video id="webcam" autoplay playsinline
                                style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
                            <canvas id="canvas" class="d-none"></canvas>
                        </div>
                    </div>

                    {{-- Tombol Presensi Terpisah --}}
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <button id="btn-masuk" class="btn btn-lg w-100 py-3 shadow-sm text-white fw-bold border-0"
                                style="background-color: #40BF89; border-radius: 10px;" disabled>
                                <i class="bi bi-box-arrow-in-right me-1"></i> Presensi Masuk
                            </button>
                        </div>
                        <div class="col-6">
                            <button id="btn-keluar" class="btn btn-lg w-100 py-3 shadow-sm text-white fw-bold border-0"
                                style="background-color: #ffc107; color: #000; border-radius: 10px;" disabled>
                                <i class="bi bi-box-arrow-right me-1"></i> Presensi Keluar
                            </button>
                        </div>
                    </div>

                    <p class="text-muted small mt-4 mb-0">
                        <i class="bi bi-info-circle"></i> Pastikan Anda terhubung ke jaringan BKK Pontianak dan mengaktifkan Kamera.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nipInput = document.getElementById('nip');
            const namaDisplay = document.getElementById('nama-pegawai');
            const btnMasuk = document.getElementById('btn-masuk');
            const btnKeluar = document.getElementById('btn-keluar');
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');

            let cameraStream = null;

            // Meminta akses dan menyalakan kamera depan
            function startCamera() {
                if (cameraStream) return;

                const constraints = {
                    video: { facingMode: "user", width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false
                };

                navigator.mediaDevices.getUserMedia(constraints)
                    .then(stream => {
                        cameraStream = stream;
                        video.srcObject = stream;
                    })
                    .catch(err => {
                        console.warn("Gagal menggunakan resolusi ideal, mencoba fallback:", err);
                        navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                            .then(stream => {
                                cameraStream = stream;
                                video.srcObject = stream;
                            })
                            .catch(fallbackErr => {
                                console.error("Gagal Mengakses Kamera:", fallbackErr);
                                alert("Akses kamera ditolak atau tidak ditemukan. Mohon beri izin akses kamera di pengaturan browser Anda.");
                            });
                    });
            }

            // Jalankan Kamera Otomatis saat Halaman Dimuat
            startCamera();

            // Cek NIP secara real-time
            nipInput.addEventListener('input', function() {
                const nip = this.value.trim();
                if (nip.length >= 4) {
                    fetch(`/wfo/get-pegawai/${nip}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                namaDisplay.innerText = "Nama: " + data.nama;
                                namaDisplay.style.color = "#40BF89";
                                btnMasuk.disabled = false;
                                btnKeluar.disabled = false;
                            } else {
                                namaDisplay.innerText = data.message;
                                namaDisplay.style.color = "red";
                                btnMasuk.disabled = true;
                                btnKeluar.disabled = true;
                            }
                        })
                        .catch(err => console.error("Error Get Pegawai:", err));
                } else {
                    namaDisplay.innerText = "";
                    btnMasuk.disabled = true;
                    btnKeluar.disabled = true;
                }
            });

            // Eksekusi Proses Presensi (tipe: 'masuk' atau 'keluar')
            function submitPresensi(type) {
                const targetBtn = type === 'masuk' ? btnMasuk : btnKeluar;
                const originalText = targetBtn.innerHTML;

                // Nonaktifkan kedua tombol selama proses berjalan
                btnMasuk.disabled = true;
                btnKeluar.disabled = true;
                targetBtn.innerText = "Memproses...";

                // Ambil Frame dari Video Kamera ke Canvas
                const context = canvas.getContext('2d');
                const width = video.videoWidth > 0 ? video.videoWidth : 640;
                const height = video.videoHeight > 0 ? video.videoHeight : 480;

                canvas.width = width;
                canvas.height = height;

                // Mirroring effect (flip horizontal)
                context.translate(width, 0);
                context.scale(-1, 1);
                context.drawImage(video, 0, 0, width, height);

                // Konversi gambar ke format Base64
                const imageBase64 = canvas.toDataURL('image/jpeg', 0.8);

                // Kirim data ke Controller
                fetch('{{ route('absen.storeWfo') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        nip: nipInput.value.trim(),
                        image: imageBase64,
                        tipe: type
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        location.reload();
                    } else {
                        targetBtn.innerHTML = originalText;
                        btnMasuk.disabled = false;
                        btnKeluar.disabled = false;
                    }
                })
                .catch(err => {
                    console.error("Error Store WFO:", err);
                    alert("Terjadi kesalahan sistem saat mengirim data.");
                    targetBtn.innerHTML = originalText;
                    btnMasuk.disabled = false;
                    btnKeluar.disabled = false;
                });
            }

            btnMasuk.addEventListener('click', () => submitPresensi('masuk'));
            btnKeluar.addEventListener('click', () => submitPresensi('keluar'));
        });
    </script>
@endsection
