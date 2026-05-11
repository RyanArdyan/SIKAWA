<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::all();

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer',
        ]);

        Location::create($request->all());

        return redirect()->back()->with('success', 'Lokasi kantor berhasil ditambahkan.');
    }

    /**
     * Menampilkan halaman form edit lokasi.
     *
     * @return View
     */
    public function edit(Location $location)
    {
        // Mengarahkan ke file views/admin/locations/edit.blade.php
        return view('admin.locations.edit', compact('location'));
    }

    /**
     * Memperbarui data lokasi di database.
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Location $location)
    {
        // 1. Validasi input
        $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|integer|min:1',
        ], [
            // Custom pesan error (opsional)
            'name.required' => 'Nama lokasi harus diisi.',
            'latitude.numeric' => 'Format latitude harus berupa angka desimal.',
            'longitude.numeric' => 'Format longitude harus berupa angka desimal.',
            'radius.integer' => 'Radius harus berupa angka bulat dalam satuan meter.',
        ]);

        // 2. Update data ke database
        $location->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
        ]);

        // 3. Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('admin.locations.index')
            ->with('success', "Lokasi {$location->name} berhasil diperbarui.");
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->back()->with('success', 'Lokasi berhasil dihapus.');
    }
}
