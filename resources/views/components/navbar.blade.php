<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">ABSENSI BKK</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('absen.home') }}">Halaman Absen</a>
                </li>
                <li class="nav-item border-start ms-lg-2 ps-lg-3">
                    <a class="nav-link bg-dark rounded px-3" href="/admin/dashboard">Admin Panel</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
