<!DOCTYPE html>
{{-- Tambahkan id="main-html" dan data-bs-theme default --}}
<html lang="id" id="main-html" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aplikasi Absensi BKK')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        /* Menggunakan variabel CSS agar fleksibel antara light/dark */
        body {
            background-color: var(--bs-body-bg);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .main-content {
            padding-top: 20px;
            padding-bottom: 50px;
        }
    </style>

    {{-- Script pencegah 'flicker' (layar putih sekejap saat refresh) --}}
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    @stack('styles')
</head>
<body>

    @include('components.navbar')

    <div class="container main-content">
        @yield('content')
    </div>

    @include('components.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleTheme() {
            const htmlTag = document.getElementById('main-html');
            const currentTheme = htmlTag.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            htmlTag.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme); // Simpan pilihan di browser

            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (icon) {
                icon.className = theme === 'light' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
            }
        }

        // Jalankan saat halaman selesai dimuat
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            updateThemeIcon(savedTheme);
        });
    </script>

    @stack('scripts')
</body>
</html>
