@extends('seles2.layout.master')

@section('content')
    <div class="pelanggan-page">

        {{-- 1. HEADER (Gradient Amber) --}}
        <div class="pelanggan-header d-flex align-items-center justify-content-between px-3 pt-3 pb-5">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('dashboard-sales') }}" class="back-btn">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="mb-0 fw-bold text-white">Daftar Pelanggan</h5>
            </div>
        </div>

        {{-- 2. FILTER & PENCARIAN (Floating Card) --}}
        <div class="px-3" style="margin-top: -35px; position: relative; z-index: 20;">
            <div class="bg-white rounded-4 shadow-sm p-3 border border-light">
                <div class="d-flex gap-2">
                    <div class="flex-grow-1">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0 text-muted ps-3">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="search-input"
                                class="form-control bg-light border-0 shadow-none fw-bold text-dark"
                                placeholder="Cari nama, HP, alamat...">
                        </div>
                    </div>
                    <div style="width: 130px;">
                        <select id="status-filter"
                            class="form-select form-select-sm bg-light border-0 fw-bold text-secondary text-center"
                            style="cursor: pointer;">
                            <option value="">Semua</option>
                            <option value="lunas">Lunas</option>
                            <option value="belum">Belum</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. LIST PELANGGAN --}}
        <div class="pelanggan-list mt-3 px-3 pb-5">
            @forelse ($pelanggan as $item)
                @php
                    // === LOGIKA PHP ASLI (TIDAK DIUBAH) ===
                    $langgananAktif = $item->langganan->sortByDesc('tanggal_mulai')->first();
                    $tagihanList = $langgananAktif?->tagihan ?? collect();
                    $tagihanTerbaru = null;

                    if ($tagihanList->count()) {
                        $tagihanTerbaru = $tagihanList->sortByDesc(fn($t) => $t->tahun * 100 + $t->bulan)->first();
                    }

                    $tanggalTagih = $tagihanTerbaru?->jatuh_tempo;
                    $statusTagihan = strtolower($tagihanTerbaru->status_tagihan ?? '');
                    $isLunas = in_array($statusTagihan, ['lunas', 'sudah lunas']);
                    $nominalTagihan = $tagihanTerbaru?->total_tagihan ?? 0;

                    // --- Logika Modal Bayar ---
                    $tagihanBelumLunas = $tagihanList
                        ->where('status_tagihan', 'belum lunas')
                        ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan);

                    $lastPaid = $tagihanList
                        ->whereIn('status_tagihan', ['lunas', 'sudah lunas'])
                        ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan)
                        ->last();

                    $lastPaidLabel = $lastPaid
                        ? \Carbon\Carbon::create($lastPaid->tahun, $lastPaid->bulan, 1)->translatedFormat('F Y')
                        : null;

                    if ($tagihanBelumLunas->isNotEmpty()) {
                        $firstUnpaid = $tagihanBelumLunas->first();
                        $startDate = \Carbon\Carbon::create($firstUnpaid->tahun, $firstUnpaid->bulan, 1);
                        $noteStart = 'Mulai dari tagihan yang belum lunas paling awal';
                    } elseif ($lastPaid) {
                        $startDate = \Carbon\Carbon::create($lastPaid->tahun, $lastPaid->bulan, 1)->addMonth();
                        $noteStart = 'Mulai setelah bulan terakhir yang sudah lunas';
                    } else {
                        $startDate = now()->startOfMonth();
                        $noteStart = 'Belum ada tagihan, mulai dari bulan ini';
                    }

                    $startYm = $startDate->format('Y-m');
                    $startLabel = $startDate->translatedFormat('F Y');
                    $maxMonths = 60;
                    $endPreview = $startDate->copy()->addMonths($maxMonths - 1);
                    $endLabel = $endPreview->translatedFormat('F Y');
                    $hargaPerBulan = optional($langgananAktif?->paket)->harga_total ?? 0;

                    $bulanTagihan = $tagihanList
                        ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan)
                        ->filter(function ($t) use ($startDate) {
                            $curr = \Carbon\Carbon::create($t->tahun, $t->bulan, 1);
                            return $curr->greaterThanOrEqualTo($startDate);
                        })
                        ->map(fn($t) => \Carbon\Carbon::create($t->tahun, $t->bulan, 1)->format('Y-m'))
                        ->values()
                        ->toArray();

                    $modalId = 'modalBayar-' . $item->id_pelanggan;
                @endphp

                {{-- KARTU PELANGGAN --}}
                <div class="pelanggan-card position-relative {{ $isLunas ? 'card-lunas' : 'card-belum' }} mb-3 shadow-sm"
                    data-nama="{{ strtolower($item->nama) }}" data-hp="{{ $item->nomor_hp }}"
                    data-area="{{ strtolower($item->area->nama_area ?? '') }}" data-alamat="{{ strtolower($item->alamat) }}"
                    data-status-bayar="{{ $isLunas ? 'lunas' : 'belum' }}"
                    @if ($langgananAktif) data-bs-toggle="modal"
                    data-bs-target="#{{ $modalId }}"
                    style="cursor: pointer;" @endif>

                    <div class="row g-0 align-items-center w-100 m-0">
                        {{-- KIRI --}}
                        <div class="col-7 pe-2">
                            {{-- Nama --}}
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0 text-truncate pelanggan-nama" style="font-size: 0.95rem;">
                                    {{ $item->nama ?? '-' }}
                                </h6>
                            </div>

                            {{-- Area Badge --}}
                            <div class="mb-1">
                                <span
                                    class="badge bg-light text-secondary border border-secondary border-opacity-25 rounded-pill fw-normal text-truncate"
                                    style="max-width: 100%; font-size: 0.65rem;">
                                    <i class="bi bi-geo-alt-fill text-warning me-1"></i>
                                    {{ strtoupper($item->area->nama_area ?? '-') }}
                                </span>
                            </div>

                            {{-- Alamat --}}
                            <div class="small text-muted text-truncate mb-2" style="font-size: 0.75rem;">
                                {{ $item->alamat ?? 'Alamat tidak tersedia' }}
                            </div>

                            {{-- Status Terakhir Bayar --}}
                            <div class="small" style="font-size: 0.7rem;">
                                @if ($lastPaidLabel)
                                    <span class="text-muted">Terakhir:</span>
                                    <span class="fw-bold text-success">{{ $lastPaidLabel }}</span>
                                @else
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill">
                                        Belum pernah bayar
                                    </span>
                                @endif
                            </div>

                            @if (!$langgananAktif)
                                <div class="badge bg-secondary bg-opacity-10 text-secondary mt-1">Non-Aktif</div>
                            @endif
                        </div>

                        {{-- KANAN --}}
                        <div class="col-5 ps-3 harga-col d-flex flex-column justify-content-between h-100">
                            <div class="d-flex flex-column align-items-end w-100">
                                {{-- IP Address --}}
                                @if (!empty($item->ip_address))
                                    <div class="badge bg-light text-dark border mb-1 fw-normal font-monospace"
                                        style="font-size: 0.65rem;">
                                        {{ $item->ip_address }}
                                    </div>
                                @endif

                                {{-- Jatuh Tempo --}}
                                @if ($tanggalTagih)
                                    <div class="small text-muted text-end" style="font-size: 0.7rem; line-height: 1.2;">
                                        Jatuh Tempo:<br>
                                        <span class="fw-bold text-dark">
                                            {{ \Carbon\Carbon::parse($tanggalTagih)->format('d M') }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Nominal --}}
                            <div class="text-end w-100 mt-2 pt-2 border-top border-light">
                                <div class="small text-muted mb-0" style="font-size: 0.65rem;">Total Tagihan</div>
                                <div class="fw-bold text-dark" style="font-size: 1rem;">
                                    <span class="text-secondary small me-1" style="font-size: 0.7rem;">Rp</span>
                                    {{ number_format($nominalTagihan, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL BAYAR PERIODE --}}
                @if ($langgananAktif)
                    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-mobile">
                            <form
                                class="modal-content form-bayar-periode-sales border-0 shadow-lg rounded-4 overflow-hidden"
                                action="{{ route('seles2.tagihan.bayar-banyak') }}" method="POST"
                                data-start-ym="{{ $startYm }}" data-start-label="{{ $startLabel }}"
                                data-max-bulan="{{ $maxMonths }}" data-harga-per-bulan="{{ $hargaPerBulan }}"
                                data-nama-pelanggan="{{ $item->nama }}"
                                data-bulan-tagihan='@json($bulanTagihan)'>

                                @csrf
                                <input type="hidden" name="id_langganan" value="{{ $langgananAktif->id_langganan }}">
                                <input type="hidden" name="start_ym" value="{{ $startYm }}">

                                {{-- Modal Header --}}
                                <div class="modal-header border-0 pb-0 bg-white">
                                    <div>
                                        <h6 class="modal-title fw-bold text-dark">Pembayaran Tagihan</h6>
                                        <small class="text-muted">{{ $item->nama }}</small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body pt-3">
                                    {{-- Info Alert --}}
                                    <div
                                        class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 rounded-3 d-flex gap-2 align-items-start mb-3">
                                        <i class="bi bi-info-circle-fill text-warning mt-1"></i>
                                        <div>
                                            <div class="small text-muted mb-1">Mulai dibayar dari:</div>
                                            <div class="fw-bold text-dark">{{ $startLabel }}</div>
                                            <div class="tiny text-muted mt-1 lh-sm">
                                                {{ $noteStart }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Input Jumlah Bulan --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted mb-1">Jumlah Bulan</label>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-light border"
                                                onclick="this.parentNode.querySelector('input').stepDown(); this.parentNode.querySelector('input').dispatchEvent(new Event('input'));">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" name="jumlah_bulan"
                                                class="form-control input-jumlah-bulan text-center fw-bold text-primary border-start-0 border-end-0"
                                                min="1" max="{{ $maxMonths }}" value="1"
                                                style="font-size: 1.2rem;">
                                            <button type="button" class="btn btn-light border"
                                                onclick="this.parentNode.querySelector('input').stepUp(); this.parentNode.querySelector('input').dispatchEvent(new Event('input'));">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <small
                                            class="text-danger warning-jumlah-bulan d-none mt-1 d-block text-center small">
                                            Minimal 1 bulan.
                                        </small>
                                    </div>

                                    {{-- Preview Box --}}
                                    <div
                                        class="preview-bayar-box p-3 mb-3 text-preview-bayar rounded-3 bg-light border border-secondary border-opacity-10 small text-center">
                                        Loading preview...
                                    </div>

                                    <div class="text-center">
                                        <a href="{{ route('seles2.pelanggan.show', $item->id_pelanggan) }}"
                                            class="text-decoration-none small fw-bold text-amber">
                                            Lihat detail lengkap pelanggan <i class="bi bi-chevron-right"
                                                style="font-size: 0.7rem;"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="modal-footer border-0 pt-0 pb-3 px-3">
                                    <div class="row w-100 g-2">
                                        <div class="col-6">
                                            <button type="button"
                                                class="btn btn-light w-100 rounded-pill fw-bold text-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit"
                                                class="btn btn-success w-100 rounded-pill fw-bold shadow-sm">
                                                Bayar
                                            </button>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                @endif

            @empty
                <div class="text-center mt-5 py-5 px-3">
                    <div class="mb-3">
                        <i class="bi bi-people text-muted opacity-25" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Tidak ada data</h6>
                    <p class="text-muted small">Data pelanggan yang Anda cari tidak ditemukan.</p>
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
            <i class="bi bi-hand-index-thumb me-1"></i> Tap kartu untuk melakukan pembayaran
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Page Style */
        .pelanggan-page {
            background: #f9fafb;
            min-height: 100vh;
            padding-bottom: 20px;
            font-family: 'Segoe UI', sans-serif;
        }

        /* HEADER */
        .pelanggan-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-bottom-left-radius: 30px;
            border-bottom-right-radius: 30px;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.25);
            margin: -16px -16px 0 -16px;
        }

        .back-btn {
            color: white;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 5px;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            justify-content: center;
            text-decoration: none;
            transition: 0.2s;
        }

        .back-btn:active {
            transform: scale(0.9);
            background: rgba(255, 255, 255, 0.4);
        }

        /* CARD PELANGGAN */
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

        /* Indikator Status (Border Kiri Tebal) */
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

        /* UTILITIES */
        .text-amber {
            color: #d97706;
        }

        .tiny {
            font-size: 0.7rem;
        }

        /* FOOTER HINT */
        .hint-footer {
            background: #fffbeb;
            color: #d97706;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #fcd34d;
        }

        /* MODAL RESPONSIVE */
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
            const searchInput = document.getElementById('search-input');
            const statusFilter = document.getElementById('status-filter');
            const cards = document.querySelectorAll('.pelanggan-card');

            function applyFilter() {
                const q = (searchInput?.value || '').toLowerCase();
                const status = (statusFilter?.value || '').toLowerCase();

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

            if (searchInput) searchInput.addEventListener('input', applyFilter);
            if (statusFilter) statusFilter.addEventListener('change', applyFilter);

            // === LOGIKA MODAL (SAMA PERSIS) ===
            function initFormBayarPeriodeSales() {
                document.querySelectorAll('.form-bayar-periode-sales').forEach(function(form) {
                    const inputJumlah = form.querySelector('.input-jumlah-bulan');
                    const previewText = form.querySelector('.text-preview-bayar');
                    const warningEl = form.querySelector('.warning-jumlah-bulan');
                    const btnSubmit = form.querySelector('button[type="submit"]');

                    if (!inputJumlah || !previewText || !btnSubmit) return;

                    function parseYm(ym) {
                        const [y, m] = ym.split('-').map(Number);
                        return {
                            year: y,
                            month: m
                        };
                    }

                    function formatBulanTahun(dateObj) {
                        const bulanNama = [
                            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];
                        return bulanNama[dateObj.month - 1] + ' ' + dateObj.year;
                    }

                    function addMonths(dateObj, n) {
                        let y = dateObj.year;
                        let m = dateObj.month + n;
                        while (m > 12) {
                            m -= 12;
                            y += 1;
                        }
                        while (m < 1) {
                            m += 12;
                            y -= 1;
                        }
                        return {
                            year: y,
                            month: m
                        };
                    }

                    function ymString(dateObj) {
                        return `${dateObj.year}-${String(dateObj.month).padStart(2,'0')}`;
                    }

                    function isAfterOrEqual(a, b) {
                        return (a.year > b.year) || (a.year === b.year && a.month >= b.month);
                    }

                    const startYm = form.dataset.startYm;
                    const hargaPerBulan = Number(form.dataset.hargaPerBulan || 0);
                    const maxBulan = parseInt(form.dataset.maxBulan || '60', 10);

                    let bulanTagihan = [];
                    try {
                        bulanTagihan = JSON.parse(form.dataset.bulanTagihan || '[]');
                    } catch (e) {
                        bulanTagihan = [];
                    }

                    const startObj = parseYm(startYm);

                    function computePaidMonths(jml) {
                        const paid = [];
                        if (bulanTagihan.length === 0) {
                            let curr = {
                                ...startObj
                            };
                            for (let i = 0; i < jml; i++) {
                                paid.push({
                                    ...curr
                                });
                                curr = addMonths(curr, 1);
                            }
                            return paid;
                        }
                        const lastExistingYm = bulanTagihan[bulanTagihan.length - 1];
                        const lastExistingObj = parseYm(lastExistingYm);
                        let curr = {
                            ...startObj
                        };
                        let count = 0;
                        while (true) {
                            const ym = ymString(curr);
                            if (bulanTagihan.includes(ym)) {
                                paid.push({
                                    ...curr
                                });
                                count++;
                                if (count === jml) return paid;
                            }
                            if (isAfterOrEqual(curr, lastExistingObj)) break;
                            curr = addMonths(curr, 1);
                        }
                        let base = addMonths(lastExistingObj, 1);
                        let curr2 = {
                            ...base
                        };
                        while (count < jml) {
                            paid.push({
                                ...curr2
                            });
                            count++;
                            curr2 = addMonths(curr2, 1);
                        }
                        return paid;
                    }

                    function updatePreview() {
                        let jml = parseInt(inputJumlah.value || '0', 10);
                        if (isNaN(jml) || jml < 1) {
                            previewText.innerHTML = 'Masukkan jumlah bulan yang valid.';
                            return;
                        }
                        if (jml > maxBulan) jml = maxBulan;

                        const paidMonths = computePaidMonths(jml);
                        if (paidMonths.length === 0) {
                            previewText.innerHTML = 'Tidak ada bulan tagihan yang bisa dibayar.';
                            return;
                        }

                        const startLabel = formatBulanTahun(paidMonths[0]);
                        const endLabel = formatBulanTahun(paidMonths[paidMonths.length - 1]);
                        const total = jml * hargaPerBulan;

                        const kalimat = (jml === 1) ?
                            `Bayar 1 bulan: <strong>${startLabel}</strong>.` :
                            `Bayar ${jml} bulan: <strong>${startLabel}</strong> s.d <strong>${endLabel}</strong>.`;

                        if (hargaPerBulan > 0) {
                            previewText.innerHTML = `
                                ${kalimat}<br>
                                Total: <strong class="text-success">Rp ${total.toLocaleString('id-ID')}</strong>
                            `;
                        } else {
                            previewText.innerHTML = kalimat +
                                '<br><span class="text-muted">Total hitung setelah simpan.</span>';
                        }
                    }

                    function validateJumlah() {
                        let val = parseInt(inputJumlah.value || '0', 10);
                        const invalid = isNaN(val) || val <= 0;
                        if (invalid) {
                            warningEl?.classList.remove('d-none');
                            btnSubmit.disabled = true;
                        } else {
                            warningEl?.classList.add('d-none');
                            btnSubmit.disabled = false;
                        }
                        return !invalid;
                    }

                    inputJumlah.addEventListener('input', function() {
                        validateJumlah();
                        updatePreview();
                    });

                    // Trigger input event for +/- buttons
                    inputJumlah.addEventListener('change', function() {
                        validateJumlah();
                        updatePreview();
                    });

                    form.addEventListener('submit', function(e) {
                        if (!validateJumlah()) {
                            e.preventDefault();
                            alert('Jumlah bulan tidak valid.');
                        }
                    });

                    validateJumlah();
                    updatePreview();
                });
            }

            initFormBayarPeriodeSales();
        });
    </script>
@endpush
