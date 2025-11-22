@extends('layouts.master')

@section('content')
<style>
    /* CSS yang sudah ada tetap dipertahankan */
    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #222;
    }

    .search-box input {
        /* Disesuaikan untuk tampilan input tunggal yang lebih besar */
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 10px 14px; /* Sedikit lebih tinggi */
        font-size: 14px;
        height: 40px; /* Tinggi yang konsisten */
    }

    /* Modifikasi untuk tombol search di gambar */
    .search-group {
        display: flex;
        align-items: center;
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 10px;
        width: 300px; /* Sesuaikan lebar */
        box-shadow: none;
    }

    .search-group input {
        border: none;
        box-shadow: none;
        padding: 8px 14px;
        flex-grow: 1;
    }

    .search-group button {
        background-color: #ffc107; /* Warna kuning */
        color: white;
        border: none;
        border-radius: 0 10px 10px 0;
        padding: 8px 14px;
        cursor: pointer;
        height: 100%;
    }

    .search-group button i {
        color: black;
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
        padding: 0 20px 20px 20px; /* Padding atas dihilangkan atau dikurangi */
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    table thead th {
        background: white; /* Diubah menjadi putih agar sesuai gambar */
        font-size: 14px; /* Sedikit lebih besar */
        font-weight: 600;
        color: #555;
        padding: 15px 10px; /* Padding lebih besar untuk header */
        border-bottom: 1px solid #eee; /* Garis bawah pemisah */
    }

    table tbody td {
        font-size: 14px;
        padding: 15px 10px; /* Padding lebih besar untuk baris data */
        vertical-align: middle;
        border-top: 1px solid #eee; /* Garis atas pemisah antar baris */
    }

    table tbody tr:hover {
        background: #fcfcfc; /* Warna hover yang sangat terang */
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
        /* min-height dihilangkan karena tidak relevan dengan tampilan gambar */
        margin-top: 20px; /* Jarak antara card dan tabel */
    }

    /* Styling untuk tombol aksi (Edit & Hapus) */
    .btn-action {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        padding: 0;
        font-size: 14px;
        border: none;
    }
    .btn-edit {
        background-color: white;
        color: #555;
        border: 1px solid #ddd;
    }
    .btn-delete {
        background-color: #dc3545; /* Merah */
        color: white;
        margin-left: 5px;
    }
    .btn-tambah-area {
        background-color: #ffc107; /* Kuning */
        color: black;
        font-weight: 600;
        border-radius: 10px;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .btn-tambah-area i {
        font-size: 16px;
    }
</style>

<div class="container-fluid p-4">

    {{-- HEADER MIRIP GAMBAR: Pencarian dan Tombol Tambah Area --}}
    <div class="d-flex justify-content-end align-items-center mb-4">
        {{-- Mengganti Title 'Pelanggan' dan tombol Tambah Data sesuai Gambar --}}
        
        <div class="d-flex align-items-center gap-3 w-100">
            {{-- Search Box di sisi kiri --}}
            <div class="search-group">
                <input type="text" id="search-input" placeholder="Cari..." style="border-radius: 10px 0 0 10px;">
                <button type="button" class="btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            {{-- Tombol Tambah Area di sisi kanan --}}
            <a href="{{ route('pelanggan.create') }}" class="btn btn-tambah-area ms-auto">
                Tambah Area <i class="fas fa-plus"></i>
            </a>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="table-card mt-2">
        <div id="loading-spinner" class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat data...</p>
        </div>

        <div id="table-container">
            <div class="table-responsive">
                <table class="table"> {{-- Hapus class table-hover untuk kesesuaian --}}
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 30%;">Nama Area</th>
                            <th style="width: 25%;">Jumlah Pelanggan</th>
                            <th style="width: 30%;">Nama Sales</th>
                            <th style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="pelanggan-table-body">
                        {{-- Data statis contoh (sesuai gambar) --}}
                        <tr>
                            <td>001</td>
                            <td>Kediri</td>
                            <td>1.000 Pelanggan</td>
                            <td>Irfan, Yoga</td>
                            <td>
                                <a href="#" class="btn btn-action btn-edit"><i class="fas fa-pencil-alt"></i></a>
                                <button data-url="#" class="btn btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>002</td>
                            <td>Malang</td>
                            <td>850 Pelanggan</td>
                            <td>Budi, Anto</td>
                            <td>
                                <a href="#" class="btn btn-action btn-edit"><i class="fas fa-pencil-alt"></i></a>
                                <button data-url="#" class="btn btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        {{-- Jika Anda tetap ingin menampilkan data pelanggan yang sebenarnya, 
                             kembalikan baris berikut: --}}
                        {{-- @include('pelanggan.partials.table_rows', ['pelanggan' => $pelanggan]) --}}
                        
                        {{-- Menggunakan dummy data sesuai gambar --}}
                        <tr>
                            <td>001</td>
                            <td>Kediri</td>
                            <td>1.000 Pelanggan</td>
                            <td>Irfan, Yoga</td>
                            <td>
                                <a href="#" class="btn btn-action btn-edit"><i class="fas fa-pencil-alt"></i></a>
                                <button data-url="#" class="btn btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>001</td>
                            <td>Kediri</td>
                            <td>1.000 Pelanggan</td>
                            <td>Irfan, Yoga</td>
                            <td>
                                <a href="#" class="btn btn-action btn-edit"><i class="fas fa-pencil-alt"></i></a>
                                <button data-url="#" class="btn btn-action btn-delete"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper" id="pagination-wrapper">
                {{-- {{ $pelanggan->links() }} --}}
                <nav>
                    <ul class="pagination">
                        <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- Script AJAX yang ada akan dipertahankan untuk fungsionalitas aslinya, 
     tetapi karena filter dan header diubah, beberapa penyesuaian mungkin diperlukan 
     di sisi JavaScript jika Anda ingin fungsionalitasnya tetap berjalan 
     dengan struktur HTML yang baru. --}}
