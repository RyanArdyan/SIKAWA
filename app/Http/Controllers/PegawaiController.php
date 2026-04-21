<?php

namespace App\Http\Controllers;

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

    // Menampilkan form tambah pegawai
    public function create()
    {
        return view('admin.pegawai.create');
    }

    // Menyimpan data pegawai baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'nip' => 'required|unique:users,nip',
            'name' => 'required',
        ]);

        User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->nip.'@bkk.go.id', // Otomatis membuat email berdasarkan NIP
            // Berikan password default (misal: nip) jika sistem login dibutuhkan nanti
            'password' => bcrypt($request->nip),
        ]);

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan!');
    }

    // Menampilkan halaman edit pegawai
    public function edit($id)
    {
        $pegawai = User::findOrFail($id);

        return view('admin.pegawai.edit', compact('pegawai'));
    }

    // Memproses pembaruan data di database
    public function update(Request $request, $id)
    {
        $pegawai = User::findOrFail($id);

        $request->validate([
            'nip' => 'required|unique:users,nip,'.$id, // Unique kecuali untuk dirinya sendiri
            'name' => 'required',
        ]);

        $pegawai->update([
            'nip' => $request->nip,
            'name' => $request->name,
            'email' => $request->nip.'@bkk.go.id', // Update email juga sesuai NIP baru
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
