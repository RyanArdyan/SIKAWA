<nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #40BF89;">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <div class="d-flex align-items-center bg-white rounded-2 p-1 me-2" style="gap: 5px;">
                <img src="{{ asset('logo/kemenkes.png') }}" alt="Kemenkes" height="30">
                <img src="{{ asset('logo/bkk.png') }}" alt="BKK Pontianak" height="30">
            </div>
            <div class="ms-1 d-flex flex-column" style="line-height: 1.2;">
                <span class="fw-bold fs-5 tracking-wider">SIKAWA</span>
                <small style="font-size: 0.6rem; color: rgba(255,255,255,0.8);">BKK KELAS I PONTIANAK</small>
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/') ? 'active fw-bold' : '' }}" href="/">Halaman Absen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('riwayat*') ? 'active fw-bold' : '' }}" href="/riwayat">Riwayat Absen</a>
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
    .navbar-brand img {
        object-fit: contain;
    }
    /* Efek garis bawah pada menu aktif */
    .nav-link.active {
        border-bottom: 2px solid white;
    }
</style>
