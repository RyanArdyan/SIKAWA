<!DOCTYPE html>
<html lang="id" id="main-html" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - SIKAWA BKK Pontianak</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --sikawa-green: #40BF89;
            --sikawa-dark: #212529;
        }

        body {
            background-color: var(--bs-body-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease;
        }

        /* Sidebar Styling */
        .sidebar {
            min-height: 100vh;
            background: var(--sikawa-green);
            color: white;
            padding-top: 0;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            transition: background 0.3s ease;
        }

        /* Mode Gelap untuk Sidebar agar tidak terlalu silau */
        [data-bs-theme="dark"] .sidebar {
            background: #1a8a5f;
        }

        .sidebar-brand-container {
            padding: 20px 15px;
            /* Sedikit dikurangi agar lebih compact */
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.05);
        }

        .logo-img {
            width: 100%;
            /* Mengikuti lebar container */
            max-width: 180px;
            /* Batas maksimal lebar agar tidak terlalu besar */
            height: auto;
            /* Menjaga rasio foto agar tidak gepeng */
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
            object-fit: contain;
        }

        /* Perbaikan Nav Link & Tombol Keluar */
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 14px 25px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
        }

        .sidebar .nav-link.active {
            background: var(--sikawa-dark);
            color: white;
            border-left: 4px solid #f1c40f;
            margin: 0 12px;
            border-radius: 6px;
        }

        /* PERBAIKAN KONTRAS: Tombol Keluar ke Absen */
        .sidebar .nav-link.text-danger-custom {
            background: rgba(0, 0, 0, 0.2);
            /* Memberi base gelap agar teks terbaca */
            color: #fff !important;
            margin: 20px 12px 0 12px;
            border-radius: 6px;
            font-weight: 600;
        }

        .sidebar .nav-link.text-danger-custom:hover {
            background: #dc3545;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .content-area {
            padding: 40px;
        }

        /* Floating Toggle Theme Button */
        .theme-toggle-admin {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--sikawa-dark);
            color: white;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>

    @stack('styles')

    <script>
        // Mencegah flicker warna putih saat reload
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
</head>

<body>
    {{-- Tombol Ganti Mode --}}
    <div class="theme-toggle-admin" onclick="toggleTheme()" title="Ganti Mode Tampilan">
        <i id="theme-icon" class="bi bi-moon-stars-fill"></i>
    </div>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0">
                <div class="position-sticky">
                    <div class="sidebar-brand-container">
                        <div class="logo-wrapper d-flex justify-content-center mb-3">
                            <img src="{{ asset('logo/kemenkes_bkk.png') }}" alt="Logo Kemenkes BKK Pontianak"
                                class="logo-img">
                        </div>
                        <h5 class="fw-bold m-0">SIKAWA</h5>
                        <span class="agency-name" style="font-size: 0.85rem; opacity: 0.9;">BKK Kelas I Pontianak</span>
                    </div>

                    <ul class="nav flex-column mt-2">

                        {{-- ========================================== --}}
                        {{-- MENU KHUSUS ADMIN & SUPER ADMIN            --}}
                        {{-- ========================================== --}}
                        @if (auth()->user()->role !== 'pegawai')
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

                            {{-- MENU KHUSUS SUPER ADMIN --}}
                            @if (auth()->user()->isSuperAdmin())
                                <li class="nav-item">
                                    <a class="nav-link {{ Request::is('admin/manage-admins*') ? 'active' : '' }}"
                                        href="/admin/manage-admins">
                                        <i class="bi bi-shield-lock me-2"></i> Kelola Admin
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('admin/absensi/hapus-massal*') ? 'active' : '' }}"
                                    href="/admin/absensi/hapus-massal">
                                    <i class="bi bi-trash3 me-2"></i> Hapus Absensi Massal
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('admin/locations*') ? 'active' : '' }}"
                                    href="/admin/locations">
                                    <i class="bi bi-geo-alt me-2"></i> Lokasi atau Wilayah Kerja
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
                        @endif

                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('pegawai/biodata*') ? 'active' : '' }}"
                                href="/pegawai/biodata">
                                <i class="bi bi-person-check me-2"></i> Ubah Biodata
                            </a>
                        </li>

                        <li class="nav-item mt-2">
                            <a class="nav-link opacity-75 small" href="{{ route('absen.home') }}" target="_blank">
                                <i class="bi bi-eye me-2"></i> Lihat Halaman Absen
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-danger-custom" href="#"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-power me-2"></i> Logout (Keluar)
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-area">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
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
    <script>
        function toggleTheme() {
            const htmlTag = document.getElementById('main-html');
            const currentTheme = htmlTag.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            htmlTag.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (icon) {
                icon.className = theme === 'light' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            updateThemeIcon(savedTheme);
        });
    </script>
    @stack('scripts')
</body>

</html>
