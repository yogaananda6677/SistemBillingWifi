@extends('layouts.master')
@section('title', 'Pembukuan Global')

@section('content')
<div class="container-fluid py-4">

    <h3 class="mb-4 fw-bold">Pembukuan Global (Sales & Admin)</h3>

    {{-- FILTER BULAN & TAHUN --}}
    <form method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-md-3 col-6">
            <label class="form-label mb-1">Bulan</label>
            <select name="bulan" class="form-select form-select-sm">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ (int)$selectedMonth === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 col-6">
            <label class="form-label mb-1">Tahun</label>
            <select name="tahun" class="form-select form-select-sm">
                @foreach(range(now()->year - 3, now()->year + 1) as $y)
                    <option value="{{ $y }}" {{ (int)$selectedYear === $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 col-12">
            <button class="btn btn-primary btn-sm w-100">
                Tampilkan
            </button>
        </div>
    </form>

    {{-- INFO PERIODE + STATISTIK ATAS --}}
    <div class="mb-3 text-muted d-flex justify-content-between flex-wrap gap-2">
        <div>
            Periode:
            <strong>{{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}</strong>
        </div>
        @isset($stat)
        <div class="small">
            <span class="me-3">Pelanggan: <strong>{{ $stat['jumlah_pelanggan'] ?? 0 }}</strong></span>
            <span class="me-3">Pembayaran: <strong>Rp {{ number_format($stat['jumlah_pembayaran'] ?? 0,0,',','.') }}</strong></span>
            <span>Pengeluaran: <strong>Rp {{ number_format($stat['jumlah_pengeluaran'] ?? 0,0,',','.') }}</strong></span>
        </div>
        @endisset
    </div>

    {{-- TABEL REKAP --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0 align-middle text-end">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th class="text-start" style="min-width:220px;">Sales / Admin</th>
                            <th>Pendapatan</th>
                            <th>Komisi</th>
                            <th>Pengeluaran</th>
                            <th>Total Bersih</th>
                            <th>Setoran</th>
                            <th>Selisih (Bulan Ini)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap as $row)
                            @php
                                $key = $row->jenis . '-' . $row->user_id;
                                $saldoGlobal = $row->saldo_global ?? 0;
                                $saldoLabel  = abs($saldoGlobal);
                                $saldoClass  = $saldoLabel == 0
                                    ? 'text-muted'
                                    : ($saldoGlobal > 0 ? 'text-success' : 'text-danger');

                                $selisih = $row->selisih ?? 0;
                                $selisihClass = $selisih == 0
                                    ? 'text-muted'
                                    : ($selisih > 0 ? 'text-success' : 'text-danger');
                            @endphp
                            <tr>
                                {{-- LABEL + SALDO AKUMULASI --}}
                                <td class="text-start">
                                    <div>{{ $row->label }}</div>
                                    <small class="text-muted d-block">
                                        Saldo akumulasi:
                                        <span class="{{ $saldoClass }}">
                                            @if($saldoLabel == 0)
                                                Pas: Rp 0
                                            @elseif($saldoGlobal > 0)
                                                Kelebihan: Rp {{ number_format($saldoLabel, 0, ',', '.') }}
                                            @else
                                                Kurang: Rp {{ number_format($saldoLabel, 0, ',', '.') }}
                                            @endif
                                        </span>
                                    </small>
                                </td>

                                {{-- Pendapatan (klik -> modal pembayaran) --}}
                                <td>
                                    @if(($row->pendapatan ?? 0) > 0)
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 text-end w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#pendapatanModal-{{ $key }}">
                                            Rp {{ number_format($row->pendapatan ?? 0, 0, ',', '.') }}
                                        </button>
                                    @else
                                        Rp {{ number_format(0, 0, ',', '.') }}
                                    @endif
                                </td>

                                {{-- Komisi --}}
                                <td class="text-danger">
                                    @if($row->jenis === 'sales' && ($row->total_komisi ?? 0) > 0)
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 text-danger text-end w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#komisiModal-{{ $key }}">
                                            Rp {{ number_format($row->total_komisi ?? 0, 0, ',', '.') }}
                                        </button>
                                    @else
                                        Rp {{ number_format($row->total_komisi ?? 0, 0, ',', '.') }}
                                    @endif
                                </td>

                                {{-- Pengeluaran --}}
                                <td class="text-danger">
                                    @if($row->jenis === 'sales' && ($row->total_pengeluaran ?? 0) > 0)
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 text-danger text-end w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#pengeluaranModal-{{ $key }}">
                                            Rp {{ number_format($row->total_pengeluaran ?? 0, 0, ',', '.') }}
                                        </button>
                                    @else
                                        Rp {{ number_format($row->total_pengeluaran ?? 0, 0, ',', '.') }}
                                    @endif
                                </td>

                                {{-- Total Bersih --}}
                                <td class="text-success fw-semibold">
                                    Rp {{ number_format($row->total_bersih ?? 0, 0, ',', '.') }}
                                </td>

                                {{-- Setoran --}}
                                <td class="text-success">
                                    @if($row->jenis === 'sales' && ($row->total_setoran ?? 0) > 0)
                                        <button type="button"
                                            class="btn btn-link btn-sm p-0 text-success text-end w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#setoranModal-{{ $key }}">
                                            Rp {{ number_format($row->total_setoran ?? 0, 0, ',', '.') }}
                                        </button>
                                    @else
                                        Rp {{ number_format($row->total_setoran ?? 0, 0, ',', '.') }}
                                    @endif
                                </td>

                                {{-- Selisih Bulan Ini --}}
                                <td class="{{ $selisihClass }} fw-semibold">
                                    Rp {{ number_format($selisih, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
                                    Belum ada data pembukuan untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODALS UNTUK SEMUA BARIS (SALES & ADMIN) --}}
    @foreach($rekap as $row)
        @php
            $key = $row->jenis . '-' . $row->user_id;
        @endphp

        {{-- PENDAPATAN --}}
        <div class="modal fade" id="pendapatanModal-{{ $key }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pendapatan – {{ $row->label }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($row->detail_pembayaran->isEmpty())
                            <p class="text-muted mb-0">Tidak ada pembayaran pada periode ini.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>No. Pembayaran</th>
                                            <th>Pelanggan</th>
                                            <th class="text-end">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row->detail_pembayaran as $item)
                                            <tr>
                                                <td>{{ $item->tanggal_bayar ? \Carbon\Carbon::parse($item->tanggal_bayar)->format('d/m/Y H:i') : '-' }}</td>
                                                <td>{{ $item->no_pembayaran }}</td>
                                                <td>{{ $item->nama_pelanggan ?? '-' }}</td>
                                                <td class="text-end">
                                                    Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="3" class="text-end">Total</td>
                                            <td class="text-end">
                                                Rp {{ number_format($row->pendapatan, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KOMISI (hanya sales) --}}
        @if($row->jenis === 'sales')
        <div class="modal fade" id="komisiModal-{{ $key }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Komisi – {{ $row->label }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($row->detail_komisi->isEmpty())
                            <p class="text-muted mb-0">Tidak ada komisi pada periode ini.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal Bayar</th>
                                            <th>No. Pembayaran</th>
                                            <th>Pelanggan</th>
                                            <th class="text-end">Jumlah</th>
                                            <th class="text-end">Nominal Komisi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row->detail_komisi as $item)
                                            <tr>
                                                <td>{{ $item->tanggal_bayar ? \Carbon\Carbon::parse($item->tanggal_bayar)->format('d/m/Y H:i') : '-' }}</td>
                                                <td>{{ $item->no_pembayaran }}</td>
                                                <td>{{ $item->nama_pelanggan ?? '-' }}</td>
                                                <td class="text-end">{{ $item->jumlah_komisi }}</td>
                                                <td class="text-end">
                                                    Rp {{ number_format($item->nominal_komisi, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="4" class="text-end">Total Komisi</td>
                                            <td class="text-end">
                                                Rp {{ number_format($row->total_komisi, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- PENGELUARAN --}}
        <div class="modal fade" id="pengeluaranModal-{{ $key }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Pengeluaran – {{ $row->label }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($row->detail_pengeluaran->isEmpty())
                            <p class="text-muted mb-0">Tidak ada pengeluaran approved pada periode ini.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal Approve</th>
                                            <th>Nama Pengeluaran</th>
                                            <th>Catatan</th>
                                            <th class="text-end">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row->detail_pengeluaran as $item)
                                            <tr>
                                                <td>{{ $item->tanggal_approve ? \Carbon\Carbon::parse($item->tanggal_approve)->format('d/m/Y H:i') : '-' }}</td>
                                                <td>{{ $item->nama_pengeluaran }}</td>
                                                <td>{{ $item->catatan ?? '-' }}</td>
                                                <td class="text-end">
                                                    Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="3" class="text-end">Total Pengeluaran</td>
                                            <td class="text-end">
                                                Rp {{ number_format($row->total_pengeluaran, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- SETORAN --}}
        <div class="modal fade" id="setoranModal-{{ $key }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detail Setoran – {{ $row->label }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @if($row->detail_setoran->isEmpty())
                            <p class="text-muted mb-0">Belum ada setoran pada periode ini.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal Setoran</th>
                                            <th>Admin Penerima</th>
                                            <th>Catatan</th>
                                            <th class="text-end">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($row->detail_setoran as $item)
                                            <tr>
                                                <td>{{ $item->tanggal_setoran ? \Carbon\Carbon::parse($item->tanggal_setoran)->format('d/m/Y H:i') : '-' }}</td>
                                                <td>{{ $item->nama_admin ?? '-' }}</td>
                                                <td>{{ $item->catatan ?? '-' }}</td>
                                                <td class="text-end">
                                                    Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="fw-bold">
                                            <td colspan="3" class="text-end">Total Setoran</td>
                                            <td class="text-end">
                                                Rp {{ number_format($row->total_setoran, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach

</div>
@endsection
