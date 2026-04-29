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
        $attendance = Attendance::with('user')->findOrFail($id);

        return view('admin.absensi.show', compact('attendance'));
    }

    public function store(Request $request)
    {
        // 1. IP dicatat hanya untuk log, tidak menentukan mode lagi
        $userIp = $request->ip();
        $tipeAbsen = 'WFA'; // Dipaksa selalu WFA (Kamera & GPS Wajib)

        // 2. Validasi (Selalu mewajibkan image dan koordinat)
        $request->validate([
            'nip' => 'required',
            'image' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = User::where('nip', $request->nip)->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan.']);
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        try {
            $imagePath = null;

            if ($request->has('image') && ! empty($request->image)) {
                $image = $request->image;
                $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
                $suffix = $attendance ? 'OUT' : 'IN';
                $imageName = $user->nip.'_'.$suffix.'_'.time().'.jpeg';

                Storage::disk('public')->put('attendances/'.$imageName, base64_decode($image));
                $imagePath = 'attendances/'.$imageName;
            }

            if (! $attendance) {
                // LOGIKA ABSEN MASUK
                $jamMasukSetting = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';
                $statusAbsen = now()->format('H:i') > $jamMasukSetting ? 'terlambat' : 'hadir';

                Attendance::create([
                    'user_id' => $user->id,
                    'photo_path' => $imagePath,
                    'check_in_time' => now(),
                    'latitude_in' => $request->latitude,
                    'longitude_in' => $request->longitude,
                    'status' => $statusAbsen,
                    'tipe_absen' => $tipeAbsen,
                    'ip_address_log' => $userIp,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Absen Masuk Berhasil! Status: '.ucfirst($statusAbsen),
                ]);

            } else {
                // LOGIKA ABSEN PULANG
                $attendance->update([
                    'check_out_time' => now(),
                    'photo_path_out' => $imagePath,
                    'latitude_out' => $request->latitude,
                    'longitude_out' => $request->longitude,
                    'ip_address_log' => $userIp,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Absen Pulang Berhasil! Hati-hati di jalan.',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sistem Error: '.$e->getMessage()]);
        }
    }

    public function getPegawai($nip, Request $request)
    {
        $user = User::where('nip', $nip)->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan']);
        }

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        $status = 'masuk';
        $laporanSudahAda = false;

        if ($attendance) {
            if ($attendance->check_out_time) {
                $status = 'selesai';
            } else {
                $status = 'pulang';
                // Karena murni WFA, cek kolom laporan_pdf secara eksplisit
                $laporanSudahAda = ! empty($attendance->laporan_pdf);
            }
        }

        return response()->json([
            'success' => true,
            'nama' => $user->name,
            'status' => $status,
            'laporan_ready' => $laporanSudahAda,
            'is_wfo' => false, // Selalu false karena fitur WFO dimatikan
        ]);
    }

    // Fungsi lain (uploadLaporan, riwayat, report, export) tetap sama...
    // [Silakan simpan fungsi sisanya dari kode lama Anda di bawah sini]

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
        // Default rentang adalah 1 bulan terakhir jika tidak diisi
        $start_date = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->query('end_date', Carbon::now()->toDateString());

        $attendances = collect();
        $user = null;

        if ($nip) {
            $user = User::where('nip', $nip)->first();

            if ($user) {
                $query = Attendance::where('user_id', $user->id);

                // Logika Filter Rentang Tanggal
                $query->whereBetween('created_at', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay(),
                ]);

                $attendances = $query->orderBy('created_at', 'desc')->get();
            }
        }

        return view('pegawai.riwayat', compact('attendances', 'nip', 'start_date', 'end_date', 'user'));
    }

    public function report(Request $request)
    {
        // Mengambil input filter dari URL
        $nip = $request->query('nip');
        $timKerjaId = $request->query('tim_kerja_id');

        // Default rentang: awal bulan ini sampai hari ini
        $start_date = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->query('end_date', Carbon::now()->toDateString());

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

        // Filter Rentang Tanggal
        $query->whereBetween('created_at', [
            Carbon::parse($start_date)->startOfDay(),
            Carbon::parse($end_date)->endOfDay(),
        ]);

        $attendances = $query->latest()->get();

        $timKerjas = TimKerja::all();
        $jamMasuk = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';

        return view('admin.absensi.index', compact(
            'attendances', 'timKerjas', 'jamMasuk', 'nip', 'timKerjaId', 'start_date', 'end_date'
        ));
    }

    public function exportPdf(Request $request)
    {
        $nip = $request->query('nip');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $user = User::where('nip', $nip)->first();

        if (! $user) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        $query = Attendance::where('user_id', $user->id);

        // Filter berdasarkan rentang tanggal yang sama dengan di riwayat
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay(),
            ]);
        }

        $attendances = $query->orderBy('created_at', 'asc')->get();

        $data = [
            'user' => $user,
            'attendances' => $attendances,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
        ];

        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Riwayat_Absen_'.$nip.'.pdf');
    }

    // Tambahkan method ini di dalam class AttendanceController
    public function exportReportPdf(Request $request)
    {
        $nip = $request->query('nip');
        $timKerjaId = $request->query('tim_kerja_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $query = Attendance::with(['user.tim_kerja']);

        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                $q->where('nip', 'like', "%$nip%")
                    ->orWhere('name', 'like', "%$nip%");
            });
        }

        if ($timKerjaId) {
            $query->whereHas('user', function ($q) use ($timKerjaId) {
                $q->where('tim_kerja_id', $timKerjaId);
            });
        }

        // Filter Rentang Tanggal
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay(),
            ]);
        }

        $attendances = $query->orderBy('created_at', 'asc')->get();

        $data = [
            'attendances' => $attendances,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
            'tim_filter' => $timKerjaId ? TimKerja::find($timKerjaId)->nama : 'Semua Tim',
        ];

        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Absensi_BKK_'.date('Ymd_His').'.pdf');
    }
}
