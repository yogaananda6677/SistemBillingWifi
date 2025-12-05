@extends('layouts.master') {{-- sesuaikan dengan layout admin-mu --}}

@section('title', 'Pembukuan Sales')

@section('content')
@php
    use Carbon\Carbon;
    /** @var \Illuminate\Support\Collection $rekap */
    $rekap = $rekap ?? collect();
    $selectedMonth = $selectedMonth ?? now()->month;
    $selectedYear  = $selectedYear ?? now()->year;
@endphp

<div class="container-fluid py-4">

    <h4 class="mb-3">Pembukuan Sales</h4>

    {{-- FILTER BULAN & TAHUN --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('pembukuan.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Bulan</label>
                    <select name="bulan" class="form-select form-select-sm">
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}"
                                {{ (int)$selectedMonth === $m ? 'selected' : '' }}>
                                {{ Carbon::create()->month($m)->translatedFormat('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        @foreach(range(now()->year - 3, now()->year + 1) as $y)
                            <option value="{{ $y }}"
                                {{ (int)$selectedYear === $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary btn-sm w-100">
                        Tampilkan
                    </button>
                </div>
                <div class="col-md-4 text-md-end small text-muted">
                    Periode:
                    <strong>{{ Carbon::create($selectedYear, $selectedMonth, 1)->translatedFormat('F Y') }}</strong>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL REKAP --}}
    <div class="card">
        <div class="card-body table-responsive">

            @if($rekap->isEmpty())
                <div class="alert alert-warning mb-0">
                    Tidak ada data pembukuan untuk periode ini.
                </div>
            @else
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Sales</th>
                            <th class="text-end">Pendapatan</th>
                            <th class="text-end">Komisi</th>
                            <th class="text-end">Pengeluaran</th>
                            <th class="text-end">Harus Disetor</th>
                            <th class="text-end">Setoran</th>
                            <th class="text-end">Selisih</th>
                            <th class="text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $row)
                            <tr>
                                <td>{{ $row->nama_sales }}</td>
                                <td class="text-end">
                                    Rp {{ number_format($row->total_pendapatan, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-danger">
                                    Rp {{ number_format($row->total_komisi, 0, ',', '.') }}
                                </td>
                                <td class="text-end text-danger">
                                    Rp {{ number_format($row->total_pengeluaran, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold">
                                    Rp {{ number_format($row->harus_disetorkan, 0, ',', '.') }}
                                </td>
                                <td class="text-end fw-semibold text-success">
                                    Rp {{ number_format($row->total_setoran, 0, ',', '.') }}
                                </td>
                                <td class="text-end">
                                    @php
                                        $selisih = $row->selisih_setoran;
                                    @endphp
                                    <span class="{{ $selisih < 0 ? 'text-danger' : ($selisih > 0 ? 'text-success' : '') }}">
                                        Rp {{ number_format($selisih, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('pembukuan.show', [
                                                'sales' => $row->id_sales,
                                                'tahun' => $selectedYear,
                                                'bulan' => $selectedMonth,
                                            ]) }}"
                                       class="btn btn-outline-primary btn-xs btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        @php
                            $totalPendapatan   = $rekap->sum('total_pendapatan');
                            $totalKomisi       = $rekap->sum('total_komisi');
                            $totalPengeluaran  = $rekap->sum('total_pengeluaran');
                            $totalHarusSetor   = $rekap->sum('harus_disetorkan');
                            $totalSetoran      = $rekap->sum('total_setoran');
                            $totalSelisih      = $rekap->sum('selisih_setoran');
                        @endphp
                        <tr>
                            <th>Total</th>
                            <th class="text-end">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
                            <th class="text-end text-danger">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</th>
                            <th class="text-end text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</th>
                            <th class="text-end fw-semibold">Rp {{ number_format($totalHarusSetor, 0, ',', '.') }}</th>
                            <th class="text-end fw-semibold text-success">Rp {{ number_format($totalSetoran, 0, ',', '.') }}</th>
                            <th class="text-end">
                                <span class="{{ $totalSelisih < 0 ? 'text-danger' : ($totalSelisih > 0 ? 'text-success' : '') }}">
                                    Rp {{ number_format($totalSelisih, 0, ',', '.') }}
                                </span>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            @endif

        </div>
    </div>

</div>
@endsection
