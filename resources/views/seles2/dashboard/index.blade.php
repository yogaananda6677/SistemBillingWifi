@extends('seles2.layout.master')

@section('content')
    {{-- KARTU WAJIB SETOR (PALING ATAS) --}}
    <div class="menu-section mb-3">
        <div class="section-header">
            <div class="section-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <h6 class="section-title mb-0">Wajib Setor</h6>
        </div>

        <div class="p-3">

            <hr class="my-2">

            <div class="d-flex justify-content-between">
                <div>
                    <small class="text-muted d-block">Wajib Setor Bulan Ini</small>
                    <div class="fw-semibold">
                        Rp {{ number_format($wajibSetorBulanIni, 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-end">
                    <small class="text-muted d-block">Sudah Setor Bulan Ini</small>
                    <div class="fw-semibold text-success">
                        Rp {{ number_format($sudahSetorBulanIni, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="mt-2 small text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Angka di atas diambil dari total pembayaran pelanggan milik Anda.
            </div>
        </div>
    </div>

    {{-- QUICK STATS PELANGGAN --}}
<div class="quick-stats">

    <!-- BARIS 1 – TOTAL PELANGGAN (1 kolom penuh) -->
<a href="{{ route('seles2.pelanggan.index') }}" class="stat-card wide">
    <div class="stat-icon primary">
        <i class="bi bi-people-fill"></i>
    </div>
    <div class="stat-value">{{ $totalPelanggan }}</div>
    <div class="stat-label">Total Pelanggan</div>
</a>


<!-- BARIS 2 -->
<a href="{{ route('seles2.pelanggan.status', ['status' => 'aktif']) }}" class="stat-card">
    <div class="stat-icon success">
        <i class="bi bi-check-circle-fill"></i>
    </div>
    <div class="stat-value">{{ $totalAktif }}</div>
    <div class="stat-label">Aktif</div>
</a>

<a href="{{ route('seles2.pelanggan.status', ['status' => 'baru']) }}" class="stat-card">
    <div class="stat-icon info">
        <i class="bi bi-person-plus-fill"></i>
    </div>
    <div class="stat-value">{{ $totalBaru }}</div>
    <div class="stat-label">Baru</div>
</a>

<!-- BARIS 3 -->
<a href="{{ route('seles2.pelanggan.status', ['status' => 'berhenti']) }}" class="stat-card">
    <div class="stat-icon danger">
        <i class="bi bi-person-x-fill"></i>
    </div>
    <div class="stat-value">{{ $totalBerhenti }}</div>
    <div class="stat-label">Berhenti</div>
</a>

<a href="{{ route('seles2.pelanggan.status', ['status' => 'isolir']) }}" class="stat-card">
    <div class="stat-icon warning">
        <i class="bi bi-shield-exclamation"></i>
    </div>
    <div class="stat-value">{{ $totalIsolir }}</div>
    <div class="stat-label">Isolir</div>
</a>
<!-- BARIS 4 -->

<a href="{{ route('seles2.pelanggan.statusBayar', ['status_bayar' => 'lunas']) }}"
   class="stat-card">
    <div class="stat-icon primary">
        <i class="bi bi-cash-coin"></i>
    </div>
    <div class="stat-value">{{ $totalSudahBayar }}</div>
    <div class="stat-label">Sudah Bayar</div>
</a>

<a href="{{ route('seles2.pelanggan.statusBayar', ['status_bayar' => 'belum']) }}"
   class="stat-card">
    <div class="stat-icon danger">
        <i class="bi bi-exclamation-octagon-fill"></i>
    </div>
    <div class="stat-value">{{ $totalBelumBayar }}</div>
    <div class="stat-label">Belum Bayar</div>
</a>
<a href="{{ route('sales.pengajuan.index') }}" class="stat-card wide">
    <div class="stat-icon primary">
        <i class="bi bi-people-fill"></i>
    </div>
    <div class="stat-label">Ajukan Pengeluaran</div>
</a>


<a href="{{ route('seles2.pembayaran.riwayat') }}" class="stat-card wide">
    <div class="stat-icon primary">
        <i class="bi bi-receipt"></i> {{-- ikon lebih nyambung ke pembayaran --}}
    </div>

    <div class="stat-label">Riwayat Pembayaran</div>
</a>


</div>



<style>
.quick-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}

.quick-stats .wide {
    grid-column: span 2;
}

.stat-card {
    background: #ffffffd9;
    border-radius: 16px;
    padding: 16px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    position: relative;
}

.stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 6px;;
    font-size: 1.4rem;
}

/* WARNA ICON BACKGROUND */
.stat-icon.primary { background: #eef2ff; color: #4f46e5; }
.stat-icon.success { background: #e6fdf2; color: #16a34a; }
.stat-icon.warning { background: #fff4e5; color: #f59e0b; }
.stat-icon.info    { background: #e6f0ff; color: #2563eb; }
.stat-icon.danger  { background: #ffecec; color: #dc2626; }

/* TEXT */
.stat-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: .85rem;
    color: #6b7280;
}

/* Hilangkan warna biru & underline pada seluruh stat-card */
.stat-card {
    color: inherit !important;
    text-decoration: none !important;
}

/* Saat ditekan (active), tetap tidak berubah warna */
.stat-card:active,
.stat-card:focus,
.stat-card:hover {
    color: inherit !important;
    text-decoration: none !important;
    outline: none !important;
    box-shadow: none !important;
}

/* Hilangkan outline biru dari <a> pada mobile */
.stat-card:focus-visible {
    outline: none !important;
}

</style>
@endsection
