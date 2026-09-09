<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\TimKerjaController;
use App\Http\Controllers\WfoAttendanceController;
use App\Http\Controllers\KantorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autentikasi Manual
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Sisi Pegawai (Frontend Mobile / Akses Mandiri)
|--------------------------------------------------------------------------
*/

Route::get('/get-pegawai/{nip}', [AttendanceController::class, 'getPegawai'])->name('absen.getPegawai');

Route::get('/absen/success', function () {
    return view('pegawai.success');
})->name('absen.success');

Route::get('/', [AttendanceController::class, 'index'])->name('absen.home');
Route::get('/riwayat', [AttendanceController::class, 'riwayat'])->name('absen.riwayat');
Route::get('/riwayat/export', [AttendanceController::class, 'exportPdf'])->name('absen.exportPdf');
Route::post('/absen/store', [AttendanceController::class, 'store'])->name('absen.store');
Route::post('/absen/upload-laporan', [AttendanceController::class, 'uploadLaporan'])->name('absen.uploadLaporan');
Route::patch('/absen/update-status/{id}', [AttendanceController::class, 'updateStatus'])->name('absen.updateStatus');

// Grouping agar rapi
Route::prefix('wfo')->group(function () {
    Route::get('/', [WfoAttendanceController::class, 'index'])->name('absen.wfo');
    Route::get('/get-pegawai/{nip}', [WfoAttendanceController::class, 'getPegawai']);
    Route::post('/store', [WfoAttendanceController::class, 'store'])->name('absen.storeWfo');
});

// rute yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/pegawai/biodata', [PegawaiController::class, 'editBiodata'])->name('pegawai.editBiodata');
    Route::put('/pegawai/biodata/update', [PegawaiController::class, 'updateBiodata'])->name('pegawai.updateBiodata');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Sisi Admin (Backend Dashboard) - Terproteksi
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // --- AKSES ADMIN & SUPER ADMIN ---

    // Dashboard Utama
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Laporan & Detail
    Route::get('/laporan', [AttendanceController::class, 'report'])->name('absensi.report');
    Route::get('/laporan/export-pdf', [AttendanceController::class, 'exportReportPdf'])->name('absensi.exportReportPdf');
    Route::get('/laporan/{id}/detail', [AttendanceController::class, 'showDetail'])->name('laporan.detail');
    // Tambahkan ini di dalam grup middleware admin
    Route::get('/laporan/{id}/lupa-absen', [AttendanceController::class, 'editLupaAbsen'])->name('laporan.editLupaAbsen');
    Route::put('/laporan/{id}/update-lupa-absen', [AttendanceController::class, 'updateLupaAbsen'])->name('laporan.updateLupaAbsen');
    Route::get('/laporan/create-manual', [AttendanceController::class, 'createManual'])->name('laporan.createManual');
    Route::post('/laporan/store-manual', [AttendanceController::class, 'storeManual'])->name('laporan.storeManual');

    // CRUD Pegawai, Tim Kerja, & kantor
    Route::resource('pegawai', PegawaiController::class);
    // Route untuk Reset Password Pegawai oleh Admin
    Route::put('/pegawai/{id}/reset-password', [PegawaiController::class, 'resetPassword'])->name('pegawai.resetPassword');
    Route::resource('tim-kerja', TimKerjaController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('kantor', KantorController::class);

    // Pengaturan Sistem
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // --- KHUSUS AKSES SUPER ADMIN ---
    // Gunakan middleware 'superadmin' yang akan kita buat
    Route::middleware(['superadmin'])->group(function () {
        Route::resource('manage-admins', AdminManagementController::class);
    });

    // Redirect otomatis jika akses root /admin
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Route untuk menampilkan halaman hapus
    Route::get('/absensi/hapus-massal', [AttendanceController::class, 'showDeletePage'])->name('absensi.showDelete');

    // Route untuk memproses penghapusan
    Route::delete('/absensi/hapus-massal', [AttendanceController::class, 'processDelete'])->name('absensi.processDelete');

    // Pastikan baris ini ada di routes/web.php Anda
    Route::put('/absensi/update-status/{id}', [AttendanceController::class, 'updateStatus'])->name('absensi.updateStatus');
});
