@extends('layouts.master')

@section('content')

<div class="container-fluid p-4">

    <h5 class="fw-bold mb-3 text-secondary">
        Tambah Pelanggan
    </h5>

    <div class="card shadow-sm border-0 p-4" style="border-radius: 14px;">
        <form action="{{ route('pelanggan.store') }}" method="POST">
            @csrf

            <!-- NAMA -->
            <div class="mb-3">
                <label class="fw-semibold">Nama</label>
                <input type="text" name="nama" class="form-control border-warning" placeholder="Nama Pelanggan" required>
            </div>

            <!-- TELEPON -->
            <div class="mb-3">
                <label class="fw-semibold">No. Telepon</label>
                <input type="text" name="nomor_hp" class="form-control border-warning" placeholder="123456789012" required>
            </div>

            <!-- NIK -->
            <div class="mb-3">
                <label class="fw-semibold">NIK</label>
                <input type="text" name="nik" class="form-control border-warning" placeholder="1234567890123456" required>
            </div>

            <!-- ALAMAT -->
            <div class="mb-3">
                <label class="fw-semibold">Alamat</label>
                <input type="text" name="alamat" class="form-control border-warning" placeholder="Alamat Pelanggan" required>
            </div>

            <!-- IP ADDRESS -->
            <div class="mb-3">
                <label class="fw-semibold">IP Address</label>
                <input type="text" name="ip_address" class="form-control border-warning" placeholder="192.168.192.100" required>
            </div>

            <!-- TANGGAL REGISTRASI -->
            {{-- <div class="mb-3">
                <label class="fw-semibold">Tanggal Registrasi</label>
                <input type="date" name="tanggal_registrasi" class="form-control border-warning" required>
            </div> --}}


            <div class="md-6 mb-3">
                <label class="form-label fw-semibold">Tanggal Registrasi</label>
                <input type="date" name="tanggal_registrasi" class="form-control border-warning" required>
                @error('tanggal_registrasi')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <!-- STATUS PELANGGAN -->
            <div class="mb-3">
                <label class="fw-semibold">Status Pelanggan</label>
                <select name="status_pelanggan" class="form-select border-warning" required>
                    <option value="" disabled selected>Pilih Status Pelanggan</option>
                    @foreach (['baru', 'aktif', 'berhenti' , 'isolir'] as $status)
                        <option value="{{ $status }}" {{ old('status_pelanggan') == $status ? 'selected' : '' }}>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <!-- PAKET LAYANAN -->
            <div class="mb-3">
                <label class="fw-semibold">Paket Layanan</label>
                <select name="id_paket" class="form-select border-warning" required>
                    <option value="" disabled selected>Pilih Paket Layanan</option>
                    @foreach ($dataPaket ?? [] as $paket)
                        <option value="{{ $paket->id_paket }}">
                            {{ $paket->nama_paket }} - {{ $paket->kecepatan }} Mbps
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- AREA -->
            <div class="mb-3">
                <label class="fw-semibold">Area</label>
                <select name="id_area" class="form-select border-warning" required>
                    <option value="" disabled selected>Pilih Area</option>
                    @foreach ($dataArea ?? [] as $area)
                        <option value="{{ $area->id_area }}">
                            {{ ucwords($area->nama_area) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- SALES -->
            <div class="mb-3">
                <label class="fw-semibold">Sales</label>
                <select name="id_sales" class="form-select border-warning" required>
                    <option value="" disabled selected>Pilih Sales</option>
                    @foreach ($dataSales ?? [] as $sales)
                        <option value="{{ $sales->id_sales }}">
                            {{ ucwords($sales->user->name ?? '-') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- BUTTONS -->
            <div class="d-flex justify-content-end mt-4 gap-3">
                <a href="{{ route('pelanggan.index') }}" class="btn btn-light px-4"
                   style="border-radius: 30px; border:1px solid #ddd;">
                   Batal
                </a>

                <button type="submit" class="btn px-4"
                        style="background:#f2be00; border-radius:30px; color:white;">
                    Tambah
                </button>
            </div>

        </form>
    </div>

</div>

@endsection
