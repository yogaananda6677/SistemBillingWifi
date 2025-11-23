@extends('layouts.master')

@section('content')
<style>
    .page-title { font-size: 22px; font-weight: 700; color: #222; }
    .search-input-group { display: flex; width: 300px; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; background: white; }
    .search-input-group input { border: none; padding: 8px 14px; font-size: 14px; flex-grow: 1; outline: none; }
    .search-input-group button { background: #fbc02d; border: none; padding: 8px 15px; color: white; cursor: pointer; }
    .table-card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    table thead th { background: #f8f9fa; font-size: 13px; font-weight: 600; padding: 10px; text-align: left; }
    table tbody td { font-size: 13px; padding: 10px; vertical-align: middle; }
    table tbody tr:hover { background: #f4f4f4; }
    .loading-spinner { display: none; text-align: center; padding: 20px; }
    .pagination-wrapper { margin-top: 20px; display: flex; justify-content: center; }
    .table-responsive { min-height: 400px; }
    .btn-action-icon { padding: 4px 6px; font-size: 12px; border-radius: 4px; color: white; margin-right: 4px; width: 28px; height: 28px; display: inline-flex; justify-content: center; align-items: center; }
    .btn-edit { background-color: #fbc02d; }
    .btn-delete { background-color: #dc3545; }
</style>

<div class="container-fluid p-4">

    {{-- SEARCH + TAMBAH --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div class="d-flex align-items-center flex-grow-1">
            <div class="search-input-group me-3">
                <input type="text" id="search-input" placeholder="Cari...">
                <button type="button" id="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        {{-- FIX ROUTE CREATE --}}
        <a href="{{ route('data-sales.create') }}" 
           class="btn btn-warning d-flex align-items-center fw-bold text-dark">
            <i class="fas fa-plus me-1"></i> Tambah Sales
        </a>
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
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Sales</th>
                            <th>No. Telepon</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Area</th>
                            <th>Pelanggan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sales-table-body">
                        @include('sales.partials.table_rows', ['data' => $data])
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper" id="pagination-wrapper">
                {!! $data->links() !!}
            </div>

        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    const deleteForm = document.getElementById('deleteForm');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            const url = this.dataset.url;
            deleteForm.action = url; // set action form
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
});
</script>
<script>

$(document).ready(function() {

    let timeout = null;
    let currentPage = 1;

    function loadData(page = 1) {

        currentPage = page;

        $('#loading-spinner').show();
        $('#table-container').hide();

        const search = $('#search-input').val();

        $.ajax({
            url: '{{ route("data-sales.index") }}',   // FIX ROUTE AJAX
            type: 'GET',
            data: {
                search: search,
                page: page,
                ajax: true
            },
            success: function(response) {
                $('#sales-table-body').html(response.html);
                $('#pagination-wrapper').html(response.pagination);
                $('#table-container').show();
                $('#loading-spinner').hide();

                updateUrl(search, page);
            },
            error: function() {
                $('#loading-spinner').hide();
                $('#table-container').show();
                alert('Terjadi kesalahan saat memuat data.');
            }
        });
    }

    function updateUrl(search, page) {
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (page > 1) params.set('page', page);

        const newUrl = params.toString()
            ? '{{ route("data-sales.index") }}?' + params.toString()
            : '{{ route("data-sales.index") }}';

        window.history.replaceState({}, '', newUrl);
    }

    // Search debounce
    $('#search-input').on('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => loadData(1), 250);
    });

    $('#search-button').on('click', function() {
        loadData(1);
    });

    // Pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = new URL($(this).attr('href')).searchParams.get('page') || 1;
        loadData(page);
    });

    // DELETE
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();

        const url = $(this).data('url');
        const button = $(this);

        if (confirm('Apakah Anda yakin ingin menghapus sales ini?')) {

            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert(response.message);
                    loadData(currentPage);
                },
                error: function() {
                    button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                    alert('Terjadi kesalahan saat menghapus.');
                }
            });
        }
    });

});

</script>

@endpush
