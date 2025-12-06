@extends('seles2.layout.master')

@section('content')
    <div class="pelanggan-page">

        {{-- HEADER (Tema Amber) --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('dashboard-sales') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="mb-0 fw-bold">Status Pelanggan</h5>
            </div>
        </div>

        @php
            // LOGIKA PHP ASLI (TIDAK DIUBAH)
            $statusPage = $status ?? request('status', 'aktif');

            $totalBaru = $statusCounts['baru'] ?? 0;
            $totalAktif = $statusCounts['aktif'] ?? 0;
            $totalBerhenti = $statusCounts['berhenti'] ?? 0;
            $totalIsolir = $statusCounts['isolir'] ?? 0;
        @endphp

        {{-- TAB STATUS (Scrollable & Amber Theme) --}}
        <div class="status-tabs-container mt-3 px-1">
            <div class="status-tabs d-flex gap-2 overflow-auto pb-2 px-2">
                <a href="{{ route('seles2.pelanggan.status', ['status' => 'baru']) }}"
                    class="status-tab {{ $statusPage === 'baru' ? 'active' : '' }}">
                    Baru
                    <span class="badge-count">{{ $totalBaru }}</span>
                </a>

                <a href="{{ route('seles2.pelanggan.status', ['status' => 'aktif']) }}"
                    class="status-tab {{ $statusPage === 'aktif' ? 'active' : '' }}">
                    Aktif
                    <span class="badge-count">{{ $totalAktif }}</span>
                </a>

                <a href="{{ route('seles2.pelanggan.status', ['status' => 'isolir']) }}"
                    class="status-tab {{ $statusPage === 'isolir' ? 'active' : '' }}">
                    Isolir
                    <span class="badge-count">{{ $totalIsolir }}</span>
                </a>

                <a href="{{ route('seles2.pelanggan.status', ['status' => 'berhenti']) }}"
                    class="status-tab {{ $statusPage === 'berhenti' ? 'active' : '' }}">
                    Berhenti
                    <span class="badge-count">{{ $totalBerhenti }}</span>
                </a>
            </div>
        </div>

        {{-- FILTER & PENCARIAN --}}
        <div class="filter-bar mt-2 px-1">
            <div class="flex-grow-1 me-2">
                <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control border-start-0 rounded-end-pill ps-0"
                        placeholder="Cari nama, HP, alamat...">
                </div>
            </div>

            <div style="width: 140px;">
                <select id="area-filter" class="form-select form-select-sm rounded-pill shadow-sm text-muted">
                    <option value="">Semua Wilayah</option>
                    @php
                        $areas = $dataArea ?? collect([]);
                    @endphp
                    @foreach ($areas as $area)
                        <option value="{{ strtolower($area->nama_area) }}">
                            {{ $area->nama_area }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- LIST PELANGGAN --}}
        <div class="pelanggan-list mt-3">
            @forelse ($pelanggan as $p)
                @php
                    // LOGIKA PHP ASLI
                    $rawStatus = $p->status_pelanggan;
                    $statusBadge = $p->status_pelanggan_efektif ?? $rawStatus;
                    $langgananAktif = $p->langganan->sortByDesc('tanggal_mulai')->first();

                    $tanggalKolom = null;
                    if ($rawStatus === 'baru') {
                        $tanggalKolom = $p->tanggal_registrasi;
                    } elseif ($langgananAktif) {
                        if ($rawStatus === 'aktif') {
                            $tanggalKolom = $langgananAktif->tanggal_mulai;
                        } elseif ($rawStatus === 'isolir') {
                            $tanggalKolom = $langgananAktif->tanggal_isolir;
                        } elseif ($rawStatus === 'berhenti') {
                            $tanggalKolom = $langgananAktif->tanggal_berhenti;
                        }
                    }

                    $labelTanggal = match ($rawStatus) {
                        'baru' => 'Registrasi',
                        'aktif' => 'Aktif Sejak',
                        'isolir' => 'Tgl Isolir',
                        'berhenti' => 'Berhenti',
                        default => 'Tanggal',
                    };

                    $areaName = $p->area->nama_area ?? '-';
                    $paketName = $langgananAktif->paket->nama_paket ?? '-';
                    $hargaTotal = $langgananAktif->paket->harga_total ?? 0;

                    // Mapping Warna Badge Bootstrap 5 (menggunakan bg-...)
                    $badgeClass = match ($statusBadge) {
                        'baru' => 'bg-warning text-dark', // Kuning
                        'aktif' => 'bg-success', // Hijau
                        'isolir' => 'bg-secondary', // Abu-abu (Isolir biasanya abu/gelap)
                        'berhenti' => 'bg-danger', // Merah
                        default => 'bg-secondary',
                    };
                @endphp

                <div class="pelanggan-card position-relative mb-3" data-nama="{{ strtolower($p->nama ?? '') }}"
                    data-hp="{{ strtolower($p->nomor_hp ?? '') }}" data-alamat="{{ strtolower($p->alamat ?? '') }}"
                    data-area="{{ strtolower($areaName) }}" data-ip="{{ strtolower($p->ip_address ?? '') }}"
                    data-status="{{ strtolower($statusBadge) }}">

                    {{-- CARD BISA DIKLIK --}}
                    <a href="{{ route('seles2.pelanggan.show', $p->id_pelanggan) }}" class="stretched-link"></a>

                    <div class="row g-0 align-items-center w-100 m-0">
                        {{-- KIRI --}}
                        <div class="col-7 pe-2">
                            {{-- Nama & Status Badge --}}
                            <div class="d-flex flex-column mb-1">
                                <h6 class="fw-bold text-dark mb-0 text-truncate pelanggan-nama">
                                    {{ $p->nama ?? '-' }}
                                </h6>
                            </div>

                            {{-- Area --}}
                            <div class="d-flex align-items-center gap-1 mb-1">
                                <span
                                    class="badge bg-light text-secondary border border-light small px-2 py-1 rounded-pill fw-normal text-truncate"
                                    style="max-width: 100%;">
                                    <i class="bi bi-geo-alt-fill me-1 text-warning"></i>
                                    {{ strtoupper($areaName) }}
                                </span>
                            </div>

                            {{-- Alamat --}}
                            <div class="small text-muted text-truncate mb-2" style="font-size: 0.75rem;">
                                {{ $p->alamat ?? 'Alamat tidak tersedia' }}
                            </div>

                            {{-- Paket --}}
                            <div class="small mt-1 bg-light d-inline-block px-2 py-1 rounded border border-light text-truncate"
                                style="max-width: 100%;">
                                <span class="text-muted">Paket:</span> <span
                                    class="fw-semibold text-dark">{{ $paketName }}</span>
                            </div>
                        </div>

                        {{-- KANAN --}}
                        <div class="col-5 ps-3 harga-col d-flex flex-column justify-content-between h-100">
                            <div class="d-flex flex-column align-items-end w-100">
                                {{-- Badge Status di Kanan Atas --}}
                                <span class="badge {{ $badgeClass }} rounded-pill mb-2 shadow-sm"
                                    style="font-size: 0.65rem;">
                                    {{ strtoupper($statusBadge) }}
                                </span>

                                {{-- IP Address --}}
                                @if (!empty($p->ip_address))
                                    <div class="small fw-normal text-muted mb-1 text-end text-break font-monospace"
                                        style="font-size: 0.7rem;">
                                        {{ $p->ip_address }}
                                    </div>
                                @endif

                                {{-- Tanggal --}}
                                @if ($tanggalKolom)
                                    <div class="small text-muted text-end" style="font-size: 0.7rem; line-height: 1.2;">
                                        {{ $labelTanggal }}<br>
                                        <span class="fw-bold text-dark">
                                            {{ \Carbon\Carbon::parse($tanggalKolom)->locale('id')->translatedFormat('d M Y') }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Harga --}}
                            <div class="text-end w-100 mt-2 pt-2 border-top border-light">
                                <div class="small text-muted mb-0" style="font-size: 0.7rem;">Tagihan</div>
                                <span class="fw-bold text-dark" style="font-size: 1.1rem;">
                                    <span class="text-secondary small me-1" style="font-size: 0.8rem;">Rp</span>
                                    {{ number_format($hargaTotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center mt-5 py-5 px-3">
                    <div class="mb-3">
                        <i class="bi bi-filter-circle text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Tidak ada data</h6>
                    <p class="text-muted small">Belum ada pelanggan dengan status ini.</p>
                </div>
            @endforelse

            {{-- PAGINATION --}}
            @if (method_exists($pelanggan, 'links'))
                <div class="mt-4 px-2">
                    {{ $pelanggan->links() }}
                </div>
            @endif
        </div>

        <div class="hint-footer text-center mt-3 mb-2 mx-3 shadow-sm">
            <i class="bi bi-hand-index-thumb me-1"></i> Tap kartu untuk detail
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
        }

        /* 1. HEADER (Gradient Amber) */
        .pelanggan-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px 20px 30px 20px;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 0 -16px;
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

        /* 2. TAB STATUS (Scrollable Pills) */
        .status-tabs-container {
            position: relative;
            z-index: 11;
            margin-top: -25px !important;
            /* Naik menimpa header */
        }

        .status-tabs::-webkit-scrollbar {
            display: none;
        }

        .status-tabs {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .status-tab {
            white-space: nowrap;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            color: #6b7280;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid #f3f4f6;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .status-tab.active {
            background: #d97706;
            /* Amber Dark */
            color: #fff;
            border-color: #d97706;
            box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3);
        }

        .badge-count {
            background: rgba(255, 255, 255, 0.25);
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .status-tab:not(.active) .badge-count {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* 3. FILTER BAR */
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

        /* 4. CARD PELANGGAN */
        .pelanggan-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border: 1px solid #f3f4f6;
            transition: transform 0.2s;
        }

        .pelanggan-card:active {
            transform: scale(0.98);
            background-color: #fcfcfc;
        }

        .pelanggan-nama {
            font-size: 1rem;
            color: #111827;
        }

        .harga-col {
            border-left: 1px dashed #e5e7eb;
        }

        /* 5. FOOTER HINT */
        .hint-footer {
            background: #fffbeb;
            color: #d97706;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #fcd34d;
        }

        .stretched-link {
            position: absolute;
            inset: 0;
            z-index: 5;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const areaFilter = document.getElementById('area-filter');
            const cards = document.querySelectorAll('.pelanggan-card');

            function applyFilter() {
                const q = (searchInput.value || '').toLowerCase();
                const area = (areaFilter.value || '').toLowerCase();

                cards.forEach(card => {
                    const nama = card.dataset.nama || '';
                    const hp = card.dataset.hp || '';
                    const alamat = card.dataset.alamat || '';
                    const areaCd = card.dataset.area || '';
                    const ip = card.dataset.ip || '';
                    const status = card.dataset.status || '';

                    const textMatch = !q ||
                        nama.includes(q) ||
                        hp.includes(q) ||
                        alamat.includes(q) ||
                        areaCd.includes(q) ||
                        ip.includes(q) ||
                        status.includes(q);

                    const areaMatch = !area || areaCd === area;

                    card.style.display = (textMatch && areaMatch) ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyFilter);
            }

            if (areaFilter) {
                areaFilter.addEventListener('change', applyFilter);
            }
        });
    </script>
@endpush
