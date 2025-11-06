<!-- @extends('layouts.master') {{-- sesuaikan --}}

@section('title', 'Detail Pembukuan Sales')

@section('content')
@php
    use Carbon\Carbon;
    $summary = $summary ?? null;
    $selectedMonth = $selectedMonth ?? now()->month;
    $selectedYear  = $selectedYear ?? now()->year;
@endphp

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('pembukuan.index', ['tahun' => $selectedYear, 'bulan' => $selectedMonth]) }}"
               class="btn btn-light btn-sm mb-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>

            <h4 class="mb-0">
                Detail Pembukuan
                @if($summary)
                    <small class="text-muted">— {{ $summary->nama_sales }}</small>
                @endif
            </h4>
            <div class="text-muted small">
                Periode:
                <strong>{{ Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}</strong>
            </div>
        </div>
    </div>

    @if(!$summary)
        <div class="alert alert-warning">
            Data pembukuan tidak ditemukan untuk sales/periode ini.
        </div>
    @else

        {{-- RINGKASAN ATAS --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <strong>Ringkasan</strong>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-6">Pendapatan Pelanggan</dt>
                            <dd class="col-6 text-end">
                                Rp {{ number_format($summary->total_pendapatan, 0, ',', '.') }}
                            </dd>

                            <dt class="col-6 text-danger">Komisi</dt>
                            <dd class="col-6 text-end text-danger">
                                Rp {{ number_format($summary->total_komisi, 0, ',', '.') }}
                            </dd>

                            <dt class="col-6 text-danger">Pengeluaran</dt>
                            <dd class="col-6 text-end text-danger">
                                Rp {{ number_format($summary->total_pengeluaran, 0, ',', '.') }}
                            </dd>

                            <dt class="col-6 mt-2">Harus Disetorkan</dt>
                            <dd class="col-6 mt-2 text-end fw-semibold">
                                Rp {{ number_format($summary->harus_disetorkan, 0, ',', '.') }}
                            </dd>

                            <dt class="col-6 mt-2">Setoran</dt>
                            <dd class="col-6 mt-2 text-end fw-semibold text-success">
                                Rp {{ number_format($summary->total_setoran, 0, ',', '.') }}
                            </dd>

                            <dt class="col-6 mt-2">Selisih</dt>
                            <dd class="col-6 mt-2 text-end">
                                @php $selisih = $summary->selisih_setoran; @endphp
                                <span class="{{ $selisih < 0 ? 'text-danger' : ($selisih > 0 ? 'text-success' : '') }}">
                                    Rp {{ number_format($selisih, 0, ',', '.') }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB DETAIL --}}
        <ul class="nav nav-tabs mb-3" id="pembukuanTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pembayaran-tab" data-bs-toggle="tab"
                        data-bs-target="#pembayaran" type="button" role="tab">
                    Pembayaran
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="komisi-tab" data-bs-toggle="tab"
                        data-bs-target="#komisi" type="button" role="tab">
                    Komisi
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pengeluaran-tab" data-bs-toggle="tab"
                        data-bs-target="#pengeluaran" type="button" role="tab">
                    Pengeluaran
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="setoran-tab" data-bs-toggle="tab"
                        data-bs-target="#setoran" type="button" role="tab">
                    Setoran
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- TAB PEMBAYARAN --}}
            <div class="tab-pane fade show active" id="pembayaran" role="tabpanel">
                <div class="card">
                    <div class="card-body table-responsive">
                        @if($pembayaran->isEmpty())
                            <div class="text-muted small">Tidak ada pembayaran di periode ini.</div>
                        @else
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No. Pembayaran</th>
                                        <th>Pelanggan</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pembayaran as $p)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($p->tanggal_bayar)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $p->no_pembayaran }}</td>
                                            <td>{{ $p->nama_pelanggan ?? '-' }}</td>
                                            <td class="text-end">
                                                Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end">
                                            Rp {{ number_format($pembayaran->sum('nominal'), 0, ',', '.') }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TAB KOMISI --}}
            <div class="tab-pane fade" id="komisi" role="tabpanel">
                <div class="card">
                    <div class="card-body table-responsive">
                        @if($komisi->isEmpty())
                            <div class="text-muted small">Tidak ada komisi di periode ini.</div>
                        @else
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal Bayar</th>
                                        <th>No. Pembayaran</th>
                                        <th>Pelanggan</th>
                                        <th class="text-end">Nominal Komisi</th>
                                        <th class="text-end">Jumlah Komisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($komisi as $k)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($k->tanggal_bayar)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $k->no_pembayaran }}</td>
                                            <td>{{ $k->nama_pelanggan ?? '-' }}</td>
                                            <td class="text-end">
                                                Rp {{ number_format($k->nominal_komisi, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end">
                                                {{ number_format($k->jumlah_komisi, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end">
                                            Rp {{ number_format($komisi->sum('nominal_komisi'), 0, ',', '.') }}
                                        </th>
                                        <th class="text-end">
                                            {{ number_format($komisi->sum('jumlah_komisi'), 0, ',', '.') }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TAB PENGELUARAN --}}
            <div class="tab-pane fade" id="pengeluaran" role="tabpanel">
                <div class="card">
                    <div class="card-body table-responsive">
                        @if($pengeluaran->isEmpty())
                            <div class="text-muted small">Tidak ada pengeluaran approved di periode ini.</div>
                        @else
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal Approve</th>
                                        <th>Nama Pengeluaran</th>
                                        <th>Admin</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pengeluaran as $pg)
                                        <tr>
                                            <td>
                                                {{ $pg->tanggal_approve
                                                    ? \Carbon\Carbon::parse($pg->tanggal_approve)->format('d/m/Y H:i')
                                                    : '-' }}
                                            </td>
                                            <td>{{ $pg->nama_pengeluaran }}</td>
                                            <td>{{ $pg->nama_admin ?? '-' }}</td>
                                            <td class="text-end">
                                                Rp {{ number_format($pg->nominal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end">
                                            Rp {{ number_format($pengeluaran->sum('nominal'), 0, ',', '.') }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            {{-- TAB SETORAN --}}
            <div class="tab-pane fade" id="setoran" role="tabpanel">
                <div class="card">
                    <div class="card-body table-responsive">
                        @if($setoran->isEmpty())
                            <div class="text-muted small">Tidak ada setoran di periode ini.</div>
                        @else
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal Setoran</th>
                                        <th>Admin Penerima</th>
                                        <th>Catatan</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($setoran as $st)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($st->tanggal_setoran)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $st->nama_admin }}</td>
                                            <td>{{ $st->catatan ?? '-' }}</td>
                                            <td class="text-end">
                                                Rp {{ number_format($st->nominal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end">
                                            Rp {{ number_format($setoran->sum('nominal'), 0, ',', '.') }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    @endif

</div>
@endsection -->
