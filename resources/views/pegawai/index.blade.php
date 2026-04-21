@extends('layouts.app')

@section('title', 'Presensi Pegawai - BKK Pontianak')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4 class="mb-0 fw-bold">ABSENSI KAMERA</h4>
                    <small>Silakan masukkan NIP untuk memulai</small>
                </div>

                <div class="card-body p-4 text-center">
                    <div class="mb-4 text-start">
                        <label for="nip" class="form-label fw-bold">Nomor Induk Pegawai (NIP)</label>
                        <input type="text" id="nip" class="form-control form-control-lg border-primary"
                            placeholder="Contoh: 1992xxxx" autocomplete="off">
                        <div id="nama-pegawai" class="mt-2 fw-bold" style="min-height: 24px;"></div>
                    </div>

                    <div class="mb-4 position-relative bg-dark rounded shadow-inner" style="min-height: 250px;">
                        <video id="kamera" autoplay playsinline class="w-100 rounded"
                            style="object-fit: cover; max-height: 350px;"></video>
                        <div class="position-absolute top-50 start-50 translate-middle border border-2 border-white opacity-25"
                            style="width: 80%; height: 80%; pointer-events: none;"></div>
                    </div>

                    <button id="btn-capture" class="btn btn-primary btn-lg w-100 py-3 shadow-sm" disabled>
                        <i class="bi bi-camera-fill me-2"></i> Ambil Foto & Kirim Absen
                    </button>

                    <div id="container-upload" class="d-none mt-4 animate__animated animate__fadeIn">
                        <div class="card border-primary bg-light">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-2"><i class="bi bi-file-earmark-pdf-fill"></i> UNGGAH
                                    LAPORAN HARIAN</h6>
                                <p class="small text-muted">Satu langkah lagi! Silakan upload laporan kegiatan (PDF).</p>
                                <div class="input-group">
                                    <input type="file" id="file-laporan" class="form-control form-control-sm"
                                        accept=".pdf">
                                    <button class="btn btn-primary btn-sm" type="button" id="btn-upload-pdf">Kirim</button>
                                </div>
                                <div id="upload-status" class="small mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mt-3">
                        <i class="bi bi-info-circle"></i> Pastikan wajah terlihat jelas dan pencahayaan cukup.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <canvas id="canvas" style="display:none;"></canvas>
@endsection

@push('scripts')
    <script>
        const nipInput = document.getElementById('nip');
        const namaDisplay = document.getElementById('nama-pegawai');
        const video = document.getElementById('kamera');
        const btnCapture = document.getElementById('btn-capture');
        const canvas = document.getElementById('canvas');

        // 1. CARI NAMA & STATUS (Update bagian ini)
        nipInput.addEventListener('input', function() {
            const nip = this.value;
            if (nip.length >= 4) {
                fetch(`/get-pegawai/${nip}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            namaDisplay.innerText = "Nama: " + data.nama;
                            namaDisplay.className = "mt-2 fw-bold text-success";

                            if (data.status === 'masuk') {
                                btnCapture.innerHTML =
                                    '<i class="bi bi-box-arrow-in-right me-2"></i> Absen Masuk';
                                btnCapture.className = "btn btn-success btn-lg w-100 py-3";
                                btnCapture.disabled = false;
                                document.getElementById('container-upload').classList.add('d-none');
                            } else if (data.status === 'pulang') {
                                btnCapture.innerHTML =
                                    '<i class="bi bi-box-arrow-right me-2"></i> Absen Pulang';
                                btnCapture.className = "btn btn-warning btn-lg w-100 py-3 text-white";
                                btnCapture.disabled = false;
                                document.getElementById('container-upload').classList.add('d-none');
                            } else if (data.status === 'selesai') {
                                btnCapture.innerHTML =
                                    '<i class="bi bi-check-circle-fill me-2"></i> Absensi Selesai';
                                btnCapture.className = "btn btn-secondary btn-lg w-100 py-3";
                                btnCapture.disabled = true;
                                // TAMPILKAN UPLOAD PDF
                                document.getElementById('container-upload').classList.remove('d-none');
                            }
                        }
                    });
            }
        });

        // 2. AKTIFKAN KAMERA BELAKANG
        navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: "environment", // Kunci untuk kamera belakang
                    width: {
                        ideal: 1280
                    },
                    height: {
                        ideal: 720
                    }
                }
            })
            .then(stream => {
                video.srcObject = stream;
            })
            .catch(err => {
                console.error("Akses Kamera Gagal:", err);
                alert("Gagal mengakses kamera belakang. Pastikan izin diberikan dan Anda menggunakan HTTPS.");
            });

        // 3. PROSES SIMPAN ABSEN
        // 3. PROSES SIMPAN ABSEN (DENGAN GPS)
        btnCapture.addEventListener('click', () => {
            // 1. Efek Loading
            btnCapture.innerText = "Mencari Lokasi & Mengirim...";
            btnCapture.disabled = true;

            // 2. Cek apakah browser mendukung Geolocation
            if (!navigator.geolocation) {
                alert("Browser kamu tidak mendukung deteksi lokasi (GPS).");
                btnCapture.innerText = "Ambil Foto & Absen";
                btnCapture.disabled = false;
                return;
            }

            // 3. Ambil Lokasi (GPS) Terlebih Dahulu
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // 4. Setelah Lokasi Didapat, Baru Ambil Gambar dari Video
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const context = canvas.getContext('2d');
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const dataURI = canvas.toDataURL('image/jpeg', 0.7);

                    // 5. Kirim Data (Foto + NIP + Koordinat)
                    fetch('{{ route('absen.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                image: dataURI,
                                nip: nipInput.value,
                                latitude: lat,
                                longitude: lng
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                window.location.href = "{{ route('absen.success') }}";
                            } else {
                                alert("Gagal: " + data.message);
                                btnCapture.innerText = "Ambil Foto & Absen";
                                btnCapture.disabled = false;
                            }
                        })
                        .catch(err => {
                            alert("Terjadi kesalahan koneksi.");
                            btnCapture.innerText = "Ambil Foto & Absen";
                            btnCapture.disabled = false;
                        });
                },
                (error) => {
                    // Handle jika user menolak izin lokasi atau GPS mati
                    let pesan = "Gagal mendapatkan lokasi.";
                    if (error.code === 1) pesan =
                        "Izin lokasi ditolak. Silakan aktifkan izin lokasi di browser.";

                    alert(pesan);
                    btnCapture.innerText = "Ambil Foto & Absen";
                    btnCapture.disabled = false;
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000
                } // Setting agar GPS lebih akurat
            );
        });


        // TAMBAHKAN INI DI BAGIAN PALING BAWAH SCRIPT
        document.getElementById('btn-upload-pdf').addEventListener('click', function() {
            const fileInput = document.getElementById('file-laporan');
            const statusDiv = document.getElementById('upload-status');

            if (fileInput.files.length === 0) return alert("Pilih file PDF!");

            const formData = new FormData();
            formData.append('laporan_pdf', fileInput.files[0]);
            formData.append('nip', nipInput.value);

            this.disabled = true;
            statusDiv.innerText = "Mengunggah...";

            fetch('{{ route('absen.uploadLaporan') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        statusDiv.innerHTML =
                            '<span class="text-success fw-bold">Berhasil! Anda boleh meninggalkan halaman ini.</span>';
                    } else {
                        alert(data.message);
                        this.disabled = false;
                    }
                });
        });
    </script>
@endpush
