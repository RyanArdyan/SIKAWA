<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TimKerjaController;
use App\Http\Controllers\LocationController;

/*
|--------------------------------------------------------------------------
| Autentikasi Manual
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Sisi Pegawai (Frontend Mobile / Akses Mandiri)
|--------------------------------------------------------------------------
*/
Route::get('/', [AttendanceController::class, 'index'])->name('absen.home');
Route::get('/riwayat', [AttendanceController::class, 'riwayat'])->name('absen.riwayat');

// Export PDF untuk Pegawai Mandiri (Tampilan Sederhana)
Route::get('/riwayat/export', [AttendanceController::class, 'exportPdf'])->name('absen.exportPdf');

Route::get('/get-pegawai/{nip}', [AttendanceController::class, 'getPegawai'])->name('absen.getPegawai');
Route::post('/absen/store', [AttendanceController::class, 'store'])->name('absen.store');

Route::get('/absen/success', function () {
    return view('pegawai.success');
})->name('absen.success');

Route::post('/absen/upload-laporan', [AttendanceController::class, 'uploadLaporan'])->name('absen.uploadLaporan');

/*
|--------------------------------------------------------------------------
| Sisi Admin (Backend Dashboard) - Terproteksi
|--------------------------------------------------------------------------
| Prefix 'admin' artinya semua URL akan diawali /admin/...
| Name 'admin.' artinya semua route name akan diawali admin.nama_route
*/
/*
|--------------------------------------------------------------------------
| Sisi Admin (Backend Dashboard) - Terproteksi
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard Utama
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Mengubah URL agar menjadi /admin/laporan sesuai menu backend kamu
    Route::get('/laporan', [AttendanceController::class, 'report'])->name('absensi.report');

    // URL untuk cetak PDF laporan admin
    Route::get('/laporan/export-pdf', [AttendanceController::class, 'exportReportPdf'])->name('absensi.exportPdf');

    // Detail Absensi (Melihat Foto & Lokasi Map)
    Route::get('/laporan/{id}/detail', [AttendanceController::class, 'showDetail'])->name('laporan.detail');

    // CRUD Pegawai & Tim Kerja
    Route::resource('pegawai', PegawaiController::class);
    Route::resource('tim-kerja', TimKerjaController::class);

    // TAMBAHKAN INI: CRUD Lokasi & IP Kantor
    Route::resource('locations', LocationController::class);

    // Pengaturan Sistem
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // Redirect otomatis jika akses root /admin
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });
});
