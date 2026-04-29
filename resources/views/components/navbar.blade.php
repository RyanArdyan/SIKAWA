<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #40BF89;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            {{-- Logo tanpa background putih --}}
            <img src="{{ asset('logo/kemenkes_bkk.png') }}" alt="BKK Pontianak"
                 class="me-3"
                 style="height: 50px; width: auto; filter: brightness(0) invert(1);">

            {{-- Teks Brand --}}
            <div class="d-flex flex-column justify-content-center" style="line-height: 1.1; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 12px;">
                <span class="fw-bold fs-5 tracking-wider">SIKAWA</span>
                <small style="font-size: 0.65rem; color: rgba(255,255,255,0.9); font-weight: 500;">BKK KELAS I PONTIANAK</small>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/') ? 'active fw-bold' : '' }}" href="/">Presensi WFA</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('absen-kantor*') ? 'active fw-bold' : '' }}" href="/absen-kantor">Presensi Kantor (WFO)</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('riwayat*') ? 'active fw-bold' : '' }}" href="/riwayat">Riwayat Absen</a>
                </li>

                <li class="nav-item ms-lg-2">
                    <button class="btn btn-link nav-link shadow-none" onclick="toggleTheme()" type="button" title="Ganti Mode Tampilan">
                        <i id="theme-icon" class="bi bi-moon-stars-fill text-white"></i>
                    </button>
                </li>

                <li class="nav-item ms-lg-3">
                    <a class="btn btn-dark btn-sm px-3 rounded-pill shadow-sm" href="/admin/dashboard">
                        <i class="bi bi-lock-fill me-1"></i> Admin Panel
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .tracking-wider {
        letter-spacing: 1px;
    }

    /* Efek garis bawah pada menu aktif */
    .nav-link.active {
        border-bottom: 2px solid white;
    }

    .btn-link:focus, .btn-link:active {
        box-shadow: none !important;
        text-decoration: none;
    }

    /* Memastikan transisi warna saat ganti tema tetap halus */
    .navbar {
        transition: all 0.3s ease;
    }
</style>
