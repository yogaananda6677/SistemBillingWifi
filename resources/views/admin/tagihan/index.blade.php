@extends('layouts.master')
@php
    use Carbon\Carbon;
@endphp

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
    .preview-bayar-box {
    background: #e8f4ff;
    border: 1px solid #b6daff;
    border-radius: 6px;
    font-size: 13px;
}

</style>

<div class="container-fluid p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">Pembayaran Tagihan oleh Admin</h4>
    </div>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- SEARCH + FILTER (Realtime, tanpa tombol) --}}
    <div class="d-flex gap-3 mb-4 flex-wrap" id="filter-admin-tagihan-wrapper">
        <div class="search-box flex-grow-1" style="min-width: 250px;">
            <input type="text" id="search-admin-tagihan" class="form-control"
                   placeholder="Cari pelanggan / paket..."
                   value="{{ request('search') }}">
        </div>

        {{-- FILTER SALES --}}
        <select id="sales-admin-tagihan" class="filter-select" style="min-width: 150px;">
            <option value="">Semua Sales</option>
            @foreach($dataSales as $s)
                <option value="{{ $s->id_sales }}" {{ request('sales') == $s->id_sales ? 'selected' : '' }}>
                    {{ $s->user->name }}
                </option>
            @endforeach
        </select>

        {{-- FILTER WILAYAH --}}
        <select id="area-admin-tagihan" class="filter-select" style="min-width: 150px;">
            <option value="">Semua Wilayah</option>
            @foreach($dataArea as $area)
                <option value="{{ $area->id_area }}" {{ request('area') == $area->id_area ? 'selected' : '' }}>
                    {{ $area->nama_area }}
                </option>
            @endforeach
        </select>

        {{-- FILTER PAKET --}}
        <select id="paket-admin-tagihan" class="filter-select" style="min-width: 150px;">
            <option value="">Semua Paket</option>
            @foreach($paketList as $paket)
                <option value="{{ $paket->id_paket }}" {{ request('paket') == $paket->id_paket ? 'selected' : '' }}>
                    {{ $paket->nama_paket }}
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
                        <th>Area & Sales</th>
                        <th>Paket Layanan</th>
                        <th>Tanggal jatuh tempo</th>
                        <th>Info Tagihan</th>
                        <th>Status</th>
                        <th>Mulai Bayar Dari</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody id="admin-tagihan-table-body">
                    @include('admin.tagihan.partials.table', ['pelanggan' => $pelanggan])
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper" id="admin-tagihan-pagination">
            {{ $pelanggan->links() }}
        </div>

        {{-- MODAL BAYAR PERIODE (biarin sama kayak punyamu tadi) --}}
        <div id="modal-container-admin-tagihan">
    @include('admin.tagihan.partials.modals', ['pelanggan' => $pelanggan])
</div>

    </div>
</div>

