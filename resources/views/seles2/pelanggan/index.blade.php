@extends('seles2.layout.master')
@section('content')
    <div class="pelanggan-page">
        {{-- HEADER (Tema Amber) --}}
        <div class="pelanggan-header d-flex align-items-center">
            <a href="{{ route('dashboard-sales') }}" class="back-btn">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-bold">Daftar Pelanggan</h5>
        </div>

        {{-- FILTER & PENCARIAN --}}
        <div class="filter-bar mt-3 px-1">
            <div class="flex-grow-1 me-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="search-input"
                        class="form-control form-control-sm border-start-0 rounded-end-pill ps-0"
                        placeholder="Cari nama, HP, atau alamat...">
                </div>
            </div>
            <div style="width: 140px;">
                <select id="status-filter" class="form-select form-select-sm rounded-pill">
                    <option value="">Semua Status</option>
                    <option value="lunas">Sudah Bayar</option>
                    <option value="belum">Belum Bayar</option>
                </select>
            </div>
        </div>

        {{-- LIST PELANGGAN --}}
        <div class="pelanggan-list mt-3">
            @forelse ($pelanggan as $item)
                @php
                    $langgananAktif = $item->langganan->sortByDesc('tanggal_mulai')->first();
                    $tagihanList = $langgananAktif?->tagihan ?? collect();
                    // === STATUS GLOBAL PELANGGAN (ada tagihan belum lunas apa tidak) ===
                    $hasUnpaid = $tagihanList->contains(fn($t) => $t->status_tagihan === 'belum lunas');
                    $isLunas = !$hasUnpaid;
                    // === TAGIHAN BELUM LUNAS PALING AWAL (kalau ada) ===
                    $tagihanBelumLunas = $tagihanList
                        ->where('status_tagihan', 'belum lunas')
                        ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan);
                    // TAGIHAN TERAKHIR (apapun statusnya)
                    $tagihanTerakhir = $tagihanList->sortByDesc(fn($t) => $t->tahun * 100 + $t->bulan)->first();
                    // TAGIHAN YANG DITAMPILKAN DI KARTU (KANAN):
                    // prioritas: belum lunas paling awal, kalau tidak ada pakai terakhir
                    $tagihanDisplay = $tagihanBelumLunas->first() ?? $tagihanTerakhir;
                    // === HITUNG JATUH TEMPO (SINKRON DENGAN ensureTagihanBulanIni) ===
                    $jatuhTempo = null;
                    if ($tagihanDisplay) {
                        if (!empty($tagihanDisplay->jatuh_tempo)) {
                            // kalau DB sudah punya jatuh_tempo, pakai itu
                            $jatuhTempo = \Carbon\Carbon::parse($tagihanDisplay->jatuh_tempo);
                        } else {
                            // fallback: hitung manual
                            $tahun = (int) $tagihanDisplay->tahun;
                            $bulan = (int) $tagihanDisplay->bulan;
                            // referensi hari aktivasi: tanggal_mulai langganan, kalau nggak ada pakai tanggal_registrasi
                            $refDate = $langgananAktif?->tanggal_mulai
                                ? \Carbon\Carbon::parse($langgananAktif->tanggal_mulai)
                                : \Carbon\Carbon::parse($item->tanggal_registrasi ?? now());
                            $dayAktif = $refDate->day;
                            $endOfMonthDay = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->day;
                            $dayJatuhTempo = min($dayAktif, $endOfMonthDay);
                            $jatuhTempo = \Carbon\Carbon::create($tahun, $bulan, $dayJatuhTempo, 23, 59, 59);
                        }
                    }
                    $nominalTagihan = $tagihanDisplay?->total_tagihan ?? 0;
                @endphp
                <div class="pelanggan-card position-relative {{ $isLunas ? 'card-lunas' : 'card-belum' }} mb-3"
                    data-nama="{{ $item->nama }}" data-hp="{{ $item->nomor_hp }}"
                    data-area="{{ $item->area->nama_area ?? '' }}" data-alamat="{{ $item->alamat }}"
                    data-status-bayar="{{ $isLunas ? 'lunas' : 'belum' }}">
                    {{-- SELURUH CARD BISA DIKLIK --}}
                    <a href="{{ route('seles2.pelanggan.show', $item->id_pelanggan) }}" class="stretched-link"></a>

                    <div class="row g-0 align-items-center w-100 m-0">
                        {{-- KIRI --}}
                        <div class="col-7 pe-2">
                            {{-- Nama & Area --}}
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0 text-truncate pelanggan-nama">
                                    {{ $item->nama ?? '-' }}
                                </h6>
                            </div>

                            <div class="d-flex align-items-center gap-1 mb-1">
                                <span
                                    class="badge bg-light text-secondary border border-light small px-2 py-1 rounded-pill fw-normal text-truncate"
                                    style="max-width: 100%;">
                                    <i class="bi bi-geo-alt-fill me-1 text-warning"></i>
                                    {{ strtoupper($item->area->nama_area ?? '-') }}
                                </span>
                            </div>

                            {{-- Alamat --}}
                            <div class="small text-muted text-truncate mb-2" style="font-size: 0.8rem;">
                                {{ $item->alamat ?? 'Alamat tidak tersedia' }}
                            </div>

                            {{-- Status Badge --}}
                            <div>
                                @if ($isLunas)
                                    <span
                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> Lunas
                                    </span>
                                @else
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Bayar
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- KANAN --}}
                        <div class="col-5 ps-3 harga-col d-flex flex-column justify-content-between h-100">
                            <div class="d-flex flex-column align-items-end w-100">
                                {{-- IP Address --}}
                                @if (!empty($item->ip_address))
                                    <div class="badge bg-light text-dark border mb-1 fw-normal font-monospace"
                                        style="font-size: 0.75rem;">
                                        {{ $item->ip_address }}
                                    </div>
                                @endif

                                {{-- Jatuh Tempo --}}
                                @if ($jatuhTempo)
                                    <div class="small text-muted text-end" style="font-size: 0.75rem; line-height: 1.2;">
                                        Tgl Tagihan:<br>
                                        <span class="fw-bold text-dark">{{ $jatuhTempo->format('d M') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Nominal --}}
                            <div class="text-end w-100 mt-2 pt-2 border-top border-light">
                                <div class="small text-muted mb-0" style="font-size: 0.7rem;">Total Tagihan</div>
                                <div class="fw-bold text-dark" style="font-size: 1.1rem;">
                                    <span class="text-secondary small me-1" style="font-size: 0.8rem;">Rp</span>
                                    {{ number_format($nominalTagihan, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center mt-5 py-5 px-3">
                    <div class="mb-3">
                        <i class="bi bi-people text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Belum ada data pelanggan</h6>
                    <p class="text-muted small">Data pelanggan yang Anda input akan muncul di sini.</p>
                </div>
            @endforelse

            {{-- Pagination --}}
            @if (method_exists($pelanggan, 'links'))
                <div class="mt-4 px-2">
                    {{ $pelanggan->links() }}
                </div>
            @endif
        </div>

        <div class="hint-footer text-center mt-3 mb-2 mx-3 shadow-sm">
            <i class="bi bi-info-circle me-1"></i> Tekan kartu untuk melihat detail lengkap
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Page Style */
        .pelanggan-page {
            background: #f9fafb;
            /* Abu-abu sangat muda bersih */
            min-height: 100vh;
            padding-bottom: 20px;
        }

        /* 1. HEADER (Gradient Kuning Emas) */
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
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
            z-index: 11;
            margin-top: -20px !important;
            /* Naik ke atas menimpa header */
            padding: 0 16px;
        }

        .filter-bar input,
        .filter-bar select,
        .input-group-text {
            border: 1px solid #f3f4f6;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            height: 42px;
            /* Lebih tinggi agar mudah disentuh */
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
            transition: transform 0.2s;
            border: 1px solid #f3f4f6;
            margin-left: 4px;
            /* Space untuk border kiri */
        }

        .pelanggan-card:active {
            transform: scale(0.98);
            background-color: #fdfdfd;
        }

        /* Indikator Status (Border Kiri Tebal) */
        .card-lunas {
            border-left: 5px solid #10b981 !important;
            /* Hijau Emerald */
        }

        .card-belum {
            border-left: 5px solid #ef4444 !important;
            /* Merah */
        }

        /* Kolom Harga (Kanan) */
        .harga-col {
            border-left: 1px dashed #e5e7eb;
        }

        /* 4. FOOTER HINT */
        .hint-footer {
            background: #fffbeb;
            /* Kuning sangat muda */
            color: #d97706;
            /* Text Kuning Emas Gelap */
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
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
            const statusFilter = document.getElementById('status-filter');
            const cards = document.querySelectorAll('.pelanggan-card');

            function applyFilter() {
                const q = (searchInput.value || '').toLowerCase();
                const status = (statusFilter.value || '').toLowerCase(); // '' | 'lunas' | 'belum'

                cards.forEach(card => {
                    const nama = (card.dataset.nama || '').toLowerCase();
                    const hp = (card.dataset.hp || '').toLowerCase();
                    const area = (card.dataset.area || '').toLowerCase();
                    const alamat = (card.dataset.alamat || '').toLowerCase();
                    const sBayar = (card.dataset.statusBayar || '').toLowerCase();

                    const textMatch = !q ||
                        nama.includes(q) ||
                        hp.includes(q) ||
                        area.includes(q) ||
                        alamat.includes(q);

                    const statusMatch = !status || sBayar === status;

                    card.style.display = (textMatch && statusMatch) ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', applyFilter);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', applyFilter);
            }
        });
    </script>
@endpush
