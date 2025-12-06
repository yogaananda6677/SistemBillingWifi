@extends('layouts.master')
@section('title', 'Sales - Setoran per Wilayah')

@section('content')
@php
    use Carbon\Carbon;
    $selectedMonth = $selectedMonth ?? now()->month;
    $selectedYear  = $selectedYear ?? now()->year;
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

    /* 2. Card Styles */
    .card-admin {
        background: #fff;
        border: none;
        border-radius: var(--card-radius);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-top: 4px solid var(--theme-yellow);
        width: 100%;
    }

    /* 3. Tombol Kuning Custom */
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

    /* 4. Form Inputs */
    .form-control-admin, .form-select-admin {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 13px;
    }
    .form-control-admin:focus, .form-select-admin:focus {
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
</style>

<div class="container-fluid p-4">

    {{-- HEADER SECTION --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="page-title mb-1">
                <i class="bi bi-cash-coin text-warning me-2"></i>Setoran Sales
            </h4>
            <div class="text-muted small">Monitor setoran per wilayah dan sales</div>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-left: 5px solid #198754;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER CARD --}}
    <div class="card-admin p-3 mb-3">
        <form method="GET" action="{{ route('admin.setoran.index') }}">
            <div class="row g-2 align-items-end">
                {{-- Filter Bulan --}}
                <div class="col-6 col-md-2">
                    <span class="filter-label">Bulan</span>
                    <select name="bulan" class="form-select form-select-admin">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}" {{ (int)$selectedMonth === $m ? 'selected' : '' }}>
                                {{ Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tahun --}}
                <div class="col-6 col-md-2">
                    <span class="filter-label">Tahun</span>
                    <select name="tahun" class="form-select form-select-admin">
                        @foreach (range(now()->year - 2, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ (int)$selectedYear === $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter --}}
                <div class="col-12 col-md-2">
                    <button type="submit" class="btn btn-admin-yellow w-100">
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                </div>

                {{-- Search Box (Client Side) --}}
                <div class="col-12 col-md-6">
                    <span class="filter-label">Pencarian Cepat</span>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 8px 0 0 8px;">
                            <i class="bi bi-search text-warning" style="font-size: 13px;"></i>
                        </span>
                        <input type="text" class="form-control form-control-admin border-start-0"
                               placeholder="Cari nama sales atau wilayah..."
                               onkeyup="filterRows(this.value)"
                               style="border-radius: 0 8px 8px 0;">
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE CARD --}}
    <div class="card-admin p-0" style="overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-admin mb-0" id="tableSetoran">
                <thead>
                    <tr>
                        <th class="ps-4" style="width:60px">No</th>
                        <th>Sales</th>
                        <th>Wilayah</th>
                        <th class="text-end">Target Setor</th>
                        <th class="text-end">Total Setor</th>
                        <th class="text-end">Sisa / Kelebihan</th>
                        <th class="text-center" style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $r)
                        @php
                            $isKelebihan = $r->sisa < 0;
                            $jumlah      = abs($r->sisa);
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ sprintf('%03d', $i+1) }}</td>
                            <td class="fw-bold text-dark">{{ $r->nama_sales }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $r->nama_area }}</span></td>

                            <td class="text-end text-muted">
                                Rp {{ number_format($r->target_setor, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-bold text-success">
                                Rp {{ number_format($r->total_setoran, 0, ',', '.') }}
                            </td>

                            <td class="text-end fw-semibold
                                @if($jumlah == 0) text-muted
                                @elseif($isKelebihan) text-success
                                @else text-danger
                                @endif
                            ">
                                @if($jumlah == 0)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Lunas</span>
                                @elseif($isKelebihan)
                                    <span class="badge bg-success bg-opacity-10 text-success">+ Rp {{ number_format($jumlah, 0, ',', '.') }}</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">- Rp {{ number_format($jumlah, 0, ',', '.') }}</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.setoran.riwayat', [
                                        'id_sales' => $r->id_sales,
                                        'id_area'  => $r->id_area,
                                        'tahun'    => $selectedYear,
                                        'bulan'    => $selectedMonth,
                                    ]) }}"
                                   class="btn btn-sm btn-outline-warning text-dark border-warning"
                                   style="font-size: 11px; font-weight: 600;">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-light-gray"></i>
                                Belum ada data relasi sales & wilayah untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    function filterRows(keyword) {
        keyword = keyword.toLowerCase();
        document.querySelectorAll('#tableSetoran tbody tr').forEach(function (row) {
            // Hindari error jika baris kosong (empty state)
            if(row.cells.length < 3) return;

            const sales = row.cells[1].innerText.toLowerCase();
            const area  = row.cells[2].innerText.toLowerCase();
            row.style.display = (sales.includes(keyword) || area.includes(keyword)) ? '' : 'none';
        });
    }
</script>
@endsection