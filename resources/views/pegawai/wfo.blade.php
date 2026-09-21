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
                        <label for="nip" class="form-label fw-bold text-secondary mb-1">Nomor Induk Pegawai
                            (NIP)</label>
                        <input type="text" id="nip" class="form-control form-control-lg border-2 shadow-none"
                            style="border-color: #e2e8f0;" placeholder="Contoh: 1992xxxx" autocomplete="off">

                        {{-- Dihapus min-height nya agar tidak menyisakan ruang kosong saat NIP belum diisi --}}
                        <div id="nama-pegawai" class="fw-bold" style="color: #40BF89;"></div>
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

                    <button id="btn-absen-wfo" class="btn btn-lg w-100 py-3 shadow-sm text-white fw-bold border-0"
                        style="background-color: #40BF89; border-radius: 10px;" disabled>
                        <i class="bi bi-send-fill me-2"></i> Kirim Presensi
                    </button>

                    <p class="text-muted small mt-4 mb-0">
                        <i class="bi bi-info-circle"></i> Pastikan Anda terhubung ke jaringan lokal BKK Pontianak dan
                        mengaktifkan GPS & Kamera.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nipInput = document.getElementById('nip');
            const namaDisplay = document.getElementById('nama-pegawai');
            const btnAbsen = document.getElementById('btn-absen-wfo');
            const video = document.getElementById('webcam');
            const canvas = document.getElementById('canvas');

            let cameraStream = null;

            // Meminta akses dan menyalakan kamera depan
            function startCamera() {
                if (cameraStream) return;

                const constraints = {
                    video: {
                        facingMode: "user",
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    },
                    audio: false
                };

                navigator.mediaDevices.getUserMedia(constraints)
                    .then(stream => {
                        cameraStream = stream;
                        video.srcObject = stream;
                    })
                    .catch(err => {
                        console.warn("Gagal menggunakan resolusi ideal, mencoba fallback:", err);
                        // Fallback jika HP/Browser menolak constraint spesifik
                        navigator.mediaDevices.getUserMedia({
                                video: true,
                                audio: false
                            })
                            .then(stream => {
                                cameraStream = stream;
                                video.srcObject = stream;
                            })
                            .catch(fallbackErr => {
                                console.error("Gagal Mengakses Kamera:", fallbackErr);
                                alert(
                                    "Akses kamera ditolak atau tidak ditemukan. Mohon beri izin akses kamera di pengaturan browser Anda.");
                            });
                    });
            }

            // Jalankan Kamera Otomatis Saat Halaman Dimuat
            startCamera();

            // Memperbarui tampilan tombol berdasarkan status pegawai dari server
            function updateButtonUI(data) {
                if (data.success) {
                    namaDisplay.innerText = "Nama: " + data.nama;
                    namaDisplay.style.color = "#40BF89";

                    if (data.status === 'masuk') {
                        btnAbsen.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Kirim Presensi Masuk';
                        btnAbsen.style.backgroundColor = "#40BF89";
                        btnAbsen.style.color = "#fff";
                        btnAbsen.disabled = false;
                    } else if (data.status === 'pulang') {
                        btnAbsen.innerHTML = '<i class="bi bi-box-arrow-right me-2"></i> Absen Pulang Sekarang';
                        btnAbsen.style.backgroundColor = "#ffc107";
                        btnAbsen.style.color = "#000";
                        btnAbsen.disabled = false;

                        const info = document.createElement('div');
                        info.innerHTML =
                            '<small class="text-muted d-block mt-1"><i class="bi bi-info-circle"></i> Anda sudah absen masuk. Silakan klik tombol untuk absen pulang.</small>';
                        namaDisplay.appendChild(info);
                    } else if (data.status === 'selesai') {
                        btnAbsen.innerHTML = '<i class="bi bi-check-all me-2"></i> Presensi Hari Ini Selesai';
                        btnAbsen.style.backgroundColor = "#6c757d";
                        btnAbsen.style.color = "#fff";
                        btnAbsen.disabled = true;
                    }
                } else {
                    namaDisplay.innerText = data.message;
                    namaDisplay.style.color = "red";
                    btnAbsen.disabled = true;
                }
            }

            // Cek NIP secara real-time
            nipInput.addEventListener('input', function() {
                const nip = this.value.trim();
                if (nip.length >= 4) {
                    fetch(`/wfo/get-pegawai/${nip}`)
                        .then(res => res.json())
                        .then(data => updateButtonUI(data))
                        .catch(err => console.error("Error Get Pegawai:", err));
                } else {
                    namaDisplay.innerText = "";
                    btnAbsen.disabled = true;
                    btnAbsen.innerHTML = '<i class="bi bi-send-fill me-2"></i> Kirim Presensi';
                    btnAbsen.style.backgroundColor = "#40BF89";
                    btnAbsen.style.color = "#fff";
                }
            });

            // Eksekusi Proses Presensi
            btnAbsen.addEventListener('click', () => {
                const originalText = btnAbsen.innerHTML;
                btnAbsen.innerText = "Mendeteksi Lokasi & Memproses Foto...";
                btnAbsen.disabled = true;

                // 1. Ambil Frame dari Kamera ke Canvas
                const context = canvas.getContext('2d');

                // Cegah ukuran 0x0 piksel jika stream video belum sempurna
                const width = video.videoWidth > 0 ? video.videoWidth : 640;
                const height = video.videoHeight > 0 ? video.videoHeight : 480;

                canvas.width = width;
                canvas.height = height;

                // Flip horizontal agar hasil foto sesuai dengan tampilan cermin di layar
                context.translate(width, 0);
                context.scale(-1, 1);
                context.drawImage(video, 0, 0, width, height);

                // Export ke format gambar Base64
                const imageBase64 = canvas.toDataURL('image/jpeg', 0.8);

                // 2. Cek Dukungan GPS Browser
                if (!navigator.geolocation) {
                    alert("Browser Anda tidak mendukung Geolocation/GPS.");
                    btnAbsen.innerHTML = originalText;
                    btnAbsen.disabled = false;
                    return;
                }

                // 3. Dapatkan Koordinat GPS
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;

                        // 4. Kirim Data via Fetch API
                        fetch('{{ route('absen.storeWfo') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify({
                                    nip: nipInput.value.trim(),
                                    latitude: lat,
                                    longitude: lon,
                                    image: imageBase64
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                alert(data.message);
                                if (data.success) {
                                    location.reload();
                                } else {
                                    btnAbsen.innerHTML = originalText;
                                    btnAbsen.disabled = false;
                                }
                            })
                            .catch(err => {
                                console.error("Error Store WFO:", err);
                                alert("Terjadi kesalahan sistem saat mengirim data.");
                                btnAbsen.innerHTML = originalText;
                                btnAbsen.disabled = false;
                            });
                    },
                    (error) => {
                        let msg = "Gagal mendapatkan lokasi.";
                        if (error.code === 1) msg =
                            "Izin GPS ditolak. Silakan aktifkan izin lokasi di browser Anda.";
                        else if (error.code === 2) msg =
                            "Sinyal GPS tidak ditemukan. Pastikan Anda berada di area terjangkau sinyal GPS.";
                        else if (error.code === 3) msg =
                            "Waktu pencarian GPS habis (Timeout). Silakan coba klik tombol kembali.";

                        alert(msg);
                        btnAbsen.innerHTML = originalText;
                        btnAbsen.disabled = false;
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        });
    </script>
@endsection
