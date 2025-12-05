@extends('layouts.master')

@section('title', 'Pembukuan Sales')

@section('content')
<div class="container-fluid">

    {{-- HEADER + BREADCRUMB --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Pembukuan Sales</h4>
            <small class="text-muted">Ringkasan pendapatan, komisi, pengeluaran, dan setoran per sales.</small>
        </div>

        {{-- Optional: tombol export --}}
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </button>
        </div>
    </div>

    {{-- FILTER BAR --}}
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('pembukuan.index') }}" method="GET" class="row g-3 align-items-end">
                {{-- Periode Dari --}}
                <div class="col-12 col-md-3">
                    <label for="start_date" class="form-label mb-1">Periode Dari</label>
                    <input type="date" name="start_date" id="start_date"
                           class="form-control"
                           value="{{ request('start_date') }}">
                </div>

                {{-- Periode Sampai --}}
                <div class="col-12 col-md-3">
                    <label for="end_date" class="form-label mb-1">Periode Sampai</label>
                    <input type="date" name="end_date" id="end_date"
                           class="form-control"
                           value="{{ request('end_date') }}">
                </div>

                {{-- Area --}}
                <div class="col-12 col-md-3">
                    <label for="area_id" class="form-label mb-1">Area</label>
                    <select name="area_id" id="area_id" class="form-select">
                        <option value="">Semua Area</option>
                        @isset($areas)
                            @foreach ($areas as $area)
                                <option value="{{ $area->id_area }}"
                                    {{ request('area_id') == $area->id_area ? 'selected' : '' }}>
                                    {{ $area->nama_area }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                {{-- Sales --}}
                <div class="col-12 col-md-3">
                    <label for="sales_id" class="form-label mb-1">Sales</label>
                    <select name="sales_id" id="sales_id" class="form-select">
                        <option value="">Semua Sales</option>
                        @isset($salesOptions)
                            @foreach ($salesOptions as $sales)
                                <option value="{{ $sales->id_sales }}"
                                    {{ request('sales_id') == $sales->id_sales ? 'selected' : '' }}>
                                    {{ $sales->nama_sales ?? $sales->user->name ?? 'Sales #'.$sales->id_sales }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                {{-- Tombol Aksi --}}
                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('pembukuan.index') }}" class="btn btn-light border">
                        Reset
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Total Pendapatan</div>
                            <div class="fw-bold fs-5">
                                Rp {{ number_format($summary['total_pendapatan'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <span class="rounded-circle bg-light p-2">
                            <i class="bi bi-cash-coin"></i>
                        </span>
                    </div>
                    <small class="text-muted">Total pembayaran dari pelanggan di periode ini.</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Total Komisi</div>
                            <div class="fw-bold fs-5">
                                Rp {{ number_format($summary['total_komisi'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <span class="rounded-circle bg-light p-2">
                            <i class="bi bi-percent"></i>
                        </span>
                    </div>
                    <small class="text-muted">Akumulasi komisi yang menjadi hak semua sales.</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Total Pengeluaran (Approved)</div>
                            <div class="fw-bold fs-5">
                                Rp {{ number_format($summary['total_pengeluaran'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <span class="rounded-circle bg-light p-2">
                            <i class="bi bi-receipt"></i>
                        </span>
                    </div>
                    <small class="text-muted">Hanya pengajuan pengeluaran yang sudah disetujui.</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small">Total Belum Disetor</div>
                            <div class="fw-bold fs-5 text-danger">
                                Rp {{ number_format($summary['total_belum_disetor'] ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                        <span class="rounded-circle bg-light p-2">
                            <i class="bi bi-exclamation-circle"></i>
                        </span>
                    </div>
                    <small class="text-muted">Selisih antara yang harus setor dan yang sudah setor.</small>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL REKAP PER SALES --}}
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Rekap Pembukuan per Sales</h6>
                {{-- tempat kecil untuk keterangan kalau perlu --}}
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>Sales</th>
                            <th>Area</th>
                            <th class="text-end">Pendapatan Kotor</th>
                            <th class="text-end">Total Komisi</th>
                            <th class="text-end">Pengeluaran (Approved)</th>
                            <th class="text-end">Harus Setor</th>
                            <th class="text-end">Sudah Setor</th>
                            <th class="text-end">Belum Disetor</th>
                            <th class="text-center" style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rekapSales ?? [] as $row)
                            @php
                                $belum = $row->belum_disetor ?? 0;
                                $badgeClass = 'bg-success-subtle text-success';

                                if ($belum > 0 && $belum <= 200000) {
                                    $badgeClass = 'bg-warning-subtle text-warning';
                                } elseif ($belum > 200000) {
                                    $badgeClass = 'bg-danger-subtle text-danger';
                                }
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $row->nama_sales ?? $row->nama ?? 'Sales #'.$row->id_sales }}
                                    </div>
                                    <small class="text-muted">
                                        {{ $row->no_hp ?? $row->no_hp_sales ?? '' }}
                                    </small>
                                </td>
                                <td>{{ $row->nama_area ?? '-' }}</td>
                                <td class="text-end">
                                    Rp {{ number_format($row->pendapatan_kotor ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($row->total_komisi ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($row->pengeluaran_approved ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp {{ number_format($row->harus_setor ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($row->sudah_setor ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    <span class="badge {{ $badgeClass }}">
                                        Rp {{ number_format($belum, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('pembukuan.show', $row->id_sales) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    Tidak ada data pembukuan untuk filter yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINATION (kalau pakai paginator di controller) --}}
        @isset($rekapSales)
            @if(method_exists($rekapSales, 'links'))
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-end">
                        {{ $rekapSales->links() }}
                    </div>
                </div>
            @endif
        @endisset
    </div>

</div>
@endsection
