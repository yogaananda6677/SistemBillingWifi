@extends('layouts.master')

@section('content')

<style>
    .page-title {
        font-size: 22px;
        font-weight: 700;
        color: #222;
    }

    .search-box input {
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 8px 14px;
        font-size: 14px;
    }

    .filter-select {
        border-radius: 10px;
        padding: 8px;
        font-size: 14px;
        border: 1px solid #ddd;
    }

    .table-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }

    table thead th {
        background: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        padding: 10px;
    }

    table tbody td {
        font-size: 13px;
        padding: 10px;
    }

    table tbody tr:hover {
        background: #f4f4f4;
    }
</style>

<div class="container-fluid p-4">

    {{-- TITLE --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="page-title">Pelanggan</h4>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="d-flex gap-3 mb-4">
        <div class="search-box flex-grow-1">
            <input type="text" class="form-control" placeholder="Cari pelanggan...">
        </div>

        <a href="{{ route('pelanggan.create') }}" class="btn btn-primary ">
            Tambah Data
        </a>

        <select class="filter-select">
            <option selected>Filter Wilayah</option>
            <option>Utara</option>
            <option>Timur</option>
            <option>Selatan</option>
            <option>Barat</option>
        </select>
    </div>

    {{-- TABLE --}}
    <div class="table-card mt-2">

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Wilayah</th>
                    <th>Paket</th>
                    <th>Tagihan</th>
                    <th>Koneksi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            @foreach ($pelanggan ?? [] as $i => $p)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $p->nama }}</td>
                    <td>{{ $p->area->nama_area ?? '-' }}</td>
                    <td>
                        @foreach($p->langganan as $l)
                            {{ $l->paket->nama_paket ?? '-' }} ({{ $l->paket->kecepatan ?? '-' }} Mbps)<br>
                        @endforeach
                    </td>
                    <td>Rp {{ number_format($p->tagihan ?? 0,0,',','.') }}</td>
                    <td>{{ $p->ip_address }}</td>
                    <td>
                        @if ($p->status_pelanggan == 'aktif')
                            <span class="badge bg-success">Aktif</span>
                        @else
                            <span class="badge bg-danger">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('pelanggan.edit', $p->id_pelanggan) }}" class="btn btn-sm btn-primary">Edit</a>
                        <button  class="btn btn-sm btn-danger btn-delete" data-url="{{ route('pelanggan.destroy', $p->id_pelanggan) }}">
                            Hapus
                        </button>

                        <a href="{{ route('pelanggan.show', $p->id_pelanggan) }}" class="btn btn-sm btn-info">Detail</a>
                    </td>
                </tr>
            @endforeach

            </tbody>

        </table>

    </div>

</div>

@endsection