{{-- GLOBAL MODAL KONFIRMASI BAYAR PERIODE --}}
<div class="modal fade" id="modal-confirm-bayar-periode" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembayaran Periode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <p id="confirm-bayar-periode-text" class="mb-0">
                    {{-- teks konfirmasi akan diisi via JS --}}
                </p>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button"
                        class="btn btn-success"
                        id="btn-confirm-bayar-periode">
                    Ya, Lanjut Bayar
                </button>
            </div>

        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let timeout     = null;
    let currentPage = 1;

    const searchInput = document.getElementById('search-admin-tagihan');
    const paketSelect = document.getElementById('paket-admin-tagihan');
    const salesSelect = document.getElementById('sales-admin-tagihan');
    const areaSelect  = document.getElementById('area-admin-tagihan');

    // ============================
    // Helper modal "Bayar Periode"
    // ============================
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
            const bulanNama = [
                'Januari','Februari','Maret','April','Mei','Juni',
                'Juli','Agustus','September','Oktober','November','Desember'
            ];
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
        try {
            bulanTagihan = JSON.parse(form.dataset.bulanTagihan || '[]');
        } catch (e) {
            bulanTagihan = [];
        }

        const startObj = parseYm(startYm);

        function computePaidMonths(jml) {
            const paid = [];

            // Kalau belum ada tagihan sama sekali → anggap maju berurutan dari start
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

            // 1️⃣ Dari start sampai bulan tagihan terakhir → hanya hitung yg ADA tagihan
            while (true) {
                const ym = ymString(curr);

                if (bulanTagihan.includes(ym)) {
                    paid.push({ ...curr });
                    count++;
                    if (count === jml) return paid;
                }

                if (isAfterOrEqual(curr, lastExistingObj)) {
                    break; // sudah sampai di bulan tagihan terakhir
                }

                curr = addMonths(curr, 1);
            }

            // 2️⃣ Kalau masih kurang → lanjut bulan setelah lastExisting, semua dianggap tagihan baru
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

            // fallback kalau ada yang aneh
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

            previewText.innerHTML = `
                ${kalimat}<br>
                Perkiraan total: <strong>Rp ${total.toLocaleString('id-ID')}</strong>
                (Rp ${hargaPerBulan.toLocaleString('id-ID')} x ${jml} bulan).
            `;
        }

        inputJumlah.addEventListener('input', updatePreview);
        updatePreview();
    });
}

    // ============================
    // Load tabel + pagination + modals via AJAX
    // ============================
    function loadAdminTagihanTable(page = 1) {
        currentPage = page;

        const search = searchInput ? searchInput.value : '';
        const paket  = paketSelect ? paketSelect.value : '';
        const sales  = salesSelect ? salesSelect.value : '';
        const area   = areaSelect  ? areaSelect.value  : '';

        const params = {
            ajax:   true,
            page:   page,
            search: search,
            paket:  paket,
            sales:  sales,
            area:   area,
        };

        fetch(`{{ route('admin.tagihan.index') }}?` + new URLSearchParams(params), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            document.getElementById('admin-tagihan-table-body').innerHTML = res.html;
            document.getElementById('admin-tagihan-pagination').innerHTML = res.pagination;

            if (res.modals) {
                document.getElementById('modal-container-admin-tagihan').innerHTML = res.modals;
            }

            updateUrl(search, paket, sales, area, page);
            initFormBayarPeriode(); // re-bind event ke modal-modal baru
        })
        .catch(err => {
            console.error(err);
            alert('Gagal memuat data tagihan');
        });
    }

    function updateUrl(search, paket, sales, area, page) {
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (paket)  params.set('paket', paket);
        if (sales)  params.set('sales', sales);
        if (area)   params.set('area', area);
        if (page > 1) params.set('page', page);

        const newUrl = params.toString()
            ? '{{ route("admin.tagihan.index") }}?' + params.toString()
            : '{{ route("admin.tagihan.index") }}';

        window.history.replaceState({}, '', newUrl);
    }

    function initFromUrl() {
        const params = new URLSearchParams(window.location.search);
        const search = params.get('search');
        const paket  = params.get('paket');
        const sales  = params.get('sales');
        const area   = params.get('area');
        const page   = params.get('page') || 1;

        if (search && searchInput) searchInput.value = search;
        if (paket && paketSelect)  paketSelect.value = paket;
        if (sales && salesSelect)  salesSelect.value = sales;
        if (area && areaSelect)    areaSelect.value  = area;

        loadAdminTagihanTable(page);
    }

    // live search
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                loadAdminTagihanTable(1);
            }, 250);
        });
    }

    [paketSelect, salesSelect, areaSelect].forEach(function (el) {
        if (!el) return;
        el.addEventListener('change', function () {
            loadAdminTagihanTable(1);
        });
    });

    // pagination via delegation
    document.addEventListener('click', function (e) {
        const link = e.target.closest('#admin-tagihan-pagination .pagination a');
        if (!link) return;

        e.preventDefault();
        const href = link.getAttribute('href') || '';
        const url  = new URL(href);
        const page = url.searchParams.get('page') || 1;
        loadAdminTagihanTable(page);
    });

    // pertama kali
    initFromUrl();
    initFormBayarPeriode();
});
</script>
@endpush
