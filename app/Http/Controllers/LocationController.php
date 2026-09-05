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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1',
        ], [
            'latitude.between' => 'Latitude harus berada di antara rentang -90 hingga 90.',
            'longitude.between' => 'Longitude harus berada di antara rentang -180 hingga 180.',
            'latitude.numeric' => 'Format Latitude harus berupa angka.',
            'longitude.numeric' => 'Format Longitude harus berupa angka.',
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
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1',
        ], [
            // Custom pesan error
            'name.required' => 'Nama lokasi harus diisi.',
            'latitude.required' => 'Latitude harus diisi.',
            'latitude.numeric' => 'Format latitude harus berupa angka desimal.',
            'latitude.between' => 'Latitude harus berada di antara rentang -90 hingga 90.',
            'longitude.required' => 'Longitude harus diisi.',
            'longitude.numeric' => 'Format longitude harus berupa angka desimal.',
            'longitude.between' => 'Longitude harus berada di antara rentang -180 hingga 180.',
            'radius.required' => 'Radius harus diisi.',
            'radius.integer' => 'Radius harus berupa angka bulat dalam satuan meter.',
            'radius.min' => 'Radius minimal bernilai 1 meter.',
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
