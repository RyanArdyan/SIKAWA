<?php

namespace App\Http\Controllers;

use App\Models\TimKerja;
use App\Models\User;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    // Menampilkan daftar semua pegawai
    public function index()
    {
        $pegawai = User::orderBy('name', 'asc')->get();

        return view('admin.pegawai.index', compact('pegawai'));
    }

    public function create()
    {
        // 1. Ambil semua data tim kerja dari database
        $tim_kerja = TimKerja::orderBy('nama', 'asc')->get();

        // 2. Kirim variabel $tim_kerja ke view menggunakan compact
        return view('admin.pegawai.create', compact('tim_kerja'));
    }

    public function store(Request $request)
    {
        // 3. Tambahkan validasi untuk tim_kerja_id
        $request->validate([
            'nip' => 'required|unique:users,nip',
            'name' => 'required|string|max:255',
            'tim_kerja_id' => 'required|exists:tim_kerja,id', // Pastikan ID ada di tabel tim_kerja
            // tambahkan validasi email/password jika diperlukan
        ]);

        // 4. Simpan data ke database
        User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'tim_kerja_id' => $request->tim_kerja_id,
            'email' => $request->nip.'@bkk.go.id', // Contoh email otomatis pakai NIP
            'password' => bcrypt('password123'),        // Password default
        ]);

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan!');
    }

    // Menampilkan halaman edit pegawai
    public function edit($id)
    {
        $pegawai = User::findOrFail($id);
        // Tambahkan ini: Ambil semua tim untuk pilihan dropdown
        $tim_kerja = TimKerja::orderBy('nama', 'asc')->get();

        return view('admin.pegawai.edit', compact('pegawai', 'tim_kerja'));
    }

    // Memproses pembaruan data di database
    // Memproses pembaruan data di database
    public function update(Request $request, $id)
    {
        $pegawai = User::findOrFail($id);

        $request->validate([
            'nip' => 'required|unique:users,nip,'.$id,
            'name' => 'required',
            'tim_kerja_id' => 'required|exists:tim_kerja,id', // Validasi tim baru
        ]);

        $pegawai->update([
            'nip' => $request->nip,
            'name' => $request->name,
            'tim_kerja_id' => $request->tim_kerja_id, // Simpan perubahan tim
            'email' => $request->nip.'@bkk.go.id',
        ]);

        return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui!');
    }

    // Menghapus data pegawai
    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Pegawai berhasil dihapus!');
    }
}
