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
    table tbody tr:hover { background: #f4f4f4; }
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

<div class="container-fluid p-4" id="page-wrapper">
    {{-- TITLE --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">Status Pelanggan</h4>
    </div>

    @php
        $statusHalaman = $status ?? 'aktif';

        if ($statusHalaman === 'isolir') {
            $colTanggalLabel = 'Tanggal Isolir';
        } elseif ($statusHalaman === 'berhenti') {
            $colTanggalLabel = 'Tanggal Berhenti';
        } elseif ($statusHalaman === 'baru') {
            $colTanggalLabel = 'Tanggal Daftar';
        } else {
            $colTanggalLabel = 'Tanggal Aktif';
        }

        $totalBaru     = $statusCounts['baru']     ?? 0;
        $totalAktif    = $statusCounts['aktif']    ?? 0;
        $totalBerhenti = $statusCounts['berhenti'] ?? 0;
        $totalIsolir   = $statusCounts['isolir']   ?? 0;
    @endphp

    {{-- TOMBOL STATUS SEBAGAI LINK --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('pelanggan.status', ['status' => 'baru']) }}"
           class="btn btn-sm {{ $statusHalaman === 'baru' ? 'btn-primary' : 'btn-outline-primary' }}">
            Baru ({{ $totalBaru }})
        </a>

        <a href="{{ route('pelanggan.status', ['status' => 'aktif']) }}"
           class="btn btn-sm {{ $statusHalaman === 'aktif' ? 'btn-primary' : 'btn-outline-primary' }}">
            Aktif ({{ $totalAktif }})
        </a>

        <a href="{{ route('pelanggan.status', ['status' => 'berhenti']) }}"
           class="btn btn-sm {{ $statusHalaman === 'berhenti' ? 'btn-primary' : 'btn-outline-primary' }}">
            Berhenti ({{ $totalBerhenti }})
        </a>

        <a href="{{ route('pelanggan.status', ['status' => 'isolir']) }}"
           class="btn btn-sm {{ $statusHalaman === 'isolir' ? 'btn-primary' : 'btn-outline-primary' }}">
            Isolir ({{ $totalIsolir }})
        </a>
    </div>

    {{-- SEARCH & FILTER: AJAX (search & filter realtime) --}}
    <div class="d-flex gap-3 mb-4 flex-wrap">
        {{-- tetap simpan status yang lagi dipilih (dipakai di JS & URL) --}}
        <input type="hidden" id="status-hidden" value="{{ $statusHalaman }}">

        <div class="search-box flex-grow-1" style="min-width: 250px;">
            <input type="text" id="search-input-status" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Cari pelanggan (nama, NIK, IP, HP, wilayah, paket)...">
        </div>

        {{-- FILTER SALES --}}
        <select class="filter-select" id="sales-filter-status" style="min-width: 160px;">
            <option value="">Semua Sales</option>
            @foreach($dataSales as $s)
                <option value="{{ $s->id_sales }}"
                    {{ request('sales') == $s->id_sales ? 'selected' : '' }}>
                    {{ $s->user->name }}
                </option>
            @endforeach
        </select>

        {{-- FILTER WILAYAH --}}
        <select class="filter-select" id="area-filter-status" style="min-width: 160px;">
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
            <table class="table table-hover">
@php
    $statusHalaman = request('status', 'aktif');
@endphp

<thead>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Area</th>
        <th>Sales</th>
        <th>Paket harga total</th>
        <th>
            @if($statusHalaman === 'baru')
                Tanggal Registrasi
            @elseif($statusHalaman === 'aktif')
                Tanggal Aktif
            @elseif($statusHalaman === 'berhenti')
                Tanggal Berhenti
            @elseif($statusHalaman === 'isolir')
                Tanggal Isolir
            @else
                Tanggal
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

    function loadStatusData(page = 1) {
        currentPage = page;

        // batalin request sebelumnya biar hasil terakhir yang dipakai
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
                ajax:   true    // penanda ke controller: ini request AJAX
            },
            success: function (response) {
                $('#pelanggan-status-body').html(response.html);
                $('#status-pagination').html(response.pagination);

                // update URL (biar bisa di-refresh / share)
                updateUrl(status, search, area, sales, page);
            },
            error: function (xhr, textStatus) {
                if (textStatus === 'abort') return; // diabaikan kalau karena abort
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

    // 🔹 SEARCH realtime
    $('#search-input-status').on('input', function () {
        loadStatusData(1);
    });

    // 🔹 FILTER area & sales
    $('#area-filter-status').on('change', function () {
        loadStatusData(1);
    });

    $('#sales-filter-status').on('change', function () {
        loadStatusData(1);
    });

    // 🔹 PAGINATION AJAX
    $(document).on('click', '#status-pagination .pagination a', function (e) {
        e.preventDefault();
        const url  = new URL($(this).attr('href'));
        const page = url.searchParams.get('page') || 1;
        loadStatusData(page);
    });

    // 🔹 INISIAL – ambil dari URL kalau ada
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

    // 🔹 HAPUS (modal) – tetap pakai event delegation vanilla
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;

        e.preventDefault();

        const url = btn.dataset.url;
        if (!url) {
            console.error('data-url tidak ditemukan di tombol delete');
            return;
        }

        const deleteForm = document.getElementById('deleteForm');
        if (!deleteForm) {
            console.error('#deleteForm tidak ditemukan');
            return;
        }
        deleteForm.action = url;

        const modalEl = document.getElementById('deleteModal');
        if (!modalEl) {
            console.error('#deleteModal tidak ditemukan');
            return;
        }

        const deleteModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        deleteModal.show();
    });

    // 🔹 SUBMIT FORM HAPUS via AJAX (kalau mau sekalian refresh tabel status)
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

                loadStatusData(currentPage); // refresh tabel sesuai filter & halaman sekarang
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
