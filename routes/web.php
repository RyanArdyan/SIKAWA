<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AuthController;

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
| Sisi Pegawai (Frontend Mobile)
|--------------------------------------------------------------------------
*/
Route::get('/', [AttendanceController::class, 'index'])->name('absen.home');
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
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Redirect otomatis jika akses /admin langsung
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/laporan', [AttendanceController::class, 'report'])->name('laporan');
    Route::get('/laporan/{id}/detail', [AttendanceController::class, 'showDetail'])->name('laporan.detail');

    // Resource route untuk CRUD Pegawai
    Route::resource('pegawai', PegawaiController::class);

    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
});
