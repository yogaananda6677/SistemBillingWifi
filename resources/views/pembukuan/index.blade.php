@extends('layouts.master')
@section('title', 'Pembukuan Global')

@section('content')
@php
    use Carbon\Carbon;
@endphp

<style>
    /* --- ADMIN YELLOW THEME (CONSISTENT COMPACT) --- */
    :root {
        --theme-yellow: #ffc107;
        --theme-yellow-dark: #e0a800;
        --theme-yellow-soft: #fff9e6;
        --text-dark: #212529;
        --card-radius: 12px;
    }

    /* 1. Typography */
    .page-title {
        font-size: 22px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    /* 2. Tombol Kuning Custom */
    .btn-admin-yellow {
        background-color: var(--theme-yellow);
        color: var(--text-dark);
        font-weight: 600;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 13px;
        box-shadow: 0 2px 6px rgba(255, 193, 7, 0.3);
        transition: all 0.2s ease;
    }
    .btn-admin-yellow:hover {
        background-color: var(--theme-yellow-dark);
        color: var(--text-dark);
        transform: translateY(-2px);
    }

    /* 3. Card Styles */
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow);
        width: 100%;
    }

    /* 4. Form Inputs */
    .form-control-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .form-control-admin:focus {
        border-color: var(--theme-yellow);
        box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
    }

    /* 5. Table Styling (COMPACT) */
    .table-admin {
        width: 100%;
        margin-bottom: 0;
    }

    .table-admin thead th {
        background-color: var(--theme-yellow-soft);
        color: var(--text-dark);
        font-weight: 700;
        font-size: 12px;
        text-transform: uppercase;
        border-bottom: 2px solid var(--theme-yellow);
        padding: 12px 10px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .table-admin tbody td {
        padding: 10px;
        vertical-align: middle;
        font-size: 13px;
        border-bottom: 1px solid #f0f0f0;
    }

    .table-admin tbody tr:hover td {
        background-color: #fffdf5;
    }
    
    /* Label Filter Kecil */
    .filter-label {
        font-size: 11px;
        font-weight: 700;
        color: #6c757d;
        margin-bottom: 4px;
        display: block;
    }

    /* Additional: Modal Header Match Theme */
    .modal-header-yellow {
        background-color: var(--theme-yellow-soft);
        border-bottom: 2px solid var(--theme-yellow);
    }
</style>

<div class="container-fluid p-4">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-journal-bookmark-fill text-warning me-2"></i>Pembukuan Global
            </h4>
            <div class="text-muted small">Laporan Sales & Admin</div>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card-admin p-3 mb-3">
        <form method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <span class="filter-label">Bulan</span>
                    <select name="bulan" class="form-select form-control-admin">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ (int)$selectedMonth === $m ? 'selected' : '' }}>
                                {{ Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <span class="filter-label">Tahun</span>
                    <select name="tahun" class="form-select form-control-admin">
                        @foreach(range(now()->year - 3, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ (int)$selectedYear === $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <button class="btn btn-admin-yellow w-100">
                        <i class="bi bi-search me-1"></i> Tampilkan
                    </button>
                </div>
            </div>
        </form>

        <hr class="my-3 text-muted" style="opacity: 0.1">

        {{-- INFO STATISTIK --}}
        <div class="d-flex flex-wrap gap-4 align-items-center">
            <div>
                <span class="filter-label">Periode</span>
                <span class="fw-bold text-dark" style="font-size: 14px;">
                    {{ Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}
                </span>
            </div>
            @isset($stat)
                <div class="vr opacity-25 d-none d-md-block"></div>
                <div>
                    <span class="filter-label">Total Pelanggan</span>
                    <span class="fw-bold text-dark">{{ $stat['jumlah_pelanggan'] ?? 0 }}</span>
                </div>
                <div class="vr opacity-25 d-none d-md-block"></div>
                <div>
                    <span class="filter-label">Total Pembayaran</span>
                    <span class="fw-bold text-success">Rp {{ number_format($stat['jumlah_pembayaran'] ?? 0,0,',','.') }}</span>
                </div>
                <div class="vr opacity-25 d-none d-md-block"></div>
                <div>
                    <span class="filter-label">Total Pengeluaran</span>
                    <span class="fw-bold text-danger">Rp {{ number_format($stat['jumlah_pengeluaran'] ?? 0,0,',','.') }}</span>
                </div>
            @endisset
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-admin mb-0 text-end">
                <thead class="text-center">
                    <tr>
                        <th class="text-start ps-3" style="min-width:200px;">Sales / Admin</th>
                        <th>Pendapatan</th>
                        <th>Komisi</th>
                        <th>Pengeluaran</th>
                        <th>Total Bersih<br><small class="fw-normal opacity-75" style="text-transform: none; font-size: 10px;">(kewajiban bulan ini)</small></th>
                        <th>Setoran<br><small class="fw-normal opacity-75" style="text-transform: none; font-size: 10px;">(bulan ini)</small></th>
                        <th class="pe-3">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @if($rekap->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-warning opacity-50"></i>
                                Belum ada data pembukuan untuk periode ini.
                            </td>
                        </tr>
                    @else
                        @foreach($rekap as $idx => $row)
                            @php
                                $key = $row->modal_key ?? ('row-' . $idx);
                                $saldoGlobal = $row->saldo_global ?? 0;
                                $saldoLabel  = abs($saldoGlobal);
                                $saldoClass  = $saldoLabel == 0 ? 'text-muted' : ($saldoGlobal > 0 ? 'text-success' : 'text-danger');

                                $selisih      = $row->selisih ?? 0;
                                $selisihAbs   = abs($selisih);
                                $selisihClass = $selisihAbs == 0 ? 'text-muted' : ($selisih > 0 ? 'text-success' : 'text-danger');
                            @endphp
                            <tr>
                                {{-- LABEL + SALDO --}}
                                <td class="text-start ps-3">
                                    <div class="fw-bold text-dark">{{ $row->label }}</div>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Saldo akumulasi: 
                                        <span class="{{ $saldoClass }}">
                                            @if($saldoLabel == 0) Pas
                                            @elseif($saldoGlobal > 0) Lebih: {{ number_format($saldoLabel, 0, ',', '.') }}
                                            @else Kurang: {{ number_format($saldoLabel, 0, ',', '.') }}
                                            @endif
                                        </span>
                                    </small>
                                </td>

                                {{-- Pendapatan --}}
                                <td>
                                    @if(($row->pendapatan ?? 0) > 0)
                                        <a href="#" class="fw-bold text-dark text-decoration-none" data-bs-toggle="modal" data-bs-target="#pendapatanModal-{{ $key }}">
                                            Rp {{ number_format($row->pendapatan ?? 0, 0, ',', '.') }}
                                        </a>
                                    @else
                                        <span class="text-muted small">Rp 0</span>
                                    @endif
                                </td>

                                {{-- Komisi --}}
                                <td>
                                    @if($row->jenis === 'sales' && ($row->total_komisi ?? 0) > 0)
                                        <a href="#" class="fw-bold text-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#komisiModal-{{ $key }}">
                                            Rp {{ number_format($row->total_komisi ?? 0, 0, ',', '.') }}
                                        </a>
                                    @else
                                        <span class="text-muted small">Rp {{ number_format($row->total_komisi ?? 0, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                {{-- Pengeluaran --}}
                                <td>
                                    @if($row->jenis === 'sales' && ($row->total_pengeluaran ?? 0) > 0)
                                        <a href="#" class="fw-bold text-danger text-decoration-none" data-bs-toggle="modal" data-bs-target="#pengeluaranModal-{{ $key }}">
                                            Rp {{ number_format($row->total_pengeluaran ?? 0, 0, ',', '.') }}
                                        </a>
                                    @else
                                        <span class="text-muted small">Rp {{ number_format($row->total_pengeluaran ?? 0, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                {{-- Total Bersih --}}
                                <td class="bg-light fw-bold text-success">
                                    Rp {{ number_format($row->total_bersih ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Setoran --}}
                                <td>
                                    @if($row->jenis === 'sales' && ($row->total_setoran ?? 0) > 0)
                                        <a href="#" class="fw-bold text-success text-decoration-none" data-bs-toggle="modal" data-bs-target="#setoranModal-{{ $key }}">
                                            Rp {{ number_format($row->total_setoran ?? 0, 0, ',', '.') }}
                                        </a>
                                    @else
                                        <span class="text-muted small">Rp {{ number_format($row->total_setoran ?? 0, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                {{-- Selisih --}}
                                <td class="{{ $selisihClass }} fw-bold pe-3">
                                    @if($selisihAbs == 0)
                                        <span class="badge bg-light text-secondary border">Pas</span>
                                    @elseif($selisih > 0)
                                        Lebih: {{ number_format($selisihAbs, 0, ',', '.') }}
                                    @else
                                        Kurang: {{ number_format($selisihAbs, 0, ',', '.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODALS SECTION --}}
    @foreach($rekap as $idx => $row)
        @php $key = $row->modal_key ?? ('row-' . $idx); @endphp

        {{-- Modal Pendapatan --}}
        <div class="modal fade" id="pendapatanModal-{{ $key }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header modal-header-yellow">
                        <h5 class="modal-title fw-bold" style="font-size: 16px;">Detail Pendapatan – {{ $row->label }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        @php $detailPembayaran = $row->detail_pembayaran ?? collect(); @endphp
                        <div class="table-responsive">
                            <table class="table table-admin table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th>No. Bayar</th>
                                        <th>Pelanggan</th>
                                        <th class="text-end pe-3">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($detailPembayaran as $item)
                                        <tr>
                                            <td class="ps-3">{{ $item->tanggal_bayar ? Carbon::parse($item->tanggal_bayar)->format('d/m/Y H:i') : '-' }}</td>
                                            <td><span class="badge bg-white border text-dark">{{ $item->no_pembayaran }}</span></td>
                                            <td>{{ $item->nama_pelanggan ?? '-' }}</td>
                                            <td class="text-end pe-3 fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($row->jenis === 'sales')
            {{-- Modal Komisi --}}
            <div class="modal fade" id="komisiModal-{{ $key }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header modal-header-yellow">
                            <h5 class="modal-title fw-bold" style="font-size: 16px;">Detail Komisi – {{ $row->label }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            @php $detailKomisi = $row->detail_komisi ?? collect(); @endphp
                            <div class="table-responsive">
                                <table class="table table-admin table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Tanggal</th>
                                            <th>Pelanggan</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end pe-3">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detailKomisi as $item)
                                            <tr>
                                                <td class="ps-3">{{ $item->tanggal_bayar ? Carbon::parse($item->tanggal_bayar)->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $item->nama_pelanggan ?? '-' }}</td>
                                                <td class="text-end">{{ $item->jumlah_komisi }}</td>
                                                <td class="text-end pe-3">Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Pengeluaran --}}
            <div class="modal fade" id="pengeluaranModal-{{ $key }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header modal-header-yellow">
                            <h5 class="modal-title fw-bold" style="font-size: 16px;">Detail Pengeluaran – {{ $row->label }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            @php $detailPengeluaran = $row->detail_pengeluaran ?? collect(); @endphp
                            <div class="table-responsive">
                                <table class="table table-admin table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Tanggal</th>
                                            <th>Nama</th>
                                            <th>Catatan</th>
                                            <th class="text-end pe-3">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detailPengeluaran as $item)
                                            <tr>
                                                <td class="ps-3">{{ $item->tanggal_approve ? Carbon::parse($item->tanggal_approve)->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $item->nama_pengeluaran }}</td>
                                                <td>{{ $item->catatan ?? '-' }}</td>
                                                <td class="text-end pe-3 fw-bold text-danger">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Setoran --}}
            <div class="modal fade" id="setoranModal-{{ $key }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header modal-header-yellow">
                            <h5 class="modal-title fw-bold" style="font-size: 16px;">Detail Setoran – {{ $row->label }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            @php $detailSetoran = $row->detail_setoran ?? collect(); @endphp
                            <div class="table-responsive">
                                <table class="table table-admin table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Tanggal</th>
                                            <th>Admin</th>
                                            <th>Catatan</th>
                                            <th class="text-end pe-3">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detailSetoran as $item)
                                            <tr>
                                                <td class="ps-3">{{ $item->tanggal_setoran ? Carbon::parse($item->tanggal_setoran)->format('d/m/Y H:i') : '-' }}</td>
                                                <td>{{ $item->nama_admin ?? '-' }}</td>
                                                <td>{{ $item->catatan ?? '-' }}</td>
                                                <td class="text-end pe-3 fw-bold text-success">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

</div>
@endsection