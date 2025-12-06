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

    /* 2. Tombol Kuning Custom */
    .btn-admin-yellow {
        background-color: var(--theme-yellow);
        color: var(--text-dark);
        font-weight: 600;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3);
        transition: all 0.2s ease;
    }
    .btn-admin-yellow:hover {
        background-color: var(--theme-yellow-dark);
        color: var(--text-dark);
        transform: translateY(-2px);
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
    .form-control-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .form-control-admin:focus {
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

<div class="container-fluid p-4">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-person-badge-fill text-warning me-2"></i>Data Sales
            </h4>
            <div class="text-muted small">Kelola akun dan data sales</div>
        </div>
        
        <a href="{{ route('data-sales.create') }}" class="btn btn-admin-yellow">
            <i class="fas fa-plus me-1"></i> Tambah Sales
        </a>
    </div>

    {{-- FILTER CARD --}}
    <div class="card-admin p-3 mb-3">
        <div class="row g-2">
            <div class="col-12 col-md-5">
                <span class="filter-label">Pencarian</span>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;">
                        <i class="fas fa-search text-warning" style="font-size: 13px;"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control form-control-admin border-start-0" 
                           value="{{ request('search') }}"
                           style="border-radius: 0 8px 8px 0;"
                           placeholder="Cari nama / email sales...">
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        
        {{-- Loading Spinner --}}
        <div id="loading-spinner" class="text-center p-4" style="display: none;">
            <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
            <p class="mt-2 text-muted small">Memuat data...</p>
        </div>

        {{-- Table Container --}}
        <div id="table-container">
            <div class="table-responsive">
                <table class="table table-admin mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" width="5%">No</th>
                            <th>Nama Sales</th>
                            <th>No. Telepon</th>
                            <th>Username</th>
                            <th>Area</th>
                            <th>Komisi</th>
                            <th>Pelanggan</th>
                            <th class="text-center" width="100px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sales-table-body">
                        @include('sales.partials.table_rows', ['data' => $data])
                    </tbody>
                </table>
            </div>

            {{-- Pagination Consistent --}}
            <div class="pagination-wrapper" id="pagination-wrapper">
                {!! $data->links('pagination::bootstrap-5') !!}
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- SCRIPT ASLI TETAP --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        let timeout = null;
        let currentPage = {{ request('page', 1) }};

        function loadData(page = 1) {
            currentPage = page;

            $('#loading-spinner').show();
            $('#table-container').css('opacity', '0.5');

            const search = $('#search-input').val();

            $.ajax({
                url: '{{ route('data-sales.index') }}',
                type: 'GET',
                data: {
                    search: search,
                    page: page
                },
                success: function(response) {
                    $('#sales-table-body').html(response.html);
                    $('#pagination-wrapper').html(response.pagination);
                    $('#table-container').css('opacity', '1');
                    $('#loading-spinner').hide();

                    updateUrl(search, page);
                },
                error: function() {
                    $('#loading-spinner').hide();
                    $('#table-container').css('opacity', '1');
                    alert('Terjadi kesalahan saat memuat data.');
                }
            });
        }

        function updateUrl(search, page) {
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            if (page > 1) params.set('page', page);

            const newUrl = params.toString() ?
                '{{ route('data-sales.index') }}?' + params.toString() :
                '{{ route('data-sales.index') }}';

            window.history.replaceState({}, '', newUrl);
        }

        // Search debounce
        $('#search-input').on('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => loadData(1), 250);
        });

        // Pagination (delegasi)
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            if (!href) return;
            const page = new URL(href).searchParams.get('page') || 1;
            loadData(page);
        });

        // Klik tombol delete -> buka modal konfirmasi
        $(document).on('click', '.btn-delete', function() {
            const url = $(this).data('url');
            $('#deleteForm').attr('action', url);

            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });

    });
</script>
@endpush