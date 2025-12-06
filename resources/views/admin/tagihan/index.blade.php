@extends('layouts.master')
@php
    use Carbon\Carbon;
@endphp

@section('content')
<style>
    /* --- ADMIN YELLOW THEME (COMPACT VERSION) --- */
    :root {
        --theme-yellow: #ffc107;
        --theme-yellow-dark: #e0a800;
        --theme-yellow-soft: #fff9e6;
        --text-dark: #212529;
        --card-radius: 12px;
    }

    /* 1. Typography */
    .page-title {
        font-size: 22px; /* Font judul sedikit diperkecil */
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

    /* 3. Form Inputs (Lebih Ramping) */
    .form-control-admin, .form-select-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px; /* Padding input dikurangi */
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
        font-size: 12px; /* Font header lebih kecil */
        text-transform: uppercase;
        border-bottom: 2px solid var(--theme-yellow);
        padding: 12px 10px; /* Padding header dikurangi */
        white-space: nowrap;
    }

    .table-admin tbody td {
        padding: 10px; /* Padding sel dikurangi agar muat banyak */
        vertical-align: middle;
        font-size: 13px; /* Font isi lebih kecil */
        border-bottom: 1px solid #f0f0f0;
    }

    .table-admin tbody tr:hover td {
        background-color: #fffdf5;
    }

    /* 5. Pagination Styling (Yellow & Center) */
    .pagination-wrapper {
        display: flex;
        justify-content: center !important;
        align-items: center;
        width: 100%;
        padding: 15px; /* Padding pagination dikurangi */
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

    /* 6. Preview Box Modal */
    .preview-bayar-box {
        background: var(--theme-yellow-soft);
        border: 1px dashed var(--theme-yellow);
        border-radius: 8px;
        font-size: 13px;
        color: #856404;
    }
</style>

<div class="container-fluid p-4">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-wallet2 text-warning me-2"></i>Pembayaran Tagihan
            </h4>
            <div class="text-muted small">Kelola pembayaran tagihan pelanggan secara manual (Admin)</div>
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
        <div class="row g-2" id="filter-admin-tagihan-wrapper">
            
            {{-- Search Box --}}
            <div class="col-12 col-md-4">
                <label class="form-label fw-bold text-muted small mb-1">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px; border-color: #dee2e6;">
                        <i class="bi bi-search text-warning" style="font-size: 13px;"></i>
                    </span>
                    <input type="text" id="search-admin-tagihan" class="form-control form-control-admin border-start-0" 
                           style="border-radius: 0 8px 8px 0;"
                           placeholder="Cari nama pelanggan / paket..."
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Filter Sales --}}
            <div class="col-12 col-md-2">
                <label class="form-label fw-bold text-muted small mb-1">Sales</label>
                <select id="sales-admin-tagihan" class="form-select form-select-admin">
                    <option value="">Semua Sales</option>
                    @foreach($dataSales as $s)
                        <option value="{{ $s->id_sales }}" {{ request('sales') == $s->id_sales ? 'selected' : '' }}>
                            {{ $s->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Wilayah --}}
            <div class="col-12 col-md-3">
                <label class="form-label fw-bold text-muted small mb-1">Wilayah</label>
                <select id="area-admin-tagihan" class="form-select form-select-admin">
                    <option value="">Semua Wilayah</option>
                    @foreach($dataArea as $area)
                        <option value="{{ $area->id_area }}" {{ request('area') == $area->id_area ? 'selected' : '' }}>
                            {{ $area->nama_area }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Paket --}}
            <div class="col-12 col-md-3">
                <label class="form-label fw-bold text-muted small mb-1">Paket</label>
                <select id="paket-admin-tagihan" class="form-select form-select-admin">
                    <option value="">Semua Paket</option>
                    @foreach($paketList as $paket)
                        <option value="{{ $paket->id_paket }}" {{ request('paket') == $paket->id_paket ? 'selected' : '' }}>
                            {{ $paket->nama_paket }}
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
                        <th>Area & Sales</th>
                        <th>Paket Layanan</th>
                        <th>Jatuh Tempo</th>
                        <th>Info Tagihan</th>
                        <th>Status</th>
                        <th>Mulai Bayar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody id="admin-tagihan-table-body">
                    @include('admin.tagihan.partials.table', ['pelanggan' => $pelanggan])
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrapper" id="admin-tagihan-pagination">
            {{ $pelanggan->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>

        {{-- MODAL CONTAINER --}}
        <div id="modal-container-admin-tagihan">
            @include('admin.tagihan.partials.modals', ['pelanggan' => $pelanggan])
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI --}}
<div class="modal fade" id="modal-confirm-bayar-periode" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-warning text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title fw-bold"><i class="bi bi-cash-stack me-2"></i>Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-question-circle-fill text-warning" style="font-size: 3rem;"></i>
                </div>
                <p id="confirm-bayar-periode-text" class="mb-0 fw-medium fs-6 text-secondary"></p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" class="btn btn-light px-4 rounded-pill border" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning px-4 rounded-pill fw-bold text-dark" id="btn-confirm-bayar-periode">Ya, Lanjut Bayar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- SCRIPT ASLI TIDAK BERUBAH SAMA SEKALI --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    let timeout     = null;
    let currentPage = 1;

    const searchInput = document.getElementById('search-admin-tagihan');
    const paketSelect = document.getElementById('paket-admin-tagihan');
    const salesSelect = document.getElementById('sales-admin-tagihan');
    const areaSelect  = document.getElementById('area-admin-tagihan');

    function initFormBayarPeriode() {
        document.querySelectorAll('.form-bayar-periode-admin').forEach(function (form) {
            const inputJumlah  = form.querySelector('.input-jumlah-bulan');
            const previewText  = form.querySelector('.text-preview-bayar');
            if (!inputJumlah || !previewText) return;

            function parseYm(ym) {
                const [y, m] = ym.split('-').map(Number);
                return { year: y, month: m };
            }

            function formatBulanTahun(dateObj) {
                const bulanNama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                return bulanNama[dateObj.month - 1] + ' ' + dateObj.year;
            }

            function addMonths(dateObj, n) {
                let y = dateObj.year;
                let m = dateObj.month + n;
                while (m > 12) { m -= 12; y += 1; }
                while (m < 1)  { m += 12; y -= 1; }
                return { year: y, month: m };
            }

            function ymString(dateObj) {
                return `${dateObj.year}-${String(dateObj.month).padStart(2,'0')}`;
            }

            function isAfterOrEqual(a, b) {
                return (a.year > b.year) || (a.year === b.year && a.month >= b.month);
            }

            const startYm       = form.dataset.startYm;
            const hargaPerBulan = Number(form.dataset.hargaPerBulan || 0);
            const maxBulan      = parseInt(form.dataset.maxBulan || '60', 10);

            let bulanTagihan = [];
            try { bulanTagihan = JSON.parse(form.dataset.bulanTagihan || '[]'); } catch (e) { bulanTagihan = []; }

            const startObj = parseYm(startYm);

            function computePaidMonths(jml) {
                const paid = [];
                if (bulanTagihan.length === 0) {
                    let curr = { ...startObj };
                    for (let i = 0; i < jml; i++) {
                        paid.push({ ...curr });
                        curr = addMonths(curr, 1);
                    }
                    return paid;
                }
                const lastExistingYm  = bulanTagihan[bulanTagihan.length - 1];
                const lastExistingObj = parseYm(lastExistingYm);
                let curr  = { ...startObj };
                let count = 0;
                while (true) {
                    const ym = ymString(curr);
                    if (bulanTagihan.includes(ym)) {
                        paid.push({ ...curr });
                        count++;
                        if (count === jml) return paid;
                    }
                    if (isAfterOrEqual(curr, lastExistingObj)) break; 
                    curr = addMonths(curr, 1);
                }
                let base = addMonths(lastExistingObj, 1);
                let curr2 = { ...base };
                while (count < jml) {
                    paid.push({ ...curr2 });
                    count++;
                    curr2 = addMonths(curr2, 1);
                }
                return paid;
            }

            function updatePreview() {
                let jml = parseInt(inputJumlah.value || '1', 10);
                if (isNaN(jml) || jml < 1) jml = 1;
                if (jml > maxBulan) jml = maxBulan;
                inputJumlah.value = jml;
                const paidMonths = computePaidMonths(jml);
                if (paidMonths.length === 0) {
                    previewText.innerHTML = 'Tidak ada bulan tagihan yang bisa dibayar.';
                    return;
                }
                const startLabel = formatBulanTahun(paidMonths[0]);
                const endLabel   = formatBulanTahun(paidMonths[paidMonths.length - 1]);
                const total = jml * hargaPerBulan;
                const kalimat = (jml === 1)
                    ? `Akan dibayar 1 bulan tagihan untuk ${startLabel}.`
                    : `Akan dibayar ${jml} bulan tagihan, dari ${startLabel} sampai ${endLabel}.`;
                previewText.innerHTML = `${kalimat}<br>Perkiraan total: <strong>Rp ${total.toLocaleString('id-ID')}</strong> (Rp ${hargaPerBulan.toLocaleString('id-ID')} x ${jml} bulan).`;
            }
            inputJumlah.addEventListener('input', updatePreview);
            updatePreview();
        });
    }

    function loadAdminTagihanTable(page = 1) {
        currentPage = page;
        const params = { ajax: true, page: page, search: searchInput.value, paket: paketSelect.value, sales: salesSelect.value, area: areaSelect.value };
        fetch(`{{ route('admin.tagihan.index') }}?` + new URLSearchParams(params), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(res => {
            document.getElementById('admin-tagihan-table-body').innerHTML = res.html;
            document.getElementById('admin-tagihan-pagination').innerHTML = res.pagination;
            if (res.modals) document.getElementById('modal-container-admin-tagihan').innerHTML = res.modals;
            updateUrl(params.search, params.paket, params.sales, params.area, page);
            initFormBayarPeriode(); 
        })
        .catch(err => { console.error(err); alert('Gagal memuat data tagihan'); });
    }

    function updateUrl(search, paket, sales, area, page) {
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (paket)  params.set('paket', paket);
        if (sales)  params.set('sales', sales);
        if (area)   params.set('area', area);
        if (page > 1) params.set('page', page);
        const newUrl = params.toString() ? '{{ route("admin.tagihan.index") }}?' + params.toString() : '{{ route("admin.tagihan.index") }}';
        window.history.replaceState({}, '', newUrl);
    }

    function initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('search')) searchInput.value = params.get('search');
        if (params.get('paket')) paketSelect.value = params.get('paket');
        if (params.get('sales')) salesSelect.value = params.get('sales');
        if (params.get('area')) areaSelect.value = params.get('area');
        loadAdminTagihanTable(params.get('page') || 1);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () { loadAdminTagihanTable(1); }, 250);
        });
    }

    [paketSelect, salesSelect, areaSelect].forEach(function (el) {
        if (!el) return;
        el.addEventListener('change', function () { loadAdminTagihanTable(1); });
    });

    document.addEventListener('click', function (e) {
        const link = e.target.closest('#admin-tagihan-pagination .pagination a');
        if (!link) return;
        e.preventDefault();
        const url  = new URL(link.getAttribute('href'));
        loadAdminTagihanTable(url.searchParams.get('page') || 1);
    });

    initFromUrl();
    initFormBayarPeriode();
});
</script>
@endpush