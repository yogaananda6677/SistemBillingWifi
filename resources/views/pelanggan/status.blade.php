@extends('layouts.master')

@section('content')
<style>
    .page-title { font-size: 22px; font-weight: 700; color: #222; }
    .search-box input {
        border-radius: 10px; border: 1px solid #ddd;
        padding: 8px 14px; font-size: 14px;
    }
    .filter-select {
        border-radius: 10px; padding: 8px; font-size: 14px;
        border: 1px solid #ddd; background: white;
    }
    .table-card {
        background: #fff; border-radius: 14px;
        padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    table thead th {
        background: #f8f9fa; font-size: 13px;
        font-weight: 600; padding: 10px;
    }
    table tbody td { font-size: 13px; padding: 10px; }
    table tbody tr:hover { background: #f4f4f4; }
    .pagination-wrapper {
        margin-top: 20px; display: flex; justify-content: center;
    }
</style>

<div class="container-fluid p-4" id="page-wrapper">
    {{-- TITLE --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">Status Pelanggan</h4>
    </div>

    @php
        $statusHalaman = $status ?? 'aktif';

        if ($statusHalaman === 'isolir') {
            $colTanggalLabel = 'Tanggal Isolir';
        } elseif ($statusHalaman === 'berhenti') {
            $colTanggalLabel = 'Tanggal Berhenti';
        } elseif ($statusHalaman === 'baru') {
            $colTanggalLabel = 'Tanggal Aktif';
        } else {
            $colTanggalLabel = 'Tanggal Aktif';
        }

        $totalBaru     = $statusCounts['baru']     ?? 0;
        $totalAktif    = $statusCounts['aktif']    ?? 0;
        $totalBerhenti = $statusCounts['berhenti'] ?? 0;
        $totalIsolir   = $statusCounts['isolir']   ?? 0;
    @endphp

    {{-- TOMBOL STATUS SEBAGAI LINK --}}
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('pelanggan.status', ['status' => 'baru']) }}"
           class="btn btn-sm {{ $statusHalaman === 'baru' ? 'btn-primary' : 'btn-outline-primary' }}">
            Baru ({{ $totalBaru }})
        </a>

        <a href="{{ route('pelanggan.status', ['status' => 'aktif']) }}"
           class="btn btn-sm {{ $statusHalaman === 'aktif' ? 'btn-primary' : 'btn-outline-primary' }}">
            Aktif ({{ $totalAktif }})
        </a>

        <a href="{{ route('pelanggan.status', ['status' => 'berhenti']) }}"
           class="btn btn-sm {{ $statusHalaman === 'berhenti' ? 'btn-primary' : 'btn-outline-primary' }}">
            Berhenti ({{ $totalBerhenti }})
        </a>

        <a href="{{ route('pelanggan.status', ['status' => 'isolir']) }}"
           class="btn btn-sm {{ $statusHalaman === 'isolir' ? 'btn-primary' : 'btn-outline-primary' }}">
            Isolir ({{ $totalIsolir }})
        </a>
    </div>

    {{-- SEARCH & FILTER: FORM GET BIASA --}}
    <form method="GET" action="{{ route('pelanggan.status') }}" class="d-flex gap-3 mb-4 flex-wrap">
        {{-- tetap kirim status yang lagi dipilih --}}
        <input type="hidden" name="status" value="{{ $statusHalaman }}">

        <div class="search-box flex-grow-1" style="min-width: 250px;">
            <input type="text" name="search" class="form-control"
                   value="{{ request('search') }}"
                   placeholder="Cari pelanggan (nama, NIK, IP, HP, wilayah, paket)...">
        </div>

        <select class="filter-select" name="area" style="min-width: 150px;">
            <option value="">Semua Wilayah</option>
            @foreach($dataArea as $area)
                <option value="{{ $area->id_area }}"
                    {{ request('area') == $area->id_area ? 'selected' : '' }}>
                    {{ $area->nama_area }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary">
            Terapkan
        </button>
    </form>

    {{-- TABLE --}}
    <div class="table-card mt-2">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Area</th>
                    <th>Sales</th>
                    <th>Paket</th>
                    <th>{{ $colTanggalLabel }}</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
                </thead>

                <tbody>
                    @include('pelanggan.partials.table_rows_status', ['pelanggan' => $pelanggan])
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $pelanggan->links() }}
        </div>
    </div>
</div>
@endsection
