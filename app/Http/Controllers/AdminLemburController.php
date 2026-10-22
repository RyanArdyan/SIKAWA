<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Models\TimKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan package ini sudah diinstal

class AdminLemburController extends Controller
{
    /**
     * Menampilkan daftar Laporan Lembur Pegawai dengan Filter
     */
    public function report(Request $request)
    {
        $nip = $request->get('nip');
        $timKerjaIds = $request->get('tim_kerja_ids', []);
        $status = $request->get('status');
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $end_date = $request->get('end_date', Carbon::now()->toDateString());
        $print_date = $request->get('print_date', Carbon::now()->toDateString());

        $query = Lembur::with(['user.tim_kerja']);

        // Filter Tanggal
        if ($start_date && $end_date) {
            $query->whereBetween('tanggal', [$start_date, $end_date]);
        }

        // Filter NIP / Nama
        if ($nip) {
            $query->whereHas('user', function ($q) use ($nip) {
                $q->where('nip', 'like', "%{$nip}%")
                  ->orWhere('name', 'like', "%{$nip}%");
            });
        }

        // Filter Tim Kerja (Multiple)
        if (!empty($timKerjaIds)) {
            $query->whereHas('user', function ($q) use ($timKerjaIds) {
                $q->whereIn('tim_kerja_id', (array) $timKerjaIds);
            });
        }

        // Filter Status Lembur (pending, disetujui, ditolak)
        if ($status) {
            $query->where('status', $status);
        }

        $lemburs = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $timKerjas = TimKerja::orderBy('nama', 'asc')->get();

        return view('admin.lembur.report', compact(
            'lemburs',
            'timKerjas',
            'nip',
            'timKerjaIds',
            'status',
            'start_date',
            'end_date',
            'print_date'
        ));
    }

    /**
     * Menampilkan Halaman Detail Lembur
     */
    public function showDetail($id)
    {
        $lembur = Lembur::with(['user.tim_kerja'])->findOrFail($id);

        return view('admin.lembur.detail', compact('lembur'));
    }

    /**
     * Mengubah Status Approval Lembur (Pending / Disetujui / Ditolak)
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
     * Hapus Massal Data Lembur
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lembur,id',
        ]);

        $lemburs = Lembur::whereIn('id', $request->ids)->get();

        foreach ($lemburs as $lembur) {
            // Hapus berkas foto & PDF jika ada di disk public
            if ($lembur->photo_path_in && Storage::disk('public')->exists('lemburs/' . $lembur->photo_path_in)) {
                Storage::disk('public')->delete('lemburs/' . $lembur->photo_path_in);
            }
            if ($lembur->photo_path_out && Storage::disk('public')->exists('lemburs/' . $lembur->photo_path_out)) {
                Storage::disk('public')->delete('lemburs/' . $lembur->photo_path_out);
            }
            if ($lembur->laporan_pdf && Storage::disk('public')->exists('lemburs/' . $lembur->laporan_pdf)) {
                Storage::disk('public')->delete('lemburs/' . $lembur->laporan_pdf);
            }

            $lembur->delete();
        }

        return redirect()->back()->with('success', count($request->ids) . ' data lembur berhasil dihapus.');
    }

    /**
     * Export Data Lembur ke PDF Berdasarkan Filter
     */
    public function exportReportPdf(Request $request)
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
            $query->whereHas('user', function ($q) use ($nip) {
                $q->where('nip', 'like', "%{$nip}%")
                  ->orWhere('name', 'like', "%{$nip}%");
            });
        }

        if (!empty($timKerjaIds)) {
            $query->whereHas('user', function ($q) use ($timKerjaIds) {
                $q->whereIn('tim_kerja_id', (array) $timKerjaIds);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Ambil semua data (tanpa pagination) untuk dicetak
        $lemburs = $query->orderBy('tanggal', 'asc')->get();

        $pdf = Pdf::loadView('admin.lembur.pdf', compact(
            'lemburs',
            'start_date',
            'end_date',
            'print_date'
        ));

        // Mengatur ukuran kertas dan orientasi menjadi portrait
        $pdf->setPaper('A4', 'portrait');

        // Gunakan stream() untuk preview di browser, atau download() untuk langsung unduh
        return $pdf->stream('Laporan_Lembur_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}
