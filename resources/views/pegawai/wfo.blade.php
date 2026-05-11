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
                    <div class="mb-4 text-start">
                        <label for="nip" class="form-label fw-bold text-secondary">Nomor Induk Pegawai (NIP)</label>
                        <input type="text" id="nip" class="form-control form-control-lg border-2 shadow-none"
                            style="border-color: #e2e8f0;" placeholder="Contoh: 1992xxxx" autocomplete="off">
                        <div id="nama-pegawai" class="mt-2 fw-bold" style="min-height: 24px; color: #40BF89;"></div>
                    </div>

                    <button id="btn-absen-wfo" class="btn btn-lg w-100 py-3 shadow-sm text-white fw-bold border-0"
                        style="background-color: #40BF89; border-radius: 10px;" disabled>
                        <i class="bi bi-send-fill me-2"></i> Kirim Presensi
                    </button>

                    <p class="text-muted small mt-4">
                        <i class="bi bi-info-circle"></i> Pastikan Anda terhubung ke jaringan lokal BKK Pontianak dan mengaktifkan GPS.
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
                        info.innerHTML = '<small class="text-muted d-block mt-1"><i class="bi bi-info-circle"></i> Anda sudah absen masuk. Silakan klik tombol untuk absen pulang.</small>';
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

            nipInput.addEventListener('input', function() {
                const nip = this.value;
                if (nip.length >= 4) {
                    fetch(`/wfo/get-pegawai/${nip}`)
                        .then(res => res.json())
                        .then(data => updateButtonUI(data))
                        .catch(err => console.error("Error:", err));
                }
            });

            // --- PERBAIKAN LOGIKA SIMPAN (DENGAN GPS) ---
            btnAbsen.addEventListener('click', () => {
                const originalText = btnAbsen.innerHTML;
                btnAbsen.innerText = "Mendeteksi Lokasi...";
                btnAbsen.disabled = true;

                if (!navigator.geolocation) {
                    alert("Browser Anda tidak mendukung GPS.");
                    btnAbsen.innerHTML = originalText;
                    btnAbsen.disabled = false;
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lon = position.coords.longitude;

                        fetch('{{ route('absen.storeWfo') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                nip: nipInput.value,
                                latitude: lat,
                                longitude: lon
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
                            alert("Terjadi kesalahan sistem.");
                            btnAbsen.innerHTML = originalText;
                            btnAbsen.disabled = false;
                        });
                    },
                    (error) => {
                        let msg = "Gagal mendapatkan lokasi.";
                        if (error.code == 1) msg = "Izin GPS ditolak. Silakan aktifkan di pengaturan browser.";
                        alert(msg);
                        btnAbsen.innerHTML = originalText;
                        btnAbsen.disabled = false;
                    },
                    { enableHighAccuracy: true, timeout: 5000 }
                );
            });
        });
    </script>
@endsection
