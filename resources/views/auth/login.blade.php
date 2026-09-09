@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-4">
        <div class="card shadow border-0 mt-5">
            {{-- Header kartu dengan warna hijau SIKAWA --}}
            <div class="card-header text-white text-center py-3" style="background-color: #40BF89;">
                <h5 class="mb-0 fw-bold">LOGIN SIKAWA</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body-secondary">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Contoh: 140123456@bkk.go.id" required autofocus>
                    </div>

                    {{-- Bagian Password dengan Fitur Toggle --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-body-secondary">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password-field" class="form-control border-end-0" required placeholder="password123">
                            <span class="input-group-text bg-body border-start-0 cursor-pointer text-body-secondary" id="toggle-password-btn" style="cursor: pointer;">
                                <i class="bi bi-eye" id="toggle-password-icon"></i>
                            </span>
                        </div>
                    </div>

                    {{-- Tombol Masuk SIKAWA --}}
                    <button type="submit" class="btn text-white w-100 py-2 fw-bold shadow-sm" style="background-color: #40BF89; border: none;">
                        MASUK
                    </button>
                </form>

                @if ($errors->any())
                    <div class="alert alert-danger mt-3 small">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- PETUNJUK LUPA PASSWORD (ADAPTIF DARK/LIGHT MODE) --}}
                <div class="mt-4 p-3 rounded text-center small bg-body-tertiary border border-secondary-subtle">
                    <i class="bi bi-info-circle-fill text-body-secondary me-1"></i>
                    <span class="fw-semibold text-body">Lupa Password?</span>
                    <p class="text-body-secondary mb-0 mt-1" style="font-size: 0.825rem;">
                        Silakan hubungi <strong class="text-body">Admin / Pengelola Kepegawaian</strong> untuk melakukan reset password akun Anda.
                    </p>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Skrip JavaScript untuk mematikan/menghidupkan tipe password --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordField = document.getElementById('password-field');
        const togglePasswordBtn = document.getElementById('toggle-password-btn');
        const togglePasswordIcon = document.getElementById('toggle-password-icon');

        togglePasswordBtn.addEventListener('click', function () {
            // Cek tipe input saat ini
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                // Ubah ikon ke mata dicoret (sembunyikan)
                togglePasswordIcon.classList.remove('bi-eye');
                togglePasswordIcon.classList.add('bi-eye-slash');
            } else {
                passwordField.type = 'password';
                // Ubah ikon kembali ke mata biasa (lihat)
                togglePasswordIcon.classList.remove('bi-eye-slash');
                togglePasswordIcon.classList.add('bi-eye');
            }
        });
    });
</script>
@endsection
