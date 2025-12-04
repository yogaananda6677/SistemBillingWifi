@extends('seles2.layout.master')

@section('content')

<div class="pelanggan-page">

    {{-- HEADER --}}
    <div class="pelanggan-header d-flex align-items-center justify-content-between px-3 py-2">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard-sales') }}" class="back-btn me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h5 class="mb-0 fw-semibold">Status Pembayaran</h5>
        </div>
    </div>

    @php
        $statusPage  = $statusBayar ?? request('status_bayar', 'belum'); // 'belum' / 'lunas'
        $totalLunas  = $countLunas  ?? 0;
        $totalBelum  = $countBelum  ?? 0;
    @endphp

    {{-- FILTER & SEARCH --}}
    <div class="filter-bar mt-3 px-3">
        <div class="flex-grow-1 me-2">
            <input
                type="text"
                id="search-input"
                class="form-control form-control-sm"
                placeholder="Cari nama / HP / alamat / wilayah..."
            >
        </div>
        <div style="width: 140px;">
            <select id="area-filter" class="form-select form-select-sm">
                <option value="">Semua Wilayah</option>
                @php
                    $areas = $dataArea ?? collect([]);
                @endphp
                @foreach($areas as $area)
                    <option value="{{ strtolower($area->nama_area) }}">
                        {{ $area->nama_area }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- LIST PELANGGAN --}}
    <div class="pelanggan-list mt-3 px-3 pb-3">
        @forelse ($pelanggan as $item)
            @php


                $langgananAktif = $item->langganan->sortByDesc('tanggal_mulai')->first();
                $areaName       = $item->area->nama_area ?? '-';

                // Semua tagihan pelanggan (dari semua langganan)
                $semuaTagihan = $item->langganan
                    ->flatMap(fn($l) => $l->tagihan);

                // TUNGGAKAN (BELUM LUNAS)
                $tunggakan = $semuaTagihan
                    ->where('status_tagihan', 'belum lunas')
                    ->sortBy(fn($t) => $t->tahun * 100 + $t->bulan);

                // TAGIHAN LUNAS TERAKHIR
                $tagihanLunasTerakhir = $semuaTagihan
                    ->whereIn('status_tagihan', ['lunas', 'sudah lunas'])
                    ->sortByDesc(fn($t) => $t->tahun * 100 + $t->bulan)
                    ->first();

                // ==== TAGIHAN UNTUK DITAMPILKAN DI KARTU (NOMINAL & JATUH TEMPO) ====
                // Kalau halaman "Belum Bayar" → pakai tunggakan paling awal
                // Kalau "Sudah Bayar"         → pakai tagihan lunas terakhir
                if ($statusPage === 'belum') {
                    $tagihanDisplay = $tunggakan->first()
                        ?? $semuaTagihan->sortByDesc(fn($t) => $t->tahun * 100 + $t->bulan)->first();
                } else { // 'lunas'
                    $tagihanDisplay = $tagihanLunasTerakhir
                        ?? $semuaTagihan->sortByDesc(fn($t) => $t->tahun * 100 + $t->bulan)->first();
                }

                $nominalTagihanDisplay = $tagihanDisplay->total_tagihan ?? 0;

// ==== HITUNG JATUH TEMPO (SINKRON DENGAN ensureTagihanBulanIni) ====
$jatuhTempoDisplay = null;
if ($tagihanDisplay) {
    if (!empty($tagihanDisplay->jatuh_tempo)) {
        $jatuhTempoDisplay = \Carbon\Carbon::parse($tagihanDisplay->jatuh_tempo);
    } else {
        $tahun = (int) $tagihanDisplay->tahun;
        $bulan = (int) $tagihanDisplay->bulan;

        // referensi hari aktivasi: tanggal_mulai langganan, kalau nggak ada pakai tanggal_registrasi
        $refDate = $langgananAktif?->tanggal_mulai
            ? \Carbon\Carbon::parse($langgananAktif->tanggal_mulai)
            : \Carbon\Carbon::parse($item->tanggal_registrasi ?? now());

        $dayAktif      = $refDate->day;
        $endOfMonthDay = \Carbon\Carbon::create($tahun, $bulan, 1)->endOfMonth()->day;
        $dayJatuhTempo = min($dayAktif, $endOfMonthDay);

        $jatuhTempoDisplay = \Carbon\Carbon::create($tahun, $bulan, $dayJatuhTempo);
    }
}

                $modalId = 'modalPembayaran-' . $item->id_pelanggan;
            @endphp



            {{-- KARTU PELANGGAN (TRIGGER MODAL) --}}
            <div
                class="pelanggan-card position-relative"
                data-nama="{{ strtolower($item->nama ?? '') }}"
                data-hp="{{ strtolower($item->nomor_hp ?? '') }}"
                data-area="{{ strtolower($areaName) }}"
                data-alamat="{{ strtolower($item->alamat ?? '') }}"
                data-bs-toggle="modal"
                data-bs-target="#{{ $modalId }}"
            >
                <div class="row g-0 align-items-center w-100 m-0">
                    {{-- KIRI --}}
                    <div class="col-7 pe-2">
                        <div class="d-flex align-items-center mb-1">
                            <div class="fw-bold text-truncate pelanggan-nama me-2">
                                {{ $item->nama ?? '-' }}
                            </div>
                        </div>

                        <div class="text-muted small text-truncate">
                            {{ strtoupper($areaName) }}
                            @if(!empty($item->nomor_hp))
                                • {{ $item->nomor_hp }}
                            @endif
                        </div>

                        <div class="small text-muted text-truncate mt-1">
                            {{ $item->alamat ?? 'Alamat tidak tersedia' }}
                        </div>

                        <div class="mt-2 small">
                            Status :
                            @if($statusPage === 'lunas')
                                <span class="fw-bold text-lunas">SUDAH BAYAR</span>
                            @else
                                <span class="fw-bold text-belum">BELUM BAYAR</span>
                            @endif
                        </div>
                    </div>

