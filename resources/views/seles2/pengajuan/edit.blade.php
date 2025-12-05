@extends('seles2.layout.master')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="pelanggan-page">

    {{-- HEADER --}}
    <div class="pelanggan-header d-flex align-items-center">
        <a href="{{ route('sales.pengajuan.index') }}" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h5 class="mb-0 fw-semibold ms-2">Edit Pengajuan</h5>
    </div>

    <div class="mt-3 px-3">
        {{-- FLASH MESSAGE --}}
        @if(session('success'))
            <div class="alert alert-success py-2 mb-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 mb-2">{{ session('error') }}</div>
        @endif

        {{-- VALIDATION ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger py-2 mb-2">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- CARD FORM --}}
        <div class="form-card bg-white p-3 rounded-3 shadow-sm">
            <form action="{{ route('sales.pengajuan.update', $pengajuan->id_pengeluaran) }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Nama Pengeluaran --}}
                <div class="mb-3">
                    <label class="form-label small mb-1">Nama Pengeluaran</label>
                    <input type="text"
                           name="nama_pengeluaran"
                           class="form-control form-control-sm @error('nama_pengeluaran') is-invalid @enderror"
                           value="{{ old('nama_pengeluaran', $pengajuan->nama_pengeluaran) }}"
                           required>
                    @error('nama_pengeluaran')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal Pengajuan --}}
                <div class="mb-3">
                    <label class="form-label small mb-1">Tanggal Pengajuan</label>
                    <input type="date"
                           name="tanggal_pengajuan"
                           class="form-control form-control-sm @error('tanggal_pengajuan') is-invalid @enderror"
                           value="{{ old('tanggal_pengajuan', \Illuminate\Support\Carbon::parse($pengajuan->tanggal_pengajuan)->format('Y-m-d')) }}"
                           required>
                    @error('tanggal_pengajuan')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Nominal --}}
                <div class="mb-3">
                    <label class="form-label small mb-1">Nominal</label>
                    <input type="number"
                           name="nominal"
                           class="form-control form-control-sm @error('nominal') is-invalid @enderror"
                           value="{{ old('nominal', $pengajuan->nominal) }}"
                           min="1"
                           required>
                    @error('nominal')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Catatan --}}
                <div class="mb-3">
                    <label class="form-label small mb-1">Catatan (opsional)</label>
                    <textarea name="catatan"
                              rows="3"
                              class="form-control form-control-sm @error('catatan') is-invalid @enderror"
                              placeholder="Tambahkan keterangan jika perlu...">{{ old('catatan', $pengajuan->catatan) }}</textarea>
                    @error('catatan')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Bukti File --}}
                <div class="mb-3">
                    <label class="form-label small mb-1">Bukti (jpg/jpeg/png/pdf, maks 2MB)</label>

                    @if($pengajuan->bukti_file)
                        <div class="mb-1 small">
                            <span class="text-muted">Bukti saat ini:</span>
                            <a href="{{ route('sales.pengajuan.bukti', $pengajuan->id_pengeluaran) }}"
                               target="_blank">
                                Lihat Bukti
                            </a>
                        </div>
                    @endif

                    <input type="file"
                           name="bukti_file"
                           class="form-control form-control-sm @error('bukti_file') is-invalid @enderror"
                           accept=".jpg,.jpeg,.png,.pdf">
                    <div class="form-text tiny">
                        Kosongkan jika tidak ingin mengubah bukti.
                    </div>
                    @error('bukti_file')
                        <div class="invalid-feedback small">{{ $message }}</div>
                    @enderror
                </div>

                {{-- BUTTONS --}}
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('sales.pengajuan.index') }}"
                       class="btn btn-light btn-sm w-50">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm w-50">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection

@push('styles')
<style>
    .pelanggan-page {
        background: #f1f3f6;
        min-height: 100vh;
        padding-bottom: 70px;
    }

    .pelanggan-header {
        background: #4f46e5;
        color: #fff;
        padding: 12px 16px;
        border-radius: 16px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        margin: -12px -12px 12px -12px;
        gap: 12px;
    }

    .back-btn {
        color: #fff;
        text-decoration: none;
        font-size: 1.2rem;
    }

    .form-card {
        border-radius: 16px;
    }

    .tiny {
        font-size: 11px;
    }
</style>
@endpush
