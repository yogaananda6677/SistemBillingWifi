@extends('layouts.master')

@section('content')
<div class="container-fluid p-4">

    <h4 class="fw-bold mb-4">Edit Paket</h4>

    <div class="card shadow-sm border-0 p-4" style="max-width: 600px;">
        <form action="{{ route('paket-layanan.update', $paket->id_paket) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nama Paket --}}
            <div class="mb-3">
                <label class="fw-semibold">Nama Paket</label>
                <input type="text" name="nama_paket"
                       class="form-control border-bottom border-2 border-secondary pb-2"
                       placeholder="Nama Paket"
                       value="{{ old('nama_paket', $paket->nama_paket) }}"
                       required>
                @error('nama_paket')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Kecepatan --}}
            <div class="mb-3">
                <label class="fw-semibold">Kecepatan</label>
                <input type="text" name="kecepatan"
                       class="form-control border-bottom border-2 border-secondary pb-2"
                       placeholder="100 Mbps"
                       value="{{ old('kecepatan', $paket->kecepatan) }}"
                       required>
                @error('kecepatan')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Harga --}}
            <div class="mb-3">
                <label class="fw-semibold">Harga (Rp)</label>
                <input type="number" name="harga_dasar"
                       class="form-control border-bottom border-2 border-secondary pb-2"
                       placeholder="100000"
                       value="{{ old('harga_dasar', $paket->harga_dasar) }}"
                       required min="0">
                @error('harga_dasar')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- PPN --}}
            <div class="mb-3">
                <label class="fw-semibold">PPN (%)</label>
                <input type="number"
                       name="ppn_nominal"
                       class="form-control border-bottom border-2 border-secondary pb-2"
                       value="{{ $ppn->presentase_ppn * 100 }}"
                       readonly>
            </div>

            {{-- Buttons --}}
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('paket-layanan.index') }}"
                   class="btn btn-light px-4" style="border-radius:30px; border:1px solid #ddd;">
                    Batal
                </a>
                <button type="submit" class="btn btn-primary px-4" style="border-radius:30px;">
                    Update
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
