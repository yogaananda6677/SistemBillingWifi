@extends('seles2.layout.master')

@section('content')
<div class="pelanggan-page">

    {{-- HEADER --}}
    <div class="pelanggan-header d-flex align-items-center">
        <a href="{{ route('dashboard-sales') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="mb-0 fw-semibold">Daftar Pelanggan</h5>
    </div>

    {{-- FILTER & PENCARIAN --}}
    <div class="filter-bar mt-3 px-3">
        <div class="flex-grow-1 me-2">
            <input
                type="text"
                id="search-input"
                class="form-control form-control-sm"
                placeholder="Cari nama / HP / alamat / wilayah..."
            >
        </div>
        <div style="width: 130px;">
            <select id="status-filter" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="lunas">Sudah Bayar</option>
                <option value="belum">Belum Bayar</option>
            </select>
        </div>
    </div>

    {{-- LIST PELANGGAN --}}
    <div class="pelanggan-list mt-3 px-3">
        @forelse ($pelanggan as $item)
            @php
                $langgananAktif = $item->langganan->sortByDesc('tanggal_mulai')->first();
                $tagihanList    = $langgananAktif?->tagihan ?? collect();
                $tagihanTerbaru = null;

                if ($tagihanList->count()) {
                    $tagihanTerbaru = $tagihanList
                        ->sortByDesc(fn ($t) => $t->tahun * 100 + $t->bulan)
                        ->first();
                }

                $tanggalTagih   = $tagihanTerbaru?->jatuh_tempo;
                $statusTagihan  = strtolower($tagihanTerbaru->status_tagihan ?? '');
                $isLunas        = in_array($statusTagihan, ['lunas', 'sudah lunas']);
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
                    $startDate   = \Carbon\Carbon::create($firstUnpaid->tahun, $firstUnpaid->bulan, 1);
                    $noteStart   = 'Mulai dari tagihan yang belum lunas paling awal';
                } elseif ($lastPaid) {
                    $startDate = \Carbon\Carbon::create($lastPaid->tahun, $lastPaid->bulan, 1)->addMonth();
                    $noteStart = 'Mulai setelah bulan terakhir yang sudah lunas';
                } else {
                    $startDate = now()->startOfMonth();
                    $noteStart = 'Belum ada tagihan, mulai dari bulan ini';
                }

                $startYm    = $startDate->format('Y-m');
                $startLabel = $startDate->translatedFormat('F Y');
                $maxMonths  = 60;
                $endPreview = $startDate->copy()->addMonths($maxMonths - 1);
                $endLabel   = $endPreview->translatedFormat('F Y');
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
            <div
                class="pelanggan-card position-relative {{ $isLunas ? 'card-lunas' : 'card-belum' }}"
                data-nama="{{ $item->nama }}"
                data-hp="{{ $item->nomor_hp }}"
                data-area="{{ $item->area->nama_area ?? '' }}"
                data-alamat="{{ $item->alamat }}"
                data-status-bayar="{{ $isLunas ? 'lunas' : 'belum' }}"
                @if($langgananAktif)
                    data-bs-toggle="modal"
                    data-bs-target="#{{ $modalId }}"
                    style="cursor: pointer;"
                @endif
            >
                <div class="row g-0 align-items-center w-100 m-0">
                    {{-- KIRI --}}
                    <div class="col-7">
                        <div class="d-flex align-items-center mb-1">
                            <div class="fw-bold text-truncate pelanggan-nama me-2">
                                {{ $item->nama ?? '-' }}
                            </div>
                        </div>

                        <div class="text-muted small text-truncate">
                            {{ strtoupper($item->area->nama_area ?? '-') }}
                            @if(!empty($item->nomor_hp))
                                • {{ $item->nomor_hp }}
                            @endif
                        </div>

                        <div class="small text-muted text-truncate mt-1">
                            {{ $item->alamat ?? 'Alamat tidak tersedia' }}
                        </div>

                        <div class="mt-2 small">
                            Terakhir bayar:
                            @if($lastPaidLabel)
                                <span class="fw-bold">{{ $lastPaidLabel }}</span>
                            @else
                                <span class="fw-bold text-belum">Belum pernah bayar</span>
                            @endif
                        </div>


                        @if(!$langgananAktif)
                            <div class="small text-muted mt-1">
                                Tidak ada langganan aktif
                            </div>
                        @endif
                    </div>

                    {{-- KANAN --}}
                    <div class="col-5 ps-2 harga-col d-flex flex-column justify-content-between">
                        <div class="d-flex flex-column align-items-end w-100">
                            @if(!empty($item->ip_address))
                                <div class="small fw-bold text-dark mb-1 text-end text-break">
                                    {{ $item->ip_address }}
                                </div>
                            @endif

                            @if($tanggalTagih)
                                <div class="small text-muted text-end">
                                    Jatuh Tempo:
                                    {{ \Carbon\Carbon::parse($tanggalTagih)->format('d') }}
                                </div>
                            @endif
                        </div>

                        <div class="text-end w-100 mt-3">
                            <span class="harga-label">Rp.</span>
                            <span class="harga-value">
                                {{ number_format($nominalTagihan, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL BAYAR PERIODE --}}
            @if($langgananAktif)
                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content form-bayar-periode-sales"
                              action="{{ route('seles2.tagihan.bayar-banyak') }}"
                              method="POST"
                              data-start-ym="{{ $startYm }}"
                              data-start-label="{{ $startLabel }}"
                              data-max-bulan="{{ $maxMonths }}"
                              data-harga-per-bulan="{{ $hargaPerBulan }}"
                              data-nama-pelanggan="{{ $item->nama }}"
                              data-bulan-tagihan='@json($bulanTagihan)'>

                            @csrf

                            <input type="hidden" name="id_langganan" value="{{ $langgananAktif->id_langganan }}">
                            <input type="hidden" name="start_ym" value="{{ $startYm }}">

                            <div class="modal-header">
                                <h5 class="modal-title">Bayar Periode – {{ $item->nama }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <p class="mb-1">
                                    <strong>Mulai dibayar dari:</strong> {{ $startLabel }}
                                </p>
                                <p class="text-muted" style="font-size: 12px;">
                                    {{ $noteStart }}.<br>
                                    Maksimal {{ $maxMonths }} bulan (perkiraan sampai {{ $endLabel }}).
                                </p>

                                <div class="mb-3">
                                    <label class="form-label">Jumlah bulan yang ingin dibayar</label>
                                    <input type="number"
                                           name="jumlah_bulan"
                                           class="form-control input-jumlah-bulan"
                                           min="1"
                                           max="{{ $maxMonths }}"
                                           value="1">
                                    <small class="text-muted">
                                        Contoh: isi 12 → sistem akan membayar 12 bulan ke depan
                                        mulai {{ $startLabel }}. Bulan yang belum punya tagihan akan
                                        dibuat otomatis.
                                    </small>
                                </div>

                                    <small class="text-danger warning-jumlah-bulan d-none">
                                        Jumlah bulan tidak boleh kosong atau 0.
                                    </small>

                                <div class="preview-bayar-box py-2 px-3 mb-0 text-preview-bayar">
                                    {{-- Akan dioverride oleh JS, ini fallback --}}
                                    @if($hargaPerBulan > 0)
                                        Perkiraan total: <strong>Rp {{ number_format($hargaPerBulan, 0, ',', '.') }}</strong>
                                        (Rp {{ number_format($hargaPerBulan, 0, ',', '.') }} x 1 bulan).
                                    @else
                                        Perkiraan total akan dihitung setelah dikirim (harga paket tidak terbaca).
                                    @endif
                                </div>

                                <div class="mt-2 text-end">
                                    <a href="{{ route('seles2.pelanggan.show', $item->id_pelanggan) }}"
                                       class="btn btn-link btn-sm text-decoration-none">
                                        Lihat detail pelanggan
                                    </a>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-success">
                                    Lanjut Bayar
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            @endif

        @empty
            <div class="text-center text-muted mt-4 p-4 bg-white rounded-3 shadow-sm mx-3">
                <i class="bi bi-person-x-fill display-4 mb-2"></i>
                <p>Belum ada pelanggan yang terdaftar.</p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if(method_exists($pelanggan, 'links'))
            <div class="mt-3">
                {{ $pelanggan->links() }}
            </div>
        @endif
    </div>

    <div class="hint-footer text-center mt-3 mb-2">
        Tap kartu untuk bayar periode / lihat status bayar.
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
    }

    .pelanggan-nama {
        font-size: 1rem;
        color: #111827;
    }

    .text-lunas {
        color: #16a34a !important;
    }

    .text-belum {
        color: #ef4444 !important;
    }

    /* KANAN (HARGA & INFO) */
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

    .card-lunas {
        background: #e6f8e8 !important; /* hijau lembut */
        border-left: 6px solid #16a34a;
    }

    .card-belum {
        background: #fde8e8 !important; /* merah lembut */
        border-left: 6px solid #ef4444;
    }

    .preview-bayar-box {
        background: #e8f4ff;
        border: 1px solid #b6daff;
        border-radius: 6px;
        font-size: 13px;
    }
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput  = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const cards        = document.querySelectorAll('.pelanggan-card');

    function applyFilter() {
        const q      = (searchInput?.value || '').toLowerCase();
        const status = (statusFilter?.value || '').toLowerCase(); // '' | 'lunas' | 'belum'

        cards.forEach(card => {
            const nama   = (card.dataset.nama || '').toLowerCase();
            const hp     = (card.dataset.hp || '').toLowerCase();
            const area   = (card.dataset.area || '').toLowerCase();
            const alamat = (card.dataset.alamat || '').toLowerCase();
            const sBayar = (card.dataset.statusBayar || '').toLowerCase();

            const textMatch =
                !q ||
                nama.includes(q) ||
                hp.includes(q) ||
                area.includes(q) ||
                alamat.includes(q);

            const statusMatch =
                !status || sBayar === status;

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
        document.querySelectorAll('.form-bayar-periode-sales').forEach(function (form) {
            const inputJumlah  = form.querySelector('.input-jumlah-bulan');
            const previewText  = form.querySelector('.text-preview-bayar');
            const warningEl    = form.querySelector('.warning-jumlah-bulan');
            const btnSubmit    = form.querySelector('button[type="submit"]');

            if (!inputJumlah || !previewText || !btnSubmit) return;

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

                if (bulanTagihan.length === 0) {
                    // belum ada tagihan sama sekali → anggap berurutan dari start
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

                // 1) iterasi dari start sampai bulan tagihan terakhir,
                //    hanya bulan yg ADA di bulanTagihan yang dihitung dulu
                while (true) {
                    const ym = ymString(curr);

                    if (bulanTagihan.includes(ym)) {
                        paid.push({ ...curr });
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
                let curr2 = { ...base };

                while (count < jml) {
                    paid.push({ ...curr2 });
                    count++;
                    curr2 = addMonths(curr2, 1);
                }

                return paid;
            }

            function updatePreview() {
                let jml = parseInt(inputJumlah.value || '0', 10);

                if (isNaN(jml) || jml < 1) {
                    // kalau belum valid, cukup kosongkan preview & biarkan validasi yang urus
                    previewText.innerHTML = 'Masukkan jumlah bulan yang valid terlebih dahulu.';
                    return;
                }
                if (jml > maxBulan) {
                    jml = maxBulan;
                    // tidak dipaksa ke input di sini, biar user bisa edit bebas
                }

                const paidMonths = computePaidMonths(jml);

                if (paidMonths.length === 0) {
                    previewText.innerHTML = 'Tidak ada bulan tagihan yang bisa dibayar.';
                    return;
                }

                const startLabel = formatBulanTahun(paidMonths[0]);
                const endLabel   = formatBulanTahun(paidMonths[paidMonths.length - 1]);
                const total      = jml * hargaPerBulan;

                const kalimat = (jml === 1)
                    ? `Akan dibayar 1 bulan tagihan untuk ${startLabel}.`
                    : `Akan dibayar ${jml} bulan tagihan, dari ${startLabel} sampai ${endLabel}.`;

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

            // 🔴 VALIDASI: tidak boleh kosong / 0 / bukan angka
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

            // Saat user mengetik
            inputJumlah.addEventListener('input', function () {
                validateJumlah();
                updatePreview();
            });

            // Saat blur, kita rapikan ke range 1..maxBulan
            inputJumlah.addEventListener('blur', function () {
                let val = parseInt(this.value || '0', 10);

                if (isNaN(val) || val < 1) val = 1;
                if (val > maxBulan)        val = maxBulan;

                this.value = val;
                validateJumlah();
                updatePreview();
            });

            // Saat submit, blok kalau invalid
            form.addEventListener('submit', function (e) {
                if (!validateJumlah()) {
                    e.preventDefault();
                    alert('Jumlah bulan tidak boleh kosong atau 0.');
                }
            });

            // init awal (pas modal pertama kali dimuat)
            validateJumlah();
            updatePreview();
        });
    }

    initFormBayarPeriodeSales();
});
</script>
@endpush

