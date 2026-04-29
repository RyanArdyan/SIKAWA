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
                                    <input type="file" id="file-laporan" class="form-control form-control-sm" accept=".pdf">
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

        let streamActive = null;

        function startKamera() {
            if (streamActive) return;
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                .then(stream => {
                    streamActive = stream;
                    video.srcObject = stream;
                })
                .catch(err => {
                    console.error("Gagal akses kamera: ", err);
                    alert("Aplikasi butuh akses kamera untuk berfungsi.");
                });
        }

        // Jalankan kamera langsung
        startKamera();

        nipInput.addEventListener('input', function() {
            const nip = this.value;
            if (nip.length >= 4) {
                fetch(`/get-pegawai/${nip}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            namaDisplay.innerText = "Nama: " + data.nama;
                            namaDisplay.style.color = "#40BF89";

                            // LOGIKA TOMBOL BERDASARKAN STATUS
                            if (data.status === 'masuk') {
                                btnCapture.innerHTML = '<i class="bi bi-camera-fill me-2"></i> Ambil Foto & Absen Masuk';
                                btnCapture.style.backgroundColor = "#40BF89";
                                btnCapture.disabled = false;
                                containerUpload.classList.add('d-none');
                            } else if (data.status === 'pulang') {
                                if (data.laporan_ready) {
                                    btnCapture.innerHTML = '<i class="bi bi-box-arrow-right me-2"></i> Ambil Foto & Absen Pulang';
                                    btnCapture.style.backgroundColor = "#ffc107";
                                    btnCapture.style.color = "#000";
                                    btnCapture.disabled = false;
                                    containerUpload.classList.add('d-none');
                                } else {
                                    btnCapture.innerHTML = '<i class="bi bi-lock-fill me-2"></i> Upload Laporan Dahulu';
                                    btnCapture.style.backgroundColor = "#6c757d";
                                    btnCapture.disabled = true;
                                    containerUpload.classList.remove('d-none');
                                }
                            } else {
                                btnCapture.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Sudah Absen Hari Ini';
                                btnCapture.disabled = true;
                                btnCapture.style.backgroundColor = "#6c757d";
                            }
                        } else {
                            namaDisplay.innerText = data.message;
                            namaDisplay.style.color = "red";
                            btnCapture.disabled = true;
                        }
                    });
            }
        });

        btnCapture.addEventListener('click', () => {
            const originalText = btnCapture.innerHTML;
            btnCapture.innerText = "Memproses...";
            btnCapture.disabled = true;

            // Wajib GPS & Foto
            navigator.geolocation.getCurrentPosition((position) => {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
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
                        btnCapture.innerHTML = originalText;
                        btnCapture.disabled = false;
                    }
                })
                .catch(err => {
                    alert("Terjadi kesalahan sistem.");
                    btnCapture.innerHTML = originalText;
                    btnCapture.disabled = false;
                });
            }, (error) => {
                alert("Izin lokasi (GPS) wajib aktif!");
                btnCapture.innerHTML = originalText;
                btnCapture.disabled = false;
            });
        });

        // Handler Upload Laporan
        document.getElementById('btn-upload-pdf').addEventListener('click', function() {
            const fileInput = document.getElementById('file-laporan');
            if (fileInput.files.length === 0) return alert("Pilih file PDF!");

            const formData = new FormData();
            formData.append('laporan_pdf', fileInput.files[0]);
            formData.append('nip', nipInput.value);

            this.disabled = true;
            document.getElementById('upload-status').innerText = "Mengunggah...";

            fetch('{{ route('absen.uploadLaporan') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload(); // Refresh untuk update status tombol
                } else {
                    alert(data.message);
                    this.disabled = false;
                }
            });
        });
    </script>
@endpush
