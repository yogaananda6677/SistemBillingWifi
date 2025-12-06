@extends('layouts.master')

@section('content')
<style>
    /* --- ADMIN YELLOW THEME (CONSISTENT COMPACT) --- */
    :root {
        --theme-yellow: #ffc107;
        --theme-yellow-dark: #e0a800;
        --theme-yellow-soft: #fff9e6;
        --text-dark: #212529;
        --card-radius: 12px;
    }

    /* 1. Typography */
    .page-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    /* 2. Tombol & Tab Status */
    .btn-tab-active {
        background-color: var(--theme-yellow);
        color: #000;
        font-weight: 700;
        border: 1px solid var(--theme-yellow);
    }
    .btn-tab-inactive {
        background-color: #fff;
        color: #666;
        border: 1px solid #dee2e6;
    }
    .btn-tab-inactive:hover {
        background-color: var(--theme-yellow-soft);
        color: #000;
        border-color: var(--theme-yellow);
    }

    /* 3. Card Styles */
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow);
        width: 100%;
    }

    /* 4. Form Inputs */
    .form-control-admin, .form-select-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .form-control-admin:focus, .form-select-admin:focus {
        border-color: var(--theme-yellow);
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    }

    /* 5. Table Styling (COMPACT) */
    .table-admin {
        width: 100%;
        margin-bottom: 0;
    }

    .table-admin thead th {
        background-color: var(--theme-yellow-soft);
        color: var(--text-dark);
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        border-bottom: 2px solid var(--theme-yellow);
        padding: 12px 10px;
        white-space: nowrap;
    }

    .table-admin tbody td {
        padding: 10px;
        vertical-align: middle;
        font-size: 13px;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-admin tbody tr:hover td {
        background-color: #fffdf5;
    }

    /* 6. Pagination Styling (Yellow & Consistent) */
    .pagination-wrapper {
        display: flex;
        justify-content: center !important;
        align-items: center;
        width: 100%;
        padding: 15px; 
        background: #fff;
        border-top: 1px solid #f0f0f0;
    }

    .pagination-wrapper nav .d-none.d-sm-flex > div:first-child {
        display: none !important; 
    }
    .pagination-wrapper nav .d-none.d-sm-flex {
        justify-content: center !important;
    }

    .page-item .page-link {
        color: #333;
        border: none;
        margin: 0 2px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
        padding: 6px 12px;
        background: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .page-item.active .page-link {
        background-color: var(--theme-yellow) !important;
        border-color: var(--theme-yellow) !important;
        color: #000 !important;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);
    }
    
    /* Label Filter Kecil */
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 4px;
        display: block;
    }
</style>

<div class="container-fluid p-4" id="page-wrapper">
    
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-lines-fill text-warning me-2"></i>Status Pelanggan
            </h4>
            <div class="text-muted small">Pantau status aktif, isolir, atau berhenti</div>
        </div>
    </div>

    @php
        $statusHalaman = $status ?? 'aktif';
        $totalBaru     = $statusCounts['baru']     ?? 0;
        $totalAktif    = $statusCounts['aktif']    ?? 0;
        $totalBerhenti = $statusCounts['berhenti'] ?? 0;
        $totalIsolir   = $statusCounts['isolir']   ?? 0;
    @endphp

    {{-- TAB STATUS (DESIGN BARU) --}}
    <div class="mb-4">
        <div class="btn-group shadow-sm" role="group" style="border-radius: 8px; overflow: hidden;">
            <a href="{{ route('pelanggan.status', ['status' => 'baru']) }}"
               class="btn btn-sm px-4 py-2 {{ $statusHalaman === 'baru' ? 'btn-tab-active' : 'btn-tab-inactive' }}">
               Baru ({{ $totalBaru }})
            </a>
            <a href="{{ route('pelanggan.status', ['status' => 'aktif']) }}"
               class="btn btn-sm px-4 py-2 {{ $statusHalaman === 'aktif' ? 'btn-tab-active' : 'btn-tab-inactive' }}">
               Aktif ({{ $totalAktif }})
            </a>
            <a href="{{ route('pelanggan.status', ['status' => 'isolir']) }}"
               class="btn btn-sm px-4 py-2 {{ $statusHalaman === 'isolir' ? 'btn-tab-active' : 'btn-tab-inactive' }}">
               Isolir ({{ $totalIsolir }})
            </a>
            <a href="{{ route('pelanggan.status', ['status' => 'berhenti']) }}"
               class="btn btn-sm px-4 py-2 {{ $statusHalaman === 'berhenti' ? 'btn-tab-active' : 'btn-tab-inactive' }}">
               Berhenti ({{ $totalBerhenti }})
            </a>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card-admin p-3 mb-3">
        {{-- HIDDEN INPUT STATUS --}}
        <input type="hidden" id="status-hidden" value="{{ $statusHalaman }}">

        <div class="row g-2">
            <div class="col-12 col-md-5">
                <span class="filter-label">Pencarian</span>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;">
                        <i class="bi bi-search text-warning" style="font-size: 13px;"></i>
                    </span>
                    <input type="text" id="search-input-status" class="form-control form-control-admin border-start-0" 
                           value="{{ request('search') }}"
                           style="border-radius: 0 8px 8px 0;"
                           placeholder="Cari pelanggan / paket...">
                </div>
            </div>

            <div class="col-6 col-md-3">
                <span class="filter-label">Sales</span>
                <select id="sales-filter-status" class="form-select form-select-admin">
                    <option value="">Semua Sales</option>
                    @foreach($dataSales as $s)
                        <option value="{{ $s->id_sales }}" {{ request('sales') == $s->id_sales ? 'selected' : '' }}>
                            {{ $s->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-4">
                <span class="filter-label">Wilayah</span>
                <select id="area-filter-status" class="form-select form-select-admin">
                    <option value="">Semua Wilayah</option>
                    @foreach($dataArea as $area)
                        <option value="{{ $area->id_area }}" {{ request('area') == $area->id_area ? 'selected' : '' }}>
                            {{ $area->nama_area }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-admin mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No</th>
                        <th>Nama</th>
                        <th>Area</th>
                        <th>Sales</th>
                        <th>Paket & Harga</th>
                        <th>
                            @if($statusHalaman === 'baru') Tgl Registrasi
                            @elseif($statusHalaman === 'aktif') Tgl Aktif
                            @elseif($statusHalaman === 'berhenti') Tgl Berhenti
                            @elseif($statusHalaman === 'isolir') Tgl Isolir
                            @else Tanggal
                            @endif
                        </th>
                        <th>IP</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="pelanggan-status-body">
                    @include('pelanggan.partials.table_rows_status', ['pelanggan' => $pelanggan])
                </tbody>
            </table>
        </div>

        {{-- PAGINATION CONSISTENT --}}
        <div class="pagination-wrapper" id="status-pagination">
            {{ $pelanggan->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- SCRIPT ASLI TETAP JALAN --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {

    let currentPage = 1;
    let currentAjax = null;

    function loadStatusData(page = 1) {
        currentPage = page;

        if (currentAjax !== null) {
            currentAjax.abort();
        }

        const status = $('#status-hidden').val() || 'aktif';
        const search = $('#search-input-status').val();
        const area   = $('#area-filter-status').val();
        const sales  = $('#sales-filter-status').val();

        currentAjax = $.ajax({
            url: '{{ route("pelanggan.status") }}',
            type: 'GET',
            cache: false,
            data: {
                status: status,
                search: search,
                area:   area,
                sales:  sales,
                page:   page,
                ajax:   true
            },
            success: function (response) {
                $('#pelanggan-status-body').html(response.html);
                $('#status-pagination').html(response.pagination);
                updateUrl(status, search, area, sales, page);
            },
            error: function (xhr, textStatus) {
                if (textStatus === 'abort') return;
                console.error(xhr.responseText);
                alert('Terjadi kesalahan saat memuat data.');
            },
            complete: function () {
                currentAjax = null;
            }
        });
    }

    function updateUrl(status, search, area, sales, page) {
        const params = new URLSearchParams();
        if (status) params.set('status', status);
        if (search) params.set('search', search);
        if (area)   params.set('area', area);
        if (sales)  params.set('sales', sales);
        if (page > 1) params.set('page', page);

        const newUrl = params.toString()
            ? '{{ route("pelanggan.status") }}?' + params.toString()
            : '{{ route("pelanggan.status") }}';

        window.history.replaceState({}, '', newUrl);
    }

    // EVENT LISTENERS
    $('#search-input-status').on('input', function () {
        loadStatusData(1);
    });

    $('#area-filter-status, #sales-filter-status').on('change', function () {
        loadStatusData(1);
    });

    $(document).on('click', '#status-pagination .pagination a', function (e) {
        e.preventDefault();
        const url  = new URL($(this).attr('href'));
        const page = url.searchParams.get('page') || 1;
        loadStatusData(page);
    });

    (function loadInitial() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status') || $('#status-hidden').val() || 'aktif';
        const search = urlParams.get('search');
        const area   = urlParams.get('area');
        const sales  = urlParams.get('sales');
        const page   = urlParams.get('page') || 1;

        $('#status-hidden').val(status);
        if (search) $('#search-input-status').val(search);
        if (area)   $('#area-filter-status').val(area);
        if (sales)  $('#sales-filter-status').val(sales);

        loadStatusData(page);
    })();

    // Script Delete Modal (Jika ada)
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;
        e.preventDefault();
        const url = btn.dataset.url;
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = url;
        const modalEl = document.getElementById('deleteModal');
        const deleteModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        deleteModal.show();
    });

    $('#deleteForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const url  = form.attr('action');
        const btn  = form.find('button[type="submit"]');
        btn.prop('disabled', true).text('Menghapus...');
        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize() + '&_method=DELETE',
            success: function (res) {
                const modalEl = document.getElementById('deleteModal');
                const deleteModal = bootstrap.Modal.getInstance(modalEl);
                deleteModal.hide();
                btn.prop('disabled', false).text('Hapus');
                loadStatusData(currentPage);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Gagal menghapus data.');
                btn.prop('disabled', false).text('Hapus');
            }
        });
    });

});
</script>
@endpush