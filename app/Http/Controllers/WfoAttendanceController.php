<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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

    /**
     * Memeriksa status pegawai berdasarkan NIP secara real-time
     */
    public function getPegawai($nip)
    {
        $pegawai = User::where('nip', $nip)->first();

        if (! $pegawai) {
            return response()->json(['success' => false, 'message' => 'NIP tidak terdaftar']);
        }

        // 1. Cari data presensi yang BELUM PULANG (check_out_time is null)
        // Aman untuk SHIFT MALAM (misal masuk jam 23:00, pulang jam 07:00 besoknya)
        $attendance = Attendance::where('user_id', $pegawai->id)
            ->whereNull('check_out_time')
            ->latest('check_in_time')
            ->first();

        $status = 'masuk';
        $pesan_tambahan = '';
        $boleh_pulang = true;

        if ($attendance) {
            $status = 'pulang';

            // Cek apakah durasi kerja sudah mencapai minimal 8 jam
            if (! $attendance->isWorkDurationMet()) {
                $boleh_pulang = false;
                $jamPulang = $attendance->getMinCheckOutTime();
                $pesan_tambahan = "Anda sudah absen masuk pada jam {$attendance->check_in_time->format('H:i')}. Minimal jam pulang Anda adalah pukul {$jamPulang} WIB (8 jam kerja).";
            } else {
                $pesan_tambahan = 'Masa kerja 8 jam terpenuhi. Silakan kirim presensi untuk pulang.';
            }
        } else {
            // 2. Cek apakah ada presensi yang baru saja selesai (sudah check-out) dalam rentang 12 jam terakhir
            $sudahSelesaiBaruSaja = Attendance::where('user_id', $pegawai->id)
                ->whereNotNull('check_out_time')
                ->where('check_out_time', '>=', Carbon::now()->subHours(12))
                ->first();

            if ($sudahSelesaiBaruSaja) {
                $status = 'selesai';
                $pesan_tambahan = 'Anda telah menyelesaikan presensi hari/shift ini.';
            }
        }

        return response()->json([
            'success' => true,
            'nama' => $pegawai->name,
            'status' => $status,
            'boleh_pulang' => $boleh_pulang,
            'pesan_tambahan' => $pesan_tambahan,
        ]);
    }

    /**
     * Menyimpan data presensi WFO (Masuk atau Pulang) tanpa melacak lokasi
     */
    public function store(Request $request)
    {
        // Validasi Input Data (Hanya NIP dan foto)
        $request->validate([
            'nip' => 'required',
            'image' => 'required',
        ]);

        // Ambil Data Pegawai berdasarkan NIP
        $pegawai = User::where('nip', $request->nip)->first();
        $now = Carbon::now();

        if (! $pegawai) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan']);
        }

        // Olah File Swafoto Base64 & Simpan ke storage/app/public/attendances
        $image = $request->image;
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'wfo_' . $pegawai->nip . '_' . time() . '.jpg';
        Storage::disk('public')->put('attendances/' . $imageName, base64_decode($image));

        // Cek apakah pegawai memiliki sesi presensi aktif (masuk dan belum pulang)
        $attendance = Attendance::where('user_id', $pegawai->id)
            ->whereNull('check_out_time')
            ->latest('check_in_time')
            ->first();

        if (! $attendance) {
            // --- PROSES ABSEN MASUK ---
            Attendance::create([
                'user_id' => $pegawai->id,
                'location_id' => $pegawai->location_id ?? null, // Mengambil ID lokasi default dari profil user jika ada
                'check_in_time' => $now,
                'photo_path' => $imageName,
                'tipe_absen' => 'WFO',
                'status' => 'hadir', // Fleksibel untuk 3 shift
                'ip_address_log' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Presensi Masuk WFO. Selamat bekerja!',
            ]);

        } else {
            // --- PROSES ABSEN PULANG ---
            // Validasi: Wajib bekerja minimal 8 jam
            if (! $attendance->isWorkDurationMet()) {
                $jamPulang = $attendance->getMinCheckOutTime();
                return response()->json([
                    'success' => false,
                    'message' => "Belum memenuhi 8 jam kerja. Anda baru dapat melakukan presensi pulang pukul {$jamPulang} WIB.",
                ]);
            }

            // Simpan data absen pulang
            $attendance->update([
                'check_out_time' => $now,
                'photo_path_out' => $imageName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Presensi Pulang WFO. Terima kasih atas kerja keras Anda!',
            ]);
        }
    }
}
