@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">
    <h5 class="fw-bold mb-4 text-secondary">Detail Pelanggan</h5>

    <div class="card shadow-sm border-0 p-4" style="border-radius: 14px;">
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Nama</div>
            <div class="col-md-8">{{ $pelanggan->nama }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">NIK</div>
            <div class="col-md-8">{{ $pelanggan->nik }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Alamat</div>
            <div class="col-md-8">{{ $pelanggan->alamat }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">No. HP</div>
            <div class="col-md-8">{{ $pelanggan->nomor_hp }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">IP Address</div>
            <div class="col-md-8">{{ $pelanggan->ip_address }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Tanggal Registrasi</div>
            <div class="col-md-8">{{ $pelanggan->tanggal_registrasi->format('d-m-Y') }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Status</div>
            <div class="col-md-8">{{ ucfirst($pelanggan->status_pelanggan) }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Area</div>
            <div class="col-md-8">{{ $pelanggan->area->nama_area ?? '-' }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Sales</div>
            <div class="col-md-8">{{ $pelanggan->sales->user->name ?? '-' }}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4 fw-semibold">Paket Terakhir</div>
            <div class="col-md-8">
                {{ $pelanggan->langganan->last()->paket->nama_paket ?? '-' }}
                - {{ $pelanggan->langganan->last()->paket->kecepatan ?? '-' }} Mbps
            </div>
        </div>

        <div class="mt-4 text-end">
            <a href="{{ route('pelanggan.index') }}" class="btn btn-light" style="border-radius: 30px; border:1px solid #ddd;">
                Kembali
            </a>
        </div>
    </div>
</div>
@endsection
