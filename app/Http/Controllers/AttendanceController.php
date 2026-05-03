<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Pegawai;
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
        // 1. IP dicatat hanya untuk log
        $userIp = $request->ip();
        $tipeAbsen = 'WFA'; // Dipaksa selalu WFA sesuai konteks proyek Anda

        // 2. Validasi input wajib (Image dan Koordinat GPS)
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

        // Cek apakah sudah ada data absensi hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        try {
            $imagePath = null;

            // Proses konversi Base64 Kamera ke File Image
            if ($request->has('image') && ! empty($request->image)) {
                $image = $request->image;
                $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
                $suffix = $attendance ? 'OUT' : 'IN';
                $imageName = $user->nip.'_'.$suffix.'_'.time().'.jpeg';

                Storage::disk('public')->put('attendances/'.$imageName, base64_decode($image));
                $imagePath = 'attendances/'.$imageName;
            }

            if (! $attendance) {
                // --- LOGIKA ABSEN MASUK ---
                $jamMasukSetting = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';
                $waktuMasukSesuaiJadwal = Carbon::createFromFormat('H:i', $jamMasukSetting);

                // Toleransi 30 menit
                $batasToleransi = $waktuMasukSesuaiJadwal->copy()->addMinutes(30);
                $statusAbsen = now()->gt($batasToleransi) ? 'terlambat' : 'hadir';

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
                // --- LOGIKA ABSEN PULANG (DENGAN PROTEKSI 8 JAM) ---

                // 1. Verifikasi Laporan PDF sudah diunggah
                if (empty($attendance->laporan_pdf)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal! Anda wajib mengunggah laporan harian terlebih dahulu.',
                    ]);
                }

                // 2. Verifikasi Durasi Kerja Minimal 8 Jam
                $waktuMasuk = Carbon::parse($attendance->check_in_time);
                $waktuMinimalPulang = $waktuMasuk->copy()->addHours(8);

                if (now()->lt($waktuMinimalPulang)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Belum waktunya pulang. Anda baru bisa absen pulang pada pukul '.$waktuMinimalPulang->format('H:i'),
                    ]);
                }

                // 3. Update data absen pulang
                $attendance->update([
                    'check_out_time' => now(),
                    'photo_path_out' => $imagePath,
                    'latitude_out' => $request->latitude,
                    'longitude_out' => $request->longitude,
                    'ip_address_log' => $userIp,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Absen Pulang Berhasil! Selamat beristirahat.',
                ]);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Sistem Error: '.$e->getMessage()]);
        }
    }

    public function getPegawai($nip, Request $request)
    {
        // 1. Cari data pegawai berdasarkan NIP
        $user = User::where('nip', $nip)->first();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Pegawai tidak ditemukan']);
        }

        // 2. Ambil data absensi hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        // Inisialisasi status default
        $status = 'masuk';
        $laporanSudahAda = false;
        $bolehUpload = true;
        $bolehPulang = true;
        $pesanWaktu = '';
        $pesanPulang = '';

        if ($attendance) {
            if ($attendance->check_out_time) {
                // Pegawai sudah menyelesaikan absen masuk dan pulang
                $status = 'selesai';
            } else {
                // Pegawai sudah masuk, tapi belum pulang
                $status = 'pulang';

                // Cek apakah kolom laporan_pdf di database sudah terisi
                $laporanSudahAda = ! empty($attendance->laporan_pdf);

                // Gunakan Carbon untuk manipulasi waktu
                $waktuMasuk = Carbon::parse($attendance->check_in_time);

                // --- LOGIKA DURASI 1 JAM (Syarat Upload Laporan) ---
                $waktuMinimalUpload = $waktuMasuk->copy()->addHour();
                if (now()->lt($waktuMinimalUpload)) {
                    $bolehUpload = false;
                    $pesanWaktu = 'Laporan harian baru dapat diunggah pukul '.$waktuMinimalUpload->format('H:i');
                }

                // --- LOGIKA DURASI 8 JAM (Syarat Absen Pulang) ---
                $waktuMinimalPulang = $waktuMasuk->copy()->addHours(8);
                if (now()->lt($waktuMinimalPulang)) {
                    $bolehPulang = false;
                    $pesanPulang = 'Absen pulang baru tersedia pukul '.$waktuMinimalPulang->format('H:i');
                }
            }
        }

        // 3. Return semua data ke JavaScript (Frontend)
        return response()->json([
            'success' => true,
            'nama' => $user->name,
            'status' => $status,
            'laporan_ready' => $laporanSudahAda,
            'boleh_upload' => $bolehUpload,
            'boleh_pulang' => $bolehPulang,   // Data krusial untuk kunci tombol pulang
            'pesan_waktu' => $pesanWaktu,     // Info ke pegawai soal jam upload
            'pesan_pulang' => $pesanPulang,   // Info ke pegawai soal jam pulang
            'is_wfo' => false,
        ]);
    }

    public function uploadLaporan(Request $request)
    {
        try {
            // 1. Validasi Input
            $request->validate([
                'nip' => 'required',
                'laporan_pdf' => 'required|mimes:pdf|max:2048',
            ]);

            // 2. Cari User berdasarkan NIP
            $user = User::where('nip', $request->nip)->first();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'User tidak ditemukan']);
            }

            // 3. Cari data absensi hari ini
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->first();

            if ($attendance) {
                // --- VALIDASI SISI SERVER (Proteksi 1 Jam) ---
                $waktuMasuk = Carbon::parse($attendance->check_in_time);
                $waktuMinimalUpload = $waktuMasuk->addHour();

                if (now()->lt($waktuMinimalUpload)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maaf, laporan baru dapat diunggah pukul '.$waktuMinimalUpload->format('H:i'),
                    ], 403);
                }

                // --- LOGIKA PEMBERSIHAN STORAGE ---
                // Jika sebelumnya sudah pernah upload, hapus file lamanya agar storage tidak penuh
                if ($attendance->laporan_pdf && Storage::disk('public')->exists($attendance->laporan_pdf)) {
                    Storage::disk('public')->delete($attendance->laporan_pdf);
                }

                // 4. Proses Upload File Baru
                if ($request->hasFile('laporan_pdf')) {
                    $file = $request->file('laporan_pdf');
                    $filename = 'laporan_'.$user->nip.'_'.time().'.pdf';

                    // Simpan ke storage/app/public/reports
                    $path = $file->storeAs('reports', $filename, 'public');

                    // 5. Update Path di Database
                    $attendance->update([
                        'laporan_pdf' => $path,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Laporan harian berhasil diunggah! Sekarang silakan klik tombol Absen Pulang.',
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'Data absensi hari ini tidak ditemukan. Silakan absen masuk terlebih dahulu.']);

        } catch (\Exception $e) {
            // Log error jika diperlukan untuk debugging
            return response()->json(['success' => false, 'message' => 'Gagal: '.$e->getMessage()], 500);
        }
    }

    public function riwayat(Request $request)
    {
        // 1. Ambil data dari query string (URL)
        $nip = $request->query('nip');
        $tipe_absen = $request->query('tipe_absen'); // Parameter filter baru (WFA/WFO)

        // 2. Tentukan default rentang tanggal jika tidak diisi (Default: Awal bulan ini sampai hari ini)
        $start_date = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->query('end_date', Carbon::now()->toDateString());

        // Inisialisasi variabel default agar view tidak error saat pertama kali dimuat
        $attendances = collect();
        $user = null;

        // 3. Proses pencarian jika NIP diinput oleh user
        if ($nip) {
            $user = User::where('nip', $nip)->first();

            if ($user) {
                // Mulai query dari model Attendance
                $query = Attendance::where('user_id', $user->id);

                // --- LOGIKA FILTER TIPE ABSEN ---
                // Jika user memilih 'WFA' atau 'WFO', tambahkan ke query.
                // Jika memilih 'semua', maka abaikan filter ini agar semua data muncul.
                if ($tipe_absen && $tipe_absen !== 'semua') {
                    $query->where('tipe_absen', $tipe_absen);
                }

                // --- LOGIKA FILTER RENTANG TANGGAL ---
                $query->whereBetween('created_at', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay(),
                ]);

                // Urutkan berdasarkan data terbaru
                $attendances = $query->orderBy('created_at', 'desc')->get();
            }
        }

        // 4. Return ke view 'pegawai.riwayat' dengan menyertakan semua variabel filter
        // Variabel ini dikirim balik agar dropdown dan input tanggal tetap terisi (terpilih)
        return view('pegawai.riwayat', compact(
            'attendances',
            'nip',
            'start_date',
            'end_date',
            'user',
            'tipe_absen'
        ));
    }

    public function report(Request $request)
    {
        // 1. Ambil semua parameter filter dari URL (Query String)
        $nip = $request->query('nip');
        $timKerjaId = $request->query('tim_kerja_id');

        // Menggunakan camelCase agar konsisten dengan variabel di Blade
        $tipeAbsen = $request->query('tipe_absen');

        // 2. Set default rentang tanggal: Awal bulan ini sampai hari ini
        $start_date = $request->query('start_date', \Illuminate\Support\Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->query('end_date', \Illuminate\Support\Carbon::now()->toDateString());

        // 3. Inisialisasi Query dengan Eager Loading
        $query = Attendance::with(['user.tim_kerja']);

        // 4. Filter NIP atau Nama Pegawai (Perbaikan Bug Exact Match NIP)
        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                if (is_numeric($nip)) {
                    // Jika input angka, cari NIP yang sama persis (Exact Match)
                    // atau nama yang mengandung angka tersebut (Wildcard)
                    $q->where('nip', $nip)
                        ->orWhere('name', 'like', "%{$nip}%");
                } else {
                    // Jika input teks, cari nama yang mirip
                    $q->where('name', 'like', "%{$nip}%");
                }
            });
        }

        // 5. Filter Tim Kerja
        if ($timKerjaId && $timKerjaId !== 'semua') {
            $query->whereHas('user', function ($q) use ($timKerjaId) {
                $q->where('tim_kerja_id', $timKerjaId);
            });
        }

        // 6. Filter Tipe Absen (WFA/WFO)
        if ($tipeAbsen && $tipeAbsen !== 'semua') {
            $query->where('tipe_absen', $tipeAbsen);
        }

        // 7. Filter Rentang Tanggal menggunakan created_at
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [
                \Illuminate\Support\Carbon::parse($start_date)->startOfDay(),
                \Illuminate\Support\Carbon::parse($end_date)->endOfDay(),
            ]);
        }

        // 8. Ambil data terbaru
        $attendances = $query->latest()->get();

        // 9. Ambil data pendukung untuk dropdown
        $timKerjas = TimKerja::all();

        // Ambil jam masuk dari tabel settings (default 08:00)
        $jamMasuk = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';

        // 10. Kirim semua variabel ke view
        return view('admin.absensi.index', compact(
            'attendances',
            'timKerjas',
            'jamMasuk',
            'nip',
            'timKerjaId',
            'start_date',
            'end_date',
            'tipeAbsen'
        ));
    }

    public function exportPdf(Request $request)
    {
        // 1. Ambil parameter dari request
        $nip = $request->query('nip');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        $tipe_absen = $request->query('tipe_absen');

        // 2. Cari user berdasarkan NIP dengan Eager Loading tim_kerja
        // Ini memastikan nama tim muncul di header laporan individu
        $user = User::with('tim_kerja')->where('nip', $nip)->first();

        // Validasi jika user tidak ditemukan
        if (! $user) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        // 3. Mulai Query Attendance milik user tersebut
        $query = Attendance::where('user_id', $user->id);

        // 4. Logika Filter Tipe Absen (WFA/WFO)
        if ($tipe_absen && $tipe_absen !== 'semua') {
            $query->where('tipe_absen', $tipe_absen);
        }

        // 5. Filter berdasarkan rentang tanggal
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay(),
            ]);
        }

        // 6. Ambil data dengan urutan tanggal menaik (asc)
        $attendances = $query->orderBy('created_at', 'asc')->get();

        // 7. Siapkan data untuk dikirim ke View PDF
        // Nama variabel disesuaikan agar tidak error saat dipanggil di report_pdf.blade.php
        $data = [
            'user' => $user, // Mengirim objek user lengkap (Nama & NIP akan otomatis terisi)
            'attendances' => $attendances,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
            'tim_filter' => $user->tim_kerja->nama ?? '-', // Diambil dari relasi user ke tim_kerja
            'tipe_filter' => $tipe_absen ?? 'Semua', // Menyesuaikan variabel $tipe_filter di view
        ];

        // 8. Generate PDF menggunakan view yang sudah dirapikan sebelumnya
        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        // 9. Download file dengan penamaan yang informatif
        return $pdf->download('Riwayat_Absen_'.$nip.'_'.date('Ymd_His').'.pdf');
    }

    // Tambahkan method ini di dalam class AttendanceController
    public function exportReportPdf(Request $request)
    {
        // 1. Ambil semua parameter filter dari request
        $nip = $request->query('nip');
        $timKerjaId = $request->query('tim_kerja_id');
        $start_raw = $request->query('start_date');
        $end_raw = $request->query('end_date');
        $tipe_absen = $request->query('tipe_absen');

        // 2. Normalisasi Format Tanggal
        $start_date = null;
        $end_date = null;

        if ($start_raw && $end_raw) {
            try {
                $start_date = Carbon::parse($start_raw)->format('Y-m-d');
                $end_date = Carbon::parse($end_raw)->format('Y-m-d');
            } catch (\Exception $e) {
                $start_date = Carbon::createFromFormat('d/m/Y', $start_raw)->format('Y-m-d');
                $end_date = Carbon::createFromFormat('d/m/Y', $end_raw)->format('Y-m-d');
            }
        }

        // 3. Ambil data user secara spesifik untuk header PDF (Perbaikan Exact Match)
        $userSelected = null;
        if ($nip) {
            $userSelected = User::where(function ($q) use ($nip) {
                if (is_numeric($nip)) {
                    $q->where('nip', $nip)
                        ->orWhere('name', 'like', "%$nip%");
                } else {
                    $q->where('name', 'like', "%$nip%");
                }
            })->first();
        }

        // 4. Mulai Query dengan Eager Loading
        $query = Attendance::with(['user.tim_kerja']);

        // 5. Filter NIP atau Nama (Perbaikan Exact Match agar tidak duplikat NIP mirip)
        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                if (is_numeric($nip)) {
                    $q->where('nip', $nip)
                        ->orWhere('name', 'like', "%$nip%");
                } else {
                    $q->where('name', 'like', "%$nip%");
                }
            });
        }

        // 6. Filter berdasarkan Tim Kerja
        if ($timKerjaId && $timKerjaId !== 'semua') {
            $query->whereHas('user', function ($q) use ($timKerjaId) {
                $q->where('tim_kerja_id', $timKerjaId);
            });
        }

        // 7. Filter berdasarkan Tipe Absen
        if ($tipe_absen && $tipe_absen !== 'semua') {
            $query->where('tipe_absen', $tipe_absen);
        }

        // 8. Filter berdasarkan Rentang Tanggal
        if ($start_date && $end_date) {
            $query->whereDate('check_in_time', '>=', $start_date)
                ->whereDate('check_in_time', '<=', $end_date);
        }

        // 9. Ambil data hasil filter
        $attendances = $query->orderBy('check_in_time', 'asc')->get();

        // 10. Siapkan data untuk dikirim ke View PDF
        $data = [
            'attendances' => $attendances,
            'user' => $userSelected,
            'start_date' => $start_date ? Carbon::parse($start_date)->format('d/m/Y') : null,
            'end_date' => $end_date ? Carbon::parse($end_date)->format('d/m/Y') : null,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
            'tim_filter' => $timKerjaId ? TimKerja::find($timKerjaId)->nama : 'Semua Tim',
            'tipe_filter' => $tipe_absen ?? 'Semua',
        ];

        // 11. Generate PDF
        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        // 12. Download
        return $pdf->download('Laporan_Absensi_BKK_'.date('Ymd_His').'.pdf');
    }

    public function batchDelete(Request $request)
    {
        // Gunakan filter yang sama dengan fungsi laporan untuk memastikan data yang dihapus akurat
        $query = Attendance::query();

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('check_in_time', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        if ($request->nip) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('nip', 'like', '%'.$request->nip.'%')
                    ->orWhere('name', 'like', '%'.$request->nip.'%');
            });
        }

        if ($request->tim_kerja_id) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('tim_kerja_id', $request->tim_kerja_id);
            });
        }

        if ($request->tipe_absen) {
            $query->where('tipe_absen', $request->tipe_absen);
        }

        $attendances = $query->get();

        if ($attendances->isEmpty()) {
            return back()->with('error', 'Tidak ada data yang sesuai untuk dihapus.');
        }

        $count = 0;
        foreach ($attendances as $attendance) {
            // Hapus file fisik dari storage agar tidak membebani server
            if ($attendance->photo_path) {
                Storage::disk('public')->delete($attendance->photo_path);
            }
            if ($attendance->photo_path_out) {
                Storage::disk('public')->delete($attendance->photo_path_out);
            }
            if ($attendance->laporan_pdf) {
                Storage::disk('public')->delete($attendance->laporan_pdf);
            }

            $attendance->delete();
            $count++;
        }

        return back()->with('success', "Berhasil menghapus $count data beserta file lampirannya.");
    }
}