{{-- KANAN --}}
<div class="col-5 ps-2 harga-col d-flex flex-column justify-content-between">
    <div class="d-flex flex-column align-items-end w-100">
        @if(!empty($item->ip_address))
            <div class="small fw-bold text-dark mb-1 text-end text-break">
                {{ $item->ip_address }}
            </div>
        @endif

        @if($jatuhTempoDisplay)
            <div class="small text-muted text-end">
                Jatuh Tempo:
                {{ $jatuhTempoDisplay->format('d-m-Y') }}
            </div>
        @endif
    </div>

    <div class="text-end w-100 mt-3">
        <span class="harga-label">Rp.</span>
        <span class="harga-value">
            {{ number_format($nominalTagihanDisplay, 0, ',', '.') }}
        </span>
    </div>
</div>

                </div>
            </div>

            {{-- MODAL DETAIL PEMBAYARAN --}}
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                Info Pembayaran – {{ $item->nama }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            @if($statusPage === 'lunas')
                                {{-- SUDAH BAYAR → tunjukkan lunas sampai kapan --}}
                                @if($tagihanLunasTerakhir)
                                    @php
                                        $tahunL  = $tagihanLunasTerakhir->tahun;
                                        $bulanL  = $tagihanLunasTerakhir->bulan;
                                        $periode = \Carbon\Carbon::create($tahunL, $bulanL, 1)
                                            ->locale('id')
                                            ->translatedFormat('F Y');
                                    @endphp

                                    <p class="mb-2">
                                        Pelanggan ini <strong>SUDAH LUNAS</strong> sampai periode:
                                    </p>
                                    <h5 class="fw-bold">{{ $periode }}</h5>

                                    <p class="mt-3 mb-0 text-muted small">
                                        Tagihan sampai bulan tersebut sudah dibayarkan.
                                        Cek riwayat pembayaran detail di halaman pelanggan jika diperlukan.
                                    </p>
                                @else
                                    <p class="text-muted mb-0">
                                        Data tagihan lunas terakhir tidak ditemukan.
                                    </p>
                                @endif

                            @else
                                {{-- BELUM BAYAR → tampilkan TUNGGAKAN --}}
                                @if($tunggakan->isNotEmpty())
                                    <p class="mb-2">
                                        Daftar tunggakan untuk <strong>{{ $item->nama }}</strong>:
                                    </p>

                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Periode</th>
                                                    <th class="text-end">Nominal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tunggakan as $tg)
                                                    @php
                                                        $periodeTg = \Carbon\Carbon::create($tg->tahun, $tg->bulan, 1)
                                                            ->locale('id')
                                                            ->translatedFormat('F Y');
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $periodeTg }}</td>
                                                        <td class="text-end">
                                                            Rp {{ number_format($tg->total_tagihan, 0, ',', '.') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <p class="mt-2 mb-0 text-muted small">
                                        Mohon ditindaklanjuti untuk penagihan tunggakan ini.
                                    </p>
                                @else
                                    <p class="text-muted mb-0">
                                        Tidak ditemukan tagihan tunggakan, tetapi status halaman ini masih "Belum Bayar".
                                        Silakan cek detail pelanggan.
                                    </p>
                                @endif
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                    data-bs-dismiss="modal">Tutup</button>
                            <a href="{{ route('seles2.pelanggan.show', $item->id_pelanggan) }}"
                               class="btn btn-primary">
                                Lihat Detail Pelanggan
                            </a>
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
        @if(method_exists($pelanggan, 'links'))
            <div class="mt-3 px-3">
                {{ $pelanggan->links() }}
            </div>
        @endif
    </div>

    <div class="hint-footer text-center mt-3 mb-3 small text-muted">
        Tap kartu untuk melihat detail status pembayaran & tunggakan.
    </div>
</div>
@endsection

<style>
.pelanggan-header {
    background: #ffffff;
    border-bottom: 1px solid #f0f0f0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
}

.back-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: #f5f5f5;
    color: #333;
    text-decoration: none;
}

.back-btn i {
    font-size: 1rem;
}

/* Filter bar */
.filter-bar {
    display: flex;
    align-items: center;
}

/* Card pelanggan */
.pelanggan-list {
    margin-top: 12px;
}

.pelanggan-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 10px 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.pelanggan-card:hover {
    background: #f9fafb;
}

.harga-label {
    font-size: 0.8rem;
    color: #888;
    margin-right: 4px;
}

.harga-value {
    font-weight: 700;
    font-size: 1rem;
}

.text-belum {
    color: #e03131;
}

.text-lunas {
    color: #2f9e44;
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const areaFilter  = document.getElementById('area-filter');
    const cards       = document.querySelectorAll('.pelanggan-card');

    function applyFilter() {
        const q    = (searchInput.value || '').toLowerCase();
        const area = (areaFilter.value || '').toLowerCase();

        cards.forEach(card => {
            const nama   = card.dataset.nama   || '';
            const hp     = card.dataset.hp     || '';
            const alamat = card.dataset.alamat || '';
            const areaCd = card.dataset.area   || '';

            const textMatch =
                !q ||
                nama.includes(q) ||
                hp.includes(q) ||
                alamat.includes(q) ||
                areaCd.includes(q);

            const areaMatch =
                !area || areaCd === area;

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
