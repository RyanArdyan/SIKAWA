<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('pegawai.index');
    }

    public function showDetail($id)
    {
        // Mengambil data absen beserta data user-nya
        $attendance = Attendance::with('user')->findOrFail($id);

        // Mengarahkan ke file view detail yang kita buat sebelumnya
        return view('admin.absensi.show', compact('attendance'));
    }

    public function store(Request $request)
    {
        // 1. Tambahkan validasi latitude & longitude agar data tidak kosong
        $request->validate([
            'nip' => 'required',
            'image' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = User::where('nip', $request->nip)->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'User tidak ditemukan']);
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        try {
            // Proses Image Base64
            $image = $request->image;
            $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
            $suffix = $attendance ? 'OUT' : 'IN';
            $imageName = $user->nip.'_'.$suffix.'_'.time().'.jpeg';
            Storage::disk('public')->put('attendances/'.$imageName, base64_decode($image));

            if (! $attendance) {
                // --- LOGIKA ABSEN MASUK ---
                $jamMasukSetting = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';
                $statusAbsen = now()->format('H:i') > $jamMasukSetting ? 'terlambat' : 'hadir';

                Attendance::create([
                    'user_id' => $user->id,
                    'photo_path' => 'attendances/'.$imageName,
                    'check_in_time' => now(),
                    'latitude_in' => $request->latitude,   // Simpan Latitude Masuk
                    'longitude_in' => $request->longitude, // Simpan Longitude Masuk
                    'status' => $statusAbsen,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Absen Masuk Berhasil! Status: '.ucfirst($statusAbsen),
                ]);

            } else {
                // --- LOGIKA ABSEN PULANG ---
                // Kita update baris yang sama dengan data kepulangan
                $attendance->update([
                    'check_out_time' => now(),
                    'photo_path_out' => 'attendances/'.$imageName,
                    'latitude_out' => $request->latitude,   // Simpan Latitude Pulang
                    'longitude_out' => $request->longitude, // Simpan Longitude Pulang
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Absen Pulang Berhasil! Hati-hati di jalan.',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: '.$e->getMessage()]);
        }
    }

    public function getPegawai($nip)
    {
        $user = User::where('nip', $nip)->first();
        if (! $user) {
            return response()->json(['success' => false]);
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        $status = 'masuk';
        if ($attendance && $attendance->check_out_time) {
            $status = 'selesai';
        } elseif ($attendance) {
            $status = 'pulang';
        }

        return response()->json([
            'success' => true,
            'nama' => $user->name,
            'status' => $status,
        ]);
    }

    public function uploadLaporan(Request $request)
    {
        try {
            // 1. Validasi
            $request->validate([
                'nip' => 'required',
                'laporan_pdf' => 'required|mimes:pdf|max:2048',
            ]);

            // 2. Cari User
            $user = User::where('nip', $request->nip)->first();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan']);
            }

            // 3. Cari Data Absen Hari Ini
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->first();

            if ($attendance) {
                // Simpan file
                if ($request->hasFile('laporan_pdf')) {
                    $file = $request->file('laporan_pdf');
                    $filename = 'laporan_'.$user->nip.'_'.time().'.pdf';
                    $path = $file->storeAs('reports', $filename, 'public');

                    // Update Database
                    $attendance->update([
                        'laporan_pdf' => $path,
                    ]);

                    return response()->json(['success' => true, 'message' => 'Berhasil diunggah']);
                }
            }

            return response()->json(['success' => false, 'message' => 'Data absensi tidak ditemukan']);

        } catch (\Exception $e) {
            // Jika ada error kodingan, akan muncul di console log response
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function report()
    {
        $attendances = Attendance::with('user')->latest()->get();
        // Ambil jam masuk sekali saja di sini
        $jamMasuk = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';

        return view('admin.absensi.index', compact('attendances', 'jamMasuk'));
    }
}
