@extends('layouts.master')
@section('title', 'Pembukuan Global')

@section('content')
@php
    use Carbon\Carbon;
@endphp

<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="fw-bold mb-0">Pembukuan Global (Sales & Admin)</h3>
        <div class="text-muted small">
            Data per: <strong>{{ ($stat['last_updated'] ?? now())->format('d/m/Y H:i') }}</strong>
        </div>
    </div>

    {{-- FILTER + EXPORT --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
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

                <div class="col-md-3 col-6">
                    <button class="btn btn-primary btn-sm w-100">
                        Tampilkan
                    </button>
                </div>

                <div class="col-md-3 col-6 text-md-end">
                    <div class="btn-group w-100">
                        <a href="{{ route('laporan.export.excel', request()->query()) }}"
                           class="btn btn-success btn-sm">
                            Export Excel
                        </a>
                        <a href="{{ route('laporan.export.pdf', request()->query()) }}"
                           class="btn btn-outline-danger btn-sm">
                            Export PDF
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- INFO PERIODE + STAT KECIL --}}
    <div class="mb-3 text-muted d-flex justify-content-between flex-wrap gap-2">
        <div>
            Periode:
            <strong>{{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}</strong>
        </div>
        @isset($stat)
        <div class="small">
            <span class="me-3">
                Pelanggan Bayar:
                <strong>{{ number_format($stat['jumlah_pelanggan'] ?? 0,0,',','.') }}</strong>
            </span>
            <span class="me-3">
                Pembayaran:
                <strong>Rp {{ number_format($stat['jumlah_pembayaran'] ?? 0,0,',','.') }}</strong>
            </span>
            <span class="me-3">
                Pengeluaran:
                <strong>Rp {{ number_format($stat['jumlah_pengeluaran'] ?? 0,0,',','.') }}</strong>
            </span>
            <span class="me-3">
                Komisi:
                <strong>Rp {{ number_format($stat['jumlah_komisi'] ?? 0,0,',','.') }}</strong>
            </span>
            <span>
                Laba Kotor:
                <strong>Rp {{ number_format($stat['laba_kotor'] ?? 0,0,',','.') }}</strong>
            </span>
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
                            <th class="text-start">Sales / Admin</th>
                            <th>Pendapatan</th>
                            <th>Komisi</th>
                            <th>Pengeluaran</th>
                            <th>Total Bersih</th>
                            <th>Setoran</th>
                            <th>Selisih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap as $row)
                            @php
                                $key = $row->jenis . '-' . $row->user_id;
                            @endphp
                            <tr>
                                <td class="text-start">
                                    {{ $row->label }}
                                </td>

                                {{-- Pendapatan (klik -> detail pembayaran) --}}
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

                                {{-- Selisih --}}
                                <td class="{{ ($row->selisih ?? 0) < 0 ? 'text-danger' : 'text-success' }} fw-semibold">
                                    Rp {{ number_format($row->selisih ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>

                            {{-- MODAL-MODAL DETAIL UNTUK ROW INI --}}
                            @include('laporan.partials.modals', ['row' => $row, 'key' => $key])

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

</div>
@endsection
