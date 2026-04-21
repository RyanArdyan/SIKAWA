<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TimKerja;

class TimKerjaController extends Controller
{
    // Menampilkan daftar semua Tim Kerja
    public function index()
    {
        $tim_kerja = TimKerja::orderBy('nama', 'asc')->get();

        return view('admin.tim_kerja.index', compact('tim_kerja'));
    }
}
