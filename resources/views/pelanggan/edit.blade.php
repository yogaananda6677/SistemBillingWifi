@extends('layouts.master')

@section('content')

<div class="container-fluid p-4">

    <h4 class="fw-bold mb-4 text-secondary">
        Edit Pelanggan
    </h4>

    <div class="card shadow-sm border-0 p-4" style="border-radius: 14px;">
        <form action="{{ route('pelanggan.update', $pelanggan->id_pelanggan) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                <!-- NAMA -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama</label>
                    <input type="text" name="nama" class="form-control border-warning"
                           value="{{ old('nama', $pelanggan->nama) }}" placeholder="Nama Pelanggan" required>
                    @error('nama')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- TELEPON -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">No. Telepon</label>
                    <input type="text" name="nomor_hp" class="form-control border-warning"
                           value="{{ old('nomor_hp', $pelanggan->nomor_hp) }}" placeholder="123456789012" required>
                    @error('nomor_hp')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- NIK -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">NIK</label>
                    <input type="text" name="nik" class="form-control border-warning"
                           value="{{ old('nik', $pelanggan->nik) }}" placeholder="1234567890123456" required>
                    @error('nik')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- ALAMAT -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Alamat</label>
                    <input type="text" name="alamat" class="form-control border-warning"
                           value="{{ old('alamat', $pelanggan->alamat) }}" placeholder="Alamat Pelanggan" required>
                    @error('alamat')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- IP ADDRESS -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">IP Address</label>
                    <input type="text" name="ip_address" class="form-control border-warning"
                           value="{{ old('ip_address', $pelanggan->ip_address) }}" placeholder="192.168.192.100" required>
                    @error('ip_address')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- TANGGAL REGISTRASI -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tanggal Registrasi</label>
                    <input type="date" name="tanggal_registrasi" class="form-control border-warning"
                           value="{{ old('tanggal_registrasi', optional($pelanggan->tanggal_registrasi)->format('Y-m-d')) }}" required>
                    @error('tanggal_registrasi')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <!-- STATUS PELANGGAN -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status Pelanggan</label>
                    <select name="status_pelanggan" class="form-select border-warning" required>
                        @foreach (['baru', 'aktif', 'berhenti' , 'isolir'] as $status)
                            <option value="{{ $status }}" {{ old('status_pelanggan', $pelanggan->status_pelanggan) == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- PAKET LAYANAN -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Paket Layanan</label>
                    <select name="id_paket" class="form-select border-warning" required>
                        @foreach ($dataPaket ?? [] as $paket)
                            <option value="{{ $paket->id_paket }}"
                                {{ old('id_paket', $pelanggan->langganan->first()->id_paket ?? null) == $paket->id_paket ? 'selected' : '' }}>
                                {{ $paket->nama_paket }} - {{ $paket->kecepatan }} Mbps
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- AREA -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Area</label>
                    <select name="id_area" class="form-select border-warning" required>
                        @foreach ($dataArea ?? [] as $area)
                            <option value="{{ $area->id_area }}"
                                {{ old('id_area', $pelanggan->id_area) == $area->id_area ? 'selected' : '' }}>
                                {{ ucwords($area->nama_area) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- SALES -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Sales</label>
                    <select name="id_sales" class="form-select border-warning" required>
                        @foreach ($dataSales ?? [] as $sales)
                            <option value="{{ $sales->id_sales }}"
                                {{ old('id_sales', $pelanggan->id_sales) == $sales->id_sales ? 'selected' : '' }}>
                                {{ ucwords($sales->user->name ?? '-') }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <!-- BUTTONS -->
            <div class="d-flex justify-content-end mt-4 gap-3">
                <a href="{{ route('pelanggan.index') }}" class="btn btn-light px-4"
                   style="border-radius: 30px; border:1px solid #ddd;">
                   Batal
                </a>

                <button type="submit" class="btn px-4 text-white"
                        style="background:#f2be00; border-radius:30px;">
                    Simpan
                </button>
            </div>

        </form>
    </div>

</div>

@endsection
