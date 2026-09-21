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
        $tipeAbsen = 'WFA'; // Dipaksa selalu WFA

        // 2. Validasi input (Termasuk tipe: masuk/keluar dari frontend)
        $request->validate([
            'nip' => 'required',
            'image' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'tipe' => 'required|in:masuk,keluar',
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
            // A. LOGIKA PRESENSI MASUK
            if ($request->tipe === 'masuk') {
                // Jika sudah pernah absen masuk hari ini, pertahankan data yang paling awal
                if ($attendance && $attendance->check_in_time) {
                    $jamMasukAwal = Carbon::parse($attendance->check_in_time)->format('H:i');
                    return response()->json([
                        'success' => true,
                        'message' => "Presensi masuk Anda sudah tercatat pada jam {$jamMasukAwal} WITA. (Sistem mempertahankan waktu masuk paling awal).",
                    ]);
                }

                // Proses Simpan Foto Masuk (IN)
                $imagePath = null;
                if ($request->has('image') && ! empty($request->image)) {
                    $image = $request->image;
                    $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
                    $imageName = $user->nip.'_IN_'.time().'.jpeg';

                    Storage::disk('public')->put('attendances/'.$imageName, base64_decode($image));
                    $imagePath = 'attendances/'.$imageName;
                }

                // Penentuan Status Terlambat/Hadir
                $jamMasukSetting = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';
                $waktuMasukSesuaiJadwal = Carbon::createFromFormat('H:i', $jamMasukSetting);
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
                    'message' => 'Presensi Masuk Berhasil! Status: '.ucfirst($statusAbsen),
                ]);
            }

            // B. LOGIKA PRESENSI KELUAR
            if ($request->tipe === 'keluar') {
                // 1. Harus sudah presensi masuk terlebih dahulu
                if (! $attendance) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal! Anda belum melakukan presensi masuk hari ini.',
                    ]);
                }

                // 2. Wajib unggah laporan PDF harian
                if (empty($attendance->laporan_pdf)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal! Anda wajib mengunggah laporan harian (PDF) terlebih dahulu sebelum presensi keluar.',
                    ]);
                }

                // Hapus foto keluar lama dari storage jika melakukan presensi keluar ulang (overwrite)
                if ($attendance->photo_path_out && Storage::disk('public')->exists($attendance->photo_path_out)) {
                    Storage::disk('public')->delete($attendance->photo_path_out);
                }

                // Proses Simpan Foto Keluar Baru (OUT)
                $imagePathOut = null;
                if ($request->has('image') && ! empty($request->image)) {
                    $image = $request->image;
                    $image = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $image);
                    $imageName = $user->nip.'_OUT_'.time().'.jpeg';

                    Storage::disk('public')->put('attendances/'.$imageName, base64_decode($image));
                    $imagePathOut = 'attendances/'.$imageName;
                }

                // Menimpa jam keluar, foto, dan koordinat ke waktu paling akhir
                $attendance->update([
                    'check_out_time' => now(),
                    'photo_path_out' => $imagePathOut,
                    'latitude_out' => $request->latitude,
                    'longitude_out' => $request->longitude,
                    'ip_address_log' => $userIp,
                ]);

                $jamKeluarBaru = now()->format('H:i');

                return response()->json([
                    'success' => true,
                    'message' => "Presensi Keluar Berhasil! Jam keluar diperbarui ke jam {$jamKeluarBaru}.",
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
            ->whereDate('created_at', Carbon::today())
            ->first();

        $status = 'masuk';
        $laporanSudahAda = false;

        if ($attendance) {
            $laporanSudahAda = ! empty($attendance->laporan_pdf);
            $status = $attendance->check_out_time ? 'sudah_keluar' : 'sudah_masuk';
        }

        return response()->json([
            'success' => true,
            'nama' => $user->name,
            'status' => $status,
            'laporan_ready' => $laporanSudahAda,
            'boleh_upload' => true,
            'boleh_pulang' => true,
            'pesan_waktu' => '',
            'pesan_pulang' => '',
            'is_wfo' => false,
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
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($attendance) {
                if ($attendance->laporan_pdf && Storage::disk('public')->exists($attendance->laporan_pdf)) {
                    Storage::disk('public')->delete($attendance->laporan_pdf);
                }

                if ($request->hasFile('laporan_pdf')) {
                    $file = $request->file('laporan_pdf');
                    $filename = 'laporan_'.$user->nip.'_'.time().'.pdf';

                    $path = $file->storeAs('reports', $filename, 'public');

                    $attendance->update([
                        'laporan_pdf' => $path,
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Laporan harian berhasil diunggah! Silakan tekan tombol Presensi Keluar.',
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'Data absensi hari ini tidak ditemukan. Silakan presensi masuk terlebih dahulu.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal: '.$e->getMessage()], 500);
        }
    }

    public function riwayat(Request $request)
    {
        $nip = $request->query('nip');
        $tipe_absen = $request->query('tipe_absen');

        $start_date = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->query('end_date', Carbon::now()->toDateString());

        $attendances = collect();
        $user = null;

        if ($nip) {
            $user = User::where('nip', $nip)->first();

            if ($user) {
                $query = Attendance::where('user_id', $user->id);

                if ($tipe_absen && $tipe_absen !== 'semua') {
                    $query->where('tipe_absen', $tipe_absen);
                }

                $query->whereBetween('created_at', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay(),
                ]);

                $attendances = $query->orderBy('created_at', 'desc')->get();
            }
        }

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
        $nip = $request->query('nip');

        $timKerjaIds = $request->query('tim_kerja_ids', []);
        if ($request->filled('tim_kerja_id') && empty($timKerjaIds)) {
            $timKerjaIds = [$request->query('tim_kerja_id')];
        }

        $tipeAbsen = $request->query('tipe_absen');
        $locationId = $request->query('location_id');

        $start_date = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->query('end_date', Carbon::now()->toDateString());

        $query = Attendance::with(['user.tim_kerja', 'location']);

        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                if (is_numeric($nip)) {
                    $q->where('nip', $nip)->orWhere('name', 'like', "%{$nip}%");
                } else {
                    $q->where('name', 'like', "%{$nip}%");
                }
            });
        }

        if (! empty($timKerjaIds) && ! in_array('semua', $timKerjaIds)) {
            $query->whereHas('user', function ($q) use ($timKerjaIds) {
                $q->whereIn('tim_kerja_id', $timKerjaIds);
            });
        }

        if ($tipeAbsen && $tipeAbsen !== 'semua') {
            $query->where('tipe_absen', $tipeAbsen);
        }

        if ($locationId && $locationId !== 'semua') {
            $query->where('location_id', $locationId);
        }

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay(),
            ]);
        }

        $attendances = $query->latest()->get();

        $timKerjas = TimKerja::all();
        $locations = Location::all();
        $jamMasuk = Setting::where('key', 'jam_masuk')->first()->value ?? '08:00';

        return view('admin.absensi.index', compact(
            'attendances',
            'timKerjas',
            'locations',
            'jamMasuk',
            'nip',
            'timKerjaIds',
            'start_date',
            'end_date',
            'tipeAbsen',
            'locationId'
        ));
    }

    public function exportPdf(Request $request)
    {
        $nip = $request->query('nip');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        $tipe_absen = $request->query('tipe_absen');

        $user = User::with('tim_kerja')->where('nip', $nip)->first();

        if (! $user) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        $query = Attendance::where('user_id', $user->id);

        if ($tipe_absen && $tipe_absen !== 'semua') {
            $query->where('tipe_absen', $tipe_absen);
        }

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
            'tim_filter' => $user->tim_kerja->nama ?? '-',
            'tipe_filter' => $tipe_absen ?? 'Semua',
            'lokasi_filter' => 'Semua Lokasi',
        ];

        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Riwayat_Absen_'.$nip.'_'.date('Ymd_His').'.pdf');
    }

    public function exportReportPdf(Request $request)
    {
        $nip = $request->query('nip');
        $timKerjaIds = $request->query('tim_kerja_ids', []);

        if ($request->filled('tim_kerja_id') && empty($timKerjaIds)) {
            $timKerjaIds = [$request->query('tim_kerja_id')];
        }

        $start_raw = $request->query('start_date');
        $end_raw = $request->query('end_date');
        $print_raw = $request->query('print_date');
        $tipe_absen = $request->query('tipe_absen');
        $locationId = $request->query('location_id');

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

        $tanggal_cetak = Carbon::now()->translatedFormat('d F Y');
        if ($print_raw) {
            try {
                $tanggal_cetak = Carbon::parse($print_raw)->translatedFormat('d F Y');
            } catch (\Exception $e) {
                try {
                    $tanggal_cetak = Carbon::createFromFormat('d/m/Y', $print_raw)->translatedFormat('d F Y');
                } catch (\Exception $ex) {
                    // Abaikan jika parsing gagal
                }
            }
        }

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

        $query = Attendance::with(['user.tim_kerja', 'location']);

        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                if (is_numeric($nip)) {
                    $q->where('nip', $nip)->orWhere('name', 'like', "%$nip%");
                } else {
                    $q->where('name', 'like', "%$nip%");
                }
            });
        }

        if (! empty($timKerjaIds) && ! in_array('semua', $timKerjaIds)) {
            $query->whereHas('user', function ($q) use ($timKerjaIds) {
                $q->whereIn('tim_kerja_id', $timKerjaIds);
            });
        }

        if ($tipe_absen && $tipe_absen !== 'semua') {
            $query->where('tipe_absen', $tipe_absen);
        }

        if ($locationId && $locationId !== 'semua') {
            $query->where('location_id', $locationId);
        }

        if ($start_date && $end_date) {
            $query->whereDate('check_in_time', '>=', $start_date)
                ->whereDate('check_in_time', '<=', $end_date);
        }

        $attendances = $query->orderBy('check_in_time', 'asc')->get();

        $timFilterText = 'Semua Tim';
        if (! empty($timKerjaIds) && ! in_array('semua', $timKerjaIds)) {
            $namaTimArray = TimKerja::whereIn('id', $timKerjaIds)->pluck('nama')->toArray();
            if (! empty($namaTimArray)) {
                $timFilterText = implode(', ', $namaTimArray);
            }
        }

        $locObj = ($locationId && $locationId !== 'semua') ? Location::find($locationId) : null;

        $data = [
            'attendances' => $attendances,
            'user' => $userSelected,
            'start_date' => $start_date ? Carbon::parse($start_date) : null,
            'end_date' => $end_date ? Carbon::parse($end_date) : null,
            'tanggal_cetak' => $tanggal_cetak,
            'tim_filter' => $timFilterText,
            'tipe_filter' => strtoupper($tipe_absen ?? 'Semua'),
            'lokasi_filter' => $locObj ? $locObj->name : 'Semua Lokasi',
        ];

        $pdf = Pdf::loadView('admin.absensi.report_pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Absensi_SIKAWA_'.date('Ymd_His').'.pdf');
    }

    public function showDeletePage()
    {
        return view('admin.absensi.hapus');
    }

    public function processDelete(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $attendances = Attendance::whereBetween('check_in_time', [$startDate, $endDate])->get();

        $deletedCount = 0;

        foreach ($attendances as $attendance) {
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
            $deletedCount++;
        }

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

    public function editLupaAbsen($id)
    {
        $attendance = Attendance::with('user')->findOrFail($id);

        return view('admin.absensi.lupa_absen', compact('attendance'));
    }

    public function updateLupaAbsen(Request $request, $id)
    {
        $request->validate([
            'check_in_time' => 'required',
            'check_out_time' => 'nullable',
        ]);

        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'check_in_time' => $request->check_in_time,
            'check_out_time' => $request->check_out_time,
            'status' => 'hadir',
        ]);

        return redirect()->route('admin.absensi.report')->with('success', 'Data lupa absen berhasil diperbarui.');
    }

    public function createManual()
    {
        $allPegawai = User::all();
        $locations = Location::all();

        return view('admin.absensi.create', compact('allPegawai', 'locations'));
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'check_in_time' => 'required',
            'check_out_time' => 'required',
            'tipe_absen' => 'required',
        ]);

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
