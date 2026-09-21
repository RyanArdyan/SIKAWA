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
     * Memeriksa keberadaan NIP secara real-time
     */
    public function getPegawai($nip)
    {
        $pegawai = User::where('nip', $nip)->first();

        if (! $pegawai) {
            return response()->json([
                'success' => false,
                'message' => 'NIP tidak terdaftar'
            ]);
        }

        return response()->json([
            'success' => true,
            'nama' => $pegawai->name,
        ]);
    }

    /**
     * Menyimpan/Memperbarui data presensi WFO (Masuk atau Keluar)
     */
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'nip' => 'required',
            'image' => 'required',
            'tipe' => 'required|in:masuk,keluar',
        ]);

        $pegawai = User::where('nip', $request->nip)->first();
        $now = Carbon::now();

        if (! $pegawai) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan'
            ]);
        }

        // Processing & Simpan Swafoto
        $image = $request->image;
        $image = str_replace('data:image/jpeg;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = 'wfo_' . $pegawai->nip . '_' . time() . '.jpg';
        Storage::disk('public')->put('attendances/' . $imageName, base64_decode($image));

        // Cari record presensi hari ini
        $attendance = Attendance::where('user_id', $pegawai->id)
            ->whereDate('check_in_time', Carbon::today())
            ->first();

        // ----------------------------------------------------
        // 1. LOGIKA PRESENSI MASUK
        // ----------------------------------------------------
        if ($request->tipe === 'masuk') {
            if (! $attendance) {
                // Presensi Masuk Pertama Kali
                Attendance::create([
                    'user_id' => $pegawai->id,
                    'location_id' => $pegawai->location_id ?? null,
                    'check_in_time' => $now,
                    'photo_path' => $imageName,
                    'tipe_absen' => 'WFO',
                    'status' => 'hadir',
                    'ip_address_log' => $request->ip(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Berhasil Presensi Masuk WFO pada pukul ' . $now->format('H:i') . ' WIB.',
                ]);
            }

            // Jika sudah ada presensi masuk hari ini, gunakan jam paling awal (abaikan penekanan tombol berikutnya)
            $jamAwal = Carbon::parse($attendance->check_in_time)->format('H:i');
            return response()->json([
                'success' => true,
                'message' => "Anda sudah mencatat presensi masuk hari ini pada pukul {$jamAwal} WIB. Jam masuk awal tetap digunakan.",
            ]);
        }

        // ----------------------------------------------------
        // 2. LOGIKA PRESENSI KELUAR / PULANG
        // ----------------------------------------------------
        if ($request->tipe === 'keluar') {
            if (! $attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal! Anda belum melakukan presensi masuk hari ini.',
                ]);
            }

            // Menimpa jam keluar & foto keluar dengan waktu/foto yang paling akhir
            $attendance->update([
                'check_out_time' => $now,
                'photo_path_out' => $imageName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Presensi Keluar WFO pada pukul ' . $now->format('H:i') . ' WIB.',
            ]);
        }
    }
}
