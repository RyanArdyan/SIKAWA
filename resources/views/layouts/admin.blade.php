<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel - BKK Absensi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background-color: #f4f7f6;
        }

        .sidebar {
            min-height: 100vh;
            background: #212529;
            color: white;
            padding-top: 20px;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: #343a40;
            color: white;
        }

        .sidebar a.active {
            background: #0d6efd;
            color: white;
            border-radius: 5px;
            margin: 0 10px;
        }

        .content-area {
            padding: 30px;
        }
    </style>
    @stack('styles')
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky">
                    <h5 class="text-center mb-4 fw-bold text-white">BKK ADMIN</h5>
                    <ul class="nav flex-column">
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
                        <li class="nav-item mt-4">
                            <hr class="bg-secondary">
                            <a class="nav-link text-danger" href="{{ route('absen.home') }}">
                                <i class="bi bi-box-arrow-left me-2"></i> Keluar ke Absen
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-area">
                <div
                    class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('header', 'Admin Dashboard')</h1>
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
