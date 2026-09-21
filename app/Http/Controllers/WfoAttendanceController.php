<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // 1. Validasi Input Data
        $request->validate([
            'nip' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'image' => 'required',
        ]);

        // 2. Ambil Data Pegawai Berdasarkan NIP
        $pegawai = User::where('nip', $request->nip)->first();
        $today = Carbon::today();
        $now = Carbon::now();

        if (! $pegawai) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan']);
        }

        // 3. Ambil Koordinat GPS
        $userLat = $request->latitude;
        $userLon = $request->longitude;

        if (! $userLat || ! $userLon) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan lokasi GPS. Harap aktifkan GPS dan berikan izin lokasi.',
            ]);
        }

        // 4. Hitung Radius Lokasi Terdekat (Haversine Formula) - Batas Radius 3000m
        $locations = Location::all();
        $currentLocation = null;
        $radiusMaksimal = 3000;

        $distanceInfo = 0;
        $terdekat = null;

        foreach ($locations as $loc) {
            $earthRadius = 6371000; // Satuan Meter

            $officeLat = (float) $loc->latitude;
            $officeLon = (float) $loc->longitude;
            $currentUserLat = (float) $userLat;
            $currentUserLon = (float) $userLon;

            $dLat = deg2rad($officeLat - $currentUserLat);
            $dLon = deg2rad($officeLon - $currentUserLon);

            $a = sin($dLat / 2) * sin($dLat / 2) +
                 cos(deg2rad($currentUserLat)) * cos(deg2rad($officeLat)) *
                 sin($dLon / 2) * sin($dLon / 2);

            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            if (is_null($terdekat) || $distance < $distanceInfo) {
                $terdekat = $loc;
                $distanceInfo = $distance;
            }

            if ($distance <= $radiusMaksimal) {
                $currentLocation = $loc;
                break;
            }
        }

        if (! $currentLocation) {
            $jarakBulat = round($distanceInfo);

            return response()->json([
                'success' => false,
                'message' => "Di luar radius! Jarak Anda ke {$terdekat->name} adalah {$jarakBulat}m (Maksimal yang diizinkan: {$radiusMaksimal}m).",
            ]);
        }

        // 5. Olah File Swafoto Base64 & Simpan ke Folder attendances
        $image = $request->image;
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);

        $imageName = 'wfo_' . $pegawai->nip . '_' . time() . '.jpg';

        // Simpan ke storage/app/public/attendances
        Storage::disk('public')->put('attendances/' . $imageName, base64_decode($image));

        // 6. Cek Riwayat Presensi Hari Ini
        $attendance = Attendance::where('user_id', $pegawai->id)
            ->whereDate('check_in_time', $today)
            ->first();

        $settingJamMasuk = Setting::where('key', 'jam_masuk')->value('value') ?? '07:30';
        $batasTerlambat = Carbon::parse($settingJamMasuk)->addMinutes(30);

        if (! $attendance) {
            // --- ABSEN MASUK ---
            $isTerlambat = $now->gt($batasTerlambat);

            Attendance::create([
                'user_id' => $pegawai->id,
                'location_id' => $currentLocation->id,
                'check_in_time' => $now,
                'latitude_in' => $userLat,
                'longitude_in' => $userLon,
                'photo_path' => $imageName, // Disesuaikan dengan nama kolom tabel
                'tipe_absen' => 'WFO',
                'status' => $isTerlambat ? 'terlambat' : 'hadir',
                'ip_address_log' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil Presensi Masuk di {$currentLocation->name}. Selamat bekerja!",
            ]);

        } else {
            // --- ABSEN PULANG ---
            if ($attendance->check_out_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan presensi pulang hari ini.',
                ]);
            }

            $attendance->update([
                'check_out_time' => $now,
                'latitude_out' => $userLat,
                'longitude_out' => $userLon,
                'photo_path_out' => $imageName, // Disesuaikan dengan nama kolom tabel
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil Presensi Pulang dari {$currentLocation->name}. Hati-hati di jalan!",
            ]);
        }
    }
}
