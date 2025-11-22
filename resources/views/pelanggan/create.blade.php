@extends('layouts.master')

@section('content')

<div class="container-fluid p-4">

    <h5 class="fw-bold mb-3 text-secondary">
        Tambah Pelanggan
    </h5>

    <div class="card shadow-sm border-0 p-4" style="border-radius: 14px;">
        <form>

            <!-- NAMA -->
            <div class="mb-3">
                <label class="fw-semibold">Nama</label>
                <input type="text" class="form-control border-warning" placeholder="Kediri">
            </div>

            <!-- TELEPON -->
            <div class="mb-3">
                <label class="fw-semibold">No. Telepon</label>
                <input type="text" class="form-control border-warning" placeholder="123456789012">
            </div>

            <!-- NIK -->
            <div class="mb-3">
                <label class="fw-semibold">NIK</label>
                <input type="text" class="form-control border-warning" placeholder="1234567890123456">
            </div>

            <!-- ALAMAT -->
            <div class="mb-3">
                <label class="fw-semibold">Alamat</label>
                <input type="text" class="form-control border-warning" placeholder="Kediri">
            </div>

            <!-- IP ADDRESS -->
            <div class="mb-3">
                <label class="fw-semibold">IP Address</label>
                <input type="text" class="form-control border-warning" placeholder="192.168.192.100">
            </div>

            <!-- TANGGAL REGISTRASI -->
            <div class="mb-3">
                <label class="fw-semibold">Tanggal Registrasi</label>
                <input type="date" class="form-control border-warning">
            </div>

            <!-- PAKET LAYANAN -->
            <div class="mb-3">
                <label class="fw-semibold">Paket Layanan</label>
                <select class="form-select border-warning">
                    <option>Pilih Paket Layanan</option>
                    <option>Nalendra Home 3 - 15 Mbps</option>
                </select>
            </div>

            <!-- KECEPATAN -->
            <div class="mb-3">
                <label class="fw-semibold">Kecepatan</label>
                <input type="text" class="form-control border-warning" placeholder="5 Mbps">
            </div>

            <!-- HARGA DASAR -->
            <div class="mb-3">
                <label class="fw-semibold">Harga Dasar</label>
                <input type="text" class="form-control border-warning" placeholder="100.000">
            </div>

            <!-- PPN -->
            <div class="mb-3">
                <label class="fw-semibold">PPN</label>
                <input type="text" class="form-control border-warning" placeholder="11.000">
            </div>

            <!-- TOTAL -->
            <div class="mb-3">
                <label class="fw-semibold">Harga Total</label>
                <input type="text" class="form-control border-warning" placeholder="111.000">
            </div>

            <!-- AREA -->
            <div class="mb-3">
                <label class="fw-semibold">Area</label>
                <select class="form-select border-warning">
                    <option>Pilih Area</option>
                </select>
            </div>

            <!-- SALES -->
            <div class="mb-3">
                <label class="fw-semibold">Sales</label>
                <select class="form-select border-warning">
                    <option>Pilih Sales</option>
                </select>
            </div>

            <!-- BUTTONS -->
            <div class="d-flex justify-content-end mt-4 gap-3">
                <a href="/pelanggan" class="btn btn-light px-4"
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
