<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - SIKAWA BKK Pontianak</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar SIKAWA Custom Styling */
        .sidebar {
            min-height: 100vh;
            background: #40BF89; /* Identitas Hijau SIKAWA */
            color: white;
            padding-top: 0;
            box-shadow: 4px 0 10px rgba(0,0,0,0.05);
            z-index: 1000;
        }

        /* Container Logo & Brand */
        .sidebar-brand-container {
            padding: 30px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.03); /* Sedikit aksen gelap di area logo */
        }

        .logo-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .logo-img {
            height: 50px; /* Ukuran proporsional untuk 2 logo */
            width: auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.15));
            transition: transform 0.3s ease;
        }

        .logo-img:hover {
            transform: scale(1.1);
        }

        .sidebar h5 {
            color: white;
            letter-spacing: 3px;
            font-size: 1.5rem;
            font-weight: 800 !important;
            margin-bottom: 5px !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .agency-name {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 1px;
            font-weight: 600;
            text-transform: uppercase;
            display: block;
        }

        /* Navigation Links */
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 14px 25px;
            display: block;
            transition: all 0.3s;
            font-weight: 500;
            border-left: 4px solid transparent;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding-left: 30px;
        }

        /* Menu Aktif */
        .sidebar .nav-link.active {
            background: #212529; /* Warna gelap agar kontras dengan hijau */
            color: white;
            border-left: 4px solid #f1c40f; /* Aksen kuning pada menu aktif */
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            margin: 0 12px;
            border-radius: 6px;
        }

        .sidebar .nav-link.text-danger {
            color: #ffcccc !important;
            margin-top: 20px;
        }

        .sidebar .nav-link.text-danger:hover {
            background: #dc3545;
            color: white !important;
        }

        /* Content Area Area */
        .content-area {
            padding: 40px;
        }

        .main-header {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 0;
        }

        hr.sidebar-divider {
            background-color: rgba(255, 255, 255, 0.2);
            height: 1px;
            border: none;
            margin: 20px 25px;
        }
    </style>
    @stack('styles')
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0">
                <div class="position-sticky">

                    <div class="sidebar-brand-container">
                        <div class="logo-wrapper">
                            {{-- Pastikan file logo-kemenkes.png dan logo-bkk.png ada di folder public/img/ --}}
                            <img src="{{ asset('logo/kemenkes.png') }}" alt="Kemenkes" class="logo-img">
                            <img src="{{ asset('logo/bkk.png') }}" alt="BKK Pontianak" class="logo-img">
                        </div>
                        <h5 class="fw-bold">SIKAWA</h5>
                        <span class="agency-name">BKK Kelas I Pontianak</span>
                    </div>

                    <ul class="nav flex-column mt-2">
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}"
                                href="/admin/dashboard">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/tim-kerja*') ? 'active' : '' }}"
                                href="/admin/tim-kerja">
                                <i class="bi bi-briefcase me-2"></i> Tim Kerja
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/pegawai*') ? 'active' : '' }}"
                                href="/admin/pegawai">
                                <i class="bi bi-people me-2"></i> Data Pegawai
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/laporan*') ? 'active' : '' }}"
                                href="/admin/laporan">
                                <i class="bi bi-file-earmark-text me-2"></i> Laporan Absensi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
                                href="{{ route('admin.settings') }}">
                                <i class="bi bi-gear me-2"></i> Pengaturan
                            </a>
                        </li>

                        <hr class="sidebar-divider">

                        <li class="nav-item">
                            <a class="nav-link text-danger" href="{{ route('absen.home') }}">
                                <i class="bi bi-box-arrow-left me-2"></i> Keluar ke Absen
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <h1 class="h2 main-header">@yield('header', 'Admin Dashboard')</h1>
                    <div class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i> {{ date('d F Y') }}
                    </div>
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
