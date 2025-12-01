@extends('sales.layouts.sales-master')

@section('content')
<!-- Profile Pelanggan -->
<div class="profile-header">
    <div class="profile-avatar">
        P
    </div>
    <h5 class="profile-name">Pelanggan 1</h5>
    <p class="profile-role">Status: <span class="text-success">Aktif</span></p>
    <p class="profile-email">Kediri - Ngasem</p>
</div>

<!-- Info Pelanggan -->
<div class="menu-section">
    <div class="section-header">
        <div class="section-icon">
            <i class="bi bi-info-circle"></i>
        </div>
        <h6 class="section-title">Informasi Pelanggan</h6>
    </div>
    
    <div class="p-3">
        <div class="row g-3">
            <div class="col-6">
                <div class="stat-card text-center">
                    <div class="stat-value">12</div>
                    <div class="stat-label">Bulan Langganan</div>
                </div>
            </div>
            <div class="col-6">
                <div class="stat-card text-center">
                    <div class="stat-value text-success">Rp 120K</div>
                    <div class="stat-label">Per Bulan</div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <h6>Detail Kontak</h6>
            <p class="mb-1"><i class="bi bi-telephone me-2"></i>081234567890</p>
            <p class="mb-0"><i class="bi bi-geo-alt me-2"></i>Jl. Contoh No. 123, Kediri</p>
        </div>
    </div>
</div>

<!-- Aksi Cepat -->
<div class="menu-grid">
    <a href="#" class="menu-item">
        <div class="menu-icon primary">
            <i class="bi bi-receipt"></i>
        </div>
        <div class="menu-label">Tagihan</div>
    </a>
    <a href="#" class="menu-item">
        <div class="menu-icon success">
            <i class="bi bi-chat"></i>
        </div>
        <div class="menu-label">Chat</div>
    </a>
    <a href="#" class="menu-item">
        <div class="menu-icon warning">
            <i class="bi bi-pencil"></i>
        </div>
        <div class="menu-label">Edit</div>
    </a>
</div>
@endsection