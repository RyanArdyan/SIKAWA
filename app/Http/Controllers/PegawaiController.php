<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\TimKerja;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PegawaiController extends Controller
{
    public function index()
    {
        // Filter hanya yang rolenya 'pegawai'
        $pegawai = User::where('role', 'pegawai')
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.pegawai.index', compact('pegawai'));
    }

    public function create()
    {
        // 1. Ambil semua data tim kerja dari database
        $tim_kerja = TimKerja::orderBy('nama', 'asc')->get();
        $locations = Location::orderBy('name', 'asc')->get(); // Tambahkan ini untuk lokasi

        // 2. Kirim variabel $tim_kerja dan $locations ke view menggunakan compact
        return view('admin.pegawai.create', compact('tim_kerja', 'locations'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (Tambahkan kolom baru dengan aturan 'nullable')
        $request->validate([
            'nip' => 'required|unique:users,nip',
            'name' => 'required|string|max:255',
            'tim_kerja_id' => 'required|exists:tim_kerja,id',
            'location_id' => 'required|exists:locations,id',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'pangkat_golongan' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'kelas_jabatan' => 'nullable|string|max:255',
            'pendidikan' => 'nullable|string|max:255',
        ]);

        // 2. Simpan data ke database beserta data kepegawaian baru
        User::create([
            'nip' => $request->nip,
            'name' => $request->name,
            'tim_kerja_id' => $request->tim_kerja_id,
            'location_id' => $request->location_id, // Simpan lokasi baru
            'gender' => $request->gender, // Simpan gender baru
            'pangkat_golongan' => $request->pangkat_golongan,
            'jabatan' => $request->jabatan,
            'kelas_jabatan' => $request->kelas_jabatan,
            'pendidikan' => $request->pendidikan,
            'email' => $request->nip.'@bkk.go.id',
            'password' => bcrypt('password123'), // Password default
        ]);

        // 3. Redirect dengan pesan sukses yang lebih informatif
        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Pegawai '.$request->name.' berhasil ditambahkan!');
    }

    // Menampilkan halaman edit pegawai
    public function edit($id)
    {
        $pegawai = User::findOrFail($id);
        // Tambahkan ini: Ambil semua tim untuk pilihan dropdown
        $tim_kerja = TimKerja::orderBy('nama', 'asc')->get();

        // TAMBAHAN: Mengambil semua data wilayah kerja untuk pilihan dropdown
        $locations = Location::orderBy('name', 'asc')->get();

        return view('admin.pegawai.edit', compact('pegawai', 'tim_kerja', 'locations'));
    }

    // Memproses pembaruan data di database
    public function update(Request $request, $id)
    {
        $pegawai = User::findOrFail($id);

        // 1. Validasi Input (Menambahkan kolom kepegawaian baru)
        $request->validate([
            'nip' => 'required|unique:users,nip,'.$id,
            'name' => 'required|string|max:255',
            'tim_kerja_id' => 'required|exists:tim_kerja,id', // Validasi tim baru
            'location_id' => 'required|exists:locations,id', // Validasi lokasi baru
            'pangkat_golongan' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'kelas_jabatan' => 'nullable|string|max:255',
            'pendidikan' => 'nullable|string|max:255',
        ]);

        // 2. Simpan perubahan ke database
        $pegawai->update([
            'nip' => $request->nip,
            'name' => $request->name,
            'tim_kerja_id' => $request->tim_kerja_id, // Simpan perubahan tim
            'location_id' => $request->location_id, // Simpan perubahan lokasi
            'pangkat_golongan' => $request->pangkat_golongan,
            'jabatan' => $request->jabatan,
            'kelas_jabatan' => $request->kelas_jabatan,
            'pendidikan' => $request->pendidikan,
            'email' => $request->nip.'@bkk.go.id', // Sinkronisasi email jika NIP ikut diubah
        ]);

        // 3. Kembali ke halaman index dengan pesan sukses
        return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai berhasil diperbarui!');
    }

    // Menghapus data pegawai
    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Pegawai berhasil dihapus!');
    }

    public function editBiodata()
    {
        $pegawai = auth()->user(); // Ambil data pegawai yang sedang login

        return view('pegawai.edit_biodata', compact('pegawai'));
    }

    // Memproses pembaruan data dari form biodata pegawai
    public function updateBiodata(Request $request)
    {
        $pegawai = Auth::user();

        // Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$pegawai->id,
            'pangkat_golongan' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'kelas_jabatan' => 'nullable|string|max:255',
            'pendidikan' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:6|confirmed', // 'confirmed' mewajibkan field password_confirmation
        ]);

        // Data dasar yang diupdate
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'pangkat_golongan' => $request->pangkat_golongan,
            'jabatan' => $request->jabatan,
            'kelas_jabatan' => $request->kelas_jabatan,
            'pendidikan' => $request->pendidikan,
        ];

        // Jika pegawai mengisi password baru, enkripsi dan masukkan ke database
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pegawai->update($data);

        return redirect()->back()->with('success', 'Biodata Anda berhasil diperbarui!');
    }

    // Reset password pegawai menjadi password default
    public function resetPassword($id)
    {
        $pegawai = User::findOrFail($id);

        // Reset password ke default 'password123'
        $pegawai->update([
            'password' => Hash::make('password123'),
        ]);

        return redirect()->back()->with(
            'success',
            'Password pegawai '.$pegawai->name.' berhasil di-reset menjadi default (password123).'
        );
    }
}
