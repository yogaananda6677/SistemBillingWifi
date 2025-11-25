@extends('layouts.master')

@section('content')
<style>
    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #222;
    }

    .search-box input {
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 8px 14px;
        font-size: 14px;
    }

    .filter-select {
        border-radius: 10px;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ddd;
        background: white;
    }

    .table-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    table thead th {
        background: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        padding: 10px;
    }

    table tbody td {
        font-size: 13px;
        padding: 10px;
    }

    table tbody tr:hover {
        background: #f4f4f4;
    }

    .loading-spinner {
        display: none;
        text-align: center;
        padding: 20px;
    }

    .no-results {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-style: italic;
    }

    .pagination-wrapper {
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    .table-responsive {
        min-height: 400px;
    }
</style>

<div class="container-fluid p-4" id="page-wrapper" data-status="{{ request('status') }}">
{{-- TITLE --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="page-title">Pelanggan</h4>
</div>
<div class="mb-3">
    <span class="badge bg-primary" style="font-size: 14px; padding: 8px 14px;">
        Total Pelanggan: {{ $totalPelanggan }}
    </span>
</div>

    {{-- SEARCH & FILTER --}}
    <div class="d-flex gap-3 mb-4 flex-wrap">
        <div class="search-box flex-grow-1" style="min-width: 250px;">
            <input type="text" id="search-input" class="form-control" placeholder="Cari pelanggan (nama, NIK, IP, HP, wilayah, paket)...">
        </div>

        <a href="{{ route('pelanggan.create') }}" class="btn btn-primary">
            Tambah Data
        </a>

        <select class="filter-select" id="area-filter" style="min-width: 150px;">
            <option value="">Semua Wilayah</option>
            @foreach($dataArea as $area)
                <option value="{{ $area->id_area }}">{{ $area->nama_area }}</option>
            @endforeach
        </select>
    </div>

    {{-- TABLE --}}
    <div class="table-card mt-2">
        <div id="loading-spinner" class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data...</p>
        </div>

        <div id="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Area</th>
                        <th>Sales</th> {{-- 🔹 tambahkan ini --}}
                        <th>Paket</th>
                        <th>Tanggal Aktif</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                    <tbody id="pelanggan-table-body">
                        @include('pelanggan.partials.table_rows', ['pelanggan' => $pelanggan])
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper" id="pagination-wrapper">
                {{ $pelanggan->links() }}
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let timeout = null;
    let currentPage = 1;

    // Fungsi untuk memuat data via AJAX
    function loadData(page = 1) {
        currentPage = page;

        $('#loading-spinner').show();
        $('#table-container').hide();

        const search = $('#search-input').val();
        const area   = $('#area-filter').val();

        $.ajax({
            url: '{{ route("pelanggan.index") }}',
            type: 'GET',
            data: {
                search: search,
                area: area,
                page: page,
                ajax: true
            },
            success: function(response) {
                $('#pelanggan-table-body').html(response.html);
                $('#pagination-wrapper').html(response.pagination);
                $('#table-container').show();
                $('#loading-spinner').hide();

                updateUrl(search, area, page);
            },
            error: function(xhr) {
                $('#loading-spinner').hide();
                $('#table-container').show();
                console.error('Error:', xhr);
                alert('Terjadi kesalahan saat memuat data');
            }
        });
    }

    // Update URL dengan parameter filter
    function updateUrl(search, area, page) {
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (area)   params.set('area', area);
        if (page > 1) params.set('page', page);

        const newUrl = params.toString()
            ? '{{ route("pelanggan.index") }}?' + params.toString()
            : '{{ route("pelanggan.index") }}';

        window.history.replaceState({}, '', newUrl);
    }


    // Real-time search dengan debounce
    $('#search-input').on('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            loadData(1);
        }, 200);
    });

    // Filter change
    $('#area-filter').on('change', function() {
        loadData(1);
    });


    // Pagination click (event delegation)
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = new URL($(this).attr('href'));
        const page = url.searchParams.get('page') || 1;
        loadData(page);
    });

    // Delete button pakai modal universal
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const url = $(this).data('url');
        const deleteForm = $('#deleteForm');
        deleteForm.attr('action', url);

        // Tampilkan modal
        const deleteModal = new bootstrap.Modal($('#deleteModal')[0]);
        deleteModal.show();
    });

    // Submit delete form dari modal
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
                alert(response.message);
                const deleteModal = bootstrap.Modal.getInstance($('#deleteModal')[0]);
                deleteModal.hide();
                loadData(currentPage);
            },
            error: function(xhr) {
                submitButton.prop('disabled', false).text('Hapus');
                alert('Terjadi kesalahan saat menghapus data');
            }
        });
    });

    // Load initial data dari URL parameters
    function loadInitialFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        const search = urlParams.get('search');
        const area = urlParams.get('area');
        const page = urlParams.get('page');

        if (search) $('#search-input').val(search);
        if (area) $('#area-filter').val(area);

        loadData(page || 1);
    }


    loadInitialFilters();
});
</script>
@endpush
