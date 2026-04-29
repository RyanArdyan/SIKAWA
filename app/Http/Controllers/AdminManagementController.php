<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminManagementController extends Controller
{
    // Menampilkan daftar Admin & Super Admin
    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->orderBy('role', 'asc')
            ->get();

        return view('admin.manage_admins.index', compact('admins'));
    }

    // Menampilkan form tambah (pilih dari pegawai)
    public function create()
    {
        // Ambil user yang rolenya masih 'pegawai' untuk dipilih jadi admin
        $pegawai = User::where('role', 'pegawai')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.manage_admins.create', compact('pegawai'));
    }

    // Memproses perubahan role pegawai menjadi admin
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,super_admin',
        ]);

        $user = User::findOrFail($request->user_id);

        // Update role user tersebut
        $user->update([
            'role' => $request->role,
        ]);

        return redirect()->route('admin.manage-admins.index')
            ->with('success', $user->name.' berhasil diangkat menjadi '.$request->role);
    }

    // Menampilkan form edit akses
    public function edit($id)
    {
        // Cari user yang akan diedit, pastikan dia memang admin/super_admin
        $admin = User::whereIn('role', ['admin', 'super_admin'])->findOrFail($id);

        return view('admin.manage_admins.edit', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validasi diperluas untuk mencakup role 'pegawai'
        $request->validate([
            'role' => 'required|in:super_admin,admin,pegawai',
        ]);

        // Proteksi: Jangan biarkan Super Admin mengubah dirinya sendiri menjadi pegawai
        // agar tidak kehilangan akses ke halaman ini secara tidak sengaja.
        if ($user->id === auth()->id() && $request->role === 'pegawai') {
            return redirect()->back()->with('error', 'Anda tidak bisa menurunkan status akun Anda sendiri menjadi pegawai!');
        }

        $user->update([
            'role' => $request->role,
        ]);

        // Jika diubah jadi pegawai, arahkan kembali ke index dengan pesan yang sesuai
        if ($request->role === 'pegawai') {
            return redirect()->route('admin.manage-admins.index')
                ->with('success', 'Akses admin '.$user->name.' telah dicabut dan kembali menjadi pegawai.');
        }

        return redirect()->route('admin.manage-admins.index')
            ->with('success', 'Hak akses '.$user->name.' berhasil diperbarui.');
    }
}
