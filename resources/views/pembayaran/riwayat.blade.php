@extends('layouts.master')

@section('content')

<style>

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

<div class="container-fluid p-4">

    <h4 class="mb-3">Riwayat Pembayaran</h4>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

{{-- FILTER BAR --}}
<form id="filter-form" class="row g-2 mb-3" method="GET" action="{{ route('pembayaran.riwayat') }}">

    {{-- BARIS 1 --}}
    <div class="col-md-3">
        <input type="text" name="search" class="form-control"
               placeholder="Cari no pembayaran / nama / area"
               value="{{ request('search') }}">
    </div>

    <div class="col-md-2">
        <select name="sumber" class="form-select">
            <option value="">Semua sumber</option>
            <option value="admin" {{ request('sumber')=='admin' ? 'selected' : '' }}>Admin</option>
            <option value="sales" {{ request('sumber')=='sales' ? 'selected' : '' }}>Sales</option>
        </select>
    </div>

    <div class="col-md-2">
        <select name="area_id" class="form-select">
            <option value="">Semua area</option>
            @foreach($areas as $area)
                <option value="{{ $area->id_area ?? $area->id }}"
                    {{ request('area_id') == ($area->id_area ?? $area->id) ? 'selected' : '' }}>
                    {{ $area->nama_area }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2">
        <select name="sales_id" class="form-select">
            <option value="">Semua sales</option>
            @foreach($salesList as $sales)
                <option value="{{ $sales->id_sales ?? $sales->id }}"
                    {{ request('sales_id') == ($sales->id_sales ?? $sales->id) ? 'selected' : '' }}>
                    {{ $sales->user->name ?? 'Sales #'.$sales->id }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- BARIS 2 --}}
    <div class="w-100"></div> {{-- pindah baris --}}

    <div class="col-md-2 position-relative">
        <small class="text-muted position-absolute"
               style="top: -8px; left: 8px; background:white; padding:0 4px; font-size:11px;">
            Tanggal Dari
        </small>
        <input type="date" name="tanggal_dari" class="form-control"
               value="{{ request('tanggal_dari') }}">
    </div>

    <div class="col-md-2 position-relative">
        <small class="text-muted position-absolute"
               style="top: -8px; left: 8px; background:white; padding:0 4px; font-size:11px;">
            Tanggal Sampai
        </small>
        <input type="date" name="tanggal_sampai" class="form-control"
               value="{{ request('tanggal_sampai') }}">
    </div>

</form>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Pembayaran</th>
                        <th>Tanggal</th>
                        <th>Pelanggan & Area</th>
                        <th>Pembayaran By</th>
                        <th>Nominal</th>
                        <th>Detail Tagihan</th>
                    </tr>
                </thead>
<tbody id="riwayat-tbody">
    @include('pembayaran.partials.table_rows_riwayat', [
        'pembayaran' => $pembayaran,
    ])
</tbody>


            </table>
        </div>

            <div class="pagination-wrapper p-3 bg-light border-top" id="pagination-wrapper" style="border-radius: 0 0 16px 16px;">
                {{-- Kita tidak perlu class flex di sini, biarkan CSS di atas yang mengatur 'jeroan' Laravel --}}
                {{ $pembayaran->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
    </div>

</div>
@endsection

@stack('modals')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @foreach($pembayaran as $pay)
        (function () {
            const checkAll   = document.getElementById('check-all-{{ $pay->id_pembayaran }}');
            const checkItems = document.querySelectorAll('.checkbox-item-{{ $pay->id_pembayaran }}');

            if (!checkAll) return;

            checkAll.addEventListener('change', function () {
                checkItems.forEach(cb => cb.checked = this.checked);
            });
        })();
    @endforeach
});
(function () {
    const form        = document.getElementById('filter-form');
    const tbody       = document.getElementById('riwayat-tbody');
    const pagination  = document.getElementById('riwayat-pagination');
    const inputs      = form.querySelectorAll('input, select');

    let timer = null;

    function fetchData(url = null) {
        const formData = new FormData(form);
        const params   = new URLSearchParams(formData);

        const targetUrl = url || (form.action + '?' + params.toString());

        fetch(targetUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.json())
            .then(data => {
                tbody.innerHTML      = data.tbody;
                pagination.innerHTML = data.pagination;
                bindPagination(); // re-bind event click pagination
            })
            .catch(err => console.error(err));
    }

    function bindPagination() {
        const links = pagination.querySelectorAll('a.page-link');
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url) {
                    fetchData(url);
                }
            });
        });
    }

    // Realtime: search (input) + filter lain (change)
    inputs.forEach(el => {
        const eventName = (el.name === 'search') ? 'input' : 'change';
        el.addEventListener(eventName, function () {
            clearTimeout(timer);
            timer = setTimeout(() => {
                fetchData();
            }, 300); // debounce 300ms
        });
    });

    bindPagination();
})();
</script>
@endpush
