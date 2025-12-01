@extends('seles2.layout.master')

@section('content')
<!-- Quick Stats -->
<div class="quick-stats">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-value">24</div>
        <div class="stat-label">Total Pelanggan</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="bi bi-check-circle"></i>
        </div>
        <div class="stat-value">18</div>
        <div class="stat-label">Sudah Bayar</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="bi bi-clock"></i>
        </div>
        <div class="stat-value">6</div>
        <div class="stat-label">Belum Bayar</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="bi bi-cash-coin"></i>
        </div>
        <div class="stat-value">Rp 2,4JT</div>
        <div class="stat-label">Pendapatan</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="menu-grid">
    <a href="{{ route('seles2.pelanggan.create') }}" class="menu-item">
        <div class="menu-icon success">
            <i class="bi bi-person-plus"></i>
        </div>
        <div class="menu-label">Tambah Pelanggan</div>
    </a>
    <a href="{{ route('seles2.pelanggan.index') }}" class="menu-item">
        <div class="menu-icon primary">
            <i class="bi bi-people"></i>
        </div>
        <div class="menu-label">Data Pelanggan</div>
    </a>
    <a href="{{ route('seles2.pembukuan.pengajuan.create') }}" class="menu-item">
        <div class="menu-icon warning">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="menu-label">Ajukan Pengeluaran</div>
    </a>
    <a href="{{ route('seles2.pembukuan.index') }}" class="menu-item">
        <div class="menu-icon secondary">
            <i class="bi bi-journal-text"></i>
        </div>
        <div class="menu-label">Pembukuan</div>
    </a>
    <a href="{{ route('seles2.setoran.index') }}" class="menu-item">
        <div class="menu-icon info">
            <i class="bi bi-cash-coin"></i>
        </div>
        <div class="menu-label">Setor Dana</div>
    </a>
    <a href="{{ route('seles2.profile') }}" class="menu-item">
        <div class="menu-icon">
            <i class="bi bi-gear"></i>
        </div>
        <div class="menu-label">Pengaturan</div>
    </a>
</div>
@endsection