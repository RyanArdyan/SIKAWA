@extends('layouts.admin')

@section('header', 'Laporan Lembur Pegawai')

@push('styles')
    {{-- CSS Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* =========================================================
         * SELECT2 CUSTOMIZATION
         * ========================================================= */
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

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: block !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            padding-left: 0 !important;
            color: var(--bs-body-color, #fff);
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            display: none !important;
        }

        .select2-container--default .select2-search--inline .select2-search__field {
            display: none !important;
        }

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

        .select2-dropdown {
            border-color: var(--bs-border-color, #495057) !important;
            border-radius: 6px;
            overflow: hidden;
        }

        /* =========================================================
         * BULK DELETE
         * ========================================================= */
        .lembur-select-checkbox, .lembur-select-all {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #dc3545;
        }

        .lembur-row-selected {
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
            .bulk-delete-toolbar, .bulk-delete-toolbar form, #btn-bulk-delete {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $isOperatorLaporan = auth()->check() && auth()->user()->role === 'operator_laporan';
    @endphp

    {{-- ALERT SUCCESS --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- =========================================================
     * FORM FILTER LAPORAN LEMBUR
     * ========================================================= --}}
    <div class="card border-0 shadow-sm mb-4 bg-body-tertiary">
        <div class="card-body p-4">
            <form id="form-filter-lembur" action="{{ route('admin.lembur.report') }}" method="GET" class="row g-3">

                {{-- BARIS 1 --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">Cari Pegawai</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body border-secondary-subtle border-end-0">
                            <i class="bi bi-search text-body-secondary"></i>
                        </span>
                        <input type="text" name="nip" class="form-control bg-body border-secondary-subtle text-body border-start-0"
                            placeholder="NIP atau Nama..." value="{{ $nip ?? '' }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">Tim Kerja (Bisa Pilih Banyak)</label>
                    <select name="tim_kerja_ids[]" id="select-tim-kerja" class="form-select bg-body border-secondary-subtle text-body" multiple>
                        @foreach ($timKerjas as $tim)
                            <option value="{{ $tim->id }}" {{ in_array($tim->id, (array) ($timKerjaIds ?? [])) ? 'selected' : '' }}>
                                {{ $tim->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">Status Lembur</label>
                    <select name="status" class="form-select bg-body border-secondary-subtle text-body">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ ($status ?? '') == 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                        <option value="disetujui" {{ ($status ?? '') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ ($status ?? '') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control bg-body border-secondary-subtle text-body" value="{{ $start_date }}">
                </div>

                {{-- BARIS 2 --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold text-body-secondary">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control bg-body border-secondary-subtle text-body" value="{{ $end_date }}">
                </div>

                @if (auth()->check() && auth()->user()->role === 'super_admin')
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-body-secondary">Tanggal Cetak</label>
                        <input type="date" name="print_date" class="form-control bg-body border-secondary-subtle text-body" value="{{ $print_date ?? date('Y-m-d') }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                @else
                    <div class="col-md-9 d-flex align-items-end gap-2">
                @endif

                    <button type="submit" class="btn text-white shadow-sm fw-bold text-nowrap w-100"
                        style="background-color: #40BF89; border: none; height: 38px; max-width: 150px;">
                        <i class="bi bi-filter"></i> Filter
                    </button>

                    @if ($lemburs->count() > 0)
                        <a href="#" id="btn-export-pdf" class="btn btn-danger shadow-sm fw-bold text-nowrap w-100"
                            style="height: 38px; max-width: 150px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-file-pdf me-1"></i> PDF
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- =========================================================
     * TABEL RIWAYAT LEMBUR
     * ========================================================= --}}
    <div class="card border-0 shadow-sm bg-body-tertiary">
        <div class="card-header bg-transparent py-3" style="border-top: 5px solid #20c997;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold text-body">
                        <i class="bi bi-clock-history me-2 text-success"></i>Riwayat Lembur Pegawai
                    </h5>
                </div>

                <div class="bulk-delete-toolbar">
                    @unless ($isOperatorLaporan)
                        <div id="bulk-delete-container">
                            <span class="badge bg-danger px-3 py-2">
                                <i class="bi bi-check2-square me-1"></i>
                                <span id="selected-count">0</span> data dipilih
                            </span>
                        </div>

                        <form id="form-bulk-delete" action="{{ route('admin.lembur.bulkDelete') }}" method="POST" class="m-0">
                            @csrf
                            @method('DELETE')
                            <div id="bulk-delete-inputs"></div>
                            <button type="submit" id="btn-bulk-delete" class="btn btn-danger shadow-sm fw-bold" disabled>
                                <i class="bi bi-trash3 me-1"></i> Hapus Terpilih
                            </button>
                        </form>
                    @endunless
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            @unless ($isOperatorLaporan)
                                <th class="py-3 text-body-secondary text-center" style="width: 50px;">
                                    <input type="checkbox" id="select-all-lembur" class="lembur-select-all" title="Pilih semua">
                                </th>
                            @endunless
                            <th class="py-3 text-body-secondary">No</th>
                            <th class="py-3 text-body-secondary">Nama Pegawai</th>
                            <th class="py-3 text-body-secondary text-center">Masuk Lembur</th>
                            <th class="py-3 text-body-secondary text-center">Pulang Lembur</th>
                            <th class="py-3 text-body-secondary text-center">Laporan PDF</th>
                            <th class="py-3 text-body-secondary text-center">Status Approval</th>
                            @unless ($isOperatorLaporan)
                                <th class="py-3 text-body-secondary text-center">Aksi</th>
                            @endunless
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lemburs as $key => $l)
                            <tr class="lembur-row">
                                @unless ($isOperatorLaporan)
                                    <td class="text-center">
                                        <input type="checkbox" name="lembur_ids[]" value="{{ $l->id }}" class="lembur-checkbox">
                                    </td>
                                @endunless

                                <td class="text-body-secondary small">
                                    {{ $lemburs->firstItem() + $key }}
                                </td>

                                <td class="fw-bold text-body">
                                    <div>{{ $l->user->name ?? 'User Terhapus' }}</div>
                                    <small class="text-muted fw-normal" style="font-size: 0.75rem;">
                                        NIP: {{ $l->user->nip ?? '-' }}
                                    </small><br>
                                    <small class="fw-bold" style="font-size: 0.75rem; color: #20c997;">
                                        <i class="bi bi-people-fill small"></i> {{ $l->user->tim_kerja->nama ?? 'Tanpa Tim' }}
                                    </small>
                                </td>

                                {{-- MASUK LEMBUR --}}
                                <td class="text-center small">
                                    @if ($l->check_in_lembur)
                                        <div class="fw-bold text-body">{{ \Carbon\Carbon::parse($l->check_in_lembur)->format('H:i') }} WIB</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($l->check_in_lembur)->format('d M Y') }}</div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                {{-- PULANG LEMBUR --}}
                                <td class="text-center small">
                                    @if ($l->check_out_lembur)
                                        <div class="fw-bold text-body">{{ \Carbon\Carbon::parse($l->check_out_lembur)->format('H:i') }} WIB</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">{{ \Carbon\Carbon::parse($l->check_out_lembur)->format('d M Y') }}</div>
                                    @else
                                        <span class="text-danger small italic">Belum Pulang</span>
                                    @endif
                                </td>

                                {{-- LAPORAN PDF --}}
                                <td class="text-center">
                                    @if ($l->laporan_pdf)
                                        <a href="{{ asset('storage/' . $l->laporan_pdf) }}" target="_blank" class="btn btn-sm btn-outline-danger px-2 py-1">
                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> lihat PDF
                                        </a>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 fw-normal">
                                            Belum Ada
                                        </span>
                                    @endif
                                </td>

                                {{-- STATUS APPROVAL --}}
                                <td class="text-center">
                                    @if ($l->status == 'disetujui')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i> Disetujui
                                        </span>
                                    @elseif($l->status == 'ditolak')
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">
                                            <i class="bi bi-x-circle me-1"></i> Ditolak
                                        </span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </span>
                                    @endif
                                </td>

                                {{-- AKSI --}}
                                @unless ($isOperatorLaporan)
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                            onclick="openEditStatusModal('{{ route('admin.lembur.updateStatus', $l->id) }}', '{{ $l->status }}')"
                                            title="Ubah Status Approval">
                                            <i class="bi bi-pencil-square me-1"></i> Status
                                        </button>
                                    </td>
                                @endunless
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isOperatorLaporan ? 6 : 8 }}" class="text-center text-body-secondary py-5">
                                    <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
                                    Tidak ada data lembur yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- PAGINASI --}}
                @if ($lemburs->hasPages())
                    <div class="d-flex justify-content-between align-items-center flex-wrap mt-4 gap-3">
                        <div class="text-body-secondary small">
                            Menampilkan <strong>{{ $lemburs->firstItem() }}</strong> - <strong>{{ $lemburs->lastItem() }}</strong> dari <strong>{{ $lemburs->total() }}</strong> data
                        </div>
                        <div>
                            {{ $lemburs->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- =========================================================
     * MODAL UBAH STATUS APPROVAL
     * ========================================================= --}}
    <div class="modal fade" id="modalEditStatusLembur" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="formEditStatusLembur" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Ubah Status Approval Lembur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Status Baru</label>
                            <select name="status" id="edit_status_lembur" class="form-select" required>
                                <option value="pending">Pending (Menunggu)</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success text-white">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- CDN jQuery & Select2 --}}
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

                $('#select-tim-kerja').on('change', function() {
                    $(this).trigger('change.select2');
                });
            }

            function formatOptionWithCheckbox(option) {
                if (!option.id) return option.text;
                var isChecked = option.selected ? 'checked' : '';
                return $('<div class="select-checkbox-item"><input type="checkbox" ' + isChecked + ' /><span>' + option.text + '</span></div>');
            }

            function formatSelectionText(option, container) {
                var selectedOptions = $('#select-tim-kerja').val();
                if (!selectedOptions || selectedOptions.length === 0) return "Pilih Tim Kerja...";
                var total = $('#select-tim-kerja option').length;
                if (selectedOptions.length === total) return "Semua Tim Kerja Dipilih";
                return selectedOptions.length + " Tim Kerja Dipilih";
            }

            /* =====================================================
             * BULK DELETE HANDLER
             * ===================================================== */
            const selectAllCheckbox = document.getElementById('select-all-lembur');
            const lemburCheckboxes = document.querySelectorAll('.lembur-checkbox');
            const btnBulkDelete = document.getElementById('btn-bulk-delete');
            const selectedCountElement = document.getElementById('selected-count');
            const bulkDeleteContainer = document.getElementById('bulk-delete-container');
            const bulkDeleteForm = document.getElementById('form-bulk-delete');
            const bulkDeleteInputs = document.getElementById('bulk-delete-inputs');

            function updateBulkDeleteUI() {
                const checkedCheckboxes = document.querySelectorAll('.lembur-checkbox:checked');
                const selectedCount = checkedCheckboxes.length;

                if (selectedCountElement) selectedCountElement.textContent = selectedCount;
                if (btnBulkDelete) btnBulkDelete.disabled = selectedCount === 0;

                if (selectedCount > 0) {
                    bulkDeleteContainer?.classList.add('show');
                } else {
                    bulkDeleteContainer?.classList.remove('show');
                }

                if (selectAllCheckbox) {
                    if (lemburCheckboxes.length === 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    } else if (selectedCount === lemburCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else if (selectedCount > 0) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    }
                }

                lemburCheckboxes.forEach(function(cb) {
                    const row = cb.closest('tr');
                    if (row) {
                        if (cb.checked) row.classList.add('lembur-row-selected');
                        else row.classList.remove('lembur-row-selected');
                    }
                });
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    lemburCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
                    updateBulkDeleteUI();
                });
            }

            lemburCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkDeleteUI);
            });

            if (bulkDeleteForm) {
                bulkDeleteForm.addEventListener('submit', function(e) {
                    const selectedCheckboxes = document.querySelectorAll('.lembur-checkbox:checked');
                    if (selectedCheckboxes.length === 0) {
                        e.preventDefault();
                        alert('Pilih minimal satu data lembur.');
                        return;
                    }

                    if (!confirm('Apakah Anda yakin ingin menghapus ' + selectedCheckboxes.length + ' data lembur terpilih?\n\nData yang sudah dihapus tidak dapat dikembalikan.')) {
                        e.preventDefault();
                        return;
                    }

                    bulkDeleteInputs.innerHTML = '';
                    selectedCheckboxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = cb.value;
                        bulkDeleteInputs.appendChild(input);
                    });
                });
            }

            updateBulkDeleteUI();

            /* =====================================================
             * EXPORT PDF (MENGAMBIL SEMUA FILTER SAAT INI)
             * ===================================================== */
            const btnExportPdf = document.getElementById('btn-export-pdf');

            if (btnExportPdf) {
                btnExportPdf.addEventListener('click', function(e) {
                    e.preventDefault();

                    const filterForm = document.getElementById('form-filter-lembur');
                    let params = new URLSearchParams();

                    // Ambil value dari form
                    let startDate = filterForm.querySelector('input[name="start_date"]')?.value || '';
                    let endDate = filterForm.querySelector('input[name="end_date"]')?.value || '';
                    let printDate = filterForm.querySelector('input[name="print_date"]')?.value || '';
                    let nip = filterForm.querySelector('[name="nip"]')?.value || '';
                    let status = filterForm.querySelector('[name="status"]')?.value || '';

                    // Append ke params URL
                    params.append('start_date', startDate);
                    params.append('end_date', endDate);
                    params.append('print_date', printDate);
                    params.append('nip', nip);
                    params.append('status', status);

                    // Ambil Multiple Select (Tim Kerja)
                    const selectedTeams = $('#select-tim-kerja').val();
                    if (selectedTeams && selectedTeams.length > 0) {
                        selectedTeams.forEach(id => {
                            params.append('tim_kerja_ids[]', id);
                        });
                    }

                    // Arahkan ke URL Cetak PDF beserta query parameters-nya
                    let url = "{{ route('admin.lembur.exportReportPdf') }}";
                    window.location.href = url + '?' + params.toString();
                });
            }
        });

        /* =========================================================
         * FUNCTION OPEN MODAL EDIT STATUS
         * ========================================================= */
        function openEditStatusModal(actionUrl, currentStatus) {
            document.getElementById('formEditStatusLembur').action = actionUrl;
            document.getElementById('edit_status_lembur').value = currentStatus;

            var modal = new bootstrap.Modal(document.getElementById('modalEditStatusLembur'));
            modal.show();
        }
    </script>
@endpush
