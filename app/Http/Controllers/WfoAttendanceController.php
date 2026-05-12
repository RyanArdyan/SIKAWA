<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\Setting; // Import model Setting
use App\Models\User;
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

        $settingJamMasuk = Setting::where('key', 'jam_masuk')->value('value') ?? '07:30';

        $attendance = Attendance::where('user_id', $pegawai->id)
            ->whereDate('check_in_time', Carbon::today())
            ->first();

        $status = 'masuk';
        $pesan_tambahan = '';
        $boleh_pulang = true; // Default selalu true agar bisa pulang kapan saja

        if ($attendance) {
            if ($attendance->check_out_time) {
                $status = 'selesai';
            } else {
                $status = 'pulang';
                // BAGIAN INI DIHAPUS/DIKOSONGKAN agar tidak ada pengecekan 8 jam lagi
                $boleh_pulang = true;
                $pesan_tambahan = 'Anda sudah absen masuk. Silakan kirim presensi untuk pulang.';
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
        // 1. Ambil data pegawai berdasarkan NIP yang dikirim dari Frontend
        $pegawai = User::where('nip', $request->nip)->first();
        $today = Carbon::today();
        $now = Carbon::now();

        if (! $pegawai) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan']);
        }

        // 2. Ambil Koordinat dari Request (dikirim via JavaScript navigator.geolocation)
        $userLat = $request->latitude;
        $userLon = $request->longitude;

        if (! $userLat || ! $userLon) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan lokasi GPS. Harap aktifkan GPS dan berikan izin lokasi.',
            ]);
        }

        // 3. LOGIKA RADIUS (Haversine Formula)
        // Mencari apakah koordinat user masuk ke salah satu radius kantor di tabel locations
        $locations = Location::all();
        $currentLocation = null;

        foreach ($locations as $loc) {
            $earthRadius = 6371000; // Dalam meter

            $dLat = deg2rad($loc->latitude - $userLat);
            $dLon = deg2rad($loc->longitude - $userLon);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($userLat)) * cos(deg2rad($loc->latitude)) *
                 sin($dLon / 2) * sin($dLon / 2);

            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            // Jika jarak user <= radius kantor (misal 500m), maka lokasi ditemukan
            if ($distance <= $loc->radius) {
                $currentLocation = $loc;
                break;
            }
        }

        if (! $currentLocation) {
            return response()->json([
                'success' => false,
                'message' => 'Anda berada di luar radius kantor yang diizinkan.',
            ]);
        }

        // 4. CEK RIWAYAT ABSENSI HARI INI
        $attendance = Attendance::where('user_id', $pegawai->id)
            ->whereDate('check_in_time', $today)
            ->first();

        // Ambil jam masuk dari tabel settings (default 07:30)
        $settingJamMasuk = Setting::where('key', 'jam_masuk')->value('value') ?? '07:30';
        $batasTerlambat = Carbon::parse($settingJamMasuk)->addMinutes(30);

        if (! $attendance) {
            // --- LOGIKA ABSEN MASUK ---
            $isTerlambat = $now->gt($batasTerlambat);

            Attendance::create([
                'user_id' => $pegawai->id,
                'location_id' => $currentLocation->id, // Mengisi location_id dari hasil deteksi radius
                'check_in_time' => $now,
                'latitude_in' => $userLat,
                'longitude_in' => $userLon,
                'tipe_absen' => 'WFO',
                'status' => $isTerlambat ? 'terlambat' : 'hadir',
                'ip_address_log' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil Presensi Masuk di {$currentLocation->name}. Selamat bekerja!",
            ]);

        } else {
            // --- LOGIKA ABSEN PULANG ---
            // Cek jika sudah pernah absen pulang
            if ($attendance->check_out_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan presensi pulang hari ini.',
                ]);
            }

            // Update data pulang (menggunakan koordinat saat ini)
            $attendance->update([
                'check_out_time' => $now,
                'latitude_out' => $userLat,
                'longitude_out' => $userLon,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil Presensi Pulang dari {$currentLocation->name}. Hati-hati di jalan!",
            ]);
        }
    }
}
