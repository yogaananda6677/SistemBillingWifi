@extends('sales.layouts.sales-master')

@section('content')
<div class="menu-section">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-wallet2"></i>
        </div>
        <h6 class="section-title">Detail Pengajuan</h6>
    </div>
    
    <div class="p-3">
        <!-- Status -->
        <div class="card border-0 bg-warning text-white mb-3">
            <div class="card-body text-center">
                <h6 class="card-title">Status Pengajuan</h6>
                <h5 class="fw-bold">PENDING</h5>
                <small>Menunggu persetujuan admin</small>
            </div>
        </div>

        <!-- Detail Pengajuan -->
        <div class="card border-0 bg-light mb-3">
            <div class="card-body">
                <h6 class="card-title">Informasi Pengajuan</h6>
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted">Jenis</small>
                        <p class="mb-0 fw-bold">Transportasi</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Nominal</small>
                        <p class="mb-0 fw-bold text-warning">Rp 75.000</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Tanggal</small>
                        <p class="mb-0">27 Nov 2023</p>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Diajukan</small>
                        <p class="mb-0">3 hari lalu</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keterangan -->
        <div class="card border-0 bg-light">
            <div class="card-body">
                <h6 class="card-title">Keterangan</h6>
                <p class="mb-0">Transport untuk kunjungan ke pelanggan di wilayah Ngasem dan sekitarnya.</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-grid gap-2 mt-3">
            <button class="btn btn-outline-warning rounded-pill py-2">
                <i class="bi bi-pencil me-2"></i>Edit Pengajuan
            </button>
            <a href="{{ route('sales.pengajuan.index') }}" class="btn btn-outline-secondary rounded-pill py-2">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</div>
@endsection