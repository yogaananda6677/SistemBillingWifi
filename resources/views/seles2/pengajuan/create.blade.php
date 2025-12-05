@extends('seles2.layout.master')

@section('content')
<div class="menu-section">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <h6 class="section-title">Tambah Pengajuan Pengeluaran</h6>
    </div>
    
    <div class="p-3">

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('sales.pengajuan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Nama Sales (hanya tampil, tidak dikirim, id_sales sudah di-handle di controller) --}}
            <div class="mb-3">
                <label class="form-label">Nama Sales</label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
            </div>

            {{-- Pengeluaran (nama_pengeluaran) --}}
            <div class="mb-3">
                <label class="form-label">Pengeluaran</label>
                <input type="text"
                       name="nama_pengeluaran"
                       class="form-control"
                       placeholder="Misal: Bensin penagihan"
                       value="{{ old('nama_pengeluaran') }}"
                       required>
            </div>
            
            {{-- Tanggal (tanggal_pengajuan) --}}
            <div class="mb-2 small text-muted">
                Tanggal pengajuan akan otomatis diisi: {{ now()->translatedFormat('d F Y, H:i') }}
            </div>

            
            {{-- Nominal --}}
            <div class="mb-3">
                <label class="form-label">Nominal</label>
                <input type="number"
                       name="nominal"
                       class="form-control"
                       placeholder="Masukkan nominal"
                       value="{{ old('nominal') }}"
                       min="1"
                       required>
            </div>

            {{-- Catatan opsional --}}
            <div class="mb-3">
                <label class="form-label">Catatan (opsional)</label>
                <textarea name="catatan" class="form-control" rows="3"
                          placeholder="Deskripsi pengeluaran...">{{ old('catatan') }}</textarea>
            </div>
            
            {{-- Bukti (bukti_file) --}}
            <div class="mb-3">
                <label class="form-label">Bukti (opsional)</label>
                <input type="file"
                       name="bukti_file"
                       class="form-control"
                       accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-text">
                    Unggah foto struk atau bukti lainnya (maks 2MB).
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill py-2">
                    <i class="bi bi-send me-2"></i>Ajukan Pengeluaran
                </button>
                <a href="{{ route('sales.pengajuan.index') }}" class="btn btn-outline-secondary rounded-pill py-2">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
