<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LemburController extends Controller
{
    /**
     * Menampilkan halaman utama presensi lembur untuk pegawai.
     */
    public function index()
    {
        return view('pegawai.lembur');
    }

    /**
     * Memeriksa dan mengambil data pegawai berdasarkan NIP secara real-time.
     */
    public function getPegawai($nip)
    {
        $user = User::where('nip', $nip)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai dengan NIP tersebut tidak ditemukan.',
            ]);
        }

        return response()->json([
            'success' => true,
            'nama' => $user->name,
            'jabatan' => $user->jabatan ?? '-',
        ]);
    }

    public function store(Request $request)
    {
        // Validasi input dari frontend
        $request->validate([
            'nip' => 'required',
            'image' => 'required',
            'tipe' => 'required|in:masuk,keluar',
            'keterangan_lembur' => 'nullable|string',
        ]);

        $user = User::where('nip', $request->nip)->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan.',
            ], 404);
        }

        $today = Carbon::today();

        // Cek apakah sudah ada data lembur hari ini untuk pegawai tersebut
        $lembur = Lembur::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        try {
            // Helper regex untuk membersihkan header base64 (support jpeg, png, webp, dll)
            $cleanBase64 = function ($base64String) {
                $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);

                return base64_decode(str_replace(' ', '+', $image));
            };

            // ==========================================
            // A. LOGIKA MASUK LEMBUR
            // ==========================================
            if ($request->tipe === 'masuk') {
                if ($lembur && $lembur->check_in_lembur) {
                    $jamMasuk = Carbon::parse($lembur->check_in_lembur)->format('H:i');

                    return response()->json([
                        'success' => false,
                        'message' => "Presensi masuk lembur Anda sudah tercatat pada jam {$jamMasuk}.",
                    ], 400);
                }

                // Proses Simpan Foto Masuk Lembur
                $imageName = $user->nip.'_LEMBUR_IN_'.time().'.jpeg';
                Storage::disk('public')->put('lemburs/'.$imageName, $cleanBase64($request->image));

                if ($lembur) {
                    $lembur->update([
                        'check_in_lembur' => now(),
                        'photo_path_in' => $imageName,
                        'keterangan_lembur' => $request->keterangan_lembur ?? $lembur->keterangan_lembur,
                    ]);
                } else {
                    Lembur::create([
                        'user_id' => $user->id,
                        'tanggal' => $today,
                        'check_in_lembur' => now(),
                        'photo_path_in' => $imageName,
                        'keterangan_lembur' => $request->keterangan_lembur,
                        'status' => 'pending',
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Presensi Masuk Lembur Berhasil Dicatat!',
                ]);
            }

            // ==========================================
            // B. LOGIKA KELUAR LEMBUR
            // ==========================================
            if ($request->tipe === 'keluar') {
                // 1. Pastikan sudah absen masuk lembur terlebih dahulu
                if (! $lembur || ! $lembur->check_in_lembur) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal! Anda belum melakukan presensi masuk lembur hari ini.',
                    ], 400);
                }

                // 2. CEK LAPORAN PDF DIHAPUS DARI SINI
                // (Blok kode pengecekan is_null($lembur->laporan_pdf) telah dihapus)

                // 3. Hapus foto keluar lama jika melakukan presensi ulang (overwrite)
                if ($lembur->photo_path_out) {
                    $oldPath = 'lemburs/'.$lembur->photo_path_out;
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }

                // 4. Proses Simpan Foto Keluar Lembur
                $imageName = $user->nip.'_LEMBUR_OUT_'.time().'.jpeg';
                Storage::disk('public')->put('lemburs/'.$imageName, $cleanBase64($request->image));

                // 5. Update waktu keluar dan foto keluar lembur
                $lembur->update([
                    'check_out_lembur' => now(),
                    'photo_path_out' => $imageName,
                ]);

                $jamKeluarBaru = now()->format('H:i');

                return response()->json([
                    'success' => true,
                    'message' => "Presensi Keluar Lembur Berhasil! Jam keluar tercatat pada pukul {$jamKeluarBaru}.",
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sistem Error: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Memproses pengunggahan laporan lembur (PDF).
     */
    public function uploadLaporan(Request $request)
    {
        // Validasi input
        $request->validate([
            'nip' => 'required',
            'laporan_pdf' => 'required|mimes:pdf|max:2048', // Maksimal 2MB
        ]);

        // Cari data pegawai berdasarkan NIP
        $user = User::where('nip', $request->nip)->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan.',
            ]);
        }

        $today = Carbon::today();

        // Cari record lembur hari ini untuk pegawai tersebut
        $lembur = Lembur::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        // Jika belum ada record lembur sama sekali hari ini
        if (! $lembur) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal! Anda harus melakukan presensi masuk lembur terlebih dahulu sebelum mengunggah laporan.',
            ]);
        }

        try {
            // Hapus laporan PDF lama jika pernah diunggah sebelumnya
            if ($lembur->laporan_pdf) {
                $oldFilePath = 'lemburs/'.$lembur->laporan_pdf;
                if (Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
            }

            // Simpan file PDF baru ke disk public folder 'lemburs'
            $file = $request->file('laporan_pdf');
            $fileName = $user->nip.'_LAPORAN_LEMBUR_'.time().'.'.$file->getClientOriginalExtension();
            $file->storeAs('lemburs', $fileName, 'public');

            // Update kolom laporan_pdf pada database
            $lembur->update([
                'laporan_pdf' => $fileName,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan lembur (PDF) berhasil diunggah!',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sistem Error: '.$e->getMessage(),
            ]);
        }
    }

    public function riwayat(Request $request)
    {
        $nip = $request->query('nip');

        $start_date = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->query('end_date', Carbon::now()->toDateString());

        $lemburs = collect();
        $user = null;

        if ($nip) {
            $user = User::where('nip', $nip)->first();

            if ($user) {
                $query = Lembur::where('user_id', $user->id);

                // Filter rentang waktu berdasarkan tanggal aktual agar lembur lintas malam terbaca aman
                $query->whereBetween('tanggal', [
                    Carbon::parse($start_date)->startOfDay(),
                    Carbon::parse($end_date)->endOfDay(),
                ]);

                // Urutkan berdasarkan tanggal, lalu jam masuk aktual
                $lemburs = $query->orderBy('tanggal', 'asc')
                    ->orderBy('check_in_lembur', 'asc')
                    ->paginate(100)->withQueryString();
                // paginate(100) artinya menampilkan 100 data per halaman
                // withQueryString() agar filter (NIP, tanggal) tidak hilang saat pindah halaman
            }
        }

        return view('pegawai.lembur_riwayat', compact(
            'lemburs',
            'nip',
            'start_date',
            'end_date',
            'user'
        ));
    }

    public function exportPdf(Request $request)
    {
        $nip = $request->query('nip');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $user = User::with('tim_kerja')->where('nip', $nip)->first();

        if (! $user) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan.');
        }

        $query = Lembur::where('user_id', $user->id);

        if ($start_date && $end_date) {
            $query->whereBetween('tanggal', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay(),
            ]);
        }

        // Urutkan berdasarkan tanggal, lalu jam aktual
        // Gunakan get() tanpa paginasi karena PDF harus mencetak seluruh rentang waktu
        $lemburs = $query->orderBy('tanggal', 'asc')
            ->orderBy('check_in_lembur', 'asc')
            ->get();

        $data = [
            'user' => $user,
            'lemburs' => $lemburs,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y'),
        ];

        $pdf = Pdf::loadView('pegawai.lembur_pdf', $data)
            ->setPaper('a4', 'portrait');

        // Gunakan ->stream() untuk menampilkan pratinjau langsung di browser (seperti gambar 1)
        return $pdf->stream('Riwayat_Lembur_'.$nip.'_'.date('Ymd_His').'.pdf');
    }
}
