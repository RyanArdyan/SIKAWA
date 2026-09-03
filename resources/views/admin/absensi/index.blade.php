@extends('layouts.admin')

@section('header', 'Laporan Absensi Pegawai')

@section('content')
    {{-- Form Filter --}}
    <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
        <div class="card-body p-4">
            <form id="form-filter-laporan" action="{{ route('admin.absensi.report') }}" method="GET" class="row g-3">
                {{-- Filter Nama/NIP --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold text-body-secondary">Cari Pegawai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                            <i class="bi bi-search text-body-secondary"></i>
                        </span>
                        <input type="text" name="nip"
                            class="form-control bg-body border-secondary-subtle text-body border-start-0"
                            placeholder="NIP atau Nama..." value="{{ $nip ?? '' }}">
                    </div>
                </div>

                {{-- Filter Tim Kerja --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold text-body-secondary">Tim Kerja</label>
                    <select name="tim_kerja_id" class="form-select bg-body border-secondary-subtle text-body">
                        <option value="">-- Semua Tim --</option>
                        @foreach ($timKerjas as $tim)
                            <option value="{{ $tim->id }}" {{ ($timKerjaId ?? '') == $tim->id ? 'selected' : '' }}>
                                {{ $tim->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- FILTER BARU: Tipe Absen --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold text-body-secondary">Tipe Absen</label>
                    <select name="tipe_absen" class="form-select bg-body border-secondary-subtle text-body">
                        <option value="">Semua</option>
                        <option value="WFO" {{ ($tipeAbsen ?? '') == 'WFO' ? 'selected' : '' }}>WFO</option>
                        <option value="WFA" {{ ($tipeAbsen ?? '') == 'WFA' ? 'selected' : '' }}>WFA</option>
                    </select>
                </div>

                {{-- FILTER BARU: Lokasi Kantor --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold text-body-secondary">Lokasi Kantor</label>
                    <select name="location_id" class="form-select bg-body border-secondary-subtle text-body">
                        <option value="">Semua Lokasi</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" {{ ($locationId ?? '') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tanggal Mulai --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold text-body-secondary">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control bg-body border-secondary-subtle text-body"
                        value="{{ $start_date }}">
                </div>

                {{-- Filter Tanggal Akhir --}}
                <div class="col-md-2">
                    <label class="form-label fw-bold text-body-secondary">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control bg-body border-secondary-subtle text-body"
                        value="{{ $end_date }}">
                </div>

                {{-- Ubah dari col-md-2 menjadi col-md-3 agar tidak terlalu sempit --}}
                <div class="col-md-3 d-flex align-items-end gap-2">
                    {{-- Tombol Filter --}}
                    <button type="submit" class="btn text-white w-100 shadow-sm fw-bold text-nowrap"
                        style="background-color: #40BF89; border: none; height: 38px;">
                        <i class="bi bi-filter"></i> Filter
                    </button>

                    {{-- Tombol PDF --}}
                    @if ($attendances->count() > 0)
                        <a href="#" id="btn-export-pdf" class="btn btn-danger w-100 shadow-sm fw-bold text-nowrap"
                            style="height: 38px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-file-pdf"></i> PDF
                        </a>
                    @endif

                    {{-- Tombol Buat Absen --}}
                    <a href="{{ route('admin.laporan.createManual') }}"
                        class="btn text-white w-100 shadow-sm fw-bold text-nowrap"
                        style="background-color: #40BF89; border: none; height: 38px;">
                        <i class="bi bi-plus-circle me-1"></i> Buat Absen
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Riwayat Kehadiran --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-body">Riwayat Kehadiran</h5>
                <span class="badge px-3 py-2" style="background-color: #40BF89;">
                    <i class="bi bi-clock me-1"></i> Jam Masuk: {{ $jamMasuk }} WIB
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="py-3 text-body-secondary">No</th>
                            <th class="py-3 text-body-secondary">Aksi</th>
                            <th class="py-3 text-body-secondary">Nama Pegawai</th>
                            <th class="py-3 text-body-secondary text-center">Tipe</th>
                            <th class="py-3 text-body-secondary">Absen Masuk</th>
                            <th class="py-3 text-body-secondary">Absen Pulang</th>
                            <th class="py-3 text-body-secondary">Status</th>
                            <th class="py-3 text-body-secondary">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $key => $a)
                            <tr>
                                <td class="text-body-secondary small">{{ $key + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.laporan.detail', $a->id) }}"
                                        class="btn btn-sm text-white px-3 fw-medium" style="background-color: #40BF89;">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                                <td class="fw-bold text-body">
                                    <div>{{ $a->user->name ?? 'User Terhapus' }}</div>
                                    <small class="text-muted fw-normal" style="font-size: 0.75rem;">NIP:
                                        {{ $a->user->nip ?? '-' }}</small>
                                    <br>
                                    <small class="fw-bold" style="font-size: 0.75rem; color: #40BF89;">
                                        <i class="bi bi-people-fill small"></i>
                                        {{ $a->user->tim_kerja->nama ?? 'Tanpa Tim' }}
                                    </small>
                                    {{-- TAMBAHKAN LOKASI DI SINI --}}
                                    @if ($a->location)
                                        <br>
                                        <small class="text-primary fw-bold" style="font-size: 0.70rem;">
                                            <i class="bi bi-geo-alt-fill small"></i> {{ $a->location->name }}
                                        </small>
                                    @endif
                                </td>

                                {{-- Tipe Absen (WFO / WFA) dengan Riwayat Perubahan --}}
                                <td class="text-center">
                                    @if ($a->reason_change_status)
                                        {{-- Tampilan jika status telah diubah oleh Admin --}}
                                        <div class="d-flex flex-column align-items-center">
                                            <span
                                                class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 mb-1">
                                                <small class="text-decoration-line-through">
                                                    {{ $a->tipe_absen == 'WFA' ? 'WFO' : 'WFA' }}
                                                </small>
                                                <i class="bi bi-arrow-right mx-1"></i>
                                                {{ $a->tipe_absen }}
                                            </span>
                                        </div>
                                    @else
                                        {{-- Tampilan asli jika belum pernah diubah --}}
                                        @if ($a->tipe_absen == 'WFO')
                                            <span
                                                class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2">
                                                WFO
                                            </span>
                                        @else
                                            <span
                                                class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2">
                                                WFA
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Absen Masuk --}}
                                <td class="text-body-secondary small">
                                    @if ($a->check_in_time)
                                        <div class="fw-bold text-body">{{ $a->check_in_time->format('H:i') }} WIB</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            {{ $a->check_in_time->format('d M Y') }}</div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- Absen Pulang --}}
                                <td class="text-body-secondary small">
                                    @if ($a->check_out_time)
                                        <div class="fw-bold text-body">{{ $a->check_out_time->format('H:i') }} WIB</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            {{ $a->check_out_time->format('d M Y') }}</div>
                                    @else
                                        <span class="text-danger small italic">Belum Pulang</span>
                                    @endif
                                </td>

                                {{-- Status Kehadiran --}}
                                <td>
                                    @if ($a->status == 'terlambat')
                                        <span
                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3">
                                            <i class="bi bi-exclamation-circle me-1"></i> Terlambat
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3">
                                            <i class="bi bi-check2-circle me-1"></i> Hadir
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <span>
                                        <a href="{{ route('admin.laporan.editLupaAbsen', $a->id) }}"
                                            class="btn btn-sm btn-outline-warning shadow-sm me-1" title="Lupa Absen">
                                            <i class="bi bi-clock-history"></i>
                                        </a>

                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                            onclick="openEditModal('{{ route('admin.absensi.updateStatus', $a->id) }}', '{{ $a->tipe_absen }}', '{{ $a->reason_change_status }}')"
                                            title="Ubah Status">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </span>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-body-secondary py-5">
                                    <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
                                    Tidak ada data absensi yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Edit Status -->
    <div class="modal fade" id="modalEditStatus" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formEditStatus" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Tipe Absensi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tipe Absen Baru</label>
                            <select name="tipe_absen" id="edit_tipe_absen" class="form-select" required>
                                <option value="WFO">WFO (Work From Office)</option>
                                <option value="WFA">WFA (Work From Anywere)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan Perubahan</label>
                            <textarea id="edit_reason" name="reason_change_status" class="form-textarea w-100" rows="3"
                                placeholder="Contoh: Kesalahan sistem saat pemilihan lokasi" required minlength="5"></textarea>
                            <small class="text-muted">Wajib diisi sebagai log audit.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Klik Tombol Export PDF
            const btnExportPdf = document.getElementById('btn-export-pdf');

            if (btnExportPdf) {
                btnExportPdf.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Ambil referensi form filter
                    const filterForm = document.getElementById('form-filter-laporan');

                    // Ambil nilai dari setiap input filter
                    let startDate = filterForm.querySelector('input[name="start_date"]').value;
                    let endDate = filterForm.querySelector('input[name="end_date"]').value;
                    const nip = filterForm.querySelector('[name="nip"]').value;
                    const timKerja = filterForm.querySelector('[name="tim_kerja_id"]').value;
                    const tipeAbsen = filterForm.querySelector('[name="tipe_absen"]').value;
                    const locationId = filterForm.querySelector('[name="location_id"]').value;

                    // Susun URL tujuan (Route Laravel)
                    let url = "{{ route('admin.absensi.exportReportPdf') }}";

                    // Masukkan parameter filter ke dalam URL Search Params
                    let params = new URLSearchParams({
                        start_date: startDate,
                        end_date: endDate,
                        nip: nip,
                        tim_kerja_id: timKerja,
                        tipe_absen: tipeAbsen,
                        location_id: locationId
                    });

                    // Eksekusi perpindahan halaman untuk memicu download file
                    window.location.href = url + '?' + params.toString();
                });
            }
        });

        // Tambahkan parameter 'reason' di sini -------------------- v
        function openEditModal(url, currentType, reason) {
            // Set Action URL Form
            document.getElementById('formEditStatus').action = url;

            // Set Nilai Default Dropdown
            document.getElementById('edit_tipe_absen').value = currentType;

            // Sekarang variabel 'reason' sudah dikenali karena sudah jadi parameter
            document.getElementById('edit_reason').value = reason || '';

            // Tampilkan Modal
            var myModal = new bootstrap.Modal(document.getElementById('modalEditStatus'));
            myModal.show();
        }
    </script>
@endpush
