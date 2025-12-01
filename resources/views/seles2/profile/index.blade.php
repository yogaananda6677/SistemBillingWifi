@extends('sales.layouts.sales-master')

@section('content')
<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-avatar">
        {{ substr(auth()->user()->name, 0, 1) }}
    </div>
    <h5 class="profile-name">{{ auth()->user()->name }}</h5>
    <p class="profile-role">Sales</p>
    <p class="profile-email">{{ auth()->user()->email }}</p>
</div>

<!-- Stats -->
<div class="row g-2 mb-4">
    <div class="col-4">
        <div class="stat-card text-center">
            <div class="stat-value">24</div>
            <div class="stat-label">Pelanggan</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-card text-center">
            <div class="stat-value">18</div>
            <div class="stat-label">Aktif</div>
        </div>
    </div>
    <div class="col-4">
        <div class="stat-card text-center">
            <div class="stat-value">Rp 2,4JT</div>
            <div class="stat-label">Pendapatan</div>
        </div>
    </div>
</div>

<!-- Menu Akun -->
<div class="menu-section">
    <a href="{{ route('sales.setoran.riwayat') }}" class="menu-list-item">
        <div class="item-icon success">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div class="item-content">
            <div class="item-label">Riwayat Setoran</div>
            <div class="item-desc">Lihat history setoran dana</div>
        </div>
        <i class="bi bi-chevron-right item-arrow"></i>
    </a>
    
    <a href="{{ route('sales.profile.edit') }}" class="menu-list-item">
        <div class="item-icon primary">
            <i class="bi bi-person-gear"></i>
        </div>
        <div class="item-content">
            <div class="item-label">Edit Profil</div>
            <div class="item-desc">Ubah data pribadi</div>
        </div>
        <i class="bi bi-chevron-right item-arrow"></i>
    </a>
    
    <a href="{{ route('sales.profile.password') }}" class="menu-list-item">
        <div class="item-icon warning">
            <i class="bi bi-shield-lock"></i>
        </div>
        <div class="item-content">
            <div class="item-label">Ubah Password</div>
            <div class="item-desc">Ganti kata sandi akun</div>
        </div>
        <i class="bi bi-chevron-right item-arrow"></i>
    </a>
</div>
@endsection