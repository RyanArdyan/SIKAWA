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

// Grouping WFO
Route::prefix('wfo')->group(function () {
    Route::get('/', [WfoAttendanceController::class, 'index'])->name('absen.wfo');
    Route::get('/get-pegawai/{nip}', [WfoAttendanceController::class, 'getPegawai']);
    Route::post('/store', [WfoAttendanceController::class, 'store'])->name('absen.storeWfo');
});

// Route Pegawai yang sudah login
Route::middleware('auth')->group(function () {
    Route::get('/pegawai/biodata', [PegawaiController::class, 'editBiodata'])->name('pegawai.editBiodata');
    Route::put('/pegawai/biodata/update', [PegawaiController::class, 'updateBiodata'])->name('pegawai.updateBiodata');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Sisi Dashboard Backend - Terproteksi Ketat Berdasarkan Role
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Redirect otomatis jika akses root /admin
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // =========================================================================
    // Dashboard Utama (Bisa diakses oleh Semua Role Admin)
    // =========================================================================
    Route::middleware(['role:super_admin,admin,operator_laporan'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    });

    // =========================================================================
    // 1. KHUSUS SUPER ADMIN
    // Fitur: HANYA Mengelola / Menambahkan Admin Saja
    // =========================================================================
    Route::middleware(['role:super_admin'])->group(function () {
        Route::resource('manage-admins', AdminManagementController::class);
    });

    // =========================================================================
    // 2. KHUSUS OPERATOR LAPORAN & ADMIN
    // Fitur: Melihat Laporan & Export PDF (Read-Only)
    // =========================================================================
    Route::middleware(['role:admin,operator_laporan'])->group(function () {
        Route::get('/laporan', [AttendanceController::class, 'report'])->name('absensi.report');
        Route::get('/laporan/export-pdf', [AttendanceController::class, 'exportReportPdf'])->name('absensi.exportReportPdf');
        Route::get('/laporan/{id}/detail', [AttendanceController::class, 'showDetail'])->name('laporan.detail');
    });

    // =========================================================================
    // 3. KHUSUS ADMIN
    // Fitur: Mengelola Seluruh Data Operasional & Pengaturan
    // =========================================================================
    Route::middleware(['role:admin'])->group(function () {

        // Manipulasi Absensi Manual (Statis didahulukan dari Dinamis)
        Route::get('/laporan/create-manual', [AttendanceController::class, 'createManual'])->name('laporan.createManual');
        Route::post('/laporan/store-manual', [AttendanceController::class, 'storeManual'])->name('laporan.storeManual');
        Route::get('/laporan/{id}/lupa-absen', [AttendanceController::class, 'editLupaAbsen'])->name('laporan.editLupaAbsen');
        Route::put('/laporan/{id}/update-lupa-absen', [AttendanceController::class, 'updateLupaAbsen'])->name('laporan.updateLupaAbsen');
        Route::delete('/laporan/bulk-delete', [AttendanceController::class, 'bulkDelete'])->name('laporan.bulkDelete');

        // Hapus Massal & Update Status Absensi
        Route::get('/absensi/hapus-massal', [AttendanceController::class, 'showDeletePage'])->name('absensi.showDelete');
        Route::delete('/absensi/hapus-massal', [AttendanceController::class, 'processDelete'])->name('absensi.processDelete');
        Route::put('/absensi/update-status/{id}', [AttendanceController::class, 'updateStatus'])->name('absensi.updateStatus');

        // Master Data (CRUD Pegawai, Tim Kerja, Lokasi, Kantor)
        Route::resource('pegawai', PegawaiController::class);
        Route::put('/pegawai/{id}/reset-password', [PegawaiController::class, 'resetPassword'])->name('pegawai.resetPassword');
        Route::resource('tim-kerja', TimKerjaController::class);
        Route::resource('locations', LocationController::class);
        Route::resource('kantor', KantorController::class);

        // Pengaturan Sistem
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });

});
