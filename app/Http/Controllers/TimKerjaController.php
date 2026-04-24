<?php

namespace App\Http\Controllers;

use App\Models\TimKerja;
use App\Models\User;
use Illuminate\Http\Request;

class TimKerjaController extends Controller
{
    // Menampilkan daftar semua Tim Kerja
    public function index()
    {
        // 1. Gunakan 'ketua' dan 'anggota' sesuai nama fungsi di Model
        // 2. Load keduanya sekaligus (Eager Loading) untuk efisiensi
        $tim_kerja = TimKerja::with(['ketua', 'anggota'])
            ->orderBy('nama', 'asc')
            ->get();

        return view('admin.tim_kerja.index', compact('tim_kerja'));
    }

    public function create()
    {
        // Ambil semua user/pegawai untuk ditampilkan di dropdown ketua
        $users = User::orderBy('name', 'asc')->get();

        return view('admin.tim_kerja.create', compact('users'));
    }

    public function store(Request $request)
    {
        // 1. Validasi dengan pesan kustom
        $request->validate([
            'nama' => 'required|string|unique:tim_kerja,nama',
            'ketua_id' => 'nullable|exists:users,id',
        ], [
            'nama.unique' => 'Nama tim kerja "' . $request->nama . '" sudah ada. Silakan gunakan nama lain.',
            'nama.required' => 'Nama tim kerja tidak boleh kosong.',
        ]);

        // 2. Simpan jika validasi lolos
        TimKerja::create([
            'nama' => $request->nama,
            'ketua_id' => $request->ketua_id,
        ]);

        return redirect()->route('admin.tim-kerja.index')->with('success', 'Tim Kerja berhasil ditambahkan!');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $tim = TimKerja::findOrFail($id);
        $users = User::orderBy('name', 'asc')->get(); // Untuk dropdown ketua

        return view('admin.tim_kerja.edit', compact('tim', 'users'));
    }

    public function update(Request $request, $id)
    {
        $tim = TimKerja::findOrFail($id);

        // Validasi: 'unique:tabel,kolom,kecuali_id'
        // Ini agar Laravel tidak error saat nama tim tidak diubah
        $request->validate([
            'nama' => 'required|unique:tim_kerja,nama,'.$tim->id,
            'ketua_id' => 'nullable|exists:users,id',
        ]);

        $tim->update([
            'nama' => $request->nama,
            'ketua_id' => $request->ketua_id,
        ]);

        return redirect()->route('admin.tim-kerja.index')->with('success', 'Data Tim Kerja berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $tim = TimKerja::findOrFail($id);

        // Hapus data tim
        $tim->delete();

        // Kembalikan ke halaman index dengan pesan sukses
        return redirect()->route('admin.tim-kerja.index')->with('success', 'Tim Kerja berhasil dihapus!');
    }
}
