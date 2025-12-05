@extends('seles2.layout.master')

@php
    use Carbon\Carbon;
@endphp

@section('content')
    <div class="pelanggan-page">

        {{-- HEADER (Tema Amber) --}}
        <div class="pelanggan-header d-flex align-items-center">
            <a href="{{ route('dashboard-sales') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold">Riwayat Pembayaran</h5>
        </div>

        {{-- FILTER & PENCARIAN --}}
        <div class="filter-bar mt-3 px-1">
            <form id="filter-form" method="GET" action="{{ route('seles2.pembayaran.riwayat') }}" class="w-100">

                {{-- BARIS 1: search --}}
                <div class="d-flex gap-2 mb-2">
                    <div class="flex-grow-1">
                        <div class="input-group input-group-sm shadow-sm">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" id="search-input"
                                class="form-control border-start-0 rounded-end-pill ps-0"
                                placeholder="Cari no bayar / nama / area..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>

                {{-- BARIS 2: area + tanggal dari --}}
                <div class="d-flex gap-2 mb-2">
                    <div class="flex-grow-1">
                        <select name="area_id" class="form-select form-select-sm rounded-pill shadow-sm text-muted">
                            <option value="">Semua area</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id_area }}"
                                    {{ request('area_id') == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nama_area }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-grow-1">
                        <input type="date" name="tanggal"
                            class="form-control form-control-sm rounded-pill shadow-sm text-muted"
                            value="{{ request('tanggal') }}">
                    </div>
                </div>
            </form>
        </div>

        {{-- LIST RIWAYAT --}}
        <div class="pelanggan-list mt-3">
            @forelse ($pembayaran as $pay)
                @php
                    // === LOGIKA PHP ASLI ===
                    $pelanggan = $pay->pelanggan;
                    $area = $pelanggan?->area?->nama_area;

                    $salesName = $pay->sales?->user?->name ?? $pelanggan?->sales?->user?->name;

                    $adminName = $pay->user?->name;

                    if (is_null($pay->id_sales)) {
                        $sumberText = 'Admin' . ($adminName ? ' • ' . $adminName : '');
                        $badgeClass =
                            'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25';
                    } else {
                        $sumberText = 'Sales' . ($salesName ? ' • ' . $salesName : '');
                        $badgeClass = 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25';
                    }

                    $tanggalBayar = $pay->tanggal_bayar
                        ? Carbon::parse($pay->tanggal_bayar)->translatedFormat('d F Y, H:i')
                        : '-';

                    $modalId = 'modal-riwayat-' . $pay->id_pembayaran;
                @endphp

                <div class="pelanggan-card position-relative mb-3" data-no="{{ $pay->no_pembayaran }}"
                    data-nama="{{ $pelanggan->nama ?? '' }}" data-area="{{ $area ?? '' }}" data-bs-toggle="modal"
                    data-bs-target="#{{ $modalId }}">
                    <div class="row g-0 align-items-center w-100 m-0">
                        {{-- KIRI --}}
                        <div class="col-7 pe-2">
                            {{-- Nama & No Bayar --}}
                            <div class="d-flex flex-column mb-1">
                                <h6 class="fw-bold text-dark mb-0 text-truncate pelanggan-nama">
                                    {{ $pelanggan->nama ?? '-' }}
                                </h6>
                                <small class="text-muted font-monospace" style="font-size: 0.7rem;">
                                    #{{ $pay->no_pembayaran }}
                                </small>
                            </div>

                            {{-- Area --}}
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <span
                                    class="badge bg-light text-secondary border border-light small px-2 py-1 rounded-pill fw-normal text-truncate"
                                    style="max-width: 100%;">
                                    <i class="bi bi-geo-alt-fill me-1 text-warning"></i>
                                    {{ strtoupper($area ?? '-') }}
                                </span>
                            </div>

                            {{-- Tanggal --}}
                            <div class="small text-muted mt-1 text-truncate" style="font-size: 0.75rem;">
                                <i class="bi bi-calendar-check me-1"></i> {{ $tanggalBayar }}
                            </div>

                            {{-- Sumber (Sales/Admin) --}}
                            <div class="mt-2">
                                <span class="badge {{ $badgeClass }} rounded-pill fw-normal px-2 py-1"
                                    style="font-size: 0.7rem;">
                                    {{ $sumberText }}
                                </span>
                            </div>
                        </div>

                        {{-- KANAN --}}
                        <div class="col-5 ps-3 harga-col d-flex flex-column justify-content-between h-100">
                            <div class="d-flex flex-column align-items-end w-100">
                                <div class="badge bg-light text-muted border mb-1 fw-normal" style="font-size: 0.65rem;">
                                    ID: {{ $pay->id_pembayaran }}
                                </div>
                            </div>

                            <div class="text-end w-100 mt-3 pt-2 border-top border-light">
                                <div class="small text-muted mb-0" style="font-size: 0.7rem;">Nominal</div>
                                <span class="fw-bold text-dark" style="font-size: 1.1rem;">
                                    <span class="text-secondary small me-1" style="font-size: 0.8rem;">Rp</span>
                                    {{ number_format($pay->nominal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL DETAIL PEMBAYARAN (Modern Style) --}}
                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-mobile">
                        <div class="modal-content border-0 shadow-lg rounded-4">

                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h6 class="modal-title fw-bold mb-0">Detail Pembayaran</h6>
                                    <small class="text-muted font-monospace" style="font-size: 0.75rem;">
                                        #{{ $pay->no_pembayaran }}
                                    </small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body p-3 small">
                                {{-- Info Utama --}}
                                <div class="mb-3 info-box bg-light border border-warning border-opacity-25 rounded-3 p-3">
                                    <div
                                        class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-10">
                                        <span class="text-muted tiny">Pelanggan</span>
                                        <span class="fw-bold text-end text-dark">
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
                                        <span class="text-muted tiny">Diterima Oleh</span>
                                        <span class="text-end">{{ $sumberText }}</span>
                                    </div>
                                    <div
                                        class="mt-2 pt-2 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                                        <span class="text-muted tiny fw-bold">TOTAL BAYAR</span>
                                        <span class="fw-bold text-success fs-6">
                                            Rp {{ number_format($pay->nominal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Tabel Item Tagihan --}}
                                @if ($pay->items->isEmpty())
                                    <div class="text-center text-muted py-3 bg-light rounded-3">
                                        <i class="bi bi-inbox small"></i> Tidak ada detail tagihan.
                                    </div>
                                @else
                                    <div class="fw-bold mb-2 text-dark tiny px-1">Rincian Tagihan</div>
                                    <div class="table-responsive rounded-3 border bg-white shadow-sm">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead class="bg-light tiny text-secondary">
                                                <tr>
                                                    <th class="ps-3 py-2 fw-normal">Bulan</th>
                                                    <th class="py-2 fw-normal">Paket</th>
                                                    <th class="text-end pe-3 py-2 fw-normal">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="tiny">
                                                @foreach ($pay->items as $item)
                                                    @php
                                                        $tagihan = $item->tagihan;
                                                        $langganan = $tagihan?->langganan;
                                                        $paket = $langganan?->paket;
                                                        $bulanTahun = $tagihan
                                                            ? Carbon::create(
                                                                $tagihan->tahun,
                                                                $tagihan->bulan,
                                                                1,
                                                            )->translatedFormat('F Y')
                                                            : '-';
                                                    @endphp
                                                    <tr>
                                                        <td class="ps-3 fw-medium">{{ $bulanTahun }}</td>
                                                        <td class="text-truncate text-muted" style="max-width: 120px;">
                                                            {{ $paket->nama_paket ?? '-' }}
                                                        </td>
                                                        <td class="text-end pe-3 fw-bold text-dark">
                                                            {{ number_format($item->nominal_bayar, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            <div class="modal-footer border-0 pt-0 pb-3">
                                <button class="btn btn-light rounded-pill w-100 fw-bold text-secondary"
                                    data-bs-dismiss="modal">
                                    Tutup
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            @empty
                <div class="text-center mt-5 py-5 px-3">
                    <div class="mb-3">
                        <i class="bi bi-receipt-cutoff text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Belum ada pembayaran</h6>
                    <p class="text-muted small">Riwayat pembayaran yang Anda proses akan muncul di sini.</p>
                </div>
            @endforelse

            {{-- PAGINATION --}}
            @if (method_exists($pembayaran, 'links'))
                <div class="mt-4 px-2">
                    {{ $pembayaran->appends(request()->query())->links() }}
                </div>
            @endif
        </div>

        <div class="hint-footer text-center mt-3 mb-2 mx-3 shadow-sm">
            <i class="bi bi-info-circle me-1"></i> Tap kartu untuk melihat rincian
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Page Style */
        .pelanggan-page {
            background: #f9fafb;
            min-height: 100vh;
            padding-bottom: 90px;
            /* Space untuk bottom nav */
        }

        /* 1. HEADER (Gradient Kuning Emas) */
        .pelanggan-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px 20px 30px 20px;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 10px -16px;
            gap: 12px;
            position: relative;
            z-index: 10;
        }

        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            justify-content: center;
            transition: 0.2s;
        }

        .back-btn:active {
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0.9);
        }

        /* 2. FILTER BAR */
        .filter-bar {
            position: relative;
            z-index: 11;
            margin-top: -20px !important;
            padding: 0 16px;
        }

        .filter-bar input,
        .filter-bar select,
        .input-group-text {
            border: 1px solid #f3f4f6;
            height: 40px;
            font-size: 0.85rem;
        }

        .filter-bar input:focus,
        .filter-bar select:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
        }

        /* 3. CARD RIWAYAT */
        .pelanggan-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f3f4f6;
            transition: transform 0.2s;
            cursor: pointer;
        }

        .pelanggan-card:active {
            transform: scale(0.98);
            background-color: #fcfcfc;
        }

        .harga-col {
            border-left: 1px dashed #e5e7eb;
        }

        /* 4. FOOTER HINT */
        .hint-footer {
            background: #fffbeb;
            color: #d97706;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #fcd34d;
        }

        /* 5. MODAL */
        .tiny {
            font-size: 12px;
        }

        .modal-mobile {
            max-width: 420px;
            margin: 0.5rem auto;
        }

        @media (max-width: 575.98px) {
            .modal-mobile {
                max-width: 95%;
                margin: 1rem auto;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filter-form');
            if (!form) return;

            const inputs = form.querySelectorAll('input[name], select[name]');
            let timer = null;

            function submitWithDebounce() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    form.submit();
                }, 500); // Sedikit diperlambat biar smooth
            }

            inputs.forEach(el => {
                const eventName =
                    (el.tagName === 'INPUT' && el.type === 'text') ?
                    'input' :
                    'change';

                el.addEventListener(eventName, submitWithDebounce);
            });
        });
    </script>
@endpush