<script>
    $(document).ready(function() {
        let timeout = null;
        let currentPage = 1;

        // Fungsi untuk memuat data via AJAX
        function loadData(page = 1) {
            currentPage = page;

            $('#loading-spinner').show();
            $('#table-container').hide();

            // Mengambil nilai search, area, dan status (jika masih digunakan di backend)
            const search = $('#search-input').val();
            // Filter area dan status dihilangkan dari HTML, jadi nilai filter-select dikosongkan/di-hardcode jika tidak ada.
            const area = ''; // Filter dihilangkan dari tampilan
            const status = ''; // Filter dihilangkan dari tampilan

            $.ajax({
                url: '{{ route("pelanggan.index") }}',
                type: 'GET',
                data: {
                    search: search,
                    area: area, // Kirim nilai kosong
                    status: status, // Kirim nilai kosong
                    page: page,
                    ajax: true
                },
                success: function(response) {
                    // Hanya sesuaikan jika Anda mengembalikan partial yang sesuai dengan tabel Area
                    // $('#pelanggan-table-body').html(response.html);
                    // $('#pagination-wrapper').html(response.pagination);
                    $('#table-container').show();
                    $('#loading-spinner').hide();

                    updateUrl(search, area, status, page);
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
        function updateUrl(search, area, status, page) {
            const params = new URLSearchParams();
            if (search) params.set('search', search);
            // if (area) params.set('area', area); // Dihapus karena filter dihilangkan
            // if (status) params.set('status', status); // Dihapus karena filter dihilangkan
            if (page > 1) params.set('page', page);

            const newUrl = params.toString() ? '{{ route("pelanggan.index") }}?' + params.toString() : '{{ route("pelanggan.index") }}';
            window.history.replaceState({}, '', newUrl);
        }

        // Real-time search dengan debounce
        $('#search-input').on('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                loadData(1);
            }, 200);
        });

        // Hapus event listener untuk filter yang sudah dihilangkan
        // $('#area-filter, #status-filter').on('change', function() {
        //     loadData(1);
        // });

        // Pagination click (event delegation)
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            const url = new URL($(this).attr('href'));
            const page = url.searchParams.get('page') || 1;
            loadData(page);
        });

        // Delete confirmation
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            const url = $(this).data('url');
            const button = $(this);

            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>'); // Mengubah ikon menjadi loading

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert(response.message);
                        loadData(currentPage);
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
                        alert('Terjadi kesalahan saat menghapus data');
                    }
                });
            }
        });

        // Load initial data dari URL parameters
        function loadInitialFilters() {
            const urlParams = new URLSearchParams(window.location.search);
            const search = urlParams.get('search');
            // const area = urlParams.get('area'); // Dihapus
            // const status = urlParams.get('status'); // Dihapus
            const page = urlParams.get('page');

            if (search) $('#search-input').val(search);
            // if (area) $('#area-filter').val(area); // Dihapus
            // if (status) $('#status-filter').val(status); // Dihapus

            // loadData(page || 1); // Tidak perlu dipanggil karena data dummy/statis
        }

        // Panggil fungsi load initial (jika Anda ingin mempertahankan fungsionalitas pencarian)
        // loadInitialFilters(); 
    });
</script>
@endpush