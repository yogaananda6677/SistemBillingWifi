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
                <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control border-start-0 rounded-end-pill ps-0"
                        placeholder="Cari nama, HP, atau alamat...">
                </div>
            </div>
            <div style="width: 140px;">
                <select id="status-filter" class="form-select form-select-sm rounded-pill shadow-sm">
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

                    // --- logika mulai bayar dari (untuk modal) ---
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

                    // daftar bulan tagihan yang benar-benar ada (>= startDate)
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

                {{-- KARTU PELANGGAN (TRIGGER MODAL BAYAR) --}}
                <div class="pelanggan-card position-relative {{ $isLunas ? 'card-lunas' : 'card-belum' }} mb-3"
                    data-nama="{{ $item->nama }}" data-hp="{{ $item->nomor_hp }}"
                    data-area="{{ $item->area->nama_area ?? '' }}" data-alamat="{{ $item->alamat }}"
                    data-status-bayar="{{ $isLunas ? 'lunas' : 'belum' }}"
                    @if ($langgananAktif) data-bs-toggle="modal"
                    data-bs-target="#{{ $modalId }}"
                    style="cursor: pointer;" @endif>
                    <div class="row g-0 align-items-center w-100 m-0">
                        {{-- KIRI --}}
                        <div class="col-7 pe-2">
                            {{-- Nama & Area --}}
                            <div class="d-flex align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0 text-truncate pelanggan-nama">
                                    {{ $item->nama ?? '-' }}
                                </h6>
                            </div>

                            <div class="d-flex align-items-center gap-1 mb-2">
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

                            {{-- Status Pembayaran (Terakhir Bayar) --}}
                            <div class="small mt-1" style="font-size: 0.75rem;">
                                @if ($lastPaidLabel)
                                    <span class="text-muted">Terakhir:</span>
                                    <span class="fw-bold text-success">{{ $lastPaidLabel }}</span>
                                @else
                                    <span
                                        class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2">
                                        Belum pernah bayar
                                    </span>
                                @endif
                            </div>

                            @if (!$langgananAktif)
                                <div class="badge bg-secondary bg-opacity-10 text-secondary mt-1">
                                    Tidak ada langganan aktif
                                </div>
                            @endif
                        </div>

                        {{-- KANAN --}}
                        <div class="col-5 ps-3 harga-col d-flex flex-column justify-content-between h-100">
                            <div class="d-flex flex-column align-items-end w-100">
                                {{-- IP Address --}}
                                @if (!empty($item->ip_address))
                                    <div class="badge bg-light text-dark border mb-1 fw-normal font-monospace"
                                        style="font-size: 0.7rem;">
                                        {{ $item->ip_address }}
                                    </div>
                                @endif

                                {{-- Jatuh Tempo --}}
                                @if ($tanggalTagih)
                                    <div class="small text-muted text-end" style="font-size: 0.7rem; line-height: 1.2;">
                                        Jatuh Tempo:<br>
                                        <span
                                            class="fw-bold text-dark">{{ \Carbon\Carbon::parse($tanggalTagih)->format('d M') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Nominal --}}
                            <div class="text-end w-100 mt-2 pt-2 border-top border-light">
                                <div class="small text-muted mb-0" style="font-size: 0.7rem;">Tagihan</div>
                                <div class="fw-bold text-dark" style="font-size: 1.1rem;">
                                    <span class="text-secondary small me-1" style="font-size: 0.8rem;">Rp</span>
                                    {{ number_format($nominalTagihan, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- MODAL BAYAR PERIODE (LOGIKA SAMA PERSIS) --}}
                @if ($langgananAktif)
                    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <form class="modal-content form-bayar-periode-sales border-0 shadow-lg rounded-4"
                                action="{{ route('seles2.tagihan.bayar-banyak') }}" method="POST"
                                data-start-ym="{{ $startYm }}" data-start-label="{{ $startLabel }}"
                                data-max-bulan="{{ $maxMonths }}" data-harga-per-bulan="{{ $hargaPerBulan }}"
                                data-nama-pelanggan="{{ $item->nama }}"
                                data-bulan-tagihan='@json($bulanTagihan)'>

                                @csrf
                                <input type="hidden" name="id_langganan" value="{{ $langgananAktif->id_langganan }}">
                                <input type="hidden" name="start_ym" value="{{ $startYm }}">

                                {{-- Modal Header Tema Kuning --}}
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold">Bayar Tagihan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body pt-2">
                                    <h6 class="text-primary fw-bold mb-3">{{ $item->nama }}</h6>

                                    <div
                                        class="alert alert-light border border-warning border-opacity-25 d-flex gap-2 align-items-start mb-3">
                                        <i class="bi bi-info-circle-fill text-warning mt-1"></i>
                                        <div>
                                            <div class="small text-muted mb-1">Mulai dibayar dari:</div>
                                            <div class="fw-bold text-dark">{{ $startLabel }}</div>
                                            <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                {{ $noteStart }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted">Jumlah bulan yang ingin
                                            dibayar</label>
                                        <div class="input-group">
                                            <input type="number" name="jumlah_bulan"
                                                class="form-control input-jumlah-bulan text-center fw-bold" min="1"
                                                max="{{ $maxMonths }}" value="1"
                                                style="font-size: 1.2rem; color: #d97706;">
                                            <span class="input-group-text bg-light">Bulan</span>
                                        </div>
                                        <small class="text-danger warning-jumlah-bulan d-none mt-1 d-block">
                                            Jumlah bulan tidak boleh kosong atau 0.
                                        </small>
                                    </div>

                                    <div class="preview-bayar-box p-3 mb-0 text-preview-bayar rounded-3 bg-light border">
                                        {{-- JS akan mengisi ini --}}
                                        Loading preview...
                                    </div>

                                    <div class="mt-3 text-center">
                                        <a href="{{ route('seles2.pelanggan.show', $item->id_pelanggan) }}"
                                            class="text-decoration-none small text-primary fw-bold">
                                            Lihat detail lengkap pelanggan <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                                        <i class="bi bi-cash-coin me-1"></i> Bayar Sekarang
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                @endif

            @empty
                <div class="text-center mt-5 py-5 px-3">
                    <div class="mb-3">
                        <i class="bi bi-people text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                    </div>
                    <h6 class="text-muted fw-bold">Belum ada pelanggan</h6>
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
            <i class="bi bi-hand-index-thumb me-1"></i> Tap kartu untuk melakukan pembayaran
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Global Page Style */
        .pelanggan-page {
            background: #f9fafb;
            /* Abu-abu bersih */
            min-height: 100vh;
            padding-bottom: 20px;
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
            height: 42px;
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

        /* Kolom Harga (Kanan) */
        .harga-col {
            border-left: 1px dashed #e5e7eb;
        }

        /* 4. FOOTER HINT */
        .hint-footer {
            background: #fffbeb;
            /* Kuning Muda */
            color: #d97706;
            /* Text Amber Gelap */
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            border: 1px solid #fcd34d;
        }

        /* 5. PREVIEW BOX DI MODAL */
        .preview-bayar-box {
            background: #fffbeb;
            border: 1px dashed #f59e0b;
            color: #b45309;
            font-size: 0.9rem;
        }
    </style>
@endpush

@push('scripts')
    {{-- SCRIPT ASLI (TIDAK DIUBAH) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const statusFilter = document.getElementById('status-filter');
            const cards = document.querySelectorAll('.pelanggan-card');

            function applyFilter() {
                const q = (searchInput?.value || '').toLowerCase();
                const status = (statusFilter?.value || '').toLowerCase(); // '' | 'lunas' | 'belum'

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

            // ========== LOGIKA PREVIEW + VALIDASI MODAL BAYAR PERIODE ==========
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
                            // belum ada tagihan sama sekali → anggap berurutan dari start
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

                        // 1) iterasi dari start sampai bulan tagihan terakhir,
                        //    hanya bulan yg ADA di bulanTagihan yang dihitung dulu
                        while (true) {
                            const ym = ymString(curr);

                            if (bulanTagihan.includes(ym)) {
                                paid.push({
                                    ...curr
                                });
                                count++;
                                if (count === jml) return paid;
                            }

                            if (isAfterOrEqual(curr, lastExistingObj)) {
                                break;
                            }

                            curr = addMonths(curr, 1);
                        }

                        // 2) kalau masih kurang → lanjut ke bulan berikutnya, dianggap tagihan baru
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
                            previewText.innerHTML = 'Masukkan jumlah bulan yang valid terlebih dahulu.';
                            return;
                        }
                        if (jml > maxBulan) {
                            jml = maxBulan;
                        }

                        const paidMonths = computePaidMonths(jml);

                        if (paidMonths.length === 0) {
                            previewText.innerHTML = 'Tidak ada bulan tagihan yang bisa dibayar.';
                            return;
                        }

                        const startLabel = formatBulanTahun(paidMonths[0]);
                        const endLabel = formatBulanTahun(paidMonths[paidMonths.length - 1]);
                        const total = jml * hargaPerBulan;

                        const kalimat = (jml === 1) ?
                            `Akan dibayar 1 bulan tagihan untuk <strong>${startLabel}</strong>.` :
                            `Akan dibayar ${jml} bulan tagihan, dari <strong>${startLabel}</strong> sampai <strong>${endLabel}</strong>.`;

                        if (hargaPerBulan > 0) {
                            previewText.innerHTML = `
                        ${kalimat}<br>
                        Perkiraan total: <strong>Rp ${total.toLocaleString('id-ID')}</strong>
                        (Rp ${hargaPerBulan.toLocaleString('id-ID')} x ${jml} bulan).
                    `;
                        } else {
                            previewText.innerHTML = kalimat +
                                '<br><span class="text-muted">Harga paket belum terbaca, total akan dihitung setelah disimpan.</span>';
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

                    inputJumlah.addEventListener('blur', function() {
                        let val = parseInt(this.value || '0', 10);
                        if (isNaN(val) || val < 1) val = 1;
                        if (val > maxBulan) val = maxBulan;
                        this.value = val;
                        validateJumlah();
                        updatePreview();
                    });

                    form.addEventListener('submit', function(e) {
                        if (!validateJumlah()) {
                            e.preventDefault();
                            alert('Jumlah bulan tidak boleh kosong atau 0.');
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
