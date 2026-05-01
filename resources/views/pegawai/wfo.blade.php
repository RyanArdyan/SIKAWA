@extends('layouts.app')

@section('title', 'Presensi Kantor - SIKAWA')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0 overflow-hidden" style="border-radius: 15px;">
                {{-- Header SIKAWA --}}
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

                    {{-- Tombol Utama --}}
                    <button id="btn-absen-wfo" class="btn btn-lg w-100 py-3 shadow-sm text-white fw-bold border-0"
                        style="background-color: #40BF89; border-radius: 10px;" disabled>
                        <i class="bi bi-send-fill me-2"></i> Kirim Presensi
                    </button>

                    <p class="text-muted small mt-4">
                        <i class="bi bi-info-circle"></i> Pastikan Anda terhubung ke jaringan lokal BKK Pontianak.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Masukkan script di dalam DOMContentLoaded untuk mencegah error 'null' --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nipInput = document.getElementById('nip');
            const namaDisplay = document.getElementById('nama-pegawai');
            const btnAbsen = document.getElementById('btn-absen-wfo');

            // 1. Update Tampilan Tombol dengan Logika 8 Jam & Toleransi
            function updateButtonUI(data) {
                if (data.success) {
                    // Tampilkan Nama
                    namaDisplay.innerText = "Nama: " + data.nama;
                    namaDisplay.style.color = "#40BF89";

                    if (data.status === 'masuk') {
                        btnAbsen.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Kirim Presensi Masuk';
                        btnAbsen.style.backgroundColor = "#40BF89";
                        btnAbsen.style.color = "#fff";
                        btnAbsen.disabled = false;
                    } else if (data.status === 'pulang') {
                        if (data.boleh_pulang) {
                            // Jika sudah kerja 8 jam
                            btnAbsen.innerHTML = '<i class="bi bi-box-arrow-right me-2"></i> Absen Pulang';
                            btnAbsen.style.backgroundColor = "#ffc107"; // Kuning peringatan
                            btnAbsen.style.color = "#000";
                            btnAbsen.disabled = false;

                            // Tambah info tambahan bahwa sudah cukup jam kerja
                            const info = document.createElement('div');
                            info.innerHTML =
                                '<small class="text-success d-block mt-1"><i class="bi bi-clock-history"></i> Sudah memenuhi durasi 8 jam kerja.</small>';
                            namaDisplay.appendChild(info);
                        } else {
                            // Jika BELUM 8 jam (Flexible Time)
                            btnAbsen.innerHTML = '<i class="bi bi-lock-fill me-2"></i> Belum Bisa Pulang';
                            btnAbsen.style.backgroundColor = "#e2e8f0"; // Abu-abu terang
                            btnAbsen.style.color = "#94a3b8";
                            btnAbsen.disabled = true;

                            // Tampilkan pesan kapan dia boleh pulang (dari Controller)
                            const peringatan = document.createElement('div');
                            peringatan.innerHTML =
                                `<small class="text-danger d-block mt-1"><i class="bi bi-exclamation-triangle"></i> ${data.pesan_tambahan}</small>`;
                            namaDisplay.appendChild(peringatan);
                        }
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
                    btnAbsen.innerHTML = '<i class="bi bi-send-fill me-2"></i> Kirim Presensi';
                    btnAbsen.style.backgroundColor = "#40BF89";
                }
            }

            // 2. Cek NIP Otomatis
            nipInput.addEventListener('input', function() {
                const nip = this.value;
                if (nip.length >= 4) {
                    fetch(`/wfo/get-pegawai/${nip}`)
                        .then(res => res.json())
                        .then(data => updateButtonUI(data))
                        .catch(err => {
                            console.error("Error:", err);
                            namaDisplay.innerText = "Gagal memuat data pegawai";
                        });
                } else {
                    namaDisplay.innerText = "";
                    btnAbsen.disabled = true;
                    btnAbsen.innerHTML = '<i class="bi bi-send-fill me-2"></i> Kirim Presensi';
                    btnAbsen.style.backgroundColor = "#40BF89";
                    btnAbsen.style.color = "#fff";
                }
            });

            // 3. Simpan Absen
            // 3. Simpan Absen
            btnAbsen.addEventListener('click', () => {
                const originalText = btnAbsen.innerHTML;
                btnAbsen.innerText = "Memproses...";
                btnAbsen.disabled = true;

                fetch('{{ route('absen.storeWfo') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            nip: nipInput.value
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // --- LOGIKA ALERT TERLAMBAT ---
                            if (data.is_terlambat) {
                                alert("Terlambat! Presensi masuk Anda tercatat melebihi batas waktu.");
                            } else {
                                alert(data.message);
                            }
                            // ------------------------------

                            nipInput.value = "";
                            namaDisplay.innerText = "";
                            btnAbsen.disabled = true;
                            btnAbsen.innerHTML = '<i class="bi bi-send-fill me-2"></i> Kirim Presensi';
                            btnAbsen.style.backgroundColor = "#40BF89";
                        } else {
                            alert(data.message);
                            btnAbsen.innerHTML = originalText;
                            btnAbsen.disabled = false;
                        }
                    })
                    .catch(err => {
                        alert("Terjadi kesalahan sistem.");
                        btnAbsen.innerHTML = originalText;
                        btnAbsen.disabled = false;
                    });
            });
        });
    </script>
@endsection
