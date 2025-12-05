@extends('seles2.layout.master')
@section('title', 'Pembukuan')

@section('content')
@php
    use Carbon\Carbon;

    $selectedMonth = $selectedMonth ?? now()->month;
    $selectedYear  = $selectedYear ?? now()->year;

    $rekap = $rekap ?? null;

    $pendapatanPelanggan = $rekap->total_pendapatan             ?? 0;
    $komisi              = $rekap->total_komisi                 ?? 0;
    $pengeluaran         = $rekap->total_pengeluaran            ?? 0;
    $total               = $rekap->pendapatan_bersih            ?? ($pendapatanPelanggan - $komisi - $pengeluaran);
    $sudahSetor          = $rekap->total_setoran                ?? 0;
    $wajibSetor          = $rekap->wajib_setor_bulan_ini        ?? $total;
    $uangBelumSetor      = $rekap->uang_belum_disetor_bulan_ini ?? max($wajibSetor - $sudahSetor, 0);

    $pembayaranList  = $pembayaranList  ?? collect();
    $komisiList      = $komisiList      ?? collect();
    $pengeluaranList = $pengeluaranList ?? collect();
    $setoranList     = $setoranList     ?? collect();
@endphp

<style>
    .pembukuan-page {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background-color: #f1f3f6;
        padding: 12px 0 80px 0;
        min-height: 100vh;
    }

    .pembukuan-page .pembukuan-header {
        background-color: #ffffff;
        padding: 10px 16px;
        margin-bottom: 8px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .pembukuan-page .filter-mini {
        font-size: 0.8rem;
        color: #6b7280;
    }

    .pembukuan-page .filter-mini form {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .pembukuan-page select {
        font-size: 0.75rem;
        padding: 2px 6px;
        border-radius: 999px;
        border: 1px solid #d1d5db;
        background: #fff;
    }

    .pembukuan-page button.filter-btn {
        font-size: 0.75rem;
        padding: 3px 10px;
        border-radius: 999px;
        border: none;
        background: #4f46e5;
        color: #fff;
    }

    .pembukuan-page .card-box {
        margin: 16px;
    }

    .pembukuan-page .box-inner {
        background-color: #ffffff;
        border-radius: 12px;
        padding: 16px 18px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    }

    .pembukuan-page .row-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .pembukuan-page .label-text {
        font-size: 0.9rem;
        color: #4b5563;
    }

    .pembukuan-page .value-text {
        font-size: 0.95rem;
        font-weight: 600;
        color: #111827;
    }

    .pembukuan-page .text-danger { color: #ef4444 !important; }
    .pembukuan-page .text-success { color: #10b981 !important; }
    .pembukuan-page .text-warning { color: #f59e0b !important; }

    .pembukuan-page .fw-semibold { font-weight: 600; }
    .pembukuan-page .fw-bold { font-weight: 700; }

    .pembukuan-page .hr-divider {
        border: 0;
        border-top: 1px solid #e5e7eb;
        margin: 10px 0;
    }

    .pembukuan-page .note-box {
        margin: 16px;
        font-size: 0.8rem;
        color: #6b7280;
    }

    .pembukuan-page .periode-text {
        font-size: 0.8rem;
        color: #6b7280;
        margin: 0 16px 8px 16px;
    }

    .pembukuan-page .clickable {
        cursor: pointer;
        text-decoration: underline;
        text-decoration-style: dotted;
    }

    /* Modal mobile */
    .modal-mobile {
        max-width: 480px;
        margin: 0.5rem auto;
    }
    @media (max-width: 575.98px) {
        .modal-mobile {
            max-width: 95%;
            margin: 1.25rem auto;
        }
        .modal-mobile .modal-content {
            border-radius: 16px;
        }
    }
    .modal-body small {
        font-size: 0.75rem;
    }
</style>

<div class="pembukuan-page">

    {{-- HEADER + FILTER MINI --}}
    <div class="pembukuan-header">
        <div>
            <h5 class="fw-semibold m-0">Pembukuan</h5>
        </div>

        <div class="filter-mini">
            <form method="GET" action="{{ route('seles2.pembukuan.index') }}">
                <select name="bulan">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (int)$selectedMonth === $m ? 'selected' : '' }}>
                            {{ Carbon::create()->month($m)->translatedFormat('M') }}
                        </option>
                    @endforeach
                </select>

                <select name="tahun">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ (int)$selectedYear === $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="filter-btn">OK</button>
            </form>
        </div>
    </div>

    {{-- Info Periode --}}
    <div class="periode-text">
        Periode:
        <strong>{{ Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}</strong>
    </div>

    @if(!$rekap)
        <div class="card-box">
            <div class="box-inner text-center">
                <span class="label-text">Belum ada data pembukuan untuk akun Anda.</span>
            </div>
        </div>
    @else

        {{-- KARTU 1: Pendapatan & Pengeluaran Bulan Ini --}}
        <div class="card-box">
            <div class="box-inner">

                {{-- Pendapatan Pelanggan (klik untuk detail pembayaran) --}}
                <div class="row-item">
                    <span class="label-text">Pendapatan Pelanggan</span>
                    <span class="value-text clickable"
                          data-bs-toggle="modal"
                          data-bs-target="#modalPendapatan">
                        Rp. {{ number_format($pendapatanPelanggan, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Komisi (klik untuk detail per pembayaran) --}}
                <div class="row-item">
                    <span class="label-text text-danger">Komisi</span>
                    <span class="value-text text-danger clickable"
                          data-bs-toggle="modal"
                          data-bs-target="#modalKomisi">
                        Rp. {{ number_format($komisi, 0, ',', '.') }}
                    </span>
                </div>

                {{-- Pengeluaran (klik untuk rincian pengeluaran approved) --}}
                <div class="row-item">
                    <span class="label-text text-danger">Pengeluaran</span>
                    <span class="value-text text-danger clickable"
                          data-bs-toggle="modal"
                          data-bs-target="#modalPengeluaran">
                        Rp. {{ number_format($pengeluaran, 0, ',', '.') }}
                    </span>
                </div>

                <hr class="hr-divider">

                <div class="row-item">
                    <span class="label-text fw-semibold">Total (Pendapatan Bersih)</span>
                    <span class="value-text text-success fw-bold">
                        Rp. {{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>

            </div>
        </div>

        {{-- KARTU 2: Setoran Bulan Ini --}}
        <div class="card-box">
            <div class="box-inner">

                <div class="row-item">
                    <span class="label-text">Uang Belum di Setor (Bulan Ini)</span>
                    <span class="value-text text-warning fw-bold">
                        Rp. {{ number_format($uangBelumSetor, 0, ',', '.') }}
                    </span>
                </div>

                <div class="row-item" style="margin-top:12px;">
                    <span class="label-text">Sudah di Setor (Bulan Ini)</span>
                    <span class="value-text text-success fw-bold clickable"
                          data-bs-toggle="modal"
                          data-bs-target="#modalSetoran">
                        Rp. {{ number_format($sudahSetor, 0, ',', '.') }}
                    </span>
                </div>

            </div>
        </div>

    @endif

    <div class="note-box">
        Catatan: Semua angka di atas adalah untuk bulan yang dipilih berdasarkan tanggal transaksi.
        <br>
        Rekap kekurangan / kelebihan lintas bulan bisa dibuat di halaman Setoran global.
    </div>
</div>

{{-- ================== MODAL DETAIL ================== --}}

{{-- 1) Modal Pendapatan Pelanggan --}}
<div class="modal fade" id="modalPendapatan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-mobile">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">Detail Pendapatan Pelanggan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @if($pembayaranList->isEmpty())
                    <p class="text-muted small mb-0">Belum ada pembayaran di bulan ini.</p>
                @else
                    <div class="small">
                        @foreach($pembayaranList as $p)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="fw-semibold">
                                    {{ $p->nama_pelanggan ?? '-' }}
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">
                                        {{ $p->tanggal_bayar ? Carbon::parse($p->tanggal_bayar)->translatedFormat('d M Y H:i') : '-' }}
                                    </span>
                                    <span class="fw-bold">
                                        Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($p->no_pembayaran)
                                    <div class="text-muted tiny">
                                        No: {{ $p->no_pembayaran }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 2) Modal Komisi --}}
<div class="modal fade" id="modalKomisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-mobile">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">Detail Komisi</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @if($komisiList->isEmpty())
                    <p class="text-muted small mb-0">Belum ada komisi di bulan ini.</p>
                @else
                    <div class="small">
                        @foreach($komisiList as $k)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="fw-semibold">
                                    {{ $k->nama_pelanggan ?? '-' }}
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">
                                        {{ $k->tanggal_bayar ? Carbon::parse($k->tanggal_bayar)->translatedFormat('d M Y H:i') : '-' }}
                                    </span>
                                    <span class="fw-bold text-danger">
                                        Rp {{ number_format($k->nominal_komisi, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-muted tiny">
                                    Bayar: Rp {{ number_format($k->nominal_bayar, 0, ',', '.') }}
                                    @if($k->jumlah_komisi)
                                        &nbsp;|&nbsp; Komisi x {{ $k->jumlah_komisi }}
                                    @endif
                                    @if($k->no_pembayaran)
                                        <br>No: {{ $k->no_pembayaran }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 3) Modal Pengeluaran --}}
<div class="modal fade" id="modalPengeluaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-mobile">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">Rincian Pengeluaran Disetujui</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @if($pengeluaranList->isEmpty())
                    <p class="text-muted small mb-0">Belum ada pengeluaran disetujui di bulan ini.</p>
                @else
                    <div class="small">
                        @foreach($pengeluaranList as $pg)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="fw-semibold">
                                    {{ $pg->nama_pengeluaran }}
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">
                                        {{ $pg->tanggal_approve ? Carbon::parse($pg->tanggal_approve)->translatedFormat('d M Y H:i') : '-' }}
                                    </span>
                                    <span class="fw-bold text-danger">
                                        Rp {{ number_format($pg->nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($pg->catatan)
                                    <div class="text-muted tiny">
                                        Catatan: {{ $pg->catatan }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 4) Modal Setoran --}}
<div class="modal fade" id="modalSetoran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-mobile">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title mb-0">Riwayat Setoran Bulan Ini</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                @if($setoranList->isEmpty())
                    <p class="text-muted small mb-0">Belum ada setoran di bulan ini.</p>
                @else
                    <div class="small">
                        @foreach($setoranList as $st)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">
                                        {{ $st->tanggal_setoran ? Carbon::parse($st->tanggal_setoran)->translatedFormat('d M Y H:i') : '-' }}
                                    </span>
                                    <span class="fw-bold text-success">
                                        Rp {{ number_format($st->nominal, 0, ',', '.') }}
                                    </span>
                                </div>
                                @if($st->catatan)
                                    <div class="text-muted tiny">
                                        Catatan: {{ $st->catatan }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
                <small class="text-muted d-block mt-2">
                    * Saat ini ditampilkan setoran yang tanggalnya berada di bulan ini.
                </small>
            </div>
        </div>
    </div>
</div>

@endsection
