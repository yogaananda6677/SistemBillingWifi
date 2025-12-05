@extends('seles2.layout.master')

@section('content')
    <div class="pelanggan-page">

        {{-- HEADER --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <a href="{{ route('dashboard-sales') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="mb-0 fw-semibold">Status Pelanggan</h5>
            </div>
        </div>

        @php
            // status halaman yang sedang aktif
            $statusPage = $status ?? request('status', 'aktif');

            // total masing-masing status (dari controller)
            $totalBaru = $statusCounts['baru'] ?? 0;
            $totalAktif = $statusCounts['aktif'] ?? 0;
            $totalBerhenti = $statusCounts['berhenti'] ?? 0;
            $totalIsolir = $statusCounts['isolir'] ?? 0;
        @endphp

        {{-- TAB STATUS (BARU / AKTIF / BERHENTI / ISOLIR) --}}
        <div class="status-tabs mt-3 px-3">
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

            <a href="{{ route('seles2.pelanggan.status', ['status' => 'berhenti']) }}"
                class="status-tab {{ $statusPage === 'berhenti' ? 'active' : '' }}">
                Berhenti
                <span class="badge-count">{{ $totalBerhenti }}</span>
            </a>

            <a href="{{ route('seles2.pelanggan.status', ['status' => 'isolir']) }}"
                class="status-tab {{ $statusPage === 'isolir' ? 'active' : '' }}">
                Isolir
                <span class="badge-count">{{ $totalIsolir }}</span>
            </a>
        </div>

        {{-- FILTER & PENCARIAN --}}
        <div class="filter-bar mt-3 px-3">
            <div class="flex-grow-1 me-2">
                <input type="text" id="search-input" class="form-control form-control-sm"
                    placeholder="Cari nama / HP / alamat / wilayah / IP...">
            </div>

            <div style="width: 130px;">
                <select id="area-filter" class="form-select form-select-sm">
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
                    // status asli dari DB
                    $rawStatus = $p->status_pelanggan;

                    // status untuk badge/tampilan (bisa dari accessor)
                    $statusBadge = $p->status_pelanggan_efektif ?? $rawStatus;

                    // langganan utama (paling baru)
                    $langgananAktif = $p->langganan->sortByDesc('tanggal_mulai')->first();

                    // PILIH TANGGAL SESUAI STATUS
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

                    // label untuk tanggal (biar bisa dipakai di kanan)
                    $labelTanggal = match ($rawStatus) {
                        'baru' => 'Tgl Registrasi:',
                        'aktif' => 'Tgl Aktif:',
                        'isolir' => 'Tgl Isolir:',
                        'berhenti' => 'Tgl Berhenti:',
                        default => 'Tanggal:',
                    };

                    $areaName = $p->area->nama_area ?? '-';
                    $paketName = $langgananAktif->paket->nama_paket ?? '-';
                    $hargaTotal = $langgananAktif->paket->harga_total ?? 0;

                    // warna status
                    $statusColor = match ($statusBadge) {
                        'baru' => 'badge-secondary',
                        'aktif' => 'badge-success',
                        'isolir' => 'badge-warning',
                        'berhenti' => 'badge-danger',
                        default => 'badge-secondary',
                    };
                @endphp

                <div class="pelanggan-card position-relative" data-nama="{{ strtolower($p->nama ?? '') }}"
                    data-hp="{{ strtolower($p->nomor_hp ?? '') }}" data-alamat="{{ strtolower($p->alamat ?? '') }}"
                    data-area="{{ strtolower($areaName) }}" data-ip="{{ strtolower($p->ip_address ?? '') }}"
                    data-status="{{ strtolower($statusBadge) }}">
                    {{-- CARD BISA DIKLIK KE DETAIL --}}
                    <a href="{{ route('seles2.pelanggan.show', $p->id_pelanggan) }}" class="stretched-link"></a>

                    <div class="row g-0 align-items-center w-100 m-0">
                        {{-- KIRI --}}
                        <div class="col-7">
                            <div class="d-flex align-items-center mb-1">
                                <div class="fw-bold text-truncate pelanggan-nama me-2">
                                    {{ $p->nama ?? '-' }}
                                </div>
                                <span class="badge {{ $statusColor }} text-uppercase ms-auto" style="font-size: 0.65rem;">
                                    {{ strtoupper($statusBadge) }}
                                </span>
                            </div>

                            <div class="text-muted small text-truncate">
                                {{ strtoupper($areaName) }}
                                @if (!empty($p->nomor_hp))
                                    • {{ $p->nomor_hp }}
                                @endif
                            </div>

                            <div class="small text-muted text-truncate mt-1">
                                {{ $p->alamat ?? 'Alamat tidak tersedia' }}
                            </div>

                            <div class="small mt-2">
                                Paket:
                                <span class="fw-semibold">{{ $paketName }}</span>
                            </div>
                        </div>

                        {{-- KANAN --}}
                        <div class="col-5 ps-2 harga-col d-flex flex-column justify-content-between">
                            <div class="d-flex flex-column align-items-end w-100">
                                @if (!empty($p->ip_address))
                                    <div class="small fw-bold text-dark mb-1 text-end text-break">
                                        {{ $p->ip_address }}
                                    </div>
                                @endif

                                {{-- ⬇️ Tanggal dipindah ke bawah IP address --}}
                                @if ($tanggalKolom)
                                    <div class="small text-muted text-end">
                                        {{ $labelTanggal }}
                                        {{ \Carbon\Carbon::parse($tanggalKolom)->locale('id')->translatedFormat('d M Y') }}
                                    </div>
                                @endif
                            </div>

                            <div class="text-end w-100 mt-3">
                                <span class="harga-label">Rp.</span>
                                <span class="harga-value">
                                    {{ number_format($hargaTotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted mt-4 p-4 bg-white rounded-3 shadow-sm mx-3">
                    <i class="bi bi-person-x-fill display-4 mb-2"></i>
                    <p class="mb-0">Tidak ada pelanggan pada status ini.</p>
                </div>
            @endforelse

            {{-- PAGINATION --}}
            @if (method_exists($pelanggan, 'links'))
                <div class="mt-3 px-3">
                    {{ $pelanggan->links() }}
                </div>
            @endif
        </div>

        <div class="hint-footer text-center mt-3 mb-3">
            Tap kartu untuk lihat detail pelanggan.
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .pelanggan-page {
            background: #f1f3f6;
            min-height: 100vh;
            padding-bottom: 16px;
        }

        .stretched-link {
            position: absolute;
            inset: 0;
            z-index: 5;
        }

        .pelanggan-header {
            background: #4f46e5;
            color: #fff;
            padding: 12px 16px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            margin: -12px -12px 0 -12px;
        }

        .back-btn {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            margin-right: 12px;
        }

        /* TAB STATUS */
        .status-tabs {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .status-tab {
            flex: 1;
            text-align: center;
            padding: 8px 10px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            color: #4b5563;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .status-tab.active {
            background: #4f46e5;
            color: #fff;
        }

        .badge-count {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 0.75rem;
        }

        /* FILTER BAR */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
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
            padding: 0 12px;
            margin-top: 12px;
        }

        .pelanggan-card {
            display: block !important;
            width: 100% !important;
            background: #ffffff;
            padding: 14px;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
        }

        .pelanggan-nama {
            font-size: 1rem;
            color: #111827;
        }

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
                        status.includes(q); // bisa cari "aktif", "isolir", dll

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
