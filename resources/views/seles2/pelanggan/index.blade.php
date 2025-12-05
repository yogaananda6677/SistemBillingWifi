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
                $tagihanTerbaru = null;

                if ($langgananAktif && $langgananAktif->tagihan->count()) {
                    $tagihanTerbaru = $langgananAktif->tagihan
                        ->sortByDesc(fn ($t) => $t->tahun * 100 + $t->bulan)
                        ->first();
                }

                $tanggalTagih   = $tagihanTerbaru?->jatuh_tempo;
                $statusTagihan  = strtolower($tagihanTerbaru->status_tagihan ?? '');
                $isLunas        = in_array($statusTagihan, ['lunas', 'sudah lunas']);
                $nominalTagihan = $tagihanTerbaru?->total_tagihan ?? 0;
            @endphp

            <div
                class="pelanggan-card position-relative {{ $isLunas ? 'card-lunas' : 'card-belum' }}"
                data-nama="{{ $item->nama }}"
                data-hp="{{ $item->nomor_hp }}"
                data-area="{{ $item->area->nama_area ?? '' }}"
                data-alamat="{{ $item->alamat }}"
                data-status-bayar="{{ $isLunas ? 'lunas' : 'belum' }}"
            >

                {{-- SELURUH CARD BISA DIKLIK --}}
                <a href="{{ route('seles2.pelanggan.show', $item->id_pelanggan) }}" class="stretched-link"></a>

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
                            Status:
                            <span class="fw-bold {{ $isLunas ? 'text-lunas' : 'text-belum' }}">
                                {{ $isLunas ? 'SUDAH BAYAR' : 'BELUM BAYAR' }}
                            </span>
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
        Tap untuk Detail.
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

    .card-lunas {
        background: #e6f8e8 !important; /* hijau lembut */
        border-left: 6px solid #16a34a;
    }

    .card-belum {
        background: #fde8e8 !important; /* merah lembut */
        border-left: 6px solid #ef4444;
    }

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
        const q      = (searchInput.value || '').toLowerCase();
        const status = (statusFilter.value || '').toLowerCase(); // '' | 'lunas' | 'belum'

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
});
</script>
@endpush
