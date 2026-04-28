@extends('layouts.admin') {{-- Sesuaikan dengan nama layout admin kamu --}}

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Daftar Lokasi Kantor (IP)</h1>
            <a href="{{ route('admin.locations.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Tambah IP Kantor
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nama Lokasi</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($locations as $loc)
                                <tr>
                                    <td>{{ $loc->name }}</td>
                                    <td><code>{{ $loc->ip_address }}</code></td>
                                    <td>
                                        <span class="badge {{ $loc->is_active ? 'badge-success' : 'badge-danger' }}">
                                            {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.locations.edit', $loc->id) }}"
                                                class="btn btn-warning btn-sm text-white">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <form action="{{ route('admin.locations.destroy', $loc->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus lokasi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
