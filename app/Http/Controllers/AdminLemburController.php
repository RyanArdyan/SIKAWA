<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Models\TimKerja;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class AdminLemburController extends Controller
{
    /**
     * Membangun query dasar dengan filter yang konsisten untuk tampilan web & cetak PDF.
     */
    private function buildFilteredQuery(Request $request): array
    {
        $nip = $request->get('nip');
        $timKerjaIds = $request->get('tim_kerja_ids', []);
        $status = $request->get('status');
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->get('end_date', Carbon::now()->toDateString());
        $print_date = $request->get('print_date', Carbon::now()->toDateString());

        $query = Lembur::with(['user.tim_kerja']);

        if ($start_date && $end_date) {
            $query->whereBetween('tanggal', [$start_date, $end_date]);
        }

        if ($nip) {
            $query->whereHas('user', function (Builder $q) use ($nip) {
                $q->where('nip', 'like', "%{$nip}%")
                  ->orWhere('name', 'like', "%{$nip}%");
            });
        }

        if (!empty($timKerjaIds)) {
            $query->whereHas('user', function (Builder $q) use ($timKerjaIds) {
                $q->whereIn('tim_kerja_id', (array) $timKerjaIds);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        return compact(
            'query',
            'nip',
            'timKerjaIds',
            'status',
            'start_date',
            'end_date',
            'print_date'
        );
    }

    /**
     * Menampilkan daftar Laporan Lembur Pegawai dengan Filter.
     */
    public function report(Request $request)
    {
        $filtered = $this->buildFilteredQuery($request);

        $lemburs = $filtered['query']
            ->orderBy('tanggal', 'desc')
            ->orderBy('check_in_lembur', 'desc')
            ->paginate(50)
            ->withQueryString();

        $timKerjas = TimKerja::orderBy('nama', 'asc')->get();

        return view('admin.lembur.report', [
            'lemburs' => $lemburs,
            'timKerjas' => $timKerjas,
            'nip' => $filtered['nip'],
            'timKerjaIds' => $filtered['timKerjaIds'],
            'status' => $filtered['status'],
            'start_date' => $filtered['start_date'],
            'end_date' => $filtered['end_date'],
            'print_date' => $filtered['print_date'],
        ]);
    }

    /**
     * Menampilkan Halaman Detail Lembur.
     */
    public function showDetail($id)
    {
        $lembur = Lembur::with(['user.tim_kerja'])->findOrFail($id);

        return view('admin.lembur.detail', compact('lembur'));
    }

    /**
     * Mengubah Status Approval Lembur (Pending / Disetujui / Ditolak).
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,disetujui,ditolak',
        ]);

        $lembur = Lembur::findOrFail($id);
        $lembur->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Status pengajuan lembur berhasil diperbarui.');
    }

    /**
     * Hapus Massal Data Lembur beserta file foto terkait.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lembur,id',
        ]);

        $lemburs = Lembur::whereIn('id', $request->ids)->get();

        foreach ($lemburs as $lembur) {
            foreach ([$lembur->photo_path_in, $lembur->photo_path_out] as $rawPath) {
                $resolvedPath = Lembur::resolveStoragePath($rawPath);

                if ($resolvedPath && Storage::disk('public')->exists($resolvedPath)) {
                    Storage::disk('public')->delete($resolvedPath);
                }
            }

            $lembur->delete();
        }

        return redirect()->back()->with('success', count($request->ids) . ' data lembur berhasil dihapus.');
    }

    /**
     * Export Data Lembur ke PDF Berdasarkan Filter.
     */
    public function exportReportPdf(Request $request)
    {
        $filtered = $this->buildFilteredQuery($request);

        $lemburs = $filtered['query']
            ->orderBy('tanggal', 'asc')
            ->orderBy('check_in_lembur', 'asc')
            ->get();

        $pdf = Pdf::loadView('admin.lembur.pdf', [
            'lemburs' => $lemburs,
            'start_date' => $filtered['start_date'],
            'end_date' => $filtered['end_date'],
            'print_date' => $filtered['print_date'],
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('Laporan_Lembur_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Menampilkan Form Penyesuaian Lupa Lembur.
     */
    public function editLupaLembur($id)
    {
        $lembur = Lembur::with('user')->findOrFail($id);

        return view('admin.lembur.lupa_lembur', compact('lembur'));
    }

    /**
     * Menyimpan Perubahan Penyesuaian Lupa Lembur & Foto Presensi.
     */
    public function updateLupaLembur(Request $request, $id)
    {
        $request->validate([
            'check_in_lembur' => 'required|date',
            'check_out_lembur' => 'nullable|date|after_or_equal:check_in_lembur',
            'photo_path_in' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photo_path_out' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $lembur = Lembur::with('user')->findOrFail($id);

        $checkInDateTime = Carbon::parse($request->check_in_lembur);
        $checkOutDateTime = $request->check_out_lembur
            ? Carbon::parse($request->check_out_lembur)
            : null;

        $updateData = [
            'tanggal' => $checkInDateTime->toDateString(),
            'check_in_lembur' => $checkInDateTime,
            'check_out_lembur' => $checkOutDateTime,
            'status' => Lembur::STATUS_DISETUJUI,
        ];

        $userNip = $lembur->user->nip ?? 'pegawai';

        // Simpan / Ganti Foto Presensi MASUK Lembur
        if ($request->hasFile('photo_path_in')) {
            $oldPathIn = Lembur::resolveStoragePath($lembur->photo_path_in);
            if ($oldPathIn && Storage::disk('public')->exists($oldPathIn)) {
                Storage::disk('public')->delete($oldPathIn);
            }

            $fileIn = $request->file('photo_path_in');
            $filenameIn = $userNip . '_LEMBUR_IN_' . time() . '.' . $fileIn->getClientOriginalExtension();

            $fileIn->storeAs('lemburs', $filenameIn, 'public');
            $updateData['photo_path_in'] = $filenameIn;
        }

        // Simpan / Ganti Foto Presensi KELUAR Lembur
        if ($request->hasFile('photo_path_out')) {
            $oldPathOut = Lembur::resolveStoragePath($lembur->photo_path_out);
            if ($oldPathOut && Storage::disk('public')->exists($oldPathOut)) {
                Storage::disk('public')->delete($oldPathOut);
            }

            $fileOut = $request->file('photo_path_out');
            $filenameOut = $userNip . '_LEMBUR_OUT_' . time() . '.' . $fileOut->getClientOriginalExtension();

            $fileOut->storeAs('lemburs', $filenameOut, 'public');
            $updateData['photo_path_out'] = $filenameOut;
        }

        $lembur->update($updateData);

        return redirect()
            ->route('admin.lembur.report')
            ->with('success', 'Data penyesuaian lupa lembur dan foto berhasil diperbarui.');
    }

    /**
     * Menampilkan Form Buat Lembur Manual.
     */
    public function createManual()
    {
        $allPegawai = User::orderBy('name', 'asc')->get();

        return view('admin.lembur.create', compact('allPegawai'));
    }

    /**
     * Menyimpan Data Lembur Manual dari Admin.
     */
    public function storeManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'check_in_lembur' => 'required|date',
            'check_out_lembur' => 'nullable|date|after_or_equal:check_in_lembur',
            'keterangan_lembur' => 'nullable|string|max:500',
        ]);

        $checkIn = Carbon::parse($request->check_in_lembur);
        $checkOut = $request->check_out_lembur
            ? Carbon::parse($request->check_out_lembur)
            : null;

        Lembur::create([
            'user_id' => $request->user_id,
            'tanggal' => $checkIn->toDateString(),
            'check_in_lembur' => $checkIn,
            'check_out_lembur' => $checkOut,
            'keterangan_lembur' => $request->keterangan_lembur,
            'status' => Lembur::STATUS_DISETUJUI,
        ]);

        return redirect()
            ->route('admin.lembur.report')
            ->with('success', 'Data lembur manual berhasil dibuat.');
    }
}
