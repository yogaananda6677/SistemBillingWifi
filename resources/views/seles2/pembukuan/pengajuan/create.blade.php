@extends('sales.layouts.sales-master')

@section('content')
<div class="menu-section">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <h6 class="section-title">Ajukan Pengeluaran Baru</h6>
    </div>
    
    <div class="p-3">
        <form>
            <div class="mb-3">
                <label class="form-label">Jenis Pengeluaran</label>
                <select class="form-select">
                    <option>Pilih jenis pengeluaran</option>
                    <option>Transportasi</option>
                    <option>Konsumsi</option>
                    <option>Administrasi</option>
                    <option>Lainnya</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Nominal</label>
                <input type="number" class="form-control" placeholder="Masukkan nominal">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Tanggal Pengeluaran</label>
                <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea class="form-control" rows="3" placeholder="Deskripsi pengeluaran..."></textarea>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Bukti (Optional)</label>
                <input type="file" class="form-control">
                <div class="form-text">Unggah foto struk atau bukti lainnya</div>
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