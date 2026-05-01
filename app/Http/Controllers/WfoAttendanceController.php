<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User; // Import model Setting
use Carbon\Carbon;
use Illuminate\Http\Request;

class WfoAttendanceController extends Controller
{
    public function index()
    {
        return view('pegawai.wfo');
    }

    public function getPegawai($nip)
    {
        $pegawai = User::where('nip', $nip)->first();

        if (! $pegawai) {
            return response()->json(['success' => false, 'message' => 'NIP tidak terdaftar']);
        }

        // Ambil Jam Masuk dari Database Settings
        $settingJamMasuk = Setting::where('key', 'jam_masuk')->value('value') ?? '07:30';

        $attendance = Attendance::where('user_id', $pegawai->id)
            ->whereDate('check_in_time', Carbon::today())
            ->first();

        $status = 'masuk';
        $pesan_tambahan = '';
        $boleh_pulang = true;

        if ($attendance) {
            if ($attendance->check_out_time) {
                $status = 'selesai';
            } else {
                $status = 'pulang';

                // LOGIKA 8 JAM: Hitung kapan dia boleh pulang
                $jamMasukPegawai = Carbon::parse($attendance->check_in_time);
                $waktuMinimalPulang = $jamMasukPegawai->addHours(8);

                if (Carbon::now()->lt($waktuMinimalPulang)) {
                    $boleh_pulang = false;
                    $pesan_tambahan = 'Anda baru bisa absen pulang pada pukul '.$waktuMinimalPulang->format('H:i');
                }
            }
        }

        return response()->json([
            'success' => true,
            'nama' => $pegawai->name,
            'status' => $status,
            'boleh_pulang' => $boleh_pulang,
            'pesan_tambahan' => $pesan_tambahan,
            'jam_masuk_setting' => $settingJamMasuk,
        ]);
    }

    public function store(Request $request)
    {
        $pegawai = User::where('nip', $request->nip)->first();
        $today = Carbon::today();
        $now = Carbon::now(); // Simpan di variabel agar waktu pengecekan konsisten

        if (! $pegawai) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan']);
        }

        $attendance = Attendance::where('user_id', $pegawai->id)
            ->whereDate('check_in_time', $today)
            ->first();

        // Ambil Setting Jam Masuk
        $settingJamMasuk = Setting::where('key', 'jam_masuk')->value('value') ?? '07:30';
        $batasTerlambat = Carbon::parse($settingJamMasuk)->addMinutes(30);

        if (! $attendance) {
            // Tentukan status keterlambatan
            $isTerlambat = $now->gt($batasTerlambat);

            // LOGIKA ABSEN MASUK
            Attendance::create([
                'user_id' => $pegawai->id,
                'check_in_time' => $now,
                'tipe_absen' => 'WFO',
                'status' => $isTerlambat ? 'terlambat' : 'hadir',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Presensi Masuk. Selamat bekerja!',
                'is_terlambat' => $isTerlambat, // Kirim flag ini ke Frontend
            ]);
        } else {
            // LOGIKA ABSEN PULANG
            $waktuMinimalPulang = Carbon::parse($attendance->check_in_time)->addHours(8);

            if ($now->lt($waktuMinimalPulang)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum cukup 8 jam kerja. Anda baru bisa pulang pukul '.$waktuMinimalPulang->format('H:i'),
                ]);
            }

            $attendance->update([
                'check_out_time' => $now,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Presensi Pulang. Hati-hati di jalan!',
                'is_terlambat' => false, // Pulang tidak dihitung terlambat
            ]);
        }
    }
}
