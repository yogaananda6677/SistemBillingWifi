@extends('layouts.master')

@section('content')
<style>
    /* --- ADMIN YELLOW THEME VARIABLES --- */
    :root {
        --theme-yellow: #ffc107;       /* Kuning Bootstrap Warning */
        --theme-yellow-dark: #e0a800;  /* Kuning lebih gelap untuk hover */
        --theme-yellow-soft: #fff9e6;  /* Kuning sangat muda untuk background */
        --text-dark: #212529;
        --card-radius: 12px;
    }

    /* 1. Typography & Header */
    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    /* 2. Tombol Kuning Custom */
    .btn-admin-yellow {
        background-color: var(--theme-yellow);
        color: var(--text-dark);
        font-weight: 600;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3);
        transition: all 0.2s ease;
    }
    
    .btn-admin-yellow:hover {
        background-color: var(--theme-yellow-dark);
        color: var(--text-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(255, 193, 7, 0.4);
    }

    /* 3. Card Styles */
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow); /* Aksen Kuning di atas Card */
    }

    /* 4. Form Inputs (Filter) */
    .form-control-admin, .form-select-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 14px;
    }
    
    .form-control-admin:focus, .form-select-admin:focus {
        border-color: var(--theme-yellow);
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2); /* Glow Kuning */
    }

    /* 5. Table Styling */
    .table-admin thead th {
        background-color: var(--theme-yellow-soft);
        color: var(--text-dark);
        font-weight: 700;
        font-size: 13px;
        text-transform: uppercase;
        border-bottom: 2px solid var(--theme-yellow);
        padding: 15px;
        white-space: nowrap; /* Header satu baris */
    }

    .table-admin tbody td {
        padding: 15px;
        vertical-align: middle;
        font-size: 14px;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-admin tbody tr:hover td {
        background-color: #fffdf5; /* Efek hover kekuningan tipis */
    }

    /* 6. Statistik Badge */
    .stat-badge {
        background-color: var(--theme-yellow-soft);
        color: #856404;
        border: 1px solid #ffeeba;
        padding: 8px 16px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
    }

    /* 7. Loading Spinner Custom Color */
    .spinner-border.text-primary {
        color: var(--theme-yellow) !important; /* Paksa jadi kuning */
    }

    /* 8. PAGINATION KUNING (CONSISTENT COMPACT) */
    .pagination-wrapper {
        display: flex;
        justify-content: center !important;
        align-items: center;
        width: 100%;
        padding: 15px; /* <-- UBAH KE 15px BIAR SAMA DENGAN TAGIHAN */
        background: #fff; /* Pastikan background putih */
        border-top: 1px solid #f0f0f0;
    }

    .pagination-wrapper nav .d-none.d-sm-flex > div:first-child {
        display: none !important; /* Hapus teks Showing */
    }

    .pagination-wrapper nav .d-none.d-sm-flex {
        justify-content: center !important;
    }

    /* Override warna pagination bawaan Bootstrap */
    .page-item .page-link {
        color: #333;
        border: none;
        margin: 0 2px; /* <-- UBAH KE 2px BIAR RAPAT */
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px; /* <-- UKURAN FONT DIKECILKAN */
        padding: 6px 12px; /* <-- PADDING DIKECILKAN */
        background: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .page-item.active .page-link {
        background-color: var(--theme-yellow) !important;
        border-color: var(--theme-yellow) !important;
        color: #000 !important;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.4);
    }
</style>

<div class="container-fluid p-4" id="page-wrapper" data-status="{{ request('status') }}">
    
    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-people-fill text-warning me-2"></i>Data Pelanggan
            </h4>
            <div class="text-muted small">Kelola data pelanggan dan layanan</div>
        </div>
        
        <div class="d-flex gap-2 align-items-center">
            <span class="stat-badge">
                <i class="bi bi-folder2-open me-1"></i> Total: {{ $totalPelanggan }}
            </span>
            <a href="{{ route('pelanggan.create') }}" class="btn btn-admin-yellow">
                <i class="bi bi-plus-lg me-1"></i> Tambah Data
            </a>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card-admin p-4 mb-4">
        <div class="row g-3">
            {{-- Search Box --}}
            <div class="col-12 col-md-5">
                <label class="form-label fw-bold text-muted small">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px; border-color: #dee2e6;">
                        <i class="bi bi-search text-warning"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control form-control-admin border-start-0" 
                           style="border-radius: 0 8px 8px 0;"
                           placeholder="Cari nama, NIK, IP, HP...">
                </div>
            </div>

            {{-- Filter Sales --}}
            <div class="col-12 col-md-3">
                <label class="form-label fw-bold text-muted small">Filter Sales</label>
                <select class="form-select form-select-admin" id="sales-filter">
                    <option value="">Semua Sales</option>
                    @foreach($dataSales as $sales)
                        <option value="{{ $sales->id_sales }}">{{ $sales->user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Wilayah --}}
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold text-muted small">Filter Wilayah</label>
                <select class="form-select form-select-admin" id="area-filter">
                    <option value="">Semua Wilayah</option>
                    @foreach($dataArea as $area)
                        <option value="{{ $area->id_area }}">{{ $area->nama_area }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        
        {{-- Loading Spinner --}}
        <div id="loading-spinner" class="loading-spinner" style="display: none; padding: 50px; text-align: center;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted fw-semibold">Memuat data...</p>
        </div>

        {{-- Table Container --}}
        <div id="table-container">
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" width="5%">No</th>
                            <th>Nama</th>
                            <th>Area</th>
                            <th>Sales</th>
                            <th>Paket Layanan</th>
                            <th>Tanggal Aktif</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pelanggan-table-body">
                        @include('pelanggan.partials.table_rows', ['pelanggan' => $pelanggan])
                    </tbody>
                </table>
            </div>

            {{-- Pagination Kuning (Compact) --}}
            <div class="pagination-wrapper bg-white border-top" id="pagination-wrapper">
                {{ $pelanggan->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- SCRIPT ASLI TIDAK DIUBAH SAMA SEKALI --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentPage   = 1;
    let currentAjax   = null; 

    function loadData(page = 1) {
        currentPage = page;

        if (currentAjax !== null) {
            currentAjax.abort();
        }

        $('#loading-spinner').show();
        $('#table-container').hide();

        const search = $('#search-input').val();
        const area   = $('#area-filter').val();
        const sales  = $('#sales-filter').val();

        currentAjax = $.ajax({
            url: '{{ route("pelanggan.index") }}',
            type: 'GET',
            cache: false, 
            data: {
                search: search,
                area:   area,
                sales:  sales,
                page:   page,
                ajax:   true
            },
            success: function(response) {
                $('#pelanggan-table-body').html(response.html);
                $('#pagination-wrapper').html(response.pagination);
                $('#table-container').show();
                $('#loading-spinner').hide();

                updateUrl(search, area, sales, page);
            },
            error: function(xhr, status) {
                if (status === 'abort') return;

                $('#loading-spinner').hide();
                $('#table-container').show();
                console.error('Error:', xhr);
                alert('Terjadi kesalahan saat memuat data');
            },
            complete: function() {
                currentAjax = null;
            }
        });
    }

    function updateUrl(search, area, sales, page) {
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (area)   params.set('area', area);
        if (sales)  params.set('sales', sales);
        if (page > 1) params.set('page', page);

        const newUrl = params.toString()
            ? '{{ route("pelanggan.index") }}?' + params.toString()
            : '{{ route("pelanggan.index") }}';

        window.history.replaceState({}, '', newUrl);
    }

    // SEARCH REALTIME
    $('#search-input').on('input', function() {
        loadData(1);
    });

    // FILTER AREA
    $('#area-filter').on('change', function() {
        loadData(1);
    });

    // FILTER SALES
    $('#sales-filter').on('change', function() {
        loadData(1);
    });

    // PAGINATION
    $(document).on('click', '.custom-pagination a', function(e) { // Jaga-jaga kalau class custom dipakai
        e.preventDefault();
        const url  = new URL($(this).attr('href'));
        const page = url.searchParams.get('page') || 1;
        loadData(page);
    });
    
    // Pagination Normal Bootstrap
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if(href){
            const url  = new URL(href);
            const page = url.searchParams.get('page') || 1;
            loadData(page);
        }
    });

    // KLIK HAPUS
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const deleteForm = $('#deleteForm');
        deleteForm.attr('action', url);

        const deleteModal = new bootstrap.Modal($('#deleteModal')[0]);
        deleteModal.show();
    });

    // SUBMIT HAPUS (AJAX)
    $('#deleteForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const submitButton = form.find('button[type="submit"]');

        submitButton.prop('disabled', true).text('Menghapus...');

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize() + '&_method=DELETE',
            success: function(response) {
                const deleteModal = bootstrap.Modal.getInstance($('#deleteModal')[0]);
                deleteModal.hide();
                submitButton.prop('disabled', false).text('Hapus');
                loadData(currentPage); 
            },
            error: function(xhr) {
                submitButton.prop('disabled', false).text('Hapus');
                console.error(xhr.responseText);
                alert('Terjadi kesalahan saat menghapus data');
            }
        });
    });

    function loadInitialFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        const search = urlParams.get('search');
        const area   = urlParams.get('area');
        const sales  = urlParams.get('sales');
        const page   = urlParams.get('page');

        if (search) $('#search-input').val(search);
        if (area)   $('#area-filter').val(area);
        if (sales)  $('#sales-filter').val(sales);

        loadData(page || 1);
    }

    loadInitialFilters();
});
</script>
@endpush