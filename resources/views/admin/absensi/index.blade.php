@extends('layouts.admin')

@section('header', 'Laporan Absensi Pegawai')

@push('styles')
    {{-- CSS Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* =========================================================
             * SELECT2
             * ========================================================= */

        /* Sembunyikan tag bawaan Select2 Multiple agar berbentuk single box dropdown */
        .select2-container--default .select2-selection--multiple {
            background-color: var(--bs-body-bg, #212529) !important;
            border-color: var(--bs-border-color, #495057) !important;
            min-height: 38px;
            height: 38px;
            padding: 2px 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        /* Mengubah item terpilih menjadi format text pendek */
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: block !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            padding-left: 0 !important;
            color: var(--bs-body-color, #fff);
        }

        /* Menyembunyikan chip tag */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            display: none !important;
        }

        /* Menyembunyikan input pengetikan utama */
        .select2-container--default .select2-search--inline .select2-search__field {
            display: none !important;
        }

        /* Styling Dropdown list dengan Checkbox */
        .select2-results__option {
            padding: 8px 12px !important;
            color: #212529;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #e9ecef !important;
            color: #212529 !important;
        }

        .select-checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }

        .select-checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #40BF89;
        }

        /* Header Search di dalam Dropdown Menu */
        .select2-dropdown {
            border-color: var(--bs-border-color, #495057) !important;
            border-radius: 6px;
            overflow: hidden;
        }

        .select2-search--dropdown {
            padding: 6px 8px;
            background-color: #f8f9fa;
        }

        .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 4px 8px;
        }

        /* =========================================================
             * BULK DELETE
             * ========================================================= */

        .attendance-select-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #dc3545;
            vertical-align: middle;
        }

        .attendance-select-all {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #dc3545;
        }

        .attendance-row-selected {
            background-color: rgba(220, 53, 69, 0.06) !important;
        }

        #bulk-delete-container {
            display: none;
        }

        #bulk-delete-container.show {
            display: block;
        }

        #selected-count {
            min-width: 22px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .bulk-delete-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .bulk-delete-toolbar {
                width: 100%;
                margin-top: 10px;
            }

            .bulk-delete-toolbar form {
                width: 100%;
            }

            #btn-bulk-delete {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')

    {{-- =========================================================
     * FORM FILTER
     * ========================================================= --}}
    <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
        <div class="card-body p-4">

            <form id="form-filter-laporan" action="{{ route('admin.absensi.report') }}" method="GET" class="row g-3">

                {{-- BARIS 1 --}}

                {{-- 1. Filter Nama/NIP --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">
                        Cari Pegawai
                    </label>

                    <div class="input-group">
                        <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                            <i class="bi bi-search text-body-secondary"></i>
                        </span>

                        <input type="text" name="nip"
                            class="form-control bg-body border-secondary-subtle text-body border-start-0"
                            placeholder="NIP atau Nama..." value="{{ $nip ?? '' }}">
                    </div>
                </div>

                {{-- 2. Filter Tim Kerja --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">
                        Tim Kerja (Bisa Pilih Banyak)
                    </label>

                    <select name="tim_kerja_ids[]" id="select-tim-kerja"
                        class="form-select bg-body border-secondary-subtle text-body" multiple>

                        @foreach ($timKerjas as $tim)
                            <option value="{{ $tim->id }}"
                                {{ in_array($tim->id, (array) ($timKerjaIds ?? [])) ? 'selected' : '' }}>
                                {{ $tim->nama }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- 3. FILTER: Tipe Absen --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">
                        Tipe Absen
                    </label>

                    <select name="tipe_absen" class="form-select bg-body border-secondary-subtle text-body">

                        <option value="">Semua</option>

                        <option value="WFO" {{ ($tipeAbsen ?? '') == 'WFO' ? 'selected' : '' }}>
                            WFO
                        </option>

                        <option value="WFA" {{ ($tipeAbsen ?? '') == 'WFA' ? 'selected' : '' }}>
                            WFA
                        </option>

                    </select>
                </div>

                {{-- 4. FILTER: Lokasi Kantor --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">
                        Lokasi Kantor
                    </label>

                    <select name="location_id" class="form-select bg-body border-secondary-subtle text-body">

                        <option value="">
                            Semua Lokasi
                        </option>

                        @foreach ($locations as $loc)
                            <option value="{{ $loc->id }}" {{ ($locationId ?? '') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- BARIS 2 --}}

                {{-- 5. Filter Tanggal Mulai --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">
                        Dari Tanggal
                    </label>

                    <input type="date" name="start_date" class="form-control bg-body border-secondary-subtle text-body"
                        value="{{ $start_date }}">
                </div>

                {{-- 6. Filter Tanggal Akhir --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">
                        Sampai Tanggal
                    </label>

                    <input type="date" name="end_date" class="form-control bg-body border-secondary-subtle text-body"
                        value="{{ $end_date }}">
                </div>

                {{-- 7. INPUT: Tanggal Cetak & Grup Tombol --}}
                @if (auth()->check() && auth()->user()->role === 'super_admin')
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-body-secondary">
                            Tanggal Cetak
                        </label>

                        <input type="date" name="print_date"
                            class="form-control bg-body border-secondary-subtle text-body"
                            value="{{ $print_date ?? date('Y-m-d') }}">
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
                    @else
                        <div class="col-md-6 d-flex align-items-end gap-2">
                @endif

                {{-- Tombol Filter --}}
                <button type="submit" class="btn text-white w-100 shadow-sm fw-bold text-nowrap"
                    style="background-color: #40BF89; border: none; height: 38px;">
                    <i class="bi bi-filter"></i>
                    Filter
                </button>

                {{-- Tombol PDF --}}
                @if ($attendances->count() > 0)
                    <a href="#" id="btn-export-pdf" class="btn btn-danger w-100 shadow-sm fw-bold text-nowrap"
                        style="height: 38px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-file-pdf"></i>
                        PDF
                    </a>
                @endif

                {{-- Tombol Buat Absen --}}
                <a href="{{ route('admin.laporan.createManual') }}"
                    class="btn text-white w-100 shadow-sm fw-bold text-nowrap"
                    style="background-color: #40BF89; border: none; height: 38px;">
                    <i class="bi bi-plus-circle me-1"></i>
                    Buat Absen
                </a>

        </div>

        </form>

    </div>
    </div>


    {{-- =========================================================
     * TABEL RIWAYAT KEHADIRAN
     * ========================================================= --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">

        {{-- CARD HEADER --}}
        <div class="card-header bg-transparent py-3" style="border-top: 5px solid #40BF89;">

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                {{-- Judul --}}
                <div>
                    <h5 class="mb-0 fw-bold text-body">
                        Riwayat Kehadiran
                    </h5>
                </div>

                {{-- Toolbar --}}
                <div class="bulk-delete-toolbar">

                    {{-- Jumlah Dipilih --}}
                    <div id="bulk-delete-container">
                        <span class="badge bg-danger px-3 py-2">
                            <i class="bi bi-check2-square me-1"></i>

                            <span id="selected-count">0</span>
                            data dipilih
                        </span>
                    </div>

                    {{-- Tombol Hapus --}}
                    <form id="form-bulk-delete" action="{{ route('admin.laporan.bulkDelete') }}" method="POST"
                        class="m-0">

                        @csrf
                        @method('DELETE')

                        {{-- Hidden input ids[] akan dibuat melalui JavaScript --}}
                        <div id="bulk-delete-inputs"></div>

                        <button type="submit" id="btn-bulk-delete" class="btn btn-danger shadow-sm fw-bold" disabled>

                            <i class="bi bi-trash3 me-1"></i>
                            Hapus Terpilih

                        </button>

                    </form>

                    {{-- Jam Masuk --}}
                    <span class="badge px-3 py-2" style="background-color: #40BF89;">

                        <i class="bi bi-clock me-1"></i>

                        Jam Masuk:
                        {{ $jamMasuk }}
                        WIB

                    </span>

                </div>

            </div>

        </div>


        {{-- CARD BODY --}}
        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead>

                        <tr>

                            {{-- Checkbox Pilih Semua --}}
                            <th class="py-3 text-body-secondary text-center" style="width: 50px;">

                                <input type="checkbox" id="select-all-attendance" class="attendance-select-all"
                                    title="Pilih semua">

                            </th>

                            {{-- No --}}
                            <th class="py-3 text-body-secondary">
                                No
                            </th>

                            {{-- Detail --}}
                            <th class="py-3 text-body-secondary">
                                Aksi
                            </th>

                            {{-- Nama --}}
                            <th class="py-3 text-body-secondary">
                                Nama Pegawai
                            </th>

                            {{-- Tipe --}}
                            <th class="py-3 text-body-secondary text-center">
                                Tipe
                            </th>

                            {{-- Masuk --}}
                            <th class="py-3 text-body-secondary">
                                Absen Masuk
                            </th>

                            {{-- Pulang --}}
                            <th class="py-3 text-body-secondary">
                                Absen Pulang
                            </th>

                            {{-- Aksi --}}
                            <th class="py-3 text-body-secondary">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($attendances as $key => $a)

                            <tr class="attendance-row">

                                {{-- =================================================
                                 * CHECKBOX PER DATA
                                 * ================================================= --}}
                                <td class="text-center">

                                    <input type="checkbox" name="attendance_ids[]" value="{{ $a->id }}"
                                        class="attendance-checkbox">

                                </td>


                                {{-- NO --}}
                                <td class="text-body-secondary small">
                                    {{ $key + 1 }}
                                </td>


                                {{-- DETAIL --}}
                                <td>

                                    <a href="{{ route('admin.laporan.detail', $a->id) }}"
                                        class="btn btn-sm text-white px-3 fw-medium" style="background-color: #40BF89;">

                                        <i class="bi bi-eye"></i>
                                        Detail

                                    </a>

                                </td>


                                {{-- NAMA PEGAWAI --}}
                                <td class="fw-bold text-body">

                                    <div>
                                        {{ $a->user->name ?? 'User Terhapus' }}
                                    </div>

                                    <small class="text-muted fw-normal" style="font-size: 0.75rem;">

                                        NIP:
                                        {{ $a->user->nip ?? '-' }}

                                    </small>

                                    <br>

                                    <small class="fw-bold" style="font-size: 0.75rem; color: #40BF89;">

                                        <i class="bi bi-people-fill small"></i>

                                        {{ $a->user->tim_kerja->nama ?? 'Tanpa Tim' }}

                                    </small>

                                    @if ($a->location)
                                        <br>

                                        <small class="text-primary fw-bold" style="font-size: 0.70rem;">

                                            <i class="bi bi-geo-alt-fill small"></i>

                                            {{ $a->location->name }}

                                        </small>
                                    @endif

                                </td>


                                {{-- TIPE ABSEN --}}
                                <td class="text-center">

                                    @if ($a->reason_change_status)
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


                                {{-- ABSEN MASUK --}}
                                <td class="text-body-secondary small">

                                    @if ($a->check_in_time)
                                        <div class="fw-bold text-body">

                                            {{ $a->check_in_time->format('H\:i') }}
                                            WIB

                                        </div>

                                        <div class="text-muted" style="font-size: 0.7rem;">

                                            {{ $a->check_in_time->format('d M Y') }}

                                        </div>
                                    @else
                                        <span class="text-muted small">
                                            -
                                        </span>
                                    @endif

                                </td>


                                {{-- ABSEN PULANG --}}
                                <td class="text-body-secondary small">

                                    @if ($a->check_out_time)
                                        <div class="fw-bold text-body">

                                            {{ $a->check_out_time->format('H\:i') }}
                                            WIB

                                        </div>

                                        <div class="text-muted" style="font-size: 0.7rem;">

                                            {{ $a->check_out_time->format('d M Y') }}

                                        </div>
                                    @else
                                        <span class="text-danger small italic">
                                            Belum Pulang
                                        </span>
                                    @endif

                                </td>


                                {{-- AKSI --}}
                                <td class="text-center">

                                    <span>

                                        {{-- Lupa Absen --}}
                                        <a href="{{ route('admin.laporan.editLupaAbsen', $a->id) }}"
                                            class="btn btn-sm btn-outline-warning shadow-sm me-1" title="Lupa Absen">

                                            <i class="bi bi-clock-history"></i>

                                        </a>


                                        {{-- Ubah Status --}}
                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                            onclick="openEditModal(
                                                    '{{ route('admin.absensi.updateStatus', $a->id) }}',
                                                    '{{ $a->tipe_absen }}',
                                                    '{{ addslashes($a->reason_change_status) }}'
                                                )"
                                            title="Ubah Status">

                                            <i class="bi bi-pencil-square"></i>

                                        </button>

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="9" class="text-center text-body-secondary py-5">

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


    {{-- =========================================================
     * MODAL EDIT STATUS
     * ========================================================= --}}
    <div class="modal fade" id="modalEditStatus" tabindex="-1" aria-hidden="true">

        <div class="modal-dialog">

            <form id="formEditStatus" method="POST">

                @csrf
                @method('PUT')

                <div class="modal-content">

                    <div class="modal-header">

                        <h5 class="modal-title">
                            Ubah Tipe Absensi
                        </h5>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>

                    </div>


                    <div class="modal-body">

                        <div class="mb-3">

                            <label class="form-label">
                                Tipe Absen Baru
                            </label>

                            <select name="tipe_absen" id="edit_tipe_absen" class="form-select" required>

                                <option value="WFO">
                                    WFO (Work From Office)
                                </option>

                                <option value="WFA">
                                    WFA (Work From Anywhere)
                                </option>

                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                Alasan Perubahan
                            </label>

                            <textarea id="edit_reason" name="reason_change_status" class="form-control w-100" rows="3"
                                placeholder="Contoh: Kesalahan sistem saat pemilihan lokasi" required minlength="5"></textarea>

                            <small class="text-muted">
                                Wajib diisi sebagai log audit.
                            </small>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                            Batal

                        </button>

                        <button type="submit" class="btn btn-primary">

                            Simpan Perubahan

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

@endsection


@push('scripts')
    {{-- =========================================================
     * CDN jQuery & Select2
     * ========================================================= --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        $(document).ready(function() {

            /* =====================================================
             * SELECT2 TIM KERJA
             * ===================================================== */

            if ($('#select-tim-kerja').length) {

                $('#select-tim-kerja').select2({

                    placeholder: "Pilih Tim Kerja...",

                    closeOnSelect: false,

                    width: '100%',

                    templateResult: formatOptionWithCheckbox,

                    templateSelection: formatSelectionText

                });


                // Re-render selection text saat item dipilih / dihapus
                $('#select-tim-kerja').on('change', function() {

                    $(this).trigger('change.select2');

                });

            }


            /* =====================================================
             * CUSTOM RENDERER SELECT2
             * ===================================================== */

            function formatOptionWithCheckbox(option) {

                if (!option.id) {
                    return option.text;
                }

                var isChecked = option.selected ? 'checked' : '';

                var $option = $(
                    '<div class="select-checkbox-item">' +

                    '<input type="checkbox" ' +
                    isChecked +
                    ' />' +

                    '<span>' +
                    option.text +
                    '</span>' +

                    '</div>'
                );

                return $option;

            }


            /* =====================================================
             * CUSTOM LABEL SELECT2
             * ===================================================== */

            function formatSelectionText(option, container) {

                var selectedOptions =
                    $('#select-tim-kerja').val();

                if (
                    !selectedOptions ||
                    selectedOptions.length === 0
                ) {

                    return "Pilih Tim Kerja...";

                }

                var total =
                    $('#select-tim-kerja option').length;

                if (selectedOptions.length === total) {

                    return "Semua Tim Kerja Dipilih";

                }

                return selectedOptions.length +
                    " Tim Kerja Dipilih";

            }


            /* =====================================================
             * BULK DELETE
             * ===================================================== */

            const selectAllCheckbox =
                document.getElementById('select-all-attendance');

            const attendanceCheckboxes =
                document.querySelectorAll('.attendance-checkbox');

            const btnBulkDelete =
                document.getElementById('btn-bulk-delete');

            const selectedCountElement =
                document.getElementById('selected-count');

            const bulkDeleteContainer =
                document.getElementById('bulk-delete-container');

            const bulkDeleteForm =
                document.getElementById('form-bulk-delete');

            const bulkDeleteInputs =
                document.getElementById('bulk-delete-inputs');


            /**
             * Update UI berdasarkan checkbox terpilih
             */
            function updateBulkDeleteUI() {

                const checkedCheckboxes =
                    document.querySelectorAll(
                        '.attendance-checkbox:checked'
                    );

                const selectedCount =
                    checkedCheckboxes.length;


                // Update jumlah
                selectedCountElement.textContent =
                    selectedCount;


                // Aktif / nonaktif tombol hapus
                btnBulkDelete.disabled =
                    selectedCount === 0;


                // Tampilkan / sembunyikan badge jumlah
                if (selectedCount > 0) {

                    bulkDeleteContainer.classList.add('show');

                } else {

                    bulkDeleteContainer.classList.remove('show');

                }


                // Update checkbox "Pilih Semua"
                if (attendanceCheckboxes.length === 0) {

                    selectAllCheckbox.checked = false;

                    selectAllCheckbox.indeterminate = false;

                } else if (
                    selectedCount === attendanceCheckboxes.length
                ) {

                    selectAllCheckbox.checked = true;

                    selectAllCheckbox.indeterminate = false;

                } else if (selectedCount > 0) {

                    selectAllCheckbox.checked = false;

                    selectAllCheckbox.indeterminate = true;

                } else {

                    selectAllCheckbox.checked = false;

                    selectAllCheckbox.indeterminate = false;

                }


                // Highlight baris yang dipilih
                attendanceCheckboxes.forEach(function(checkbox) {

                    const row =
                        checkbox.closest('tr');

                    if (!row) {
                        return;
                    }

                    if (checkbox.checked) {

                        row.classList.add(
                            'attendance-row-selected'
                        );

                    } else {

                        row.classList.remove(
                            'attendance-row-selected'
                        );

                    }

                });

            }


            /* =====================================================
             * CHECKBOX PILIH SEMUA
             * ===================================================== */

            if (selectAllCheckbox) {

                selectAllCheckbox.addEventListener(
                    'change',
                    function() {

                        attendanceCheckboxes.forEach(
                            function(checkbox) {

                                checkbox.checked =
                                    selectAllCheckbox.checked;

                            }
                        );


                        updateBulkDeleteUI();

                    }
                );

            }


            /* =====================================================
             * CHECKBOX INDIVIDUAL
             * ===================================================== */

            attendanceCheckboxes.forEach(
                function(checkbox) {

                    checkbox.addEventListener(
                        'change',
                        function() {

                            updateBulkDeleteUI();

                        }
                    );

                }
            );


            /* =====================================================
             * SUBMIT BULK DELETE
             * ===================================================== */

            if (bulkDeleteForm) {

                bulkDeleteForm.addEventListener(
                    'submit',
                    function(event) {

                        const selectedCheckboxes =
                            document.querySelectorAll(
                                '.attendance-checkbox:checked'
                            );


                        // Tidak ada data yang dipilih
                        if (selectedCheckboxes.length === 0) {

                            event.preventDefault();

                            alert(
                                'Silakan pilih minimal satu riwayat kehadiran.'
                            );

                            return;

                        }


                        // Konfirmasi
                        const selectedCount =
                            selectedCheckboxes.length;


                        const confirmation =
                            confirm(
                                'Apakah Anda yakin ingin menghapus ' +
                                selectedCount +
                                ' riwayat kehadiran yang dipilih?\n\n' +
                                'Data yang sudah dihapus tidak dapat dikembalikan.'
                            );


                        if (!confirmation) {

                            event.preventDefault();

                            return;

                        }


                        /*
                         * Hapus hidden input lama
                         * agar tidak terjadi duplikasi.
                         */
                        bulkDeleteInputs.innerHTML = '';


                        /*
                         * Masukkan semua ID yang dipilih
                         * ke dalam form sebagai ids[]
                         */
                        selectedCheckboxes.forEach(
                            function(checkbox) {

                                const hiddenInput =
                                    document.createElement('input');

                                hiddenInput.type =
                                    'hidden';

                                hiddenInput.name =
                                    'ids[]';

                                hiddenInput.value =
                                    checkbox.value;

                                bulkDeleteInputs.appendChild(
                                    hiddenInput
                                );

                            }
                        );

                    }
                );

            }


            // Jalankan sekali ketika halaman selesai dimuat
            updateBulkDeleteUI();


            /* =====================================================
             * EXPORT PDF
             * ===================================================== */

            const btnExportPdf =
                document.getElementById('btn-export-pdf');


            if (btnExportPdf) {

                btnExportPdf.addEventListener(
                    'click',
                    function(e) {

                        e.preventDefault();


                        const filterForm =
                            document.getElementById(
                                'form-filter-laporan'
                            );


                        let startDate =
                            filterForm.querySelector(
                                'input[name="start_date"]'
                            )?.value || '';


                        let endDate =
                            filterForm.querySelector(
                                'input[name="end_date"]'
                            )?.value || '';


                        let printDate =
                            filterForm.querySelector(
                                'input[name="print_date"]'
                            )?.value || '';


                        const nip =
                            filterForm.querySelector(
                                '[name="nip"]'
                            )?.value || '';


                        const tipeAbsen =
                            filterForm.querySelector(
                                '[name="tipe_absen"]'
                            )?.value || '';


                        const locationId =
                            filterForm.querySelector(
                                '[name="location_id"]'
                            )?.value || '';


                        let params =
                            new URLSearchParams({

                                start_date: startDate,

                                end_date: endDate,

                                print_date: printDate,

                                nip: nip,

                                tipe_absen: tipeAbsen,

                                location_id: locationId

                            });


                        const selectedTeams =
                            $('#select-tim-kerja').val();


                        if (
                            selectedTeams &&
                            selectedTeams.length > 0
                        ) {

                            selectedTeams.forEach(
                                id => {

                                    params.append(
                                        'tim_kerja_ids[]',
                                        id
                                    );

                                }
                            );

                        }


                        let url =
                            "{{ route('admin.absensi.exportReportPdf') }}";


                        window.location.href =
                            url + '?' + params.toString();

                    }
                );

            }

        });


        /* =========================================================
         * FUNCTION MODAL EDIT STATUS
         * ========================================================= */

        function openEditModal(
            url,
            currentType,
            reason
        ) {

            document.getElementById(
                'formEditStatus'
            ).action = url;


            document.getElementById(
                'edit_tipe_absen'
            ).value = currentType;


            document.getElementById(
                'edit_reason'
            ).value = reason || '';


            var myModal =
                new bootstrap.Modal(
                    document.getElementById(
                        'modalEditStatus'
                    )
                );


            myModal.show();

        }
    </script>
@endpush
