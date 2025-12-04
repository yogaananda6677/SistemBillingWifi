@extends('seles2.layout.master')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="pelanggan-page">

    {{-- HEADER --}}
    <div class="pelanggan-header d-flex align-items-center">
        <a href="{{ route('dashboard-sales') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="mb-0 fw-semibold">Riwayat Pembayaran</h5>
    </div>

    {{-- FILTER & PENCARIAN --}}
    <div class="filter-bar mt-3 px-3">
        <form id="filter-form"
              method="GET"
              action="{{ route('seles2.pembayaran.riwayat') }}"
              class="w-100">

            {{-- BARIS 1: search --}}
            <div class="d-flex gap-2 mb-2">
                <div class="flex-grow-1">
                    <input
                        type="text"
                        name="search"
                        id="search-input"
                        class="form-control form-control-sm"
                        placeholder="Cari no bayar / nama / area..."
                        value="{{ request('search') }}"
                    >
                </div>
            </div>

            {{-- BARIS 2: area + tanggal dari --}}
            <div class="d-flex gap-2 mb-2">
                <div class="flex-grow-1">
                    <select name="area_id" class="form-select form-select-sm rounded-pill">
                        <option value="">Semua area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id_area }}"
                                {{ request('area_id') == $area->id_area ? 'selected' : '' }}>
                                {{ $area->nama_area }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-grow-1">
                    <input
                        type="date"
                        name="tanggal"
                        class="form-control form-control-sm rounded-pill"
                        value="{{ request('tanggal') }}"
                    >
                </div>

            </div>
        </form>
    </div>

    {{-- LIST RIWAYAT --}}
    <div class="pelanggan-list mt-3 px-3">
        @forelse ($pembayaran as $pay)
            @php
                $pelanggan = $pay->pelanggan;
                $area      = $pelanggan?->area?->nama_area;

                $salesName = $pay->sales?->user?->name
                    ?? $pelanggan?->sales?->user?->name;

                $adminName = $pay->user?->name;

                if (is_null($pay->id_sales)) {
                    $sumberText = 'Admin' . ($adminName ? ' • ' . $adminName : '');
                } else {
                    $sumberText = 'Sales' . ($salesName ? ' • ' . $salesName : '');
                }

                $tanggalBayar = $pay->tanggal_bayar
                    ? Carbon::parse($pay->tanggal_bayar)->translatedFormat('d F Y, H:i')
                    : '-';

                $modalId = 'modal-riwayat-' . $pay->id_pembayaran;
            @endphp

            <div
                class="pelanggan-card position-relative"
                data-no="{{ $pay->no_pembayaran }}"
                data-nama="{{ $pelanggan->nama ?? '' }}"
                data-area="{{ $area ?? '' }}"
                data-bs-toggle="modal"
                data-bs-target="#{{ $modalId }}"
            >
                <div class="row g-0 align-items-center w-100 m-0">
                    {{-- KIRI --}}
                    <div class="col-7">
                        <div class="d-flex flex-column mb-1">
                            <div class="fw-bold text-truncate pelanggan-nama">
                                {{ $pelanggan->nama ?? '-' }}
                            </div>
                            <div class="small text-muted text-truncate">
                                No: {{ $pay->no_pembayaran }}
                            </div>
                        </div>

                        <div class="text-muted small text-truncate">
                            {{ strtoupper($area ?? '-') }}
                        </div>

                        <div class="small text-muted mt-1 text-truncate">
                            {{ $tanggalBayar }}
                        </div>

                        <div class="mt-2 small">
                            <span class="fw-bold">
                                {{ $sumberText }}
                            </span>
                        </div>
                    </div>

                    {{-- KANAN --}}
                    <div class="col-5 ps-2 harga-col d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column align-items-end w-100">
                            <div class="small text-muted text-end">
                                ID: {{ $pay->id_pembayaran }}
                            </div>
                        </div>

                        <div class="text-end w-100 mt-3">
                            <span class="harga-label">Rp.</span>
                            <span class="harga-value">
                                {{ number_format($pay->nominal, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL DETAIL PEMBAYARAN (kecil, center) --}}
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-mobile">
                    <div class="modal-content">

                        <div class="modal-header py-2">
                            <div>
                                <h6 class="modal-title mb-0">Detail Pembayaran</h6>
                                <small class="text-muted">
                                    No: {{ $pay->no_pembayaran }} • ID: {{ $pay->id_pembayaran }}
                                </small>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-3 small">
                            <div class="mb-3 info-box">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted tiny">Pelanggan</span>
                                    <span class="fw-semibold text-end">
                                        {{ $pelanggan->nama ?? '-' }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted tiny">Area</span>
                                    <span class="text-end">{{ $area ?? '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted tiny">Tanggal Bayar</span>
                                    <span class="text-end">{{ $tanggalBayar }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted tiny">Sumber</span>
                                    <span class="text-end">{{ $sumberText }}</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted tiny">Total Pembayaran</span>
                                    <span class="fw-bold text-success">
                                        Rp {{ number_format($pay->nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            @if($pay->items->isEmpty())
                                <div class="text-center text-muted py-2">
                                    Tidak ada detail tagihan.
                                </div>
                            @else
                                <div class="fw-semibold mb-1 tiny">Detail Tagihan</div>
                                <div class="table-responsive rounded-3 border bg-white">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light tiny">
                                            <tr>
                                                <th>Bulan</th>
                                                <th>Paket</th>
                                                <th class="text-end">Nominal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="tiny">
                                            @foreach($pay->items as $item)
                                                @php
                                                    $tagihan    = $item->tagihan;
                                                    $langganan  = $tagihan?->langganan;
                                                    $paket      = $langganan?->paket;
                                                    $bulanTahun = $tagihan
                                                        ? Carbon::create($tagihan->tahun, $tagihan->bulan, 1)
                                                            ->translatedFormat('F Y')
                                                        : '-';
                                                @endphp
                                                <tr>
                                                    <td>{{ $bulanTahun }}</td>
                                                    <td class="text-truncate" style="max-width: 130px;">
                                                        {{ $paket->nama_paket ?? '-' }}
                                                    </td>
                                                    <td class="text-end">
                                                        Rp {{ number_format($item->nominal_bayar, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer py-2">
                            <button class="btn btn-secondary btn-sm w-100" data-bs-dismiss="modal">
                                Tutup
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        @empty
            <div class="text-center text-muted mt-4 p-4 bg-white rounded-3 shadow-sm mx-3">
                <i class="bi bi-receipt-cutoff display-4 mb-2"></i>
                <p>Belum ada pembayaran yang tercatat.</p>
            </div>
        @endforelse

        {{-- PAGINATION --}}
        @if(method_exists($pembayaran, 'links'))
            <div class="mt-3">
                {{ $pembayaran->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    <div class="hint-footer text-center mt-3 mb-2">
        Tap kartu untuk lihat detail pembayaran & tagihan.
    </div>
</div>
@endsection

@push('styles')
<style>
    .pelanggan-page {
        background: #f1f3f6;
        min-height: 100vh;
        padding-bottom: 70px; /* ruang bottom nav */
    }

    /* HEADER */
    .pelanggan-header {
        background: #4f46e5;
        color: #fff;
        padding: 12px 16px;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        margin: -12px -12px 12px -12px;
        gap: 12px;
    }

    .back-btn {
        color: #fff;
        text-decoration: none;
        font-size: 1.2rem;
    }

    /* FILTER BAR */
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-bar input,
    .filter-bar select {
        font-size: 0.85rem;
        border-radius: 999px;
    }

    /* LIST */
    .pelanggan-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin: 0;
    }

    /* CARD */
    .pelanggan-card {
        display: block !important;
        width: 100% !important;
        background: #ffffff;
        padding: 14px;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .pelanggan-nama {
        font-size: 1rem;
        color: #111827;
    }

    .pelanggan-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.08);
        transition: all 0.15s;
    }

    /* KANAN (INFO & NOMINAL) */
    .harga-col {
        border-left: 1px solid #e5e7eb;
        padding-left: 12px !important;
        text-align: right;
    }

    .harga-label {
        color: #111827;
        font-weight: 700;
        font-size: 0.9rem;
        margin-right: 2px;
    }

    .harga-value {
        color: #111827;
        font-weight: 800;
        font-size: 1.1rem;
    }

    .hint-footer {
        background: #4f46e5;
        color: #fff;
        padding: 8px 12px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin: 12px 12px 0 12px;
    }

    .info-box {
        background: #f9fafb;
        border-radius: 12px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
    }

    .tiny {
        font-size: 11px;
    }

    /* MODAL KECIL, MOBILE FRIENDLY */
    .modal-mobile {
        max-width: 420px;
        margin: 0.5rem auto;
    }

    @media (max-width: 575.98px) {
        .modal-mobile {
            max-width: 95%;
            margin: 1.25rem auto;
        }
        .modal-mobile .modal-content {
            border-radius: 16px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form   = document.getElementById('filter-form');
    if (!form) return;

    const inputs = form.querySelectorAll('input[name], select[name]');
    let timer    = null;

    function submitWithDebounce() {
        clearTimeout(timer);
        timer = setTimeout(() => {
            form.submit();
        }, 300);
    }

    inputs.forEach(el => {
        const eventName =
            (el.tagName === 'INPUT' && el.type === 'text')
                ? 'input'
                : 'change';

        el.addEventListener(eventName, submitWithDebounce);
    });
});
</script>
@endpush
