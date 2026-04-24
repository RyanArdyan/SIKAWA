<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\TimKerja;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $laporanSudahAda = false; // Variabel baru untuk mengecek status PDF

        if ($attendance) {
            if ($attendance->check_out_time) {
                $status = 'selesai';
            } else {
                $status = 'pulang';
                // CEK APAKAH FILE PDF SUDAH ADA DI DATABASE
                // Sesuaikan nama kolom 'laporan_pdf' dengan kolom di database kamu
                if (! empty($attendance->laporan_pdf)) {
                    $laporanSudahAda = true;
                }
            }
        }

        return response()->json([
            'success' => true,
            'nama' => $user->name,
            'status' => $status,
            'laporan_ready' => $laporanSudahAda, // Kirim status ini ke frontend
        ]);
    }

    public function uploadLaporan(Request $request)
    {
        try {
            $request->validate([
                'nip' => 'required',
                'laporan_pdf' => 'required|mimes:pdf|max:2048',
            ]);

            $user = User::where('nip', $request->nip)->first();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan']);
            }

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->first();

            if ($attendance) {
                if ($request->hasFile('laporan_pdf')) {
                    $file = $request->file('laporan_pdf');
                    $filename = 'laporan_'.$user->nip.'_'.time().'.pdf';
                    // Gunakan storeAs agar tersimpan di storage/app/public/reports
                    $path = $file->storeAs('reports', $filename, 'public');

                    // Update database
                    $attendance->update([
                        'laporan_pdf' => $path, // Pastikan nama kolom di DB adalah laporan_pdf
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Laporan harian berhasil diunggah! Sekarang silakan klik tombol Absen Pulang.',
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'Data absensi hari ini tidak ditemukan. Silakan absen masuk terlebih dahulu.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: '.$e->getMessage()], 500);
        }
    }

    public function riwayat(Request $request)
    {
        $nip = $request->query('nip');
        $filter = $request->query('filter', 'today');
        $attendances = collect();
        $user = null;

        if ($nip) {
            // Cari user berdasarkan NIP
            $user = User::where('nip', $nip)->first();

            if ($user) {
                $query = Attendance::where('user_id', $user->id);

                // Logika Filter
                if ($filter == 'today') {
                    $query->whereDate('created_at', Carbon::today());
                } elseif ($filter == 'weekly') {
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } elseif ($filter == 'monthly') {
                    $query->whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year);
                }

                $attendances = $query->orderBy('created_at', 'desc')->get();
            }
        }

        // SESUAI SCREENSHOT: File ada di folder pegawai/riwayat.blade.php
        return view('pegawai.riwayat', compact('attendances', 'nip', 'filter', 'user'));
    }

    public function report(Request $request)
    {
        // Mengambil input filter dari URL
        $nip = $request->query('nip');
        $timKerjaId = $request->query('tim_kerja_id');
        $periode = $request->query('periode', 'today');

        // Query dengan Eager Loading agar tidak berat (N+1 Problem)
        $query = Attendance::with(['user.tim_kerja']);

        // Filter NIP atau Nama
        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                $q->where('nip', 'like', "%$nip%")
                    ->orWhere('name', 'like', "%$nip%");
            });
        }

        // Filter Tim Kerja
        if ($timKerjaId) {
            $query->whereHas('user', function ($q) use ($timKerjaId) {
                $q->where('tim_kerja_id', $timKerjaId);
            });
        }

        // Filter Periode Waktu
        if ($periode == 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($periode == 'weekly') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($periode == 'monthly') {
            $query->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        }

        $attendances = $query->latest()->get();

        // Data tambahan untuk dropdown dan informasi di View
        $timKerjas = TimKerja::all();
        $jamMasuk = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';

        return view('admin.absensi.index', compact(
            'attendances', 'timKerjas', 'jamMasuk', 'nip', 'timKerjaId', 'periode'
        ));
    }

    public function exportPdf(Request $request)
    {
        // AMBIL PARAMETER (Pastikan namanya 'filter' sesuai dengan di View)
        $nip = $request->query('nip');
        $filter = $request->query('filter', 'today');

        // 1. Cari Usernya dulu secara spesifik (agar tidak tertukar)
        $user = User::where('nip', $nip)->first();

        if (! $user) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        // 2. Buat Query Absensi khusus untuk user tersebut
        $query = Attendance::where('user_id', $user->id);

        // 3. Logika Filter Tanggal (Harus sama persis dengan fungsi riwayat)
        if ($filter == 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($filter == 'weekly') {
            $query->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ]);
        } elseif ($filter == 'monthly') {
            $query->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        }

        // 4. Ambil SEMUA data hasil filter (Gunakan get(), jangan first())
        $attendances = $query->orderBy('created_at', 'desc')->get();

        // 5. Kirim data ke View PDF
        $data = [
            'user' => $user,
            'attendances' => $attendances,
            'filter' => $filter,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
        ];

        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Riwayat_Absen_'.$nip.'.pdf');
    }

    // Tambahkan method ini di dalam class AttendanceController
    public function exportReportPdf(Request $request)
    {
        // 1. Ambil data filter dari URL
        $nip = $request->query('nip');
        $timKerjaId = $request->query('tim_kerja_id');
        $periode = $request->query('periode', 'today');

        // 2. Query data dengan relasi user dan tim_kerja
        $query = Attendance::with(['user.tim_kerja']);

        // Filter NIP atau Nama
        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                $q->where('nip', 'like', "%$nip%")
                    ->orWhere('name', 'like', "%$nip%");
            });
        }

        // Filter Tim Kerja
        if ($timKerjaId) {
            $query->whereHas('user', function ($q) use ($timKerjaId) {
                $q->where('tim_kerja_id', $timKerjaId);
            });
        }

        // Filter Periode Waktu
        if ($periode == 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($periode == 'weekly') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($periode == 'monthly') {
            $query->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        }

        // Ambil data (urutkan dari yang terlama ke terbaru untuk laporan)
        $attendances = $query->orderBy('created_at', 'asc')->get();

        // 3. Siapkan data untuk dikirim ke view PDF
        $data = [
            'attendances' => $attendances,
            'periode' => $periode,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
            'tim_filter' => $timKerjaId ? TimKerja::find($timKerjaId)->nama : 'Semua Tim',
        ];

        // 4. Generate PDF menggunakan view khusus admin
        // Pastikan kamu sudah membuat file: resources/views/admin/absensi/report_pdf.blade.php
        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape'); // Landscape agar tabel luas

        return $pdf->download('Laporan_Absensi_BKK_'.date('Ymd_His').'.pdf');
    }
}
