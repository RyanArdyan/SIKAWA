<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        // Ambil data pengguna yang baru saja login
        $user = Auth::user();

        // Kondisi jika pengguna adalah pegawai
        if ($user->role === 'pegawai') {
            return redirect()->route('pegawai.editBiodata');
        }

        // Jika bukan pegawai (Admin/Super Admin), arahkan ke dashboard admin
        return redirect()->intended('admin/dashboard');
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ]);
}

    // app/Http/Controllers/AuthController.php

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        // Langsung ke login agar bersih
        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }
}
