<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Hitung total pegawai
        $totalPegawai = User::count();

        // Hitung yang sudah absen hari ini
        $absenHariIni = Attendance::whereDate('check_in_time', Carbon::today())->count();

        return view('admin.dashboard', compact('totalPegawai', 'absenHariIni'));
    }

    public function settings()
    {
        // Mengambil data dengan nilai default jika data di database kosong
        $jamMasuk = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';
        $jamPulang = Setting::where('key', 'jam_pulang')->first()->value ?? '16:00';

        return view('admin.settings', compact('jamMasuk', 'jamPulang'));
    }

    public function updateSettings(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'jam_masuk' => 'required',
            'jam_pulang' => 'required',
        ]);

        // 2. Update Jam Masuk
        Setting::updateOrCreate(
            ['key' => 'jam_masuk'],
            ['value' => $request->jam_masuk]
        );

        // 3. Update Jam Pulang
        Setting::updateOrCreate(
            ['key' => 'jam_pulang'],
            ['value' => $request->jam_pulang]
        );

        return back()->with('success', 'Pengaturan jam kerja berhasil diperbarui!');
    }
}
