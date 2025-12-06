@extends('seles2.layout.master')

@section('content')

    <div class="pelanggan-page">

        {{-- HEADER (Tema Amber) --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-between px-3 py-2">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('dashboard-sales') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="mb-0 fw-bold">Status Pembayaran</h5>
            </div>
        </div>

        @php
            $statusPage = $statusBayar ?? request('status_bayar', 'belum'); // 'belum' / 'lunas'
        @endphp

        {{-- FILTER & SEARCH --}}
        <div class="filter-bar mt-3 px-1">
            <div class="flex-grow-1 me-2">
                <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control border-start-0 rounded-end-pill ps-0"
                        placeholder="Cari nama / HP / alamat...">
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
        <div class="pelanggan-list mt-3 pb-3">
            @forelse ($pelanggan as $item)
                @php
                    // === LOGIKA PHP ASLI (TIDAK DIUBAH) ===
                    $langgananAktif = $item->langganan->sortByDesc('tanggal_mulai')->first();
                    $areaName = $item->area->nama_area ?? '-';

                    // Semua tagihan pelanggan
                    $semuaTagihan = $item->langganan->flatMap(fn($l) => $l->tagihan);

                    // TUNGGAKAN
                    $tunggakan = $semuaTagihan
                        ->where('status_tagihan', 'belum lunas')
                        ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan);

                    // TAGIHAN LUNAS TERAKHIR
                    $tagihanLunasTerakhir = $semuaTagihan
                        ->whereIn('status_tagihan', ['lunas', 'sudah lunas'])
                        ->sortByDesc(fn($t) => $t->tahun * 100 + $t->bulan)
                        ->first();

                    // ==== TAGIHAN DISPLAY ====
                    if ($statusPage === 'belum') {
                        $tagihanDisplay =
                            $tunggakan->first() ??
                            $semuaTagihan->sortByDesc(fn($t) => $t->tahun * 100 + $t->bulan)->first();
                        $cardClass = 'card-belum'; // Style Merah
                    } else {
                        // 'lunas'
                        $tagihanDisplay =
                            $tagihanLunasTerakhir ??
                            $semuaTagihan->sortByDesc(fn($t) => $t->tahun * 100 + $t->bulan)->first();
                        $cardClass = 'card-lunas'; // Style Hijau
                    }

                    $nominalTagihanDisplay = $tagihanDisplay->total_tagihan ?? 0;

                    // ==== HITUNG JATUH TEMPO ====
                    $jatuhTempoDisplay = null;
                    if ($tagihanDisplay) {
                        if (!empty($tagihanDisplay->jatuh_tempo)) {
                            $jatuhTempoDisplay = \Carbon\Carbon::parse($tagihanDisplay->jatuh_tempo);
                        } else {
                            $tahun = (int) $tagihanDisplay->tahun;
                            $bulan = (int) $tagihanDisplay->bulan;

                            $refDate = $langgananAktif?->tanggal_mulai
                                ? \Carbon\Carbon::parse($langgananAktif->tanggal_mulai)
                                : \Carbon\Carbon::parse($item->tanggal_registrasi ?? now());

                            $dayAktif = $refDate->day;
                            $endOfMonthDay = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->day;
                            $dayJatuhTempo = min($dayAktif, $endOfMonthDay);

                            $jatuhTempoDisplay = \Carbon\Carbon::create($tahun, $bulan, $dayJatuhTempo);
                        }
                    }

                    $modalId = 'modalPembayaran-' . $item->id_pelanggan;
                @endphp

                {{-- KARTU PELANGGAN --}}
                <div class="pelanggan-card position-relative {{ $cardClass }} mb-3"
                    data-nama="{{ strtolower($item->nama ?? '') }}" data-hp="{{ strtolower($item->nomor_hp ?? '') }}"
                    data-area="{{ strtolower($areaName) }}" data-alamat="{{ strtolower($item->alamat ?? '') }}"
                    data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
                    <div class="row g-0 align-items-center w-100 m-0">
                        {{-- KIRI --}}
                        <div class="col-7 pe-2">
                            {{-- Nama --}}
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0 text-truncate pelanggan-nama">
                                    {{ $item->nama ?? '-' }}
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
                                {{ $item->alamat ?? 'Alamat tidak tersedia' }}
                            </div>

                            {{-- Status Text --}}
                            <div class="mt-2 small">
                                @if ($statusPage === 'lunas')
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> SUDAH BAYAR
                                    </span>
                                @else
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i> BELUM BAYAR
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- KANAN --}}
                        <div class="col-5 ps-3 harga-col d-flex flex-column justify-content-between h-100">
                            <div class="d-flex flex-column align-items-end w-100">
                                {{-- IP --}}
                                @if (!empty($item->ip_address))
                                    <div class="badge bg-light text-dark border mb-1 fw-normal font-monospace"
                                        style="font-size: 0.7rem;">
                                        {{ $item->ip_address }}
                                    </div>
                                @endif

                                {{-- Jatuh Tempo --}}
                                @if ($jatuhTempoDisplay)
                                    <div class="small text-muted text-end" style="font-size: 0.7rem; line-height: 1.2;">
                                        Jatuh Tempo:<br>
                                        <span class="fw-bold text-dark">{{ $jatuhTempoDisplay->format('d M Y') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Nominal --}}
                            <div class="text-end w-100 mt-2 pt-2 border-top border-light">
                                <div class="small text-muted mb-0" style="font-size: 0.7rem;">Total Tagihan</div>
                                <span class="fw-bold text-dark" style="font-size: 1.1rem;">
                                    <span class="text-secondary small me-1" style="font-size: 0.8rem;">Rp</span>
                                    {{ number_format($nominalTagihanDisplay, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL DETAIL PEMBAYARAN (Modern Style) --}}
                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Info Pembayaran</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body pt-2">
                                <h6 class="text-primary fw-bold mb-3">{{ $item->nama }}</h6>

                                @if ($statusPage === 'lunas')
                                    {{-- KONTEN SUDAH BAYAR --}}
                                    @if ($tagihanLunasTerakhir)
                                        @php
                                            $periode = \Carbon\Carbon::create(
                                                $tagihanLunasTerakhir->tahun,
                                                $tagihanLunasTerakhir->bulan,
                                                1,
                                            )
                                                ->locale('id')
                                                ->translatedFormat('F Y');
                                        @endphp

                                        <div
                                            class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 rounded-3">
                                            <div class="small text-muted mb-1">Status Lunas Sampai:</div>
                                            <h4 class="fw-bold text-success mb-0">{{ $periode }}</h4>
                                        </div>
                                        <p class="text-muted small">
                                            Tagihan sampai bulan tersebut sudah beres. Cek detail di halaman pelanggan.
                                        </p>
                                    @else
                                        <p class="text-muted small">Data tagihan lunas terakhir tidak ditemukan.</p>
                                    @endif
                                @else
                                    {{-- KONTEN BELUM BAYAR (TUNGGAKAN) --}}
                                    @if ($tunggakan->isNotEmpty())
                                        <p class="mb-2 fw-bold text-danger">Daftar Tunggakan:</p>
                                        <div class="table-responsive border rounded-3">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="ps-3">Periode</th>
                                                        <th class="text-end pe-3">Nominal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($tunggakan as $tg)
                                                        @php
                                                            $periodeTg = \Carbon\Carbon::create(
                                                                $tg->tahun,
                                                                $tg->bulan,
                                                                1,
                                                            )
                                                                ->locale('id')
                                                                ->translatedFormat('F Y');
                                                        @endphp
                                                        <tr>
                                                            <td class="ps-3">{{ $periodeTg }}</td>
                                                            <td class="text-end pe-3 fw-semibold">
                                                                Rp {{ number_format($tg->total_tagihan, 0, ',', '.') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="mt-2 mb-0 text-muted small fst-italic">
                                            Mohon segera ditindaklanjuti.
                                        </p>
                                    @else
                                        <p class="text-muted mb-0 small">
                                            Tidak ditemukan data tunggakan spesifik.
                                        </p>
                                    @endif
                                @endif
                            </div>

                            <div class="modal-footer border-0 pt-0 pb-3">
                                <button type="button" class="btn btn-light rounded-pill px-4"
                                    data-bs-dismiss="modal">Tutup</button>
                                <a href="{{ route('seles2.pelanggan.show', $item->id_pelanggan) }}"
                                    class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                    Detail Pelanggan <i class="bi bi-arrow-right ms-1"></i>
                                </a>
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
                    <p class="text-muted small">Tidak ada pelanggan pada status pembayaran ini.</p>
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
            <i class="bi bi-hand-index-thumb me-1"></i> Tap kartu untuk info pembayaran
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
            /* Padding bawah lebih besar untuk efek lengkung */
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 10px -16px;
            /* Negatif margin agar full width */
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
            margin-top: 10px !important;
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

        /* 3. CARD PELANGGAN */
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

        /* Indikator Garis Tepi (Penting untuk Status) */
        .card-lunas {
            border-left: 5px solid #10b981 !important;
            /* Hijau */
        }

        .card-belum {
            border-left: 5px solid #ef4444 !important;
            /* Merah */
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

                    const textMatch = !q ||
                        nama.includes(q) ||
                        hp.includes(q) ||
                        alamat.includes(q) ||
                        areaCd.includes(q);

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
