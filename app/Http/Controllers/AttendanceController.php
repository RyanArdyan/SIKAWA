<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Location;
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
                // --- LOGIKA ABSEN PULANG ---

                // 1. Verifikasi Laporan PDF sudah diunggah
                if (empty($attendance->laporan_pdf)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal! Anda wajib mengunggah laporan harian terlebih dahulu.',
                    ]);
                }

                // --- LOGIKA DURASI 8 JAM TELAH DIHAPUS ---
                // Validasi waktu minimal pulang ($waktuMinimalPulang) dan response error 403/kondisi lt()
                // telah dihilangkan agar proses update check-out langsung dieksekusi.

                // 2. Update data absen pulang
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
        $bolehPulang = true; // Selalu true agar bisa langsung pulang setelah upload laporan
        $pesanWaktu = '';
        $pesanPulang = '';   // Kosong karena fitur "Bisa Pulang JAM xx:xx" sudah dihapus

        if ($attendance) {
            if ($attendance->check_out_time) {
                // Pegawai sudah menyelesaikan absen masuk dan pulang
                $status = 'selesai';
            } else {
                // Pegawai sudah masuk, tapi belum pulang
                $status = 'pulang';

                // Cek apakah kolom laporan_pdf di database sudah terisi
                $laporanSudahAda = ! empty($attendance->laporan_pdf);

                // --- LOGIKA DURASI 1 JAM & 8 JAM TELAH DIHAPUS ---
                // Tidak ada lagi pengecekan addHours(8) atau now()->lt() di sini.
                // Pegawai bisa langsung pulang kapan saja begitu laporan_pdf terisi.
            }
        }

        // 3. Return semua data ke JavaScript (Frontend)
        return response()->json([
            'success' => true,
            'nama' => $user->name,
            'status' => $status,
            'laporan_ready' => $laporanSudahAda,
            'boleh_upload' => $bolehUpload,
            'boleh_pulang' => $bolehPulang,   // Bernilai true
            'pesan_waktu' => $pesanWaktu,
            'pesan_pulang' => $pesanPulang,   // Bernilai string kosong
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
                // --- VALIDASI SISI SERVER (Proteksi 1 Jam) HARUS DIHAPUS ---
                // Bagian pengecekan waktu check_in_time dan throw error 403 telah dihilangkan
                // agar pegawai bisa langsung upload kapan saja setelah absen masuk.

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
        // 1. Ambil semua parameter filter dari URL
        $nip = $request->query('nip');

        // GANTI: Ambil sebagai array (bisa multiple ID)
        $timKerjaIds = $request->query('tim_kerja_ids', []);
        // Jika masih ada sisa link lama / single input:
        if ($request->filled('tim_kerja_id') && empty($timKerjaIds)) {
            $timKerjaIds = [$request->query('tim_kerja_id')];
        }

        $tipeAbsen = $request->query('tipe_absen');
        $locationId = $request->query('location_id');

        // 2. Set default rentang tanggal
        $start_date = $request->query('start_date', \Illuminate\Support\Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->query('end_date', \Illuminate\Support\Carbon::now()->toDateString());

        // 3. Inisialisasi Query dengan Eager Loading
        $query = Attendance::with(['user.tim_kerja', 'location']);

        // 4. Filter NIP atau Nama Pegawai
        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                if (is_numeric($nip)) {
                    $q->where('nip', $nip)->orWhere('name', 'like', "%{$nip}%");
                } else {
                    $q->where('name', 'like', "%{$nip}%");
                }
            });
        }

        // 5. PERBAIKAN: Filter Multiple Tim Kerja (Gunakan whereIn)
        if (! empty($timKerjaIds) && ! in_array('semua', $timKerjaIds)) {
            $query->whereHas('user', function ($q) use ($timKerjaIds) {
                $q->whereIn('tim_kerja_id', $timKerjaIds);
            });
        }

        // 6. Filter Tipe Absen (WFA/WFO)
        if ($tipeAbsen && $tipeAbsen !== 'semua') {
            $query->where('tipe_absen', $tipeAbsen);
        }

        // 7. Filter Lokasi Kantor
        if ($locationId && $locationId !== 'semua') {
            $query->where('location_id', $locationId);
        }

        // 8. Filter Rentang Tanggal
        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [
                \Illuminate\Support\Carbon::parse($start_date)->startOfDay(),
                \Illuminate\Support\Carbon::parse($end_date)->endOfDay(),
            ]);
        }

        // 9. Ambil data
        $attendances = $query->latest()->get();

        // 10. Ambil data pendukung
        $timKerjas = TimKerja::all();
        $locations = Location::all();
        $jamMasuk = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';

        // 11. Kirim variabel ke view
        return view('admin.absensi.index', compact(
            'attendances',
            'timKerjas',
            'locations',
            'jamMasuk',
            'nip',
            'timKerjaIds', // Kirim sebagai array timKerjaIds
            'start_date',
            'end_date',
            'tipeAbsen',
            'locationId'
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
            'lokasi_filter' => 'Semua Lokasi',
        ];

        // 8. Generate PDF menggunakan view yang sudah dirapikan sebelumnya
        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        // 9. Download file dengan penamaan yang informatif
        return $pdf->download('Riwayat_Absen_'.$nip.'_'.date('Ymd_His').'.pdf');
    }

    public function exportReportPdf(Request $request)
    {
        // 1. Ambil semua parameter filter dari request
        $nip = $request->query('nip');

        // PERBAIKAN: Ambil tim_kerja_ids sebagai array
        $timKerjaIds = $request->query('tim_kerja_ids', []);

        // Backward compatibility jika ada request tunggal 'tim_kerja_id'
        if ($request->filled('tim_kerja_id') && empty($timKerjaIds)) {
            $timKerjaIds = [$request->query('tim_kerja_id')];
        }

        $start_raw = $request->query('start_date');
        $end_raw = $request->query('end_date');
        $print_raw = $request->query('print_date');
        $tipe_absen = $request->query('tipe_absen');
        $locationId = $request->query('location_id');

        // 2. Normalisasi Format Tanggal Rentang Absensi
        $start_date = null;
        $end_date = null;

        if ($start_raw && $end_raw) {
            try {
                // Standar input date HTML5 (Y-m-d)
                $start_date = Carbon::parse($start_raw)->format('Y-m-d');
                $end_date = Carbon::parse($end_raw)->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback jika format datang dalam d/m/Y
                $start_date = Carbon::createFromFormat('d/m/Y', $start_raw)->format('Y-m-d');
                $end_date = Carbon::createFromFormat('d/m/Y', $end_raw)->format('Y-m-d');
            }
        }

        // Normalisasi Format Tanggal Cetak
        $tanggal_cetak = Carbon::now()->translatedFormat('d F Y'); // Default
        if ($print_raw) {
            try {
                $tanggal_cetak = Carbon::parse($print_raw)->translatedFormat('d F Y');
            } catch (\Exception $e) {
                try {
                    $tanggal_cetak = Carbon::createFromFormat('d/m/Y', $print_raw)->translatedFormat('d F Y');
                } catch (\Exception $ex) {
                    // Biarkan tetap default jika gagal parse
                }
            }
        }

        // 3. Ambil data user secara spesifik untuk header PDF (jika filter pegawai diisi)
        $userSelected = null;
        if ($nip) {
            $userSelected = User::where(function ($q) use ($nip) {
                if (is_numeric($nip)) {
                    $q->where('nip', $nip)->orWhere('name', 'like', "%$nip%");
                } else {
                    $q->where('name', 'like', "%$nip%");
                }
            })->first();
        }

        // 4. Mulai Query dengan Eager Loading
        $query = Attendance::with(['user.tim_kerja', 'location']);

        // 5. Filter NIP atau Nama
        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                if (is_numeric($nip)) {
                    $q->where('nip', $nip)->orWhere('name', 'like', "%$nip%");
                } else {
                    $q->where('name', 'like', "%$nip%");
                }
            });
        }

        // 6. PERBAIKAN: Filter berdasarkan Banyak Tim Kerja (whereIn)
        if (! empty($timKerjaIds) && ! in_array('semua', $timKerjaIds)) {
            $query->whereHas('user', function ($q) use ($timKerjaIds) {
                $q->whereIn('tim_kerja_id', $timKerjaIds);
            });
        }

        // 7. Filter berdasarkan Tipe Absen (WFO/WFA)
        if ($tipe_absen && $tipe_absen !== 'semua') {
            $query->where('tipe_absen', $tipe_absen);
        }

        // 8. Filter berdasarkan Lokasi Kantor
        if ($locationId && $locationId !== 'semua') {
            $query->where('location_id', $locationId);
        }

        // 9. Filter berdasarkan Rentang Tanggal
        if ($start_date && $end_date) {
            $query->whereDate('check_in_time', '>=', $start_date)
                ->whereDate('check_in_time', '<=', $end_date);
        }

        // 10. Ambil data hasil filter dengan urutan waktu masuk
        $attendances = $query->orderBy('check_in_time', 'asc')->get();

        // 11. PERBAIKAN: Format teks tim kerja untuk header PDF
        $timFilterText = 'Semua Tim';
        if (! empty($timKerjaIds) && ! in_array('semua', $timKerjaIds)) {
            // Ambil semua nama tim yang dipilih dan gabungkan dengan koma
            $namaTimArray = TimKerja::whereIn('id', $timKerjaIds)->pluck('nama')->toArray();
            if (! empty($namaTimArray)) {
                $timFilterText = implode(', ', $namaTimArray);
            }
        }

        $locObj = ($locationId && $locationId !== 'semua') ? Location::find($locationId) : null;

        $data = [
            'attendances' => $attendances,
            'user' => $userSelected,
            'start_date' => $start_date ? Carbon::parse($start_date)->format('d/m/Y') : null,
            'end_date' => $end_date ? Carbon::parse($end_date)->format('d/m/Y') : null,
            'tanggal_cetak' => $tanggal_cetak,
            'tim_filter' => $timFilterText, // Hasil gabungan nama tim
            'tipe_filter' => strtoupper($tipe_absen ?? 'Semua'),
            'lokasi_filter' => $locObj ? $locObj->name : 'Semua Lokasi',
        ];

        // 12. Generate PDF
        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        // 13. Stream/Download
        return $pdf->download('Laporan_Absensi_SIKAWA_'.date('Ymd_His').'.pdf');
    }

    public function showDeletePage()
    {
        // Mengarahkan ke file view hapus.blade.php
        return view('admin.absensi.hapus');
    }

    public function processDelete(Request $request)
    {
        // Validasi input
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Format tanggal agar mencakup waktu dari 00:00:00 sampai 23:59:59
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Ambil data yang berada di rentang tanggal tersebut (berdasarkan check_in_time)
        $attendances = Attendance::whereBetween('check_in_time', [$startDate, $endDate])->get();

        $deletedCount = 0;

        foreach ($attendances as $attendance) {
            // Hapus file fisik dari storage jika ada (sesuai field di database Anda)
            if ($attendance->photo_path) {
                Storage::disk('public')->delete($attendance->photo_path);
            }
            if ($attendance->photo_path_out) {
                Storage::disk('public')->delete($attendance->photo_path_out);
            }
            if ($attendance->laporan_pdf) {
                Storage::disk('public')->delete($attendance->laporan_pdf);
            }

            // Hapus data dari database
            $attendance->delete();
            $deletedCount++;
        }

        // Kembali ke halaman form dengan pesan sukses
        return redirect()->back()->with('success', "Berhasil! $deletedCount data absensi beserta file fotonya telah dihapus permanen.");
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'tipe_absen' => 'required|in:WFO,WFA',
            'reason_change_status' => 'required|string|min:5',
        ]);

        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'tipe_absen' => $request->tipe_absen,
            'reason_change_status' => $request->reason_change_status,
        ]);

        return redirect()->back()->with('success', 'Status absensi berhasil diperbarui.');
    }

    // Menampilkan form edit lupa absen
    public function editLupaAbsen($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        return view('admin.absensi.lupa_absen', compact('attendance'));
    }

    // Memproses perubahan jam masuk dan pulang
    public function updateLupaAbsen(Request $request, $id)
    {
        $request->validate([
            'check_in_time' => 'required',
            'check_out_time' => 'nullable',
        ]);

        $attendance = Attendance::findOrFail($id);

        // Update data berdasarkan input manual admin
        $attendance->update([
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'status' => 'hadir',
        ]);

        return redirect()->route('admin.absensi.report')->with('success', 'Data lupa absen berhasil diperbarui.');
    }

    // Fungsi untuk menampilkan form
    public function createManual()
    {
        $allPegawai = User::all(); // Mengambil semua data pegawai untuk dropdown
        $locations = Location::all(); // Mengambil data lokasi dari tabel locations

        return view('admin.absensi.create', compact('allPegawai', 'locations'));
    }

    // Fungsi untuk simpan data
    public function storeManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'check_in_time' => 'required',
            'check_out_time' => 'required',
            'tipe_absen' => 'required',
        ]);

        // Logika penentuan status berdasarkan jam masuk (08:00)
        $checkIn = new Carbon($request->check_in_time);
        $status = ($checkIn->format('H:i') > '08:00') ? 'terlambat' : 'hadir';

        Attendance::create([
            'user_id' => $request->user_id,
            'location_id' => $request->location_id,
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'tipe_absen' => $request->tipe_absen,
            'status' => $status,
        ]);

        return redirect()->route('admin.absensi.report')->with('success', 'Presensi manual berhasil dibuat.');
    }
}
