<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // Menampilkan daftar IP Kantor
    public function index()
    {
        $locations = Location::latest()->get();

        return view('admin.locations.index', compact('locations'));
    }

    // Form tambah lokasi/IP
    public function create()
    {
        return view('admin.locations.create');
    }

    // Simpan ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'ip_address' => 'required|unique:locations,ip_address',
        ]);

        Location::create($request->all());

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi kantor berhasil ditambahkan.');
    }

    // Hapus lokasi
    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }

    // Form edit lokasi
    public function edit(Location $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    // Update data di database
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required',
            // Validasi unique dikecualikan untuk ID yang sedang diedit
            'ip_address' => 'required|ip|unique:locations,ip_address,'.$location->id,
            'is_active' => 'required|boolean',
        ]);

        $location->update($request->all());

        return redirect()->route('admin.locations.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }
}
