@extends('sales.layouts.sales-master')

@section('content')
<!-- Summary Cards -->
<div class="summary-cards">
    <div class="summary-card income">
        <i class="bi bi-arrow-down-circle"></i>
        <div class="summary-info">
            <div class="summary-label">Pendapatan</div>
            <div class="summary-value">Rp 2.400.000</div>
        </div>
    </div>
    <div class="summary-card fee">
        <i class="bi bi-percent"></i>
        <div class="summary-info">
            <div class="summary-label">Fee Sales</div>
            <div class="summary-value">Rp 240.000</div>
        </div>
    </div>
    <div class="summary-card expense">
        <i class="bi bi-arrow-up-circle"></i>
        <div class="summary-info">
            <div class="summary-label">Pengeluaran</div>
            <div class="summary-value">Rp 150.000</div>
        </div>
    </div>
</div>

<!-- Menu Pembukuan -->
<div class="menu-section">
    <a href="{{ route('sales.pengajuan.index') }}" class="menu-list-item">
        <div class="item-icon warning">
            <i class="bi bi-wallet2"></i>
        </div>
        <div class="item-content">
            <div class="item-label">Pengajuan Pengeluaran</div>
            <div class="item-desc">Ajukan pengeluaran operasional</div>
        </div>
        <span class="badge-new">Baru</span>
        <i class="bi bi-chevron-right item-arrow"></i>
    </a>
    
    <a href="{{ route('sales.pembukuan.detail') }}" class="menu-list-item">
        <div class="item-icon primary">
            <i class="bi bi-receipt"></i>
        </