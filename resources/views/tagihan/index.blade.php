@extends('layouts.master')

@section('content')
<style>
    .page-title { font-size: 22px; font-weight: 700; color: #222; }
    .search-box input {
        border-radius: 10px; border: 1px solid #ddd;
        padding: 8px 14px; font-size: 14px;
    }
    .filter-select {
        border-radius: 10px; padding: 8px; font-size: 14px;
        border: 1px solid #ddd; background: white;
    }
    .table-card {
        background: #fff; border-radius: 14px;
        padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    table thead th {
        background: #f8f9fa; font-size: 13px;
        font-weight: 600; padding: 10px;
    }
    table tbody td { font-size: 13px; padding: 10px; }
    table tbody tr:hover { background: #f4f4f4; 
    }
    .pagination-wrapper {
        margin-top: 20px; display: flex; justify-content: center;
    }
        .pagination {
        justify-content: flex-end; /* Posisi di kanan */
        gap: 6px; /* Jarak antar tombol */
        margin-bottom: 0;
    }

    .page-item .page-link {
        border: none;
        border-radius: 8px; /* Membuat sudut melengkung */
        color: #64748b;     /* Warna teks abu lembut */
        font-weight: 600;
        font-size: 13px;
        padding: 8px 14px;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Bayangan tipis */
        transition: all 0.2s;
    }

    /* Efek saat mouse diarahkan */
    .page-item .page-link:hover {
        background-color: #f1f5f9;
        color: #0284c7;
        transform: translateY(-1px);
    }

    /* Tombol Aktif (Halaman sekarang) */
    .page-item.active .page-link {
        background-color: #0284c7; /* Biru modern */
        color: white;
        box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3); /* Efek glowing biru */
    }

    /* Tombol Disabled (Panah mati) */
    .page-item.disabled .page-link {
        background-color: #f8fafc;
        color: #cbd5e1;
        box-shadow: none;
    }

    div.pagination-wrapper nav .d-none.flex-fill.d-sm-flex .text-muted {
        display: none !important;
    }
    
    /* Atau penargetan yang lebih agresif jika yang atas tidak mempan */
    div.pagination-wrapper nav p.small.text-muted {
        display: none !important;
    }

    /* Pastikan Pagination Rata Kanan & Rapi */
    div.pagination-wrapper nav {
        display: flex;
        justify-content: center; /* Tombol geser ke kanan */
        width: 100%;
    }

    /* ... CSS lainnya biarkan ... */

    /* --- PAKSA PAGINATION KE TENGAH & HILANGKAN TEKS --- */
    
    /* 1. Sembunyikan teks 'Showing...' (elemen div pertama di dalam nav) */
    .pagination-wrapper nav .d-none.d-sm-flex > div:first-child {
        display: none !important;
    }

    /* 2. Ubah layout container dari 'Between' menjadi 'Center' */
    .pagination-wrapper nav .d-none.d-sm-flex {
        justify-content: center !important;
    }

    /* 3. Gaya Tombol (Opsional: Agar lebih cantik seperti sebelumnya) */
    .page-item .page-link {
        border: none;
        border-radius: 8px;
        color: #64748b;
        margin: 0 3px; /* Jarak antar tombol */
        font-weight: 600;
        font-size: 13px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .page-item.active .page-link {
        background-color: #0284c7;
        color: white;
        box-shadow: 0 4px 8px rgba(2, 132, 199, 0.2);
    }
</style>

<div class="container-fluid p-4" id="tagihan-page-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">Status Tagihan per Pelanggan</h4>
    </div>

    @php
        $statusFilter = $statusFilter ?? request('status', '');
    @endphp

    {{-- TAB STATUS (bukan dropdown) --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('tagihan.index') }}"
           class="btn btn-sm {{ $statusFilter === '' ? 'btn-primary' : 'btn-outline-primary' }}">
            Semua
        </a>

        <a href="{{ route('tagihan.index', ['status' => 'belum_lunas']) }}"
           class="btn btn-sm {{ $statusFilter === 'belum_lunas' ? 'btn-primary' : 'btn-outline-warning' }}">
            Belum Lunas
        </a>

        <a href="{{ route('tagihan.index', ['status' => 'lunas']) }}"
           class="btn btn-sm {{ $statusFilter === 'lunas' ? 'btn-primary' : 'btn-outline-success' }}">
            Lunas
        </a>
    </div>

    {{-- SEARCH + FILTER: AJAX (realtime, tanpa tombol) --}}
    <div class="d-flex gap-3 mb-4 flex-wrap" id="filter-tagihan-wrapper">
        {{-- simpan status sekarang untuk dipakai di JS --}}
        <input type="hidden" id="status-tagihan" value="{{ $statusFilter }}">

        <div class="search-box flex-grow-1" style="min-width: 250px;">
            <input type="text" id="search-tagihan" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Cari pelanggan / paket...">
        </div>

        {{-- FILTER SALES --}}
        <select id="sales-tagihan" class="filter-select" style="min-width: 160px;">
            <option value="">Semua Sales</option>
            @foreach($dataSales as $s)
                <option value="{{ $s->id_sales }}"
                    {{ request('sales') == $s->id_sales ? 'selected' : '' }}>
                    {{ $s->user->name }}
                </option>
            @endforeach
        </select>

        {{-- FILTER WILAYAH --}}
        <select id="area-tagihan" class="filter-select" style="min-width: 160px;">
            <option value="">Semua Wilayah</option>
            @foreach($dataArea as $area)
                <option value="{{ $area->id_area }}"
                    {{ request('area') == $area->id_area ? 'selected' : '' }}>
                    {{ $area->nama_area }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- TABLE --}}
    <div class="table-card mt-2">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Area &amp; Sales</th>
                    <th>Paket Layanan</th>
                    <th>IP Address</th>
                    <th>Tanggal jatuh tempo</th>
                    <th>Total Tagihan</th>
                    <th>Status</th>
                </tr>
                </thead>

                <tbody id="tagihan-table-body">
                    @include('tagihan.partials.table', ['pelanggan' => $pelanggan])
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper p-3 bg-light border-top" id="pagination-wrapper" style="border-radius: 0 0 16px 16px;">
            {{-- Kita tidak perlu class flex di sini, biarkan CSS di atas yang mengatur 'jeroan' Laravel --}}
            {{ $pelanggan->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {

    let currentPage = 1;
    let currentAjax = null;

    function loadTagihanData(page = 1) {
        currentPage = page;

        // batalin request sebelumnya biar hasil terakhir yang dipakai
        if (currentAjax !== null) {
            currentAjax.abort();
        }

        const status = $('#status-tagihan').val() || '';
        const search = $('#search-tagihan').val();
        const paket  = $('#paket-tagihan').val();
        const sales  = $('#sales-tagihan').val();
        const area   = $('#area-tagihan').val();

        currentAjax = $.ajax({
            url: '{{ route("tagihan.index") }}',
            type: 'GET',
            cache: false,
            data: {
                ajax:   true,
                page:   page,
                status: status,
                search: search,
                paket:  paket,
                sales:  sales,
                area:   area,
            },
            success: function (response) {
                $('#tagihan-table-body').html(response.html);
                $('#pagination-wrapper').html(response.pagination);

                updateUrl(status, search, paket, sales, area, page);
            },
            error: function (xhr, textStatus) {
                if (textStatus === 'abort') return; // diabaikan kalau karena abort
                console.error(xhr.responseText);
                alert('Terjadi kesalahan saat memuat data tagihan.');
            },
            complete: function () {
                currentAjax = null;
            }
        });
    }

    function updateUrl(status, search, paket, sales, area, page) {
        const params = new URLSearchParams();
        if (status) params.set('status', status);
        if (search) params.set('search', search);
        if (paket)  params.set('paket', paket);
        if (sales)  params.set('sales', sales);
        if (area)   params.set('area', area);
        if (page > 1) params.set('page', page);

        const newUrl = params.toString()
            ? '{{ route("tagihan.index") }}?' + params.toString()
            : '{{ route("tagihan.index") }}';

        window.history.replaceState({}, '', newUrl);
    }

    // 🔹 SEARCH realtime (kayak contoh status pelanggan)
    $('#search-tagihan').on('input', function () {
        loadTagihanData(1);
    });

    // 🔹 FILTER realtime juga
    $('#paket-tagihan, #sales-tagihan, #area-tagihan').on('change', function () {
        loadTagihanData(1);
    });

    // 🔹 PAGINATION AJAX
    $(document).on('click', '#pagination-wrapper .pagination a', function (e) {
        e.preventDefault();
        const url  = new URL($(this).attr('href'));
        const page = url.searchParams.get('page') || 1;
        loadTagihanData(page);
    });

    // 🔹 INISIAL – ambil dari URL kalau ada (biar refresh / share URL tetep sama)
    (function loadInitial() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status') ?? $('#status-tagihan').val() ?? '';
        const search = urlParams.get('search');
        const paket  = urlParams.get('paket');
        const sales  = urlParams.get('sales');
        const area   = urlParams.get('area');
        const page   = urlParams.get('page') || 1;

        $('#status-tagihan').val(status);
        if (search) $('#search-tagihan').val(search);
        if (paket)  $('#paket-tagihan').val(paket);
        if (sales)  $('#sales-tagihan').val(sales);
        if (area)   $('#area-tagihan').val(area);

        loadTagihanData(page);
    })();

});
</script>
@endpush
