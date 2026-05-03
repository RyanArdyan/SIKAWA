@extends('layouts.app')

@section('title', 'Presensi Pegawai - SIKAWA')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 overflow-hidden">
                <div class="card-header text-white text-center py-3" style="background-color: #40BF89;">
                    <h4 class="mb-0 fw-bold">ABSENSI KAMERA</h4>
                    <small id="subtitle-absen">Silakan masukkan NIP untuk memulai</small>
                </div>

                <div class="card-body p-4 text-center">
                    <div class="mb-4 text-start">
                        <label for="nip" class="form-label fw-bold">Nomor Induk Pegawai (NIP)</label>
                        <input type="text" id="nip" class="form-control form-control-lg"
                            style="border-color: #40BF89;" placeholder="Contoh: 1992xxxx" autocomplete="off">
                        <div id="nama-pegawai" class="mt-2 fw-bold" style="min-height: 24px;"></div>
                    </div>

                    {{-- AREA KAMERA: SELALU AKTIF --}}
                    <div id="container-kamera" class="mb-4 position-relative bg-dark rounded shadow-inner"
                        style="min-height: 250px;">
                        <video id="kamera" autoplay playsinline class="w-100 rounded"
                            style="object-fit: cover; max-height: 350px;"></video>
                        <div class="position-absolute top-50 start-50 translate-middle border border-2 border-white opacity-25"
                            style="width: 80%; height: 80%; pointer-events: none;"></div>
                    </div>

                    <button id="btn-capture" class="btn btn-lg w-100 py-3 shadow-sm text-white"
                        style="background-color: #40BF89; border: none;" disabled>
                        <i class="bi bi-camera-fill me-2"></i> Ambil Foto & Kirim Absen
                    </button>

                    {{-- CONTAINER UPLOAD LAPORAN --}}
                    <div id="container-upload" class="d-none mt-4 animate__animated animate__fadeIn">
                        <div class="card border-0 bg-light" style="border-left: 5px solid #40BF89 !important;">
                            <div class="card-body text-start">
                                <h6 class="fw-bold mb-2" style="color: #40BF89;">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> UNGGAH LAPORAN HARIAN
                                </h6>
                                <p class="small text-muted">Silakan upload laporan kegiatan hari ini (PDF).</p>
                                <div class="input-group">
                                    <input type="file" id="file-laporan" class="form-control form-control-sm"
                                        accept=".pdf">
                                    <button class="btn btn-sm text-white" style="background-color: #40BF89;" type="button"
                                        id="btn-upload-pdf">Kirim</button>
                                </div>
                                <div id="upload-status" class="small mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mt-3">
                        <i class="bi bi-info-circle"></i> Pastikan wajah terlihat jelas dan GPS aktif.
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
        const containerUpload = document.getElementById('container-upload');
        const uploadStatus = document.getElementById('upload-status');

        let streamActive = null;

        // 1. Fungsi Akses Kamera
        function startKamera() {
            if (streamActive) return;
            navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: "user",
                        width: {
                            ideal: 640
                        }, // Diubah ke 640
                        height: {
                            ideal: 480
                        } // Diubah ke 480
                    }
                })
                .then(stream => {
                    streamActive = stream;
                    video.srcObject = stream;
                })
                .catch(err => {
                    console.error("Gagal akses kamera: ", err);
                    alert("Aplikasi butuh izin kamera untuk verifikasi wajah.");
                });
        }

        // Jalankan kamera saat halaman dimuat
        startKamera();

        // 2. Handler Input NIP & Verifikasi Status Pegawai
        nipInput.addEventListener('input', function() {
            const nip = this.value;
            if (nip.length >= 4) {
                namaDisplay.innerText = "Mencari data...";
                namaDisplay.style.color = "#6c757d";

                fetch(`/get-pegawai/${nip}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            namaDisplay.innerText = "Nama: " + data.nama;
                            namaDisplay.style.color = "#40BF89";

                            // --- LOGIKA TOMBOL DINAMIS SIKAWA ---
                            if (data.status === 'masuk') {
                                btnCapture.innerHTML =
                                    '<i class="bi bi-camera-fill me-2"></i> Ambil Foto & Absen Masuk';
                                btnCapture.className = "btn btn-lg w-100 py-3 shadow-sm text-white";
                                btnCapture.style.backgroundColor = "#40BF89";
                                btnCapture.disabled = false;
                                containerUpload.classList.add('d-none');
                                uploadStatus.innerHTML = "";

                            } else if (data.status === 'pulang') {
                                // KONDISI 1: Sudah Upload Laporan DAN Sudah 8 Jam
                                if (data.laporan_ready && data.boleh_pulang) {
                                    btnCapture.innerHTML =
                                        '<i class="bi bi-box-arrow-right me-2"></i> Ambil Foto & Absen Pulang';
                                    btnCapture.className =
                                        "btn btn-lg w-100 py-3 shadow-sm btn-warning fw-bold text-dark";
                                    btnCapture.disabled = false;
                                    containerUpload.classList.add('d-none');
                                    uploadStatus.innerHTML = "";

                                }
                                // KONDISI 2: Laporan Belum Diupload
                                else if (!data.laporan_ready) {
                                    btnCapture.innerHTML =
                                        '<i class="bi bi-lock-fill me-2"></i> Upload Laporan Dahulu';
                                    btnCapture.className = "btn btn-lg w-100 py-3 shadow-sm btn-secondary";
                                    btnCapture.disabled = true;
                                    containerUpload.classList.remove('d-none');

                                    // Info jika belum 1 jam untuk upload
                                    uploadStatus.innerHTML = data.boleh_upload ? "" :
                                        `<span class="text-danger small fw-bold">${data.pesan_waktu}</span>`;

                                }
                                // KONDISI 3: Laporan Sudah Ada, Tapi Belum 8 Jam (Kunci Tombol & Tampilkan Jam)
                                else if (!data.boleh_pulang) {
                                    // Mengambil jam saja dari string "Absen pulang baru tersedia pukul 16:00"
                                    const jamTersedia = data.pesan_pulang.split('pukul ')[1];

                                    btnCapture.innerHTML =
                                        `<i class="bi bi-clock-history me-2"></i> BISA PULANG JAM ${jamTersedia}`;
                                    btnCapture.className = "btn btn-lg w-100 py-3 shadow-sm btn-danger fw-bold";
                                    btnCapture.disabled = true;
                                    containerUpload.classList.add('d-none');

                                    uploadStatus.innerHTML = `
                                    <div class="alert alert-info py-2 mt-3 small shadow-sm animate__animated animate__fadeIn">
                                        <i class="bi bi-info-circle-fill"></i>
                                        Sesuai aturan WFA, Anda baru dapat absen pulang setelah 8 jam kerja.
                                    </div>`;
                                }

                            } else {
                                // Status 'selesai'
                                btnCapture.innerHTML =
                                    '<i class="bi bi-check-circle-fill me-2"></i> Sudah Absen Hari Ini';
                                btnCapture.className = "btn btn-lg w-100 py-3 shadow-sm btn-secondary";
                                btnCapture.disabled = true;
                                containerUpload.classList.add('d-none');
                                uploadStatus.innerHTML = "";
                            }

                        } else {
                            namaDisplay.innerText = data.message;
                            namaDisplay.style.color = "red";
                            btnCapture.disabled = true;
                            containerUpload.classList.add('d-none');
                            uploadStatus.innerHTML = "";
                        }
                    })
                    .catch(err => {
                        console.error("Fetch error:", err);
                        namaDisplay.innerText = "Gagal terhubung ke server.";
                    });
            }
        });

        // 3. Handler Tombol Absen (Kamera + GPS)
        btnCapture.addEventListener('click', () => {
            const originalContent = btnCapture.innerHTML;
            btnCapture.innerText = "Mengunci Lokasi & Mengambil Foto...";
            btnCapture.disabled = true;

            const gpsOptions = {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition((position) => {
                // --- PROSES KOMPRESI & RESIZE ---
                const targetWidth = 640;
                const targetHeight = 480;

                canvas.width = targetWidth;
                canvas.height = targetHeight;

                const ctx = canvas.getContext('2d');

                // Menggambar video ke canvas dengan ukuran target (Resize)
                ctx.drawImage(video, 0, 0, targetWidth, targetHeight);

                // Kompresi kualitas ke 0.7 (70%) untuk memperkecil ukuran file base64
                const dataURI = canvas.toDataURL('image/jpeg', 0.7);

                fetch('{{ route('absen.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            image: dataURI,
                            nip: nipInput.value,
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert("Gagal: " + data.message);
                            btnCapture.innerHTML = originalContent;
                            btnCapture.disabled = false;
                        }
                    })
                    .catch(err => {
                        alert("Terjadi kesalahan koneksi ke server.");
                        btnCapture.innerHTML = originalContent;
                        btnCapture.disabled = false;
                    });

            }, (error) => {
                let msg = "Izin lokasi (GPS) wajib aktif!";
                if (error.code === 3) msg =
                    "Gagal mendapatkan lokasi (Timeout). Pastikan GPS Anda aktif dan akurat.";
                alert(msg);
                btnCapture.innerHTML = originalContent;
                btnCapture.disabled = false;
            }, gpsOptions);
        });

        // 4. Handler Upload Laporan PDF
        document.getElementById('btn-upload-pdf').addEventListener('click', function() {
            const fileInput = document.getElementById('file-laporan');
            const btnUpload = this;

            if (fileInput.files.length === 0) return alert("Pilih file PDF terlebih dahulu!");
            if (fileInput.files[0].size > 2 * 1024 * 1024) return alert("File PDF terlalu besar! Maksimal 2MB.");

            const formData = new FormData();
            formData.append('laporan_pdf', fileInput.files[0]);
            formData.append('nip', nipInput.value);

            btnUpload.disabled = true;
            uploadStatus.innerHTML =
                '<span class="text-primary animate__animated animate__pulse animate__infinite d-block">Sedang mengunggah...</span>';

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
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message);
                        btnUpload.disabled = false;
                        uploadStatus.innerText = "";
                    }
                })
                .catch(err => {
                    alert("Gagal mengunggah laporan.");
                    btnUpload.disabled = false;
                    uploadStatus.innerText = "";
                });
        });
    </script>
@endpush
