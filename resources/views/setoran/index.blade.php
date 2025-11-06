@extends('layouts.master')
@section('title', 'Sales - Setoran per Wilayah')

@section('content')
@php
    use Carbon\Carbon;
    $selectedMonth = $selectedMonth ?? now()->month;
    $selectedYear  = $selectedYear ?? now()->year;
@endphp

<style>
    .card-soft {
        border-radius: 18px;
        box-shadow: 0 6px 20px rgba(0,0,0,.06);
        border: none;
    }
    .btn-nalen {
        background: #FFC400;
        border: none;
        border-radius: 999px;
        font-weight: 600;
        padding-inline: 18px;
    }
    .btn-nalen:hover {
        background: #ffb000;
    }
</style>

<div class="container-fluid py-3">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER BULAN / TAHUN --}}
    <div class="mb-3">
        <form method="GET" action="{{ route('admin.setoran.index') }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <select name="bulan" class="form-select form-select-sm">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (int)$selectedMonth === $m ? 'selected' : '' }}>
                            {{ Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="tahun" class="form-select form-select-sm">
                    @foreach (range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ (int)$selectedYear === $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-nalen btn-sm">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="card card-soft">
        <div class="card-body">

            {{-- Pencarian --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="flex-grow-1 me-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0"
                               placeholder="Cari sales / wilayah..."
                               onkeyup="filterRows(this.value)">
                    </div>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table table-sm align-middle" id="tableSetoran">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60px">No</th>
                            <th>Sales</th>
                            <th>Wilayah</th>
                            <th class="text-end">Target Setor (bulan ini)</th>
                            <th class="text-end">Setoran (bulan ini)</th>
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
                                <td>{{ sprintf('%03d', $i+1) }}</td>
                                <td>{{ $r->nama_sales }}</td>
                                <td>{{ $r->nama_area }}</td>

                                <td class="text-end">
                                    Rp {{ number_format($r->target_setor, 0, ',', '.') }}
                                </td>

                                <td class="text-end text-success">
                                    Rp {{ number_format($r->total_setoran, 0, ',', '.') }}
                                </td>

                                <td class="text-end
                                    @if($jumlah == 0)
                                        text-muted
                                    @elseif($isKelebihan)
                                        text-success
                                    @else
                                        text-danger
                                    @endif
                                ">
                                    @if($jumlah == 0)
                                        Pas: Rp 0
                                    @elseif($isKelebihan)
                                        Kelebihan: Rp {{ number_format($jumlah, 0, ',', '.') }}
                                    @else
                                        Sisa: Rp {{ number_format($jumlah, 0, ',', '.') }}
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('admin.setoran.riwayat', [
                                            'id_sales' => $r->id_sales,
                                            'id_area'  => $r->id_area,
                                            'tahun'    => $selectedYear,
                                            'bulan'    => $selectedMonth,
                                        ]) }}"
                                       class="btn btn-nalen btn-sm">
                                        Riwayat & Setor
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">
                                    Belum ada relasi sales-wilayah.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
    function filterRows(keyword) {
        keyword = keyword.toLowerCase();
        document.querySelectorAll('#tableSetoran tbody tr').forEach(function (row) {
            const sales  = row.cells[1].innerText.toLowerCase();
            const area   = row.cells[2].innerText.toLowerCase();
            row.style.display = (sales.includes(keyword) || area.includes(keyword)) ? '' : 'none';
        });
    }
</script>
@endsection
