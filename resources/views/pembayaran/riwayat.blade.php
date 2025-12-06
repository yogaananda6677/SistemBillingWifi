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

    /* 2. Card Styles */
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow);
        width: 100%;
    }

    /* 3. Form Inputs */
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

    /* 4. Table Styling (COMPACT) */
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

    /* 5. Pagination Styling (Yellow & Consistent) */
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
    
    /* Label Filter Kecil di atas input */
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 4px;
        display: block;
    }
</style>

<div class="container-fluid p-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-clock-history text-warning me-2"></i>Riwayat Pembayaran
            </h4>
            <div class="text-muted small">Log transaksi pembayaran yang masuk</div>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-left: 5px solid #198754;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-left: 5px solid #dc3545;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER CARD --}}
    <div class="card-admin p-3 mb-3">
        <form id="filter-form" action="{{ route('pembayaran.riwayat') }}" method="GET">
            <div class="row g-2">
                {{-- BARIS 1 --}}
                <div class="col-12 col-md-3">
                    <span class="filter-label">Pencarian</span>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;">
                            <i class="bi bi-search text-warning" style="font-size: 13px;"></i>
                        </span>
                        <input type="text" name="search" class="form-control form-control-admin border-start-0" 
                               style="border-radius: 0 8px 8px 0;"
                               placeholder="Cari no bayar / nama..."
                               value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <span class="filter-label">Sumber</span>
                    <select name="sumber" class="form-select form-select-admin">
                        <option value="">Semua</option>
                        <option value="admin" {{ request('sumber')=='admin' ? 'selected' : '' }}>Admin</option>
                        <option value="sales" {{ request('sumber')=='sales' ? 'selected' : '' }}>Sales</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <span class="filter-label">Area</span>
                    <select name="area_id" class="form-select form-select-admin">
                        <option value="">Semua</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id_area ?? $area->id }}" 
                                {{ request('area_id') == ($area->id_area ?? $area->id) ? 'selected' : '' }}>
                                {{ $area->nama_area }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <span class="filter-label">Sales</span>
                    <select name="sales_id" class="form-select form-select-admin">
                        <option value="">Semua</option>
                        @foreach($salesList as $sales)
                            <option value="{{ $sales->id_sales ?? $sales->id }}" 
                                {{ request('sales_id') == ($sales->id_sales ?? $sales->id) ? 'selected' : '' }}>
                                {{ $sales->user->name ?? 'Sales #'.$sales->id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BARIS 2 (TANGGAL) --}}
                <div class="col-6 col-md-3 d-flex gap-2">
                    <div class="w-50">
                        <span class="filter-label">Dari Tgl</span>
                        <input type="date" name="tanggal_dari" class="form-control form-control-admin" 
                               value="{{ request('tanggal_dari') }}">
                    </div>
                    <div class="w-50">
                        <span class="filter-label">Sampai Tgl</span>
                        <input type="date" name="tanggal_sampai" class="form-control form-control-admin" 
                               value="{{ request('tanggal_sampai') }}">
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-admin mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No</th>
                        <th>No Pembayaran</th>
                        <th>Tanggal</th>
                        <th>Pelanggan & Area</th>
                        <th>Pembayaran By</th>
                        <th>Nominal</th>
                        <th>Detail Tagihan</th>
                    </tr>
                </thead>
                <tbody id="riwayat-tbody">
                    @include('pembayaran.partials.table_rows_riwayat', ['pembayaran' => $pembayaran])
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrapper" id="riwayat-pagination">
            {{ $pembayaran->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>

</div>
@endsection

@stack('modals')
@push('scripts')
{{-- SCRIPT ASLI TETAP JALAN --}}
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
    const form       = document.getElementById('filter-form');
    const tbody      = document.getElementById('riwayat-tbody');
    const pagination = document.getElementById('riwayat-pagination');
    const inputs     = form.querySelectorAll('input, select');

    let timer = null;

    function fetchData(url = null) {
        const formData = new FormData(form);
        const params   = new URLSearchParams(formData);
        const targetUrl = url || (form.action + '?' + params.toString());

        fetch(targetUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            tbody.innerHTML      = data.tbody;
            pagination.innerHTML = data.pagination;
            bindPagination();
        })
        .catch(err => console.error(err));
    }

    function bindPagination() {
        const links = pagination.querySelectorAll('a.page-link');
        links.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url) fetchData(url);
            });
        });
    }

    inputs.forEach(el => {
        const eventName = (el.name === 'search') ? 'input' : 'change';
        el.addEventListener(eventName, function () {
            clearTimeout(timer);
            timer = setTimeout(() => { fetchData(); }, 300);
        });
    });

    bindPagination();
})();
</script>
@endpush